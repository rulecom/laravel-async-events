<?php
namespace Rule\AsyncEvents\Emitter;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Illuminate\Support\Collection;

interface Emitter
{
    /**
     * Emit for all subscribed consumers and event specific channels
     * @param AsyncEvent $event
     * @return mixed
     */
    public function emit(AsyncEvent $event);
    public function emitBulk(Collection $events);
}
