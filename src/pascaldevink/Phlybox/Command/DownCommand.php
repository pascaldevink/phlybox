<?php

namespace pascaldevink\Phlybox\Command;

use pascaldevink\Phlybox\Service\Workflow\DownService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use pascaldevink\Phlybox\Service\Storage\SqliteStorageService;
use pascaldevink\Phlybox\Service\Virtualization\VagrantService;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DownCommand extends Command implements EventSubscriberInterface
{
    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this
            ->setName('down')
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
        $this->output = $output;

        $virtualizationService = new VagrantService();
        $metaStorageService = new SqliteStorageService();

        $id = $input->getArgument('id');

        $downService = new DownService($id, $metaStorageService, $virtualizationService);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($this);
        $downService->setEventDispatcher($eventDispatcher);

        $downService->go();
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
            'BringingBoxDown' => 'notifyUser',
            'BoxIsDown' => 'notifyUser',
        );
    }

    public function notifyUser(Event $event, $eventName)
    {
        $this->output->writeln("<info>$eventName</info>");
    }
}