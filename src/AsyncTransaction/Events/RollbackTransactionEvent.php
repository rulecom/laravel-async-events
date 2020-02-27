<?php
namespace App\AsyncTransaction\Events;


class RollbackTransactionEvent extends TransactionEvent
{
    public const EVENT_NAME = "transaction_rollback";
}
