<?php

namespace tobimori\DreamForm\Support;

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

	public static function isHtmxRequest(): bool
	{
		return App::instance()->request()->header('Hx-Request') === 'true';
	}

	/**
	 * Returns the secret key for encrypting and decrypting values
	 */
	private static function secret(): string
	{
		$secret = DreamForm::option('secret');

		if (empty($secret)) {
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
