<?php
namespace Rule\AsyncEvents\EventScope;

use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\EventWorker\EventWorker;
use Rule\AsyncEvents\Listener\Listener;

interface EventScope
{
    public function getScopeId(): string;
    public function before(callable $initCallback);
    public function after(callable $after);
    public function run();
    public function stop();

    public function setHandlersMap(array $handlersMap);
    /*public function setDispatcher(Dispatcher $dispatcher);
    public function setListener(Listener $listener);
    public function setWorker(EventWorker $worker);*/
    public function setWorkerTtl(int $seconds);
    public function setWorkerPoll(int $ms);
}