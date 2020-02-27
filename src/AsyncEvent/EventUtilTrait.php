<?php
namespace Rule\AsyncEvents\AsyncEvent;


trait EventUtilTrait
{
    private function getEventListenerChannelName(string $eventName): string
    {
        return 'event_listener_' . $eventName;
    }
}
