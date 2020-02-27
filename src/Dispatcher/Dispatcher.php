<?php


namespace Rule\AsyncEvents\Dispatcher;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

interface Dispatcher
{
    public function dispatch(AsyncEvent $event);
    public function registerHandler(string $eventName, EventHandler $handler);
}
