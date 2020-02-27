<?php
namespace Rule\AsyncEvents\Router;


use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\Channel\Channel;

/**
 * This might be complete isolated service for event managing
 * Interface Router
 * @package Rule\AsyncEvents\Router
 */
interface Router
{
    /**
     * Register channels to distribute event into
     * @param string $eventName
     * @param string $channelName
     * @return mixed
     */
    public function registerEventRoute(string $eventName, string $channelName);

    /**
     * Get event channels list
     * @param AsyncEvent $event
     * @return mixed
     */
    public function getEventRoutes(string $eventName);
}
