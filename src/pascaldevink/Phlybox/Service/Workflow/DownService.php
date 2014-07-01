<?php

namespace pascaldevink\Phlybox\Service\Workflow;

use pascaldevink\Phlybox\Service\BoxStatus;
use pascaldevink\Phlybox\Service\Storage\MetaStorageService;
use pascaldevink\Phlybox\Service\Virtualization\VirtualizationService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DownService implements WorkflowCommand
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var int */
    private $id;

    /** @var VirtualizationService */
    private $virtualizationService;

    /** @var MetaStorageService */
    private $metaStorageService;

    public function __construct(
        $id,
        MetaStorageService $metaStorageService,
        VirtualizationService $virtualizationService)
    {
        $this->id = $id;
        $this->metaStorageService = $metaStorageService;
        $this->virtualizationService = $virtualizationService;
    }


    /**
     * Starts the workflow. If the event dispatcher is provided, events can be triggered between each command
     * in the workflow.
     *
     * @return void
     */
    public function go()
    {
        $this->eventDispatcher->dispatch("BringingBoxDown");
        $box = $this->metaStorageService->getBoxByIdentifier($this->id);

        $this->virtualizationService->down($box['boxName']);
        $this->metaStorageService->setBoxStatus($this->id, BoxStatus::STATUS_HALTED);
        $this->eventDispatcher->dispatch("BoxIsDown");
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return WorkflowCommand
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}