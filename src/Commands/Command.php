<?php

namespace SilverStripe\Cow\Commands;

use Exception;
use InvalidArgumentException;
use SilverStripe\Cow\Model\ReleaseVersion;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends Console\Command\Command
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription($this->description);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->fire();
    }

    /**
     * Defers to the subclass functionality.
     */
    abstract protected function fire();

	/**
	 * Error if version is invalid
	 * 
	 * @param string $version
	 * @return array List of major, minor, patch, and stability identifier (null if stable)
	 * @throws InvalidArgumentException
	 */
	public function validateVersion($version) {
		if(!preg_match('/^(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)(\-(?<stability>(rc|alpha|beta)\d*))?$/', $version, $matches)) {
			throw new InvalidArgumentException(
				"Invalid version $version. Expect full version (3.1.13) with optional rc|alpha|beta suffix"
			);
		}
		$stability = empty($matches['stability']) ? null : $matches['stability'];
		return array($matches['major'], $matches['minor'], $matches['patch'], $stability);
	}



	/**
	 * Guess a directory to install the given version
	 *
	 * @param ReleaseVersion $version
	 * @return string
	 * @throws Exception
	 */
	protected function pickDirectory(ReleaseVersion $version) {
		$path = getenv('COW_RELEASE_PATH');
		if(empty($path)) {
			throw new Exception('Please set COW_RELEASE_PATH in your ~/.bash_profile');
		}
		if(!realpath($path)) {
			throw new Exception("{$path} does not exist");
		}

		return realpath($path) . DIRECTORY_SEPARATOR . 'release-' . $version->getValue();
	}
}
