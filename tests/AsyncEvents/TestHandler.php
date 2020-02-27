<?php
namespace Rule\Tests\AsyncEvents;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\Dispatcher\EventHandler;

class TestHandler implements EventHandler
{
    public $dispatchedEvent;

    public function handle(AsyncEvent $event)
    {
        $this->dispatchedEvent = $event;
    }
}
