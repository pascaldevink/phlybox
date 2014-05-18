<?php

namespace pascaldevink\Phlybox\Command;

use pascaldevink\Phlybox\Service\BoxStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use pascaldevink\Phlybox\Service\SqliteStorageService;
use pascaldevink\Phlybox\Service\VagrantService;

class DownCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('phlybox:down')
            ->setDescription('Bring down an environment')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Unique identifier'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vagrantService = new VagrantService();
        $metaStorageService = new SqliteStorageService();

        $id = $input->getArgument('id');
        $box = $metaStorageService->getBoxByIdentifier($id);

        $vagrantService->vagrantHalt($box['boxName']);
        $metaStorageService->setBoxStatus($id, BoxStatus::STATUS_HALTED);

        $output->writeln("<info>Box is down</info>");
    }
} 