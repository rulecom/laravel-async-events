<?php
namespace Rule\AsyncEvents\EventScope;

interface EventScope
{
    const STATUS_INIT = 1;
    const STATUS_RUNNING = 2;
    const STATUS_FINISHED = 3;
    const STATUS_TIMEOUT = 4;

    public function getScopeId(): string;
    public function before(callable $initCallback);
    public function after(callable $after);
    public function run();
    public function stop();

    public function addEventCallback(string $eventName, callable $callback);
    public function addEventHandler(string $eventName, ScopeEventHandler $handler);
    public function setWorkerTtl(int $seconds);
    public function setWorkerPoll(int $ms);

    public function getStatus(): int;
}