<?php

namespace pascaldevink\Phlybox\Command;

use Symfony\Component\Console\Command\Command;
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
        $boxName = $this->generateBoxName();
        $boxIp = $this->generateBoxIp();

        $repositoryOwner = $input->getArgument('repositoryOwner');
        $repository = $input->getArgument('repository');
        $baseBranch = $input->getArgument('baseBranch');
        $prNumber = $input->getArgument('prNumber');

        $output->writeln('Cloning...');
        $this->gitClone($repositoryOwner, $repository, $boxName);
        $output->writeln('Branching...');
        $this->gitBranch($boxName, $baseBranch);

        $output->writeln('Getting PR Info...');
        $prInfoOutput = $this->getInfoForPR($repositoryOwner, $repository, $prNumber);
        $prUrl = $this->getPRUrlFromPRInfo($prInfoOutput);
        $prBranch = $this->getPRBranchFromPRInfo($prInfoOutput);

        $output->writeln('Pulling...');
        $this->gitPullPr($boxName, $baseBranch, $prUrl, $prBranch);

        $output->writeln("Getting the vagrant box up and running on IP: $boxIp");
        $this->vagrantUp($boxName, $boxIp);

        $output->writeln("Box is up at: http://$boxIp");
    }

    protected function generateBoxName()
    {
        return time();
    }

    protected function generateBoxIp()
    {
        $subIp = mt_rand(10,255);
        return "192.168.34.$subIp";
    }

    protected function gitClone($repositoryOwner, $repository, $boxName)
    {
        $command = "git clone git@github.com:$repositoryOwner/$repository.git ./$boxName";
        system($command);
    }

    protected function gitBranch($boxName, $baseBranch)
    {
        $command = "cd $boxName && git checkout $baseBranch";
        system($command);
    }

    protected function gitPullPr($boxName, $baseBranch, $prUrl, $prBranch)
    {
        $command = "cd $boxName && git checkout -b $prBranch $baseBranch";
        system($command);

        $command = "cd $boxName && git pull $prUrl $prBranch";
        system($command);

        $command = "cd $boxName && git checkout $baseBranch";
        system($command);

        $command = "cd $boxName && git merge $prBranch";
        system($command);
    }

    protected function getInfoForPR($repositoryOwner, $repository, $prNumber)
    {
        $curlUrl = "https://api.github.com/repos/$repositoryOwner/$repository/pulls/$prNumber";
        $ch = curl_init($curlUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, "phlybox");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rawOutput = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($rawOutput);

        return $output;
    }

    protected function getPRUrlFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->repo->ssh_url;
    }

    protected function getPRBranchFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->ref;
    }

    protected function vagrantUp($boxName, $boxIp)
    {
        $command = "cd $boxName && IP=$boxIp vagrant up --provision";
        system($command);
    }
} 