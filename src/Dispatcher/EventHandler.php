<?php


namespace Rule\AsyncEvents\Dispatcher;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

interface EventHandler
{
    public function handle(AsyncEvent $event);
}
