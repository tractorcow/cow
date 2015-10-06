<?php

namespace SilverStripe\Cow\Model;

use InvalidArgumentException;

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
	
	/**
	 *
	 * @var int|null
	 */
	protected $stabilityVersion;

	public function __construct($version) {
		if(!preg_match('/^(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)(\-(?<stability>rc|alpha|beta)(?<stabilityVersion>\d+)?)?$/', $version, $matches)) {
			throw new InvalidArgumentException(
				"Invalid version $version. Expect full version (3.1.13) with optional rc|alpha|beta suffix"
			);
		}
		$this->major = $matches['major'];
		$this->minor = $matches['minor'];
		$this->patch = $matches['patch'];
		$this->stabilityVersion = null;
		if(empty($matches['stability'])) {
			$this->stability = null;
		} else {
			$this->stability = $matches['stability'];
			if(!empty($matches['stabilityVersion'])) {
				$this->stabilityVersion = $matches['stabilityVersion'];
			}
		}
	}

	public function __toString() {
		return $this->getValue();
	}
	
	public function getStability() {
		return $this->stability;
	}
	
	public function setStability($stability) {
		$this->stability = $stability;
	}
	
	public function getStabilityVersion() {
		return $this->stabilityVersion;
	}
	
	public function setStabilityVersion($stabilityVersion) {
		$this->stabilityVersion = $stabilityVersion;
	}
	
	public function getMajor() {
		return $this->major;
	}
	
	public function setMajor($major) {
		$this->major = $major;
	}
	
	public function getPatch() {
		return $this->patch;
	}
	
	public function setPatch($patch) {
		$this->patch = $patch;
	}

	/**
	 * Get stable version this version is targetting (ignoring rc, beta, etc)
	 *
	 * @return string
	 */
	public function getValueStable() {
		return implode('.', array($this->major, $this->minor, $this->patch));
	}

	/**
	 * Get version string
	 *
	 * @return string
	 */
	public function getValue() {
		$value = $this->getValueStable();
		if($this->stability) {
			$value .= "-{$this->stability}{$this->stabilityVersion}";
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
	
	/**
	 * Guess the best prior version to release as changelog
	 * 
	 * @return ReleaseVersion
	 */
	public function getPriorVersion() {
		$prior = clone $this;
		
		// If beta2 or above, guess prior version to be beta1
		$stabilityVersion = $prior->getStabilityVersion();
		if($stabilityVersion > 1) {
			$prior->setStabilityVersion($stabilityVersion - 1);
			return $prior;
		}
		
		// Set prior version to stable only
		$prior->setStability(null);
		$prior->setStabilityVersion(null);
		
		// If patch version is 0 we really can't guess
		$patch = $prior->getPatch();
		if(empty($patch)) {
			throw new InvalidArgumentException(
				"Can't guess version which comes before " . $this->getValue()
			);
		}
		
		// Select prior patch version (e.g. 3.1.14 -> 3.1.13)
		$prior->setPatch($patch - 1);
		return $prior;
	}

	/**
	 * Get all filenames
	 *
	 * @return string
	 */
	public function getReleaseFilenames() {
		$names = array();
		foreach(array(false, true) as $includeCMS) {
			foreach(array('.zip', '.tar.gz') as $extension) {
				$names[] = $this->getReleaseFilename($includeCMS, $extension);
			}
		}
		return $names;
	}

	/**
	 * For this version, generate the filename
	 *
	 * @param bool $includeCMS Does this include CMS?
	 * @param string $extension archive extension (including period)
	 * @return string
	 */
	public function getReleaseFilename($includeCMS = true, $extension = '.tar.gz') {
		$type = $includeCMS ? 'cms' : 'framework';
		$version = $this->getValue();
		return "SilverStripe-{$type}-v{$version}{$extension}";
	}
}
