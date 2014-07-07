<?php

namespace spec\pascaldevink\Phlybox\Configuration;

use pascaldevink\Phlybox\Configuration\ConfigurationContainer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Puzzle\Configuration\Memory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConfigurationContainerSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('pascaldevink\Phlybox\Configuration\ConfigurationContainer');
    }

    function it_converts_configuration_to_an_object()
    {
        $rawConfiguration = new Memory(array());

        $this
            ->get($rawConfiguration)
            ->shouldReturnAnInstanceOf('pascaldevink\Phlybox\Configuration\Configuration');
    }

    function it_converts_ip_configuration()
    {
        $rawConfiguration = new Memory(array(
            'phlybox/ip_base'       => '192.168.0.0/32',
        ));

        $this
            ->get($rawConfiguration)
            ->shouldReturnAnInstanceOf('pascaldevink\Phlybox\Configuration\Configuration');
    }

    function it_dispatcher_configuration_event_for_notifications(
        EventDispatcherInterface $eventDispatcher)
    {
        $rawConfiguration = new Memory(array(
            'phlybox/notification'      => 'slack',
            'phlybox/slack_team'        => 'phlybox',
        ));

        $eventDispatcher->dispatch(
            ConfigurationContainer::CONFIG_EVENT_NOTIFICATIONS,
            Argument::type('pascaldevink\Phlybox\Configuration\ConfigurationEvent')
        )->shouldBeCalled();

        $this->get($rawConfiguration);
    }
}
