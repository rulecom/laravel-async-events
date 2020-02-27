<?php
namespace Rule\AsyncEvents\Channel;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

interface Channel
{
    public function push(AsyncEvent $event);
    public function pop(): ?AsyncEvent;
    public function ack(AsyncEvent $event);
}
