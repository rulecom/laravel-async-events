<?php
namespace Rule\AsyncEvents\Serializer;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

interface Serializer
{
    public function serialize(AsyncEvent $event);
    public function deserialize(string $serializedEvent): AsyncEvent;
}
