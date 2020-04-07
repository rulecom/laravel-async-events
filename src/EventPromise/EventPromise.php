<?php
namespace Rule\AsyncEvents\EventPromise;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\EventScope\EventScope;

interface EventPromise
{
    /**
     * Event, from which we start our scope
     */
    public function promise(AsyncEvent $event);

    /**
     * If resolve event had been received
     */
    public function resolve(callable $callback, ?string $resolveEvent): self;

    /**
     * If reject event was received or timeout had passed
     */
    public function reject(callable $callback, ?string $rejectEvent): self;

    /**
     * Run promise
     */
    public function wait(int $timeoutSeconds);

    public function getScope(): EventScope;
}