<?php
namespace Rule\AsyncEvents\EventWorker;


use Rule\AsyncEvents\Listener\Listener;

interface EventWorker
{
    public function setListener(Listener $listener);

    public function setTTL(int $seconds);

    public function setPollFrequency(int $milliseconds);

    public function run();

    public function stop();
}
