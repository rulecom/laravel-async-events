<?php
namespace App\AsyncTransaction\EventHandlers;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\Dispatcher\EventHandler;
use App\AsyncTransaction\Transaction;

class TransactionEventHandler implements EventHandler
{
    private $callback;
    private $transaction;
    public function __construct(callable $callback, Transaction $transaction)
    {
        $this->callback = $callback;
        $this->transaction = $transaction;
    }

    public function handle(AsyncEvent $event)
    {
        call_user_func($this->callback, $event, $this->transaction);
    }

}
