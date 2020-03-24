<?php
namespace Rule\AsyncEvents\AsyncTransaction;


use Rule\AsyncEvents\Dispatcher\BaseEventDispatcher;
use Rule\AsyncEvents\Emitter\Emitter;
use Rule\AsyncEvents\Listener\RedisListener;
use Illuminate\Support\Collection;
use Rule\AsyncEvents\Dispatcher\LocalDispatcher;
use Rule\AsyncEvents\Listener\LocalListener;

class TransactionFactory
{
    private $emitter;

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function make(
        Collection $initialEvents,
        array $handlersMap,
        ?array $waitForEvents,
        ?callable $commitCallback,
        ?callable $rollbackCallback,
        int $timeout = 5,
        int $pollInterval = 100
    ) {
        $dispatcher = app(LocalDispatcher::class);
        $listener = app(LocalListener::class);

        $transaction = new Transaction(
            $this->emitter,
            $dispatcher,
            $listener,
            $timeout,
            $pollInterval);

        $transaction->setInitiateEvents($initialEvents);
        $transaction->setHandlersMap($handlersMap);
        if ($commitCallback) {
            $transaction->setCommitCallback($commitCallback);
        }

        if ($rollbackCallback) {
            $transaction->setRollbackCallback($rollbackCallback);
        }

        if ($waitForEvents) {
            $transaction->waitForEvents($waitForEvents);
        }

        return $transaction;
    }
}
