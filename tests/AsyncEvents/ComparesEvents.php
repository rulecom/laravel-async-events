<?php
namespace Rule\Tests\AsyncEvents;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

trait ComparesEvents
{
    private function assertSameEvents(AsyncEvent $expected, AsyncEvent $actual)
    {
        $this->assertEquals($expected->getName(), $actual->getName());
        $this->assertEquals($expected->getPayload(), $actual->getPayload());
        $this->assertEquals($expected->getChannel(), $actual->getChannel());
        $this->assertEquals($expected->getCallbackChannel(), $actual->getCallbackChannel());
    }
}
