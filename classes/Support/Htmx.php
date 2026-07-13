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
	public const REQUEST_INSTANCE = 'dreamform:instance';
	public const REQUEST_SEQUENCE = 'dreamform:request';
	public const CACHE_TTL = 60 * 24;
	private static bool $staleRequest = false;

	private function __construct()
	{
		throw new \Error('This class cannot be instantiated');
	}

	public static function isActive(): bool
	{
		return DreamForm::option('mode', 'prg') === 'htmx';
	}

	public static function isHtmxRequest(): bool
	{
		return App::instance()->request()->header('Hx-Request') === 'true';
	}

	public static function requestInstance(): string|null
	{
		$value = App::instance()->request()->body()->get(static::REQUEST_INSTANCE);
		if (!is_string($value) || $value === '') {
			return null;
		}

		try {
			return static::decrypt($value);
		} catch (\Throwable) {
			return null;
		}
	}

	public static function requestSequence(): int|null
	{
		$value = App::instance()->request()->body()->get(static::REQUEST_SEQUENCE);
		if (!(is_int($value) || is_string($value) && ctype_digit($value))) {
			return null;
		}

		$value = (int) $value;
		return $value > 0 ? $value : null;
	}

	public static function isStaleRequest(): bool
	{
		return static::$staleRequest;
	}

	public static function rejectRequest(): void
	{
		static::$staleRequest = true;
	}

	public static function instanceCacheKey(string $instance): string
	{
		return 'request-instance:' . hash('sha256', $instance);
	}

	/**
	 * Serializes requests for one rendered form and marks stale request sequences.
	 */
	public static function synchronizeRequest(string $instance, int $sequence, Closure $callback): mixed
	{
		$hash = hash('sha256', $instance);
		$lock = fopen(sys_get_temp_dir() . '/kirby-dreamform-' . substr($hash, 0, 2) . '.lock', 'c+');
		if ($lock === false) {
			throw new \RuntimeException('[DreamForm] Could not create request lock');
		}

		try {
			if (flock($lock, LOCK_EX) === false) {
				throw new \RuntimeException('[DreamForm] Could not acquire request lock');
			}

			$cache = App::instance()->cache('tobimori.dreamform.sessionless');
			$key = static::instanceCacheKey($instance);
			$state = $cache->get($key) ?? [];
			$latest = (int) ($state['sequence'] ?? 0);
			$isCurrent = $sequence > $latest;
			static::$staleRequest = !$isCurrent;

			if ($isCurrent) {
				$state['sequence'] = $sequence;
				$cache->set($key, $state, static::CACHE_TTL);
			}

			return $callback($isCurrent);
		} finally {
			flock($lock, LOCK_UN);
			fclose($lock);
		}
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

	public const CIPHER = 'AES-128-CBC';

	/**
	 * Encrypt a string value for use in HTMX attributes
	 * Based on example code from https://www.php.net/manual/en/function.openssl-encrypt.php
	 */
	public static function encrypt(string $value): string
	{
		$ivlen = openssl_cipher_iv_length(self::CIPHER);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$encrypted = openssl_encrypt($value, self::CIPHER, static::secret(), OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $encrypted, static::secret(), true);

		return base64_encode($iv . $hmac . $encrypted);
	}

	/**
	 * Decrypt a string value from HTMX attributes
	 */
	public static function decrypt(string $value): string
	{
		$c = base64_decode($value);
		$ivlen = openssl_cipher_iv_length(static::CIPHER);
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len = 32);
		$encrypted = substr($c, $ivlen + $sha2len);

		if (empty($hmac) || !hash_equals($hmac, hash_hmac('sha256', $encrypted, static::secret(), true))) {
			throw new \Exception('Decryption failed');
		}

		return openssl_decrypt($encrypted, static::CIPHER, static::secret(), OPENSSL_RAW_DATA, $iv);
	}
}
