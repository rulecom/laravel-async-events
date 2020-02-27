<?php
namespace App\AsyncTransaction\Events;

use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;

class InitTransactionEvent extends TransactionEvent
{
    public const EVENT_NAME = 'transaction_init';
}
