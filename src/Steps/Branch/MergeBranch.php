<?php

namespace SilverStripe\Cow\Steps\Branch;

use Gitonomy\Git\Exception\ProcessException;
use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\Module;
use SilverStripe\Cow\Steps\Release\ModuleStep;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MergeBranch
 *
 * @package SilverStripe\Cow\Steps\Branch
 */
class MergeBranch extends ModuleStep
{
    /**
     * From branch
     *
     * @var string
     */
    protected $from = null;

    /**
     * To branch
     *
     * @var string
     */
    protected $to = null;

    /**
     * @var bool
     */
    protected $push = false;

    /**
     * List of repos with conflicts
     *
     * @var array
     */
    protected $conflicts = [];

    public function __construct(Command $command, $directory = '.', $modules = array(), $listIsExclusive = false, $from, $to, $push)
    {
        parent::__construct($command, $directory, $modules, $listIsExclusive);
        $this->setFrom($from);
        $this->setTo($to);
        $this->setPush($push);
    }

    public function getStepName()
    {
        return 'merge';
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->log($output, "Merging from <info>" . $this->getFrom() . "</info> to <info>" . $this->getTo() . "</info>");
        if($this->getPush()) {
            $this->log($output, "Successful merges will be pushed to origin");
        }

        $this->conflicts = [];
        foreach($this->getModules() as $module) {
            $this->mergeModule($output, $module);
        }

        // Display output
        if($this->conflicts) {
            $this->log($output, "Merge conflicts exist which must be resolved manually:");
            foreach($this->conflicts as $module) {
                /** @var Module $module */
                $this->log($output, "<comment>" . $module->getDirectory() . "</comment>");
            }
        } else {
            $this->log($output, "All modules were merged without any conflicts");
        }
    }

    /**
     * Merge the given branches on this module
     *
     * @param OutputInterface $output
     * @param Module $module
     */
    protected function mergeModule(OutputInterface $output, Module $module) {
        $this->log($output, "Merging module <info>" . $module->getComposerName() . "</info>");

        $module->fetch($output);
        $module->checkout($output, $this->getFrom());
        $module->checkout($output, $this->getTo());

        try {
            $module->merge($output, $this->getFrom());
            $this->Log($output, "Merge successful!");

            if($this->getPush()) {
                $this->log($output, "Pushing upstream");
                $module->pushTo();
            }

        } catch(ProcessException $ex) {
            // Module has conflicts; Please merge!
            $this->log($output, "<error>Merge conflict in module " . $module->getName() . "</error>");
            $this->conflicts[] = $module;
        }
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPush()
    {
        return $this->push;
    }

    /**
     * @param boolean $push
     * @return $this
     */
    public function setPush($push)
    {
        $this->push = $push;
        return $this;
    }

}
