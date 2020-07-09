<?php
namespace Rule\AsyncEvents\Channel;

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
        $this->redis->rpush($this->getPrefixedKey($this->getName()), $this->serializer->serialize($event));
    }

    public function pop(): ?AsyncEvent
    {
        $serializedEvent = $this->redis->rpop($this->getPrefixedKey($this->getName()));
        return $serializedEvent ? $this->serializer->deserialize($serializedEvent) : null;
    }

    public function ack(AsyncEvent $event)
    {
        // we pull event from channel, so its already ack'ed that its gone, ie at most once delivery
        return;
    }

    public function keepAlive()
    {
        $key = $this->getKeepAliveKey();
        $this->redis->set($key, 'true', 'EX', $this->keepAliveSeconds);
    }

    public function setKeepAliveSeconds(int $sec)
    {
        $this->keepAliveSeconds = $sec;
    }

    public function getName(): string
    {
        return $this->channelName;
    }

    public function getPrefixedKey(string $key): string
    {
        return config('database.redis.options.prefix', '') . $key;
    }

    public function getKeepAliveKey()
    {
        return sprintf('%s:%s', static::KEEP_ALIVE_KEY_PREFIX, $this->getName());
    }

    private function registerSystemHandlers()
    {
        register_shutdown_function(function () {
            $this->clearChannel();
        });
    }

    private function clearChannel()
    {
        $this->redis->expire($this->getRedisKey($this->getKeepAliveKey()), 0);
        $this->redis->del($this->getRedisKey($this->getName()));
    }

    public function __destruct()
    {
        if ($this->clearOnDestruct) {
            $this->clearChannel();
        }
    }
}
