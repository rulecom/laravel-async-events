<?php
namespace Tests\AsyncEvents;

use Rule\AsyncEvents\Router\EventRouter;
use PHPUnit\Framework\TestCase;

class EventRouterTest extends TestCase
{
    /**
     * @test
     */
    public function testAddRoute()
    {
        $router = $this->getRouter();
        $eventName = 'some_event';
        $channelName = 'some_channel';

        $router->registerEventRoute($eventName, $channelName);
        $this->assertContains($channelName, $router->getEventRoutes($eventName));

        $otherChannel = 'some_other_channel';
        $router->registerEventRoute($eventName, $otherChannel);
        $this->assertContains($channelName, $router->getEventRoutes($eventName));
        $this->assertContains($otherChannel, $router->getEventRoutes($eventName));

        $otherEvent = 'some_other_event';
        $router->registerEventRoute($otherEvent, $channelName);
        $this->assertContains($channelName, $router->getEventRoutes($eventName));
        $this->assertContains($otherChannel, $router->getEventRoutes($eventName));
        $this->assertContains($channelName, $router->getEventRoutes($otherEvent));
        $this->assertNotContains($otherChannel, $router->getEventRoutes($otherEvent));
    }

    private function getRouter()
    {
        return new EventRouter();
    }
}
