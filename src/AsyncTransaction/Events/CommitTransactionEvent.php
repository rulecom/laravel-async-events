<?php
namespace App\AsyncTransaction\Events;


class CommitTransactionEvent extends TransactionEvent
{
    public const EVENT_NAME = 'transaction_commit';
}
