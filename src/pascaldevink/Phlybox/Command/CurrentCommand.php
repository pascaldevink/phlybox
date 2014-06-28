<?php

namespace pascaldevink\Phlybox\Command;

use pascaldevink\Phlybox\Service\BoxStatus;
use pascaldevink\Phlybox\Service\Storage\SqliteStorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CurrentCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('current')
            ->setDescription('Lists the currently running environment')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $metaStorageService = new SqliteStorageService();

        $output->setDecorated(true);
        $output->setFormatter(new OutputFormatter(true, array(
            'info' => new OutputFormatterStyle('green')
        )));

        $boxes = $metaStorageService->getAllBoxes();

        /** @var TableHelper $table */
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(array(
            'ID',
            'Box name',
            'Repository owner',
            'Repository',
            'Base branch',
            'PR',
            'Status',
        ));

        $output->writeln('<info>ID | Box name | Repository owner | Repository | Branch | PR | Status</info>');

        foreach($boxes as $box) {
            $table->addRow(array(
                $box['id'],
                $box['boxName'],
                $box['repositoryOwner'],
                $box['repositoryName'],
                $box['branch'],
                $box['prNumber'],
                BoxStatus::getStatusByNumber($box['status']),
            ));
        }

        $table->render($output);
    }
} 