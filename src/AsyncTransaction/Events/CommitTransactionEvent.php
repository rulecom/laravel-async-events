<?php
namespace Rule\AsyncEvents\AsyncTransaction\Events;


class CommitTransactionEvent extends TransactionEvent
{
    public const EVENT_NAME = 'transaction_commit';
}
