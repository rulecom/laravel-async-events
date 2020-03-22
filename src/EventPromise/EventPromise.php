<?php
namespace Rule\AsyncEvents\EventPromise;

interface EventPromise
{
    public function wait();
    public function then(callable $callback);
    public function error(callable $callback);

    public function setResolveEvent(string $event);
    public function setErrorEvent(string $event);
}