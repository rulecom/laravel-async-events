<?php
namespace Rule\AsyncEvents\Serializer;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;

class BaseSerializer implements Serializer
{
    const NAME_KEY = 'name';
    const PAYLOAD_KEY = 'payload';
    const CHANNEL_KEY = 'channel';
    const CALLBACK_CHANNEL_KEY = 'callback_channel';

    public function serialize(AsyncEvent $event)
    {
        return json_encode([
          self::NAME_KEY => $event->getName(),
          self::PAYLOAD_KEY => $event->getPayload(),
          self::CHANNEL_KEY => $event->getChannel(),
          self::CALLBACK_CHANNEL_KEY => $event->getCallbackChannel()
        ]);
    }

    public function deserialize(string $serializedEvent): AsyncEvent
    {
        $eventArray = json_decode($serializedEvent, true);
        return new BaseAsyncEvent(
            $eventArray[self::NAME_KEY],
            $eventArray[self::PAYLOAD_KEY],
            $eventArray[self::CHANNEL_KEY],
            $eventArray[self::CALLBACK_CHANNEL_KEY]
        );
    }

}
