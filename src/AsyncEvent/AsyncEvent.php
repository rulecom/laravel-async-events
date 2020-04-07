<?php
namespace Rule\AsyncEvents\AsyncEvent;


interface AsyncEvent
{
    /**
     * Get name of the event. Could be used by routing
     * @return string
     */
    public function getName(): string;

    /**
     * serializable representation or the event payload
     * @return array
     */
    public function getPayload(): array;

    /**
     * @param array $payload
     * @return void
     */
    public function setPayload(array $payload);

    /**
     * Request - Response queue. Consumer should emit and/or double produced events
     * by appropriate routing
     * @return string
     */
    public function getCallbackChannel(): string;

    public function setCallbackChannel(string $channel);
}
