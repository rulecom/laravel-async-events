<?php
namespace Rule\AsyncEvents;


use Rule\AsyncEvents\Dispatcher\BaseEventDispatcher;
use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\Emitter\Emitter;
use Rule\AsyncEvents\Emitter\RedisEmitter;
use Rule\AsyncEvents\Listener\Listener;
use Rule\AsyncEvents\Listener\RedisListener;
use Rule\AsyncEvents\Router\EventRouter;
use Rule\AsyncEvents\Router\Router;
use Illuminate\Support\ServiceProvider;

class AsyncEventServiceProvider extends ServiceProvider
{
    public function register()
    {
        // global stuff
        $this->app->singleton(Dispatcher::class, BaseEventDispatcher::class);
        $this->app->singleton(Router::class, EventRouter::class);
        $this->app->singleton(Emitter::class, RedisEmitter::class);

        $this->app->bind(Listener::class, RedisListener::class);
    }
}
