<?php

namespace pascaldevink\Phlybox\Command;

use pascaldevink\Phlybox\Service\GithubRepositoryService;
use pascaldevink\Phlybox\Service\SlackNotificationService;
use pascaldevink\Phlybox\Service\VagrantService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('phlybox:up')
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
        $vcsRepositoryService = new GithubRepositoryService();
        $vagrantService = new VagrantService();
        $notificationService = new SlackNotificationService('autotrack', 'fMxE9J6BYNerp3pnKd8bE9TY', '#deploys', 'phlybox');

        $output->setDecorated(true);
        $output->setFormatter(new OutputFormatter(true, array(
            'info' => new OutputFormatterStyle('green')
        )));

        $boxName = $vagrantService->generateBoxName();
        $boxIp = $vagrantService->generateBoxIp();

        $repositoryOwner = $input->getArgument('repositoryOwner');
        $repository = $input->getArgument('repository');
        $baseBranch = $input->getArgument('baseBranch');
        $prNumber = $input->getArgument('prNumber');

        $output->writeln('<info>Cloning...</info>');
        $vcsRepositoryService->checkoutRepository($repositoryOwner, $repository, $boxName);
        $output->writeln('<info>Branching...</info>');
        $vcsRepositoryService->setRepositoryBranch($boxName, $baseBranch);

        $output->writeln('<info>Getting PR Info...</info>');
        $prInfoOutput = $vcsRepositoryService->getInfoForPullRequest($repositoryOwner, $repository, $prNumber);
        $prUrl = $this->getPRUrlFromPRInfo($prInfoOutput);
        $prBranch = $this->getPRBranchFromPRInfo($prInfoOutput);

        $output->writeln('<info>Pulling...</info>');
        $vcsRepositoryService->pullInPullRequest($boxName, $baseBranch, $prUrl, $prBranch);

        $output->writeln("<info>Getting the vagrant box up and running on IP: $boxIp</info>");
        $vagrantService->vagrantUp($boxName, $boxIp);

        $output->writeln("<info>Box is up at: http://$boxIp</info>");

        $notificationService->notify("Box is up for $prUrl at: http://$boxIp");
    }

    protected function getPRUrlFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->repo->ssh_url;
    }

    protected function getPRBranchFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->ref;
    }
} 