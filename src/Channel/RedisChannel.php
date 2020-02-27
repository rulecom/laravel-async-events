<?php
namespace Rule\AsyncEvents\Channel;

use Rule\AsyncEvents\AsyncEvent\BaseAsyncEvent;
use Rule\AsyncEvents\Serializer\BaseSerializer;
use Illuminate\Support\Facades\Redis;
use Rule\AsyncEvents\AsyncEvent\AsyncEvent;

class RedisChannel implements Channel
{
    const KEEP_ALIVE_KEY_PREFIX = 'keep_alive';
    private $redis;
    /**
     * Basically - list key in redis
     * @var string
     */
    private $channelName;
    private $clearOnDestruct = true;
    private $serializer;
    /**
     * If key is not renewed for this amount of time - keep alive key dies and we stop pushing in this channel
     * @var int
     */
    private $keepAliveSeconds = 5;

    public function __construct($channelName, $clearOnDestruct = true)
    {
        $this->channelName = $channelName;
        $this->redis = Redis::connection();
        $this->serializer = new BaseSerializer();
        $this->clearOnDestruct = $clearOnDestruct;

        if ($clearOnDestruct) {
            $this->registerSystemHandlers();
        }
    }

    public function push(AsyncEvent $event)
    {
        $this->redis->rpush($this->channelName, $this->serializer->serialize($event));
    }

    public function pop(): ?AsyncEvent
    {
        $serializedEvent = $this->redis->rpop($this->channelName);

        return $serializedEvent ? $this->serializer->deserialize($serializedEvent) : null;
    }

    public function ack(AsyncEvent $event)
    {
        // we pull event from channel, so its already ack'ed that its gone, ie at most once delivery
        return;
    }

    public function keepAlive()
    {
        $this->redis->set($this->getKeepAliveKey(), 'true', 'EX', $this->keepAliveSeconds);
    }

    public function setKeepAliveSeconds(int $sec)
    {
        $this->keepAliveSeconds = $sec;
    }

    public function getName(): string
    {
        return $this->channelName;
    }

    public function getKeepAliveKey()
    {
        return sprintf('%s:%s', static::KEEP_ALIVE_KEY_PREFIX, $this->getName());
    }

    private function initChannel(string $channelName)
    {
        if (!$this->redis->exists($channelName)) {
            // super hacky shit to make channel alive as Redis deletes keys with empty lists :(
            // also thats why its stack, not queue - ie LIFO
            // TODO: make some kind or reserve keys to manage appropriate keys for push and pop
            //$this->push(new BaseAsyncEvent('keep_alive', []));
        }
    }

    private function registerSystemHandlers()
    {
        register_shutdown_function(function () {
            $this->clearChannel();
        });

        /*if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () {
                $this->clearChannel();
            });
        }*/
    }

    private function clearChannel()
    {
        $this->redis->del($this->getName());
    }

    public function __destruct()
    {
        if ($this->clearOnDestruct) {
            $this->clearChannel();
        }
    }
}
