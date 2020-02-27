<?php
namespace Rule\AsyncEvents\Router;


class EventRouter implements Router
{
    private $eventMap = [];

    public function registerEventRoute(string $eventName, string $channelName)
    {
        $this->eventMap[$eventName] = isset($this->eventMap[$eventName])
            ? array_merge($this->eventMap[$eventName], [$channelName])
            : $this->eventMap[$eventName] = [$channelName];
    }

    public function getEventRoutes(string $eventName)
    {
        return $this->eventMap[$eventName] ?? [];
    }
}
