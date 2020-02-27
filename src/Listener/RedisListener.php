<?php


namespace Rule\AsyncEvents\Listener;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\AsyncEvent\EventUtilTrait;
use Rule\AsyncEvents\Channel\RedisChannel;
use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\Listener\Exceptions\AlreadyListeningChannel;
use Rule\AsyncEvents\Listener\Exceptions\NoDispatcherRegistered;
use Illuminate\Support\Facades\Redis;

class RedisListener implements Listener
{
    use EventUtilTrait;

    private $channels;
    private $eventDispatcher;
    private $redis;

    public function __construct(?Dispatcher $eventDispatcher)
    {
        $this->channels = [];
        $this->eventDispatcher = $eventDispatcher;
        $this->redis = Redis::connection();
    }

    /**
     * Create uniq listener channel for consuming channel events.
     * @param string $channelName
     * @param string $listenerGroup Use if you need to use couple of processes as one consumer(ie each message would be consumed only once from the channel)
     * @return mixed|void
     */
    public function listenChannel(string $channelName, string $listenerGroup = '')
    {
        $this->assertNotListeningChannel($channelName);
        $keyName = $this->getRedisKeyByChannelName($channelName, $listenerGroup);
        $this->bootstrapChannel($keyName, empty($listenerGroup));
    }

    public function listenEvent(string $eventName, string $listenerGroup = '')
    {
        $channelName = $this->getEventListenerChannelName($eventName);
        $this->assertNotListeningChannel($channelName);
        $keyName = $this->getRedisKeyByChannelName($channelName, $listenerGroup);
        $this->bootstrapChannel($keyName, empty($listenerGroup));
    }

    public function run()
    {
        if (!$this->eventDispatcher) {
            throw new NoDispatcherRegistered("No dispatcher registered for listener!");
        }

        $events = array_reduce($this->channels, function (array $carry, RedisChannel $channel) {
            // renew channel
            $channel->keepAlive();
            $currentChannelEvent = $channel->pop();

            if (!is_null($currentChannelEvent)) {
                $carry[] = $currentChannelEvent;
            }

            return $carry;
        }, []);

        array_walk($events, function (AsyncEvent $event) {
            try {
                $this->eventDispatcher->dispatch($event);
            } catch (\Throwable $e) {
                // just report error to not block further dispatches
                report($e);
            }
        });

        return count($events);
    }

    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    private function assertNotListeningChannel(string $channelName)
    {
        array_walk($this->channels, function (RedisChannel $channel) use ($channelName) {
            if (strpos($channel->getName(), $channelName) !== false) {
                throw new AlreadyListeningChannel("Already listening channel: " . $channelName);
            }
        });
    }

    private function bootstrapChannel(string $keyName, bool $isGroupChannel)
    {
        $this->channels[] = new RedisChannel($keyName, $isGroupChannel);
    }

    private function getRedisKeyByChannelName(string $channelName, string $listenerGroup): string
    {
        return $channelName . ':' . ($listenerGroup ? $listenerGroup : uniqid(rand(), true));
    }
}
