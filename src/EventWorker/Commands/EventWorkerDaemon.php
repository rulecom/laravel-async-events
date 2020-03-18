<?php

namespace Rule\AsyncEvents\EventWorker\Commands;



use Illuminate\Console\Command;
use Rule\AsyncEvents\Dispatcher\Dispatcher;
use Rule\AsyncEvents\EventWorker\Worker;
use Rule\AsyncEvents\Listener\RedisListener;

class EventWorkerDaemon extends Command
{
    protected $signature = "events:work "
        . "{--poll-frequency= : frequency of the events consumtion in ms}"
        . "{--ttl= : workers lifespan in seconds}"
        . "{--channels= : channels to listen(overrides default listener)}"
        . "{--events= : events to listen(overrides default listener)}"
        . "{--group= : channel group to join. ignored if no channels or events provided}";

    protected $description = "Listen for events in configured channels";

    private $eventDispatcher;

    public function __construct(Dispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct();
    }

    public function handle()
    {
        $channelsString = $this->option('channels');
        $eventsString = $this->option('events');

        $channels = empty($channelsString) ? [] : explode(',', $channelsString);
        $events = empty($eventsString) ? [] : explode(',', $eventsString);
        $group = $this->option('group') ?: '';


        if (empty($channels) && empty($events)) {
            throw new \Exception("Listener configs are not implemented yet :( Please, specify channels and/or events to listen to");
        } else {
            $listener = new RedisListener($this->eventDispatcher);

            foreach ($channels as $channel) {
                $listener->listenChannel($channel, $group);
            }

            foreach ($events as $event) {
                $listener->listenEvent($event, $group);
            }
        }

        $ttl = $this->option('ttl') ?: -1;
        $pollFrequency = $this->option('poll-frequency') ?: 500;
        $worker = new Worker($listener, $ttl, $pollFrequency);

        $worker->run();
    }
}
