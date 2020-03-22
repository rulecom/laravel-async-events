<?php
namespace Rule\AsyncEvents\EventPromise;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;
use Rule\AsyncEvents\Emitter\Emitter;
use Rule\AsyncEvents\EventScope\EventScope;

class AsyncEventPromise implements EventPromise
{
    private $scope;
    private $emitter;

    private $initEvent;
    private $resolveEvent = 'success';
    private $errorEvent = 'failure';
    private $resolveCallback;
    private $errorCallback;

    public function __construct(EventScope $scope, Emitter $emitter, AsyncEvent $event)
    {
        $this->scope = $scope;
        $this->emitter = $emitter;
        $this->initEvent = $event;
    }

    public function wait()
    {
        $this->init();
        $this->scope->run();
    }

    public function then(callable $callback, ?string $event)
    {
        $this->resolveCallback = $callback;

        if (!is_null($event)) {
            $this->resolveEvent = $event;
        }
    }

    public function error(callable $callback, ?string $event)
    {
        $this->errorCallback = $callback;

        if (!is_null($event)) {
            $this->errorEvent = $event;
        }
    }

    private function init()
    {
        $this->scope->setHandlersMap([
            $this->resolveEvent => $this->resolveCallback,
            $this->errorEvent => $this->errorCallback
        ]);

        $this->scope->before(function (EventScope $scope) {
            $this->emitter->emit(new BaseAsyncEvent(
                $this->initEvent->getName(),
                $this->initEvent->getPayload(),
                $this->initEvent->getCallbackChannel()
            ));
        });
    }
}