<?php

namespace pascaldevink\Phlybox\Service\Workflow;

use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use pascaldevink\Phlybox\Configuration\ConfigurationContainer;
use pascaldevink\Phlybox\Configuration\Model\Configuration;
use pascaldevink\Phlybox\Configuration\Model\Notification\NotificationConfiguration;
use pascaldevink\Phlybox\Service\BoxStatus;
use pascaldevink\Phlybox\Service\Notification\NotificationServiceFactory;
use pascaldevink\Phlybox\Service\Storage\MetaStorageService;
use pascaldevink\Phlybox\Service\VersionControl\VCSRepositoryService;
use pascaldevink\Phlybox\Service\Virtualization\VirtualizationService;
use Puzzle\Configuration\Yaml;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

class UpService implements WorkflowCommand
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $boxName, $repositoryOwner, $repository, $baseBranch, $prNumber;

    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var MetaStorageService
     */
    private $metaStorageService;

    /**
     * @var VCSRepositoryService
     */
    private $vcsRepositoryService;

    /**
     * @var VirtualizationService
     */
    private $virtualisationService;

    public function __construct(
        $boxName,
        $repositoryOwner,
        $repository,
        $baseBranch,
        $prNumber,
        $workingDirectory,
        MetaStorageService $metaStorageService,
        VCSRepositoryService $vcsRepositoryService,
        VirtualizationService $virtualisationService
        )
    {
        $this->boxName = $boxName;
        $this->repositoryOwner = $repositoryOwner;
        $this->repository = $repository;
        $this->baseBranch = $baseBranch;
        $this->prNumber = $prNumber;

        $this->workingDirectory = $workingDirectory;

        $this->metaStorageService = $metaStorageService;
        $this->vcsRepositoryService = $vcsRepositoryService;
        $this->virtualisationService = $virtualisationService;
    }

    /**
     * Starts the workflow. If the event dispatcher is provided, events can be triggered between each command
     * in the workflow.
     *
     * @return void
     */
    public function go()
    {
        $id = $this->metaStorageService->addBox(
            $this->boxName,
            $this->repositoryOwner,
            $this->repository,
            $this->baseBranch,
            $this->prNumber
        );

        $this->eventDispatcher->dispatch('CloningRepository');
        $this->metaStorageService->setBoxStatus($id, BoxStatus::STATUS_CLONING);
        $this->vcsRepositoryService->checkoutRepository(
            $this->repositoryOwner,
            $this->repository,
            $this->boxName
        );

        $this->eventDispatcher->dispatch('SettingBaseBranchOnRepository');
        $this->vcsRepositoryService->setRepositoryBranch(
            $this->boxName,
            $this->baseBranch
        );

        $this->eventDispatcher->dispatch('GettingPullRequestInformation');
        $prInfoOutput = $this->vcsRepositoryService->getInfoForPullRequest(
            $this->repositoryOwner,
            $this->repository,
            $this->prNumber
        );
        $prUrl = $this->getPRUrlFromPRInfo($prInfoOutput);
        $prBranch = $this->getPRBranchFromPRInfo($prInfoOutput);

        $this->eventDispatcher->dispatch('PullingPullRequestIntoRepository');
        $this->metaStorageService->setBoxStatus($id, BoxStatus::STATUS_MERGING);
        $this->vcsRepositoryService->pullInPullRequest(
            $this->boxName,
            $this->baseBranch,
            $prUrl,
            $prBranch);

        $configuration = $this->getProjectConfiguration($this->workingDirectory, $this->boxName);
        $ipBase = $configuration->getIpBase();
        $boxIp = $this->virtualisationService->generateBoxIp($ipBase);

        $notificationConfiguration = $configuration->getNotificationConfiguration();
        if ($notificationConfiguration !== false) {
            $this->notifyStarted($notificationConfiguration, $boxIp, $id);
        }

        $this->eventDispatcher->dispatch('StartingVirtualisation');
        $this->metaStorageService->setBoxStatus($id, BoxStatus::STATUS_BOOTING);
        $this->virtualisationService->up($this->boxName, $boxIp);

        $this->metaStorageService->setBoxStatus($id, BoxStatus::STATUS_READY);
        $this->eventDispatcher->dispatch('VirtualisationIsDone');

        if ($notificationConfiguration !== false) {
            $this->notifyUp($notificationConfiguration, $boxIp, $id);
        }
    }

    protected function getPRUrlFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->repo->ssh_url;
    }

    protected function getPRBranchFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->ref;
    }

    /**
     * @param NotificationConfiguration $notificationConfiguration
     */
    protected function notifyStarted(NotificationConfiguration $notificationConfiguration)
    {
        $notificationService = NotificationServiceFactory::generate($notificationConfiguration);

        $notificationService->notify("Cloned the repository, now starting the box...");
    }

    /**
     * @param NotificationConfiguration $notificationConfiguration
     * @param string $boxIp
     * @param int $id
     */
    protected function notifyUp($notificationConfiguration, $boxIp, $id)
    {
        $notificationService = NotificationServiceFactory::generate($notificationConfiguration);

        $notificationService->notify("Box is up at: http://$boxIp with ID: $id");
    }

    /**
     * @param $currentDirectory
     * @param $boxName
     * @return Configuration
     */
    protected function getProjectConfiguration($currentDirectory, $boxName)
    {
        $configurationContainer = new ConfigurationContainer($this->eventDispatcher);
        $rawConfiguration = new Yaml(new Filesystem(new Local($currentDirectory . '/' . $boxName)));
        $configuration = $configurationContainer->get($rawConfiguration);

        return $configuration;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return WorkflowCommand
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }
}