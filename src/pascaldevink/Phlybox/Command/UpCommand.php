<?php

namespace pascaldevink\Phlybox\Command;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pascaldevink\Phlybox\Service\BoxStatus;
use pascaldevink\Phlybox\Service\Configuration\ConfigReaderService;
use pascaldevink\Phlybox\Service\VersionControl\GithubRepositoryService;
use pascaldevink\Phlybox\Service\Notification\NotificationServiceFactory;
use pascaldevink\Phlybox\Service\Storage\SqliteStorageService;
use pascaldevink\Phlybox\Service\Virtualisation\VagrantService;
use pascaldevink\Phlybox\Service\Configuration\YamlConfigReaderService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UpCommand extends Command
{
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
        $currentDirectory = $this->getCurrentWorkingDirectory();

        $logger = $this->createLogger($currentDirectory);

        $vcsRepositoryService = new GithubRepositoryService();
        $vagrantService = new VagrantService();
        $vagrantService->setLogger($logger);
        $metaStorageService = new SqliteStorageService();

        $output->setDecorated(true);
        $output->setFormatter(new OutputFormatter(true, array(
            'info' => new OutputFormatterStyle('green')
        )));

        $repositoryOwner = $input->getArgument('repositoryOwner');
        $repository = $input->getArgument('repository');
        $baseBranch = $input->getArgument('baseBranch');
        $prNumber = $input->getArgument('prNumber');
        $boxName = $vagrantService->generateBoxName();

        $id = $metaStorageService->addBox($boxName, $repositoryOwner, $repository, $baseBranch, $prNumber);

        $output->writeln('<info>Cloning...</info>');
        $metaStorageService->setBoxStatus($id, BoxStatus::STATUS_CLONING);
        $vcsRepositoryService->checkoutRepository($repositoryOwner, $repository, $boxName);

        $output->writeln('<info>Branching...</info>');
        $vcsRepositoryService->setRepositoryBranch($boxName, $baseBranch);

        $output->writeln('<info>Getting PR Info...</info>');
        $prInfoOutput = $vcsRepositoryService->getInfoForPullRequest($repositoryOwner, $repository, $prNumber);
        $prUrl = $this->getPRUrlFromPRInfo($prInfoOutput);
        $prBranch = $this->getPRBranchFromPRInfo($prInfoOutput);

        $output->writeln('<info>Pulling...</info>');
        $metaStorageService->setBoxStatus($id, BoxStatus::STATUS_MERGING);
        $vcsRepositoryService->pullInPullRequest($boxName, $baseBranch, $prUrl, $prBranch);

        $configurationReaderService = $this->getProjectConfiguration($currentDirectory, $boxName);
        $ipBase = $configurationReaderService->getIpBase();
        $boxIp = $vagrantService->generateBoxIp($ipBase);

        $notificationServiceConfiguration = $configurationReaderService->getNotificationService();
        if ($notificationServiceConfiguration !== false) {
            $this->notifyStarted($notificationServiceConfiguration, $boxIp, $id);
        }

        $output->writeln("<info>Getting the vagrant box up and running on IP: $boxIp</info>");
        $metaStorageService->setBoxStatus($id, BoxStatus::STATUS_BOOTING);
        $vagrantService->vagrantUp($boxName, $boxIp);

        $metaStorageService->setBoxStatus($id, BoxStatus::STATUS_READY);
        $output->writeln("<info>Box is up at: http://$boxIp with ID: $id</info>");

        if ($notificationServiceConfiguration !== false) {
            $this->notifyUp($notificationServiceConfiguration, $boxIp, $id);
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
     * @param array $notificationServiceConfiguration
     */
    protected function notifyStarted(array $notificationServiceConfiguration)
    {
        $notificationService = NotificationServiceFactory::generate(
            $notificationServiceConfiguration['serviceName'],
            $notificationServiceConfiguration['serviceConfiguration']
        );

        $notificationService->notify("Cloned the repository, now starting the box...");
    }

    /**
     * @param array $notificationServiceConfiguration
     * @param string $boxIp
     * @param int $id
     */
    protected function notifyUp(array $notificationServiceConfiguration, $boxIp, $id)
    {
        $notificationService = NotificationServiceFactory::generate(
            $notificationServiceConfiguration['serviceName'],
            $notificationServiceConfiguration['serviceConfiguration']
        );

        $notificationService->notify("Box is up at: http://$boxIp with ID: $id");
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
     * @param $currentDirectory
     * @param $boxName
     * @return ConfigReaderService
     */
    protected function getProjectConfiguration($currentDirectory, $boxName)
    {
        $configurationReaderService = new YamlConfigReaderService($currentDirectory . '/' . $boxName);
        return $configurationReaderService;
    }
}
