<?php
namespace Rule\AsyncEvents\Dispatcher;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

class CallbackEventHandler implements EventHandler
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function handle(AsyncEvent $event)
    {
        call_user_func($this->callback, $event);
    }
}