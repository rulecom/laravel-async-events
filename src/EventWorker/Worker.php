<?php
namespace Rule\AsyncEvents\EventWorker;


use Rule\AsyncEvents\Listener\Listener;
use Rule\AsyncEvents\EventWorker\Exceptions\EventProcessingException;

class Worker implements EventWorker
{
    private $listener;
    private $shouldStop;
    private $startAt;
    private $ttl;
    private $pollFrequency;

    public function __construct(Listener $listener, int $ttl = -1, int $pollFrequency = 1000)
    {
        $this->listener = $listener;
        $this->ttl = $ttl;
        $this->pollFrequency = $pollFrequency;
    }

    public function setListener(Listener $listener)
    {
        $this->listener = $listener;
    }

    public function setTTL(int $seconds)
    {
        $this->ttl = $seconds;
    }

    public function setPollFrequency(int $milliseconds)
    {
        $this->pollFrequency = $milliseconds;
    }

    public function run()
    {
        $this->startAt = time();
        $this->shouldStop = false;

        while (!$this->shouldStop) {
            $eventsCount = 0;
            try {
                $eventsCount = $this->listener->run();
            } catch (\Throwable $e) {
                throw new EventProcessingException("Failed to process job", 0, $e);

            }

            if ($eventsCount == 0 ) {
                usleep($this->pollFrequency * 1000);
            }

            $this->shouldStop = $this->checkShouldStop();
        }
    }

    private function checkShouldStop(): bool
    {
        if ($this->ttl < 0) {
            return false;
        }

        if ((!$this->shouldStop && $this->startAt + $this->ttl <= time()) || $this->shouldStop) {
            return true;
        }

        return false;
    }

    public function stop()
    {
        $this->shouldStop = true;
    }
}
