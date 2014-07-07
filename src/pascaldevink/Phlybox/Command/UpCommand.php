<?php

namespace pascaldevink\Phlybox\Command;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pascaldevink\Phlybox\Service\VersionControl\GithubRepositoryService;
use pascaldevink\Phlybox\Service\Storage\SqliteStorageService;
use pascaldevink\Phlybox\Service\Virtualization\VagrantService;
use pascaldevink\Phlybox\Service\Workflow\UpService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

class UpCommand extends Command implements EventSubscriberInterface
{
    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this
            ->setName('up')
            ->setDescription('Bring up a new environment')
            ->addArgument(
                'repositoryOwner',
                InputArgument::REQUIRED,
                'Repository owner'
            )
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Repository'
            )
            ->addArgument(
                'baseBranch',
                InputArgument::REQUIRED,
                'Base branch to start with'
            )
            ->addArgument(
                'prNumber',
                InputArgument::REQUIRED,
                'PR number to pull into base branch'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $currentDirectory = $this->getCurrentWorkingDirectory();

        $logger = $this->createLogger($currentDirectory);

        $vcsRepositoryService = new GithubRepositoryService();
        $virtualizationService = new VagrantService();
        $virtualizationService->setLogger($logger);
        $metaStorageService = new SqliteStorageService();

        $output->setDecorated(true);
        $output->setFormatter(new OutputFormatter(true, array(
            'info' => new OutputFormatterStyle('green')
        )));

        $repositoryOwner = $input->getArgument('repositoryOwner');
        $repository = $input->getArgument('repository');
        $baseBranch = $input->getArgument('baseBranch');
        $prNumber = $input->getArgument('prNumber');
        $boxName = $virtualizationService->generateBoxName();

        $upService = new UpService(
            $boxName,
            $repositoryOwner,
            $repository,
            $baseBranch,
            $prNumber,
            $currentDirectory,
            $metaStorageService,
            $vcsRepositoryService,
            $virtualizationService
        );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($this);
        $upService->setEventDispatcher($eventDispatcher);

        $upService->go();
    }

    /**
     * @return string
     */
    protected function getCurrentWorkingDirectory()
    {
        $process = new Process("pwd");
        $process->run();
        $currentDirectory = trim($process->getOutput());
        return $currentDirectory;
    }

    /**
     * @param $currentDirectory
     *
     * @return LoggerInterface
     */
    protected function createLogger($currentDirectory)
    {
        $logger = new Logger("phlybox");
        $logger->pushHandler(new StreamHandler($currentDirectory . '/phlybox.log'));

        return $logger;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        // Move this
        return array(
            'CloningRepository' => 'notifyUser',
            'SettingBaseBranchOnRepository' => 'notifyUser',
            'GettingPullRequestInformation' => 'notifyUser',
            'PullingPullRequestIntoRepository' => 'notifyUser',
            'StartingVirtualisation' => 'notifyUser',
            'VirtualisationIsDone' => 'notifyUser',
        );
    }

    public function notifyUser(Event $event, $eventName)
    {
        $this->output->writeln("<info>$eventName</info>");
    }
}
