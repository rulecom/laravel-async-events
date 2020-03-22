<?php
namespace Rule\AsyncEvents\EventScope;

use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\EventWorker\Worker;
use Rule\AsyncEvents\Listener\Listener;

class AsyncCallbackScope implements EventScope
{
    private $id;
    private $handlersMap;

    private $dispatcher;
    private $listener;
    private $worker;

    private $workerTtl;
    private $workerPoll;
    private $beforeCallback;
    private $afterCallback;

    public function __construct(Dispatcher $dispatcher, Listener $listener, Worker $worker)
    {
        $this->dispatcher = $dispatcher;
        $this->listener = $listener;
        $this->worker = $worker;

        $this->id = uniqid(rand());
    }

    public function getScopeId(): string
    {
        return $this->id;
    }

    public function before(callable $callback)
    {
        $this->beforeCallback = $callback;
    }

    public function after(callable $callback)
    {
        $this->afterCallback = $callback;
    }

    public function run()
    {
        call_user_func($this->beforeCallback, $this);
        $this->setUpHandlers();
        $this->listener->setEventDispatcher($this->dispatcher);
        $this->listener->run(); // for init channels

        $this->worker->setTTL($this->workerTtl);
        $this->worker->setPollFrequency($this->workerPoll);
        $this->worker->run();
        call_user_func($this->afterCallback, $this);
    }

    public function stop()
    {
        $this->worker->stop();
    }

    public function setWorkerTtl(int $seconds)
    {
        $this->workerTtl = $seconds;
    }

    public function setWorkerPoll(int $ms)
    {
        $this->workerPoll = $ms;
    }

    private function wrapHandler(callable $handler): ScopeEventHandler
    {
        return new CallbackScopeEventHandler($handler);
    }

    private function setUpHandlers()
    {
        foreach ($this->handlersMap as $event => $handler) {
            if (is_callable($handler)) {
                $handler = $this->wrapHandler($handler);
            }

            $handler->setScope($this);
            $this->dispatcher->registerHandler($event, $handler);
        }
    }
}