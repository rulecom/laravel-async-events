<?php
namespace App\AsyncTransaction;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;
use Rule\AsyncEvents\Dispatcher\BaseEventDispatcher;
use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\Dispatcher\EventHandler;
use Rule\AsyncEvents\Emitter\Emitter;
use Rule\AsyncEvents\Emitter\RedisEmitter;
use Rule\AsyncEvents\Listener\Listener;
use Rule\AsyncEvents\Listener\RedisListener;
use App\AsyncTransaction\EventHandlers\TransactionEventHandler;
use App\AsyncTransaction\Events\CommitTransactionEvent;
use App\AsyncTransaction\Events\InitTransactionEvent;
use App\AsyncTransaction\Events\RollbackTransactionEvent;
use App\EventWorker\EventWorker;
use App\EventWorker\Worker;
use Illuminate\Support\Collection;

class Transaction implements AsyncTransaction
{
    /**
     * @var Collection
     */
    private $initialEvents;

    private $eventsWaitingList;

    /**
     * @var Emitter
     */
    private $emitter;

    /**
     * @var Dispatcher
     */
    private $dispatcher;


    private $handlersMap = [];

    /**
     * @var Listener
     */
    private $listener;

    /**
     * @var EventWorker
     */
    private $worker;

    private $commitCallback;

    private $rollbackCallback;

    private $transactionId;

    /**
     * Transaction execution timeout
     * @var int
     */
    private $timeout;

    /**
     * Listener polling interval
     * @var int
     */
    private $listenerInterval;

    private $commited = false;
    private $rollback = false;

    public function __construct(
        Emitter $emitter,
        Dispatcher $dispatcher,
        Listener $listener,
        int $timeout = 5,
        int $listenerInterval = 100
    ) {
        $this->transactionId = uniqid(rand());
        $this->timeout = $timeout;
        $this->listenerInterval = $listenerInterval;

        $this->setEmitter($emitter);
        $this->setDispatcher($dispatcher);
        $this->setListener($listener);
        $this->listener->listenChannel($this->transactionId);

        $this->worker = new Worker($this->listener, $this->timeout, $this->listenerInterval);
    }

    public function setInitiateEvents(Collection $initialEvents)
    {
        $this->initialEvents = $initialEvents;
    }

    public function waitForEvents(array $eventNames)
    {
        $this->eventsWaitingList = $eventNames;
    }

    public function setListener(Listener $listener)
    {
        $this->listener = $listener;
        $listener->setEventDispatcher($this->dispatcher);
    }

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function setHandlersMap(array $handlersMap)
    {
        $this->handlersMap = $handlersMap;
    }

    public function setEmitter(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function setCommitCallback(callable $callback)
    {
        $this->commitCallback = $callback;
    }

    public function setRollbackCallback(callable $callback)
    {
        $this->rollbackCallback = $callback;
    }

    public function run()
    {
        $this->setUpHandlers();
        $this->listener->run();// for init channels
        $this->emitter->emit(new InitTransactionEvent([], $this->transactionId));

        $this->worker->run();
    }

    public function stop()
    {
        $this->worker->stop();
    }

    public function commit()
    {
        $this->emitter->emit(new CommitTransactionEvent([], $this->transactionId));
    }

    public function rollback()
    {
        $this->emitter->emit(new RollbackTransactionEvent([], $this->transactionId));
    }

    private function getInitEventHandler(): EventHandler
    {
        $callback = function (AsyncEvent $event) {
            $this->emitter->emitBulk($this->initialEvents->map(function (AsyncEvent $event) {
                return new BaseAsyncEvent($event->getName(), $event->getPayload(), $event->getChannel(), $this->transactionId);
            }));
        };

        return new TransactionEventHandler($callback, $this);
    }

    private function getCommitHandler(): EventHandler
    {
        $callback = function (AsyncEvent $event) {
            $this->commited = true;
            $this->stop();

            if ($this->commitCallback) {
                call_user_func($this->commitCallback, $event);
            }
        };

        return new TransactionEventHandler($callback, $this);
    }

    private function getRollbackHandler(): EventHandler
    {
        $callback = function (AsyncEvent $event) {
            $this->rollback = true;
            $this->stop();

            if ($this->rollbackCallback) {
                call_user_func($this->rollbackCallback, $event);
            }
        };

        return new TransactionEventHandler($callback, $this);
    }

    private function wrapHandler($handler): EventHandler
    {
        $callback = function (AsyncEvent $event, Transaction $transaction) use ($handler) {
            if (is_array($this->eventsWaitingList)) {
                $revertedList = array_flip($this->eventsWaitingList);
                unset($revertedList[$event->getName()]);
                $this->eventsWaitingList = array_flip($revertedList);
            }

            if (is_callable($handler)) {
                call_user_func($handler, $event, $transaction);
            } else if ($handler instanceof EventHandler) {
                $handler->handle($event);
            }

            if ($this->checkForCommit()) {
                $this->emitter->emit(new CommitTransactionEvent([], $this->transactionId));
            }
        };

        return new TransactionEventHandler($callback, $this);
    }

    private function checkForCommit()
    {
        if (is_array($this->eventsWaitingList) && empty($this->eventsWaitingList)) {
            return true;
        }

        return false;
    }

    private function setUpHandlers()
    {
        $this->setUpDefaultHandlers();

        $this->setUpHandlerMap();
    }

    private function setUpDefaultHandlers()
    {
        $this->dispatcher->registerHandler(
            InitTransactionEvent::EVENT_NAME,
            $this->getInitEventHandler()
        );
        $this->dispatcher->registerHandler(
            CommitTransactionEvent::EVENT_NAME,
            $this->getCommitHandler()
        );
        $this->dispatcher->registerHandler(
            RollbackTransactionEvent::EVENT_NAME,
            $this->getRollbackHandler()
        );
    }

    private function setUpHandlerMap()
    {
        foreach ($this->handlersMap as $event => $handler) {
            $eventHandler = $this->wrapHandler($handler);

            $this->dispatcher->registerHandler($event, $eventHandler);
        }
    }
}
