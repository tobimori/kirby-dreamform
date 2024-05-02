<?php

namespace tobimori\DreamForm\Support;

use Exception;
use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

/**
 * License management & validation
 *
 * If you're here to crack the plugin, please buy a license instead.
 * I'm an independent developer and this plugin helps fund my open-source work as well.
 * https://plugins.andkindness.com/dreamform/pricing
 *
 * If you're unable to afford a license, or you encounter any issues with
 * the license validation being too strict, please let me know at support@andkindness.com.
 * I'm happy to help.
 */
final class License
{
	private const LICENSE_FILE = '.dreamform_license';
	private const BASE = "https://plugins.andkindness.com/licenses/";

	private function __construct(
		protected string|null $license = null,
		protected string|null $plugin = null,
		protected string|null $edition = null,
		protected bool $allowOfflineUse = false,
		protected string|null $purchasedOn = null,
		protected string|null $assignedUrl = null,
		protected string|null $email = null,
		protected string|null $signature = null
	) {
	}

	public function licenseData(): array
	{
		return [
			'license' => $this->license,
			'plugin' => $this->plugin,
			'edition' => $this->edition,
			'allowOfflineUse' => $this->allowOfflineUse,
			'purchasedOn' => $this->purchasedOn,
			'assignedUrl' => $this->assignedUrl,
			'email' => $this->email,
			'signature' => $this->signature
		];
	}

	private function signedData(): string
	{
		return Json::encode(array_diff_key($this->licenseData(), ['signature' => null]));
	}

	public static function licenseFile(): string
	{
		return dirname(App::instance()->root('license')) . '/' . License::LICENSE_FILE;
	}

	public static function fromDisk(): License
	{
		$licenseFile = static::licenseFile();
		if (!F::exists($licenseFile)) {
			return new License();
		}

		try {
			$licenseData = Json::read($licenseFile);
		} catch (Exception $e) {
			return new License();
		}

		return new License(...$licenseData);
	}

	public function isComplete(): bool
	{
		return $this->license !== null
			&& $this->plugin !== null
			&& $this->edition !== null
			&& $this->purchasedOn !== null
			&& $this->assignedUrl !== null
			&& $this->email !== null
			&& $this->signature !== null;
	}

	private $signatureStatus = false;
	public function isSigned(): bool
	{
		if ($this->signatureStatus) {
			return true;
		}

		if ($this->signature === null) {
			return false;
		}

		return $this->signatureStatus = openssl_verify(
			$this->signedData(),
			base64_decode($this->signature),
			openssl_pkey_get_public('file://' . dirname(__DIR__, 2) . '/public.pem'),
			'RSA-SHA256'
		) === 1;
	}

	private $remoteStatus = false;
	public function isValid(): bool
	{
		if (!$this->isSigned() || !$this->isComplete()) {
			return false;
		}

		if ($this->assignedUrl !== static::normalizeUrl(App::instance()->system()->indexUrl())) {
			return false;
		}

		if ($this->allowOfflineUse || $this->remoteStatus) {
			return true;
		}

		$licenseCache = App::instance()->cache('tobimori.dreamform.performer');
		if ($licenseCache->get("license.{$this->license}") === true) {
			return $this->remoteStatus = true;
		}

		$license = Str::lower($this->license);
		$request = Remote::post(License::BASE . "{$license}/validate", [
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
			'data' => Json::encode([
				'url' => App::instance()->system()->indexUrl(),
			])
		]);

		if ($request->code() !== 200) {
			return false;
		}

		$licenseCache->set("license.{$this->license}", true, 60 * 24);
		return $this->remoteStatus = true;
	}

	public static function normalizeUrl(string $url): string
	{
		return preg_replace(
			'/^https?:\/\/(?:www\.|staging\.|test\.|dev\.)?|\/$/',
			'',
			$url
		);
	}

	public static function downloadLicense(string $email, string $license): static
	{
		$license = Str::lower($license);
		$request = Remote::post(License::BASE . "{$license}/download", [
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
			'data' => Json::encode([
				'email' => $email,
				'url' => static::normalizeUrl(App::instance()->system()->indexUrl()),
			])
		]);

		if ($request->code() !== 200) {
			throw new \Exception('Invalid license');
		}

		$license = new License(...$request->json());
		if (!$license->isValid()) {
			throw new \Exception('Downloaded license is invalid');
		}

		Json::write(static::licenseFile(), $license->licenseData());
		return $license;
	}
}
