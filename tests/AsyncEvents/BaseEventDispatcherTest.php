<?php
namespace Rule\Tests\AsyncEvents;

use PHPUnit\Framework\TestCase;
use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;
use Rule\AsyncEvents\Dispatcher\BaseEventDispatcher;


class BaseEventDispatcherTest extends TestCase
{
    use ComparesEvents;

    public function testDispatchEventWithHandler()
    {
        $dispatcher = new BaseEventDispatcher();
        $event = new BaseAsyncEvent('base_event', ['test' => 'event']);
        $handler = new TestHandler();

        $dispatcher->registerHandler($event->getName(), $handler);
        $dispatcher->dispatch($event);

        $this->assertSameEvents($event, $handler->dispatchedEvent);
    }

    public function testDontDispatchUnhandledEvent()
    {
        $dispatcher = new BaseEventDispatcher();
        $event = new BaseAsyncEvent('base_event', ['test' => 'event']);
        $handler = new TestHandler();

        $dispatcher->registerHandler($event->getName(), $handler);
        $dispatcher->dispatch(new BaseAsyncEvent('other_event', []));

        $this->assertNull($handler->dispatchedEvent);
    }

    public function testMultipleHandlersOnSameEvent()
    {
        $dispatcher = new BaseEventDispatcher();
        $event = new BaseAsyncEvent('base_event', ['test' => 'event']);
        $handler = new TestHandler();
        $otherHandler = new TestHandler();

        $dispatcher->registerHandler($event->getName(), $handler);
        $dispatcher->registerHandler($event->getName(), $otherHandler);
        $dispatcher->dispatch($event);

        $this->assertSameEvents($event, $handler->dispatchedEvent);
        $this->assertSameEvents($event, $otherHandler->dispatchedEvent);
    }

    public function testMultipleEventsWithSameHandler()
    {
        $dispatcher = new BaseEventDispatcher();
        $event = new BaseAsyncEvent('base_event', ['test' => 'event']);
        $otherEvent = new BaseAsyncEvent('other_base_event', ['field' => 'value']);
        $handler = new TestHandler();

        $dispatcher->registerHandler($event->getName(), $handler);
        $dispatcher->registerHandler($otherEvent->getName(), $handler);

        $dispatcher->dispatch($event);
        $this->assertSameEvents($event, $handler->dispatchedEvent);

        $dispatcher->dispatch($otherEvent);
        $this->assertSameEvents($otherEvent, $handler->dispatchedEvent);
    }
}
