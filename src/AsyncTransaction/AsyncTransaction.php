<?php
namespace Rule\AsyncEvents\AsyncTransaction;

use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\Emitter\Emitter;
use Rule\AsyncEvents\Listener\Listener;
use Illuminate\Support\Collection;

interface AsyncTransaction
{
    /**
     * Set event, that should be emitted right after transaction started
     * @param Collection $initialEvents
     * @return mixed
     */
    public function setInitiateEvents(Collection $initialEvents);

    /**
     * Set events list after which we would consider transaction successful and
     * call CommitCallback
     * @param Collection $eventNames
     * @return mixed
     */
    public function waitForEvents(array $eventNames);

    public function setDispatcher(Dispatcher $dispatcher);

    /**
     * Map to populate dispatcher from in form of 'event_name' => $handlerObject
     * @param array $handlersMap
     * @return mixed
     */
    public function setHandlersMap(array $handlersMap);

    /**
     * This one should be global, unless you know what you are doing.
     * @param Emitter $emitter
     * @return mixed
     */
    public function setEmitter(Emitter $emitter);

    /**
     * Inner listener, should be private for the transaction, noone else should push events there
     * @param Listener $listener
     * @return mixed
     */
    public function setListener(Listener $listener);

    /**
     * Handler to execute after all waiting events are satisfied
     * @param callable $callback
     * @return mixed
     */
    public function setCommitCallback(callable $callback);

    /**
     * Executed if transaction needs to be rolled back.
     * @param callable $callback
     * @return mixed
     */
    public function setRollbackCallback(callable $callback);

    public function run();
}
