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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

class UpCommand extends Command implements EventSubscriberInterface
{
    /** @var OutputInterface */
    private $output;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $currentWorkingDirectory;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        $currentWorkingDirectory,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    )
    {
        parent::__construct();

        $this->currentWorkingDirectory = $currentWorkingDirectory;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

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

        $vcsRepositoryService = new GithubRepositoryService();
        $virtualizationService = new VagrantService();
        $virtualizationService->setLogger($this->logger);
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
            $this->currentWorkingDirectory,
            $metaStorageService,
            $vcsRepositoryService,
            $virtualizationService
        );

        $this->eventDispatcher->addSubscriber($this);
        $upService->setEventDispatcher($this->eventDispatcher);

        $upService->go();
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
