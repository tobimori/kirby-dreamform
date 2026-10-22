<?php

namespace tobimori\DreamForm\Support;

use Closure;
use Kirby\Cms\App;
use tobimori\DreamForm\DreamForm;

/**
 * Helper class for HTMX support
 */
final class Htmx
{
	private function __construct()
	{
		throw new \Error('This class cannot be instantiated');
	}

	public static function isActive(): bool
	{
		return DreamForm::option('mode', 'prg') === 'htmx';
	}

	/**
	 * Returns the configured HTMX major version
	 */
	public static function version(): int
	{
		$version = DreamForm::option('htmx.version', 2);

		if (!in_array($version, [2, 4, '2', '4'], true)) {
			throw new \InvalidArgumentException('[DreamForm] HTMX version must be 2 or 4');
		}

		return (int)$version;
	}

	public static function isVersion(int $version): bool
	{
		return static::version() === $version;
	}

	public static function isHtmxRequest(): bool
	{
		return App::instance()->request()->header('Hx-Request') === 'true';
	}

	/**
	 * Returns the ID of the element that triggered the request
	 */
	public static function sourceId(): string|null
	{
		$request = App::instance()->request();
		$source = $request->header('Hx-Source');

		// HTMX 4 sends "tag#id" in HX-Source
		if (is_string($source) && str_contains($source, '#')) {
			return explode('#', $source, 2)[1];
		}

		// HTMX 2 sends the element ID in HX-Trigger
		$trigger = $request->header('Hx-Trigger');
		return is_string($trigger) && $trigger !== '' ? $trigger : null;
	}

	/**
	 * Returns a lifecycle event name for the configured HTMX version
	 */
	public static function eventName(string $event): string
	{
		if (static::isVersion(2)) {
			return "htmx:{$event}";
		}

		return match ($event) {
			'afterSettle' => 'htmx:after:settle',
			'beforeSwap' => 'htmx:before:swap',
			default => throw new \InvalidArgumentException("[DreamForm] Unknown HTMX event: {$event}")
		};
	}

	/**
	 * Returns the secret key for encrypting and decrypting values
	 */
	private static function secret(): string
	{
		$secret = DreamForm::option('secret');

		if (empty($secret)) {
			$salt = App::instance()->option('content.salt');

			if ($salt instanceof Closure) {
				$salt = $salt(null);
			}

			if (is_string($salt) && empty($salt) === false) {
				$secret = hash_hkdf('sha256', $salt, 32, 'tobimori/dreamform');
			}
		}

		if (empty($secret) || is_string($secret) === false) {
			throw new \Exception('[DreamForm] Secret not set');
		}

		return $secret;
	}

	private static function key(string $purpose, int $length): string
	{
		return hash_hkdf('sha256', static::secret(), $length, "tobimori/dreamform:htmx:{$purpose}");
	}

	public const CIPHER = 'AES-128-CBC';

	/**
	 * Encrypt a string value for use in HTMX attributes
	 * Based on example code from https://www.php.net/manual/en/function.openssl-encrypt.php
	 */
	public static function encrypt(string $value): string
	{
		$ivlen = openssl_cipher_iv_length(self::CIPHER);
		$iv = random_bytes($ivlen);
		$encrypted = openssl_encrypt($value, self::CIPHER, static::key('encryption', 16), OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $iv . $encrypted, static::key('authentication', 32), true);

		return base64_encode($iv . $hmac . $encrypted);
	}

	/**
	 * Decrypt a string value from HTMX attributes
	 */
	public static function decrypt(string $value): string
	{
		$c = base64_decode($value, true);
		$ivlen = openssl_cipher_iv_length(static::CIPHER);
		$sha2len = 32;

		if ($c === false || strlen($c) <= $ivlen + $sha2len) {
			throw new \Exception('Decryption failed');
		}

		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len);
		$encrypted = substr($c, $ivlen + $sha2len);

		if (!hash_equals(hash_hmac('sha256', $iv . $encrypted, static::key('authentication', 32), true), $hmac)) {
			throw new \Exception('Decryption failed');
		}

		$decrypted = openssl_decrypt($encrypted, static::CIPHER, static::key('encryption', 16), OPENSSL_RAW_DATA, $iv);

		if ($decrypted === false) {
			throw new \Exception('Decryption failed');
		}

		return $decrypted;
	}
}
