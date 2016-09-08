<?php

namespace SilverStripe\Cow\Steps\Module;

use Github\Api\Repo;
use Github\Client as GithubClient;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\Changelog;
use SilverStripe\Cow\Model\Module;
use SilverStripe\Cow\Model\Project;
use SilverStripe\Cow\Model\ReleaseVersion;
use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class TagAnnotatedModule extends Step
{
    /**
     * Line formatting for the changelog
     *
     * @var string
     */
    protected $changelogLineFormat = ' * {message} ({author}) - [{shortHash}]({link})';

    /**
     * @var Project root project
     */
    protected $project;

    /**
     * Version to tag
     *
     * @var ReleaseVersion
     */
    protected $version;

    /**
     * From version for the changelog
     *
     * @var ReleaseVersion
     */
    protected $from;

    /**
     * @var Module
     */
    protected $module;

    /**
     * @var string
     */
    protected $message;

    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param Module $module
     * @return TagAnnotatedModule
     */
    public function setModule(Module $module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return ReleaseVersion
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param ReleaseVersion $version
     * @return TagAnnotatedModule
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return ReleaseVersion
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param ReleaseVersion $from
     * @return $this
     */
    public function setFrom(ReleaseVersion $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     * @return $this
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message ?: $this->getVersion()->getValue();
    }

    /**
     * @param $message string
     * @return $this
     */
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    public function getStepName()
    {
        return 'tag-annotated';
    }

    public function __construct(Command $command, ReleaseVersion $version, ReleaseVersion $from, $directory, $module, $message)
    {
        parent::__construct($command);
        $this->setVersion($version);
        $this->setFrom($from);
        $this->setProject(new Project($directory));
        $this->setMessage($message);
        $module = $this->getProject()->getModule($module);
        if (empty($module)) {
            throw new \InvalidArgumentException("No module $module found in project");
        }
        $this->setModule($module);
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $module = $this->getModule();
        $name = $module->getName();
        $version = $this->getVersion()->getValue();
        $from = $this->getFrom()->getValue();
        $slug = $module->getGithubSlug();
        $interactive = $input->isInteractive() ? "(interactive)" : "(non-interactive)";
        $this->log(
            $output,
            "Releasing module <info>$name</info> version <info>$version</info> from <info>$from</info> $interactive"
        );

        // Validate this is a real github module
        if (!$slug) {
            throw new \InvalidArgumentException("Github remote could not be found in this repository");
        }

        // Check token and authenticate
        $token = getenv('GITHUB_API_TOKEN');
        if (empty($token)) {
            throw new \InvalidArgumentException("Missing GITHUB_API_TOKEN: Cannot authenticate with github");
        }
        $client = $this->getClient($token);

        // build changelog
        $markdown = $this->buildMarkdown($input, $output);

        // Push up changes (otherwise github can't tag)
        $this->log($output, "Pushing module <info>" . $name . "</info>");
        $module->pushTo('origin', true);

        // Invoke github API to create release
        $this->release($output, $client, $slug, $markdown);
    }

    /**
     * @param string $token API token
     * @return GithubClient
     */
    protected function getClient($token)
    {
        // Create authenticated github client
        $client = new GithubClient();
        $httpClient = new GuzzleClient();
        $client->setHttpClient($httpClient);
        $client->authenticate($token, null, GithubClient::AUTH_HTTP_TOKEN);
        return $client;
    }

    /**
     * Push the release to github
     *
     * @param OutputInterface $output
     * @param GithubClient $client Client
     * @param string $slug Github slug
     * @param string $markdown Markdown for changelog
     */
    protected function release(OutputInterface $output, GithubClient $client, $slug, $markdown)
    {
        $module = $this->getModule();
        $version = $this->getVersion();
        $sha = $module->getRepository()->getHeadCommit()->getHash();
        list($org, $repo) = explode('/', $slug);
        $this->log($output, "Creating github release <info>{$org}/{$repo} v{$version}</info> at <info>$sha</info>");

        /** @var Repo $repo */
        $reposAPI = $client->api('repo');
        $result = $reposAPI->releases()->create($org, $repo, [
            'tag_name' => $version->getValue(),
            'target_commitish' => $sha,
            'name' => $this->getMessage(),
            'body' => $markdown,
            'prerelease' => in_array($version->getStability(), ['rc', 'beta', 'alpha']),
            'draft' => false,
        ]);

        if (isset($result['created_at'])) {
            $this->log($output, "Successfully created at <info>" . $result['created_at'] . "</info>");
        } else {
            $this->log($output, "<error>Push didn't seem to be successful.</error>");
        }
    }

    /**
     * Build markdown for this changelog
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function buildMarkdown(InputInterface $input, OutputInterface $output)
    {
        $this->log($output, "Building changelog");
        $changelog = new Changelog([$this->getModule()], $this->getFrom());
        $changelog->setLineFormat($this->getChangelogLineFormat());
        $markdown = $changelog->getMarkdown($output, Changelog::FORMAT_FLAT);
        $this->log($output, "Changelog complete: Preview below (note: This can be edited in the github interface)");
        $this->log($output, "\n<info>$markdown</info>");

        // Prompt for confirmation
        // check interactive mode
        if (!$input->isInteractive()) {
            return $markdown;
        }

        // Check if this is acceptable
        $helper = $this->getQuestionHelper();
        $this->log($output, "");
        $question = new ChoiceQuestion(
            "Tag with this changelog? (defaults to continue): ",
            array("continue", "abort"),
            "continue"
        );
        $answer = $helper->ask($input, $output, $question);
        // Let's get out of here!
        if ($answer === 'abort') {
            $this->log($output, "Aborting");
            die();
        }
        return $markdown;
    }

    /**
     * @return string
     */
    public function getChangelogLineFormat()
    {
        return $this->changelogLineFormat;
    }

    /**
     * @param string $changelogLineFormat
     * @return $this
     */
    public function setChangelogLineFormat($changelogLineFormat)
    {
        $this->changelogLineFormat = $changelogLineFormat;
        return $this;
    }
}
