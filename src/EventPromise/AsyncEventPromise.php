<?php
namespace Rule\AsyncEvents\EventPromise;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\Emitter\Emitter;
use Rule\AsyncEvents\EventScope\EventScope;

class AsyncEventPromise implements EventPromise
{
    private const DEFAULT_WORKER_POLL = 50; // ms
    private const DEFAULT_WORKER_TIMEOUT = 5; // s

    private $scope;
    private $emitter;

    private $initEvent;
    private $resolveEvent = 'success';
    private $rejectEvent = 'failure';
    private $resolveCallback;
    private $rejectCallback;

    public function __construct(EventScope $scope, Emitter $emitter)
    {
        $this->scope = $scope;
        $this->emitter = $emitter;
    }

    public function wait(int $timeout = 0)
    {
        $this->init();

        $this->scope->setWorkerPoll(self::DEFAULT_WORKER_POLL);
        $this->scope->setWorkerTtl( self::DEFAULT_WORKER_TIMEOUT);
        $this->scope->run();
    }

    public function promise(AsyncEvent $event): self
    {
        $this->initEvent = $event;

        return $this;
    }

    public function resolve(callable $callback, ?string $event): self
    {
        $this->resolveCallback = function (AsyncEvent $event, EventScope $scope) use ($callback) {
            $scope->stop();
            $callback($event, $scope);
        };

        if (!is_null($event)) {
            $this->resolveEvent = $event;
        }

        return $this;
    }

    public function reject(callable $callback, ?string $event): self
    {
        $this->rejectCallback = function (AsyncEvent $event, EventScope $scope) use ($callback) {
            $scope->stop();
            $callback($event, $scope);
        };

        if (!is_null($event)) {
            $this->rejectEvent = $event;
        }

        return $this;
    }

    public function getScope(): EventScope
    {
        return $this->scope;
    }

    private function init()
    {
        $this->scope->addEventCallback($this->resolveEvent, $this->resolveCallback);
        $this->scope->addEventCallback($this->rejectEvent, $this->rejectCallback);

        $this->scope->before(function (EventScope $scope) {
            $this->initEvent->setCallbackChannel($scope->getScopeId());
            $this->emitter->emit($this->initEvent);
        });
    }
}