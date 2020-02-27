<?php
namespace App\AsyncTransaction\Events;


use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;

class TransactionEvent extends BaseAsyncEvent
{
    const EVENT_NAME = "";

    public function __construct(array $payload, string $transactionId)
    {
        parent::__construct(static::EVENT_NAME, $payload, $transactionId, $transactionId);
    }
}
