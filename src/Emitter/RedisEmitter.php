<?php
namespace Rule\AsyncEvents\Emitter;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\AsyncEvent\EventUtilTrait;
use Rule\AsyncEvents\Channel\RedisChannel;
use Rule\AsyncEvents\Router\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class RedisEmitter implements Emitter
{
    use EventUtilTrait;

    private $redis;
    private $router;

    public function __construct(Router $eventRouter)
    {
        $this->redis = Redis::connection();
        $this->router = $eventRouter;
    }

    public function emit(AsyncEvent $event)
    {
        $targetChannels = $this->findAllTargetChannels($event);

        array_walk($targetChannels, function (string $channelName) use ($event) {
            $channel = new RedisChannel($channelName, false);
            $channel->push($event);
        });
    }

    public function emitBulk(Collection $events)
    {
        $events->each(function (AsyncEvent $event) {
            $this->emit($event);
        });
    }

    private function findAllTargetChannels(AsyncEvent $event): array
    {
        $eventListenerChannels = $this->findActiveEventChannels($event);
        $listenerChannels = $this->findActiveListenerChannels($event);
        $callbackChannels = $this->findActiveCallbackChannels($event);

        return array_unique(array_merge($eventListenerChannels, $listenerChannels, $callbackChannels));
    }

    private function findActiveEventChannels(AsyncEvent $event): array
    {
        return $this->findAliveListenerChannels($this->getEventListenerChannelName($event->getName()));
    }

    private function findActiveCallbackChannels(AsyncEvent $event): array
    {
        return $this->findAliveListenerChannels($event->getCallbackChannel());
    }

    private function findActiveListenerChannels(AsyncEvent $event): array
    {
        $routerChannels = $this->router->getEventRoutes($event->getName());

        return array_reduce($routerChannels, function (array $carry, string $channelName) {
            return array_merge($carry, $this->findAliveListenerChannels($channelName));
        }, []);
    }

    private function findAliveListenerChannels($channelName)
    {
        $prefixLength = strlen(config('database.redis.options.prefix', '') . RedisChannel::KEEP_ALIVE_KEY_PREFIX);
        $redisKeepAliveKeysPattern = sprintf(
            '%s:%s:*',
            RedisChannel::KEEP_ALIVE_KEY_PREFIX,
            $channelName
        );

        return array_map(
            function (string $keepAliveKey) use ($prefixLength) {
                return substr($keepAliveKey, $prefixLength + 1);
            },
            $this->redis->keys($redisKeepAliveKeysPattern)
        );
    }
}
