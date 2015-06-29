<?php

namespace SilverStripe\Cow\Commands\Git;

use RuntimeException;
use SilverStripe\Cow\Commands\Command;
use Symfony\Component\Console\Input\InputOption;

class Branch extends Command
{
    /**
     * @var string
     */
    protected $name = "git:branch";

    /**
     * @var string
     */
    protected $description = "Manage multiple branches with ease";

    protected function fire()
    {
        $upstream = $this->getOption("upstream");

        if ($upstream) {
            $output = $this->exec("git fetch {$upstream}");

            // TODO: add remote branches to the list
        }

        $maxName = 0;
        $maxSha = 0;
        $maxTracking = 0;

        $items = array_map(function($line) use (&$maxName, &$maxSha, &$maxTracking) {
            $line = trim($line, "*");

            $parts = array_filter(explode(" ", $line));

            $name = array_shift($parts);
            $sha = array_shift($parts);
            $tracking = array_shift($parts);

            if ($tracking[0] !== "[") {
                $tracking = "none";
            } else {
                $tracking = trim($tracking, "[]");
            }

            $maxName = max($maxName, strlen($name));
            $maxSha = max($maxSha, strlen($sha));
            $maxTracking = max($maxTracking, strlen($tracking));

            return [$name, $sha, $tracking];
        }, $this->exec("git branch -vv"));

        $lines = array_map(function($item) use ($maxName, $maxSha, $maxTracking) {
            return sprintf(
                "ignore %s %s → %s",
                str_pad($item[1], $maxSha, " ", STR_PAD_RIGHT),
                str_pad($item[0], $maxName, " ", STR_PAD_RIGHT),
                str_pad($item[2], $maxTracking, " ", STR_PAD_RIGHT)
            );
        }, $items);

        $template = file_get_contents(__DIR__ . "/template.txt");

        file_put_contents(
            "cow.git.branch.temp",
            join(PHP_EOL, $lines) . PHP_EOL . PHP_EOL . $template
        );

        $this->exec("vim cow.git.branch.temp > `tty`");

        $contents = file_get_contents("cow.git.branch.temp");

        // TODO operate on modified contents

        unlink("cow.git.branch.temp");
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            ["upstream", "u", InputOption::VALUE_REQUIRED, "Upstream repository"],
        ];
    }
}
