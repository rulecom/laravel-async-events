<?php
namespace Rule\AsyncEvents\EventScope;

use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\Dispatcher\LocalDispatcher;
use Rule\AsyncEvents\EventScope\Exceptions\ScopeExecutionTimeout;
use Rule\AsyncEvents\EventWorker\Worker;
use Rule\AsyncEvents\Listener\Listener;
use Rule\AsyncEvents\Listener\LocalListener;

class AsyncEventScope implements EventScope
{
    private $id;

    private $dispatcher;
    private $listener;
    private $worker;

    private $workerTtl;
    private $workerPoll;

    private $status;
    private $beforeCallback;
    private $afterCallback;

    public function __construct(LocalDispatcher $dispatcher, LocalListener $listener, Worker $worker)
    {
        $this->dispatcher = $dispatcher;
        $this->listener = $listener;
        $this->worker = $worker;

        $this->status = EventScope::STATUS_INIT;
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
        $this->init();

        if ($this->beforeCallback) {
            call_user_func($this->beforeCallback, $this);
        }


        $this->listener->run();
        $this->status = EventScope::STATUS_RUNNING;
        $this->worker->run();

        if ($this->status != EventScope::STATUS_FINISHED) {
            $this->status = EventScope::STATUS_TIMEOUT;

            throw new ScopeExecutionTimeout('Scope wasn\'t finished before timeout exceeded');
        }

        if ($this->afterCallback) {
            call_user_func($this->afterCallback, $this);
        }
    }

    public function stop()
    {
        $this->status = EventScope::STATUS_FINISHED;
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

    public function addEventHandler(string $eventName, ScopeEventHandler $handler)
    {
        $handler->setScope($this);

        $this->dispatcher->registerHandler($eventName, $handler);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    private function setScopeId()
    {
        $this->id = uniqid(rand());
    }

    private function init()
    {
        $this->setScopeId();

        $this->listener->setEventDispatcher($this->dispatcher);
        $this->listener->listenChannel($this->getScopeId());

        $this->worker->setListener($this->listener);
        $this->worker->setTTL($this->workerTtl);
        $this->worker->setPollFrequency($this->workerPoll);
    }

    private function wrapCallback(callable $handler): ScopeEventHandler
    {
        return new CallbackScopeEventHandler($handler);
    }

    public function addEventCallback(string $eventName, callable $callback)
    {
        $handler = $this->wrapCallback($callback);

        $this->addEventHandler($eventName, $handler);
    }
}
