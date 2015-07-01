<?php

namespace SilverStripe\Cow\Model;

/**
 * Represents a version for a release
 */
class ReleaseVersion {

	/**
	 * @var int
	 */
	protected $major;

	/**
	 * @var int
	 */
	protected $minor;

	/**
	 * @var int|null
	 */
	protected $patch;

	/**
	 * @var string|null
	 */
	protected $stability;

	public function __construct($version) {
		if(!preg_match('/^(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)(\-(?<stability>(rc|alpha|beta)\d*))?$/', $version, $matches)) {
			throw new InvalidArgumentException(
				"Invalid version $version. Expect full version (3.1.13) with optional rc|alpha|beta suffix"
			);
		}
		$this->major = $matches['major'];
		$this->minor = $matches['minor'];
		$this->patch = $matches['patch'];
		if(empty($matches['stability'])) {
			$this->stability = null;
		} else {
			$this->stability = $matches['stability'];
		}
	}

	public function __toString() {
		return $this->getValue();
	}

	/**
	 * Get version string
	 *
	 * @return string
	 */
	public function getValue() {
		$value = implode('.', array($this->major, $this->minor, $this->patch));
		if($this->stability) {
			$value .= "-{$this->stability}";
		}
		return $value;
	}

	/**
	 * Get list of preferred versions for installing this release
	 *
	 * @array List of composer versions from best to worst
	 */
	public function getComposerVersions() {
		$versions = array();

		// Prefer exact version (e.g. 3.1.13-rc1)
		$versions[] = $this->getValue();

		// Fall back to patch branch (e.g. 3.1.13.x-dev, 3.1.x-dev, 3.x-dev)
		$parts = array($this->major, $this->minor, $this->patch);
		while($parts) {
			$versions[] = implode('.', $parts) . '.x-dev';
			array_pop($parts);
		}

		// If we need to fallback to dev-master we probably have done something wrong
		
		return $versions;
	}
}