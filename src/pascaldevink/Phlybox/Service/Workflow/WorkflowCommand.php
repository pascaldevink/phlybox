<?php

namespace pascaldevink\Phlybox\Service\Workflow;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A workflow is a set of actions that must be done before the entire workflow is completed.
 *
 */
interface WorkflowCommand
{
    /**
     * Starts the workflow. If the event dispatcher is provided, events can be triggered between each command
     * in the workflow.
     *
     * @return void
     */
    public function go();

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return WorkflowCommand
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);
} 