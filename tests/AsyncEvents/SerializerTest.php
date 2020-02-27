<?php
namespace Rule\Tests\AsyncEvents;

use PHPUnit\Framework\TestCase;
use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;
use Rule\AsyncEvents\Serializer\BaseSerializer;

class SerializerTest extends TestCase
{
    use ComparesEvents;

    public function testSerializer()
    {
        $serializer = new BaseSerializer();
        $event = new BaseAsyncEvent(
            'base_event',
            ['payload' => ['some' => ['really' => 'inherited']]],
            'channel',
            'callback'
        );

        $serialized = $serializer->serialize($event);

        $this->assertSameEvents($event, $serializer->deserialize($serialized));
    }
}
