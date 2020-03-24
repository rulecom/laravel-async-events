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
use Rule\AsyncEvents\Dispatcher\LocalDispatcher;
use Rule\AsyncEvents\EventWorker\Commands\EventWorkerDaemon;
use Rule\AsyncEvents\Listener\LocalListener;

class AsyncEventServiceProvider extends ServiceProvider
{
    public function register()
    {
        // global stuff
        $this->commands([
            EventWorkerDaemon::class
        ]);

        $this->app->singleton(Dispatcher::class, BaseEventDispatcher::class);
        $this->app->singleton(Router::class, EventRouter::class);
        $this->app->singleton(Emitter::class, RedisEmitter::class);
        $this->app->singleton(Listener::class, RedisListener::class);

        $this->app->bind(LocalDispatcher::class, BaseEventDispatcher::class);
        $this->app->bind(LocalListener::class, RedisListener::class);
    }
}
