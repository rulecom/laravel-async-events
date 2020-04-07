<?php


namespace Rule\AsyncEvents\AsyncEvent;


class BaseAsyncEvent implements AsyncEvent
{
    protected $eventName;
    protected $payload;
    protected $callbackChannel;

    public function __construct(string $name, array $payload, string $callbackChannel = '')
    {
        $this->eventName = $name;
        $this->payload = $payload;
        $this->callbackChannel = $callbackChannel;
    }

    public function getName(): string
    {
        return $this->eventName;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    public function getCallbackChannel(): string
    {
        return $this->callbackChannel;
    }

    public function setCallbackChannel(string $channel)
    {
        $this->callbackChannel = $channel;
    }
}
