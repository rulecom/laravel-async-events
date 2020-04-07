<?php
namespace Rule\AsyncEvents\Dispatcher;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

class BaseEventDispatcher implements Dispatcher, LocalDispatcher
{
    private $handlerMap;

    public function dispatch(AsyncEvent $event)
    {
        $handlers = $this->handlerMap[$event->getName()] ?? [];

        array_walk($handlers, function (EventHandler $handler) use ($event) {
            $handler->handle($event);
        });
    }

    public function registerHandler(string $eventName, EventHandler $handler)
    {
        $this->handlerMap[$eventName] = !isset($this->handlerMap[$eventName])
            ? [$handler]
            : array_merge($this->handlerMap[$eventName], [$handler]);
    }
}
