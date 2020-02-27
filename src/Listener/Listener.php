<?php


namespace Rule\AsyncEvents\Listener;


use Rule\AsyncEvents\Channel\Channel;
use Rule\AsyncEvents\Dispatcher\Dispatcher;

interface Listener
{
    /**
     * Create subscription channel for event channel
     * @param string $channelName
     * @param string $listenerId
     * @return mixed
     */
    public function listenChannel(string $channelName, string $listenerId = '');

    /**
     * Create subscription channel for specific event
     * @param string $eventName
     * @param string $listenerId
     * @return mixed
     */
    public function listenEvent(string $eventName, string $listenerId = '');

    public function setEventDispatcher(Dispatcher $dispatcher);

    /**
     * Run listener cycle
     */
    public function run();
}
