<?php

namespace tobimori\DreamForm\Support;

use Exception;
use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Plugin\License as KirbyLicense;
use Kirby\Plugin\LicenseStatus;
use Kirby\Plugin\Plugin;
use Kirby\Toolkit\Str;

/**
 * DreamForm License implementation for Kirby 5
 *
 * If you're here to crack the plugin, please buy a license instead.
 * I'm an independent developer and this plugin helps fund my open-source work as well.
 * https://plugins.andkindness.com/dreamform/pricing
 *
 * If you're unable to afford a license, or you encounter any issues with
 * the license validation being too strict, please let me know at support@andkindness.com.
 * I'm happy to help.
 */
final class License extends KirbyLicense
{
	private const LICENSE_FILE = '.dreamform_license';
	private const BASE = "https://plugins.andkindness.com/licenses/";

	protected string|null $license = null;
	protected string|null $pluginName = null;
	protected string|null $edition = null;
	protected bool $allowOfflineUse = false;
	protected string|null $purchasedOn = null;
	protected string|null $assignedUrl = null;
	protected string|null $email = null;
	protected string|null $signature = null;

	public function __construct(
		protected Plugin $plugin
	) {
		$this->name = 'DreamForm License';
		$this->link = 'https://plugins.andkindness.com/license-agreement';

		// Load license data from disk
		$this->loadFromDisk();
		$kirby = App::instance();

		// Determine status based on existing license validation
		if ($this->isValid()) {
			$this->status = LicenseStatus::from('active');
		} elseif ($kirby->system()->isLocal()) {
			// Local environment - show as demo
			$demo = LicenseStatus::from('demo');
			$this->status = new LicenseStatus(
				value: $demo->value(),
				icon: $demo->icon(),
				label: $demo->label(),
				theme: $demo->theme(),
				dialog: 'dreamform/activate'
			);
		} else {
			// Production without valid license
			$missing = LicenseStatus::from('missing');
			$this->status = new LicenseStatus(
				value: $missing->value(),
				icon: $missing->icon(),
				label: $missing->label(),
				theme: $missing->theme(),
				dialog: 'dreamform/activate'
			);
		}
	}

	public function licenseData(): array
	{
		return [
			'license' => $this->license,
			'plugin' => $this->pluginName,
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
		return dirname(App::instance()->root('license')) . '/' . self::LICENSE_FILE;
	}

	protected function loadFromDisk(): void
	{
		$licenseFile = static::licenseFile();
		if (!F::exists($licenseFile)) {
			return;
		}

		try {
			$licenseData = Json::read($licenseFile);
			foreach ($licenseData as $key => $value) {
				// Map 'plugin' to 'pluginName' to avoid conflict with parent property
				if ($key === 'plugin') {
					$this->pluginName = $value;
				} elseif (property_exists($this, $key) && $key !== 'plugin') {
					$this->$key = $value;
				}
			}
		} catch (Exception $e) {
			// Invalid license file
		}
	}

	public function isComplete(): bool
	{
		return $this->license !== null
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
		$request = Remote::post(self::BASE . "{$license}/validate", [
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
		$request = Remote::post(self::BASE . "{$license}/download", [
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

		$licenseData = $request->json();
		// Save to disk
		Json::write(static::licenseFile(), $licenseData);

		// Create new instance with downloaded data
		$newLicense = new static(App::instance()->plugin('tobimori/dreamform'));

		if (!$newLicense->isValid()) {
			throw new \Exception('Downloaded license is invalid');
		}

		return $newLicense;
	}

	/**
	 * Create a License instance from disk for backwards compatibility
	 */
	public static function fromDisk(): static
	{
		return new static(App::instance()->plugin('tobimori/dreamform'));
	}
}
