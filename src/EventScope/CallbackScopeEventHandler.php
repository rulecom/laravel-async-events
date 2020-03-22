<?php
namespace Rule\AsyncEvents\EventScope;

use Rule\AsyncEvents\AsyncEvent\AsyncEvent;
use Rule\AsyncEvents\Dispatcher\CallbackEventHandler;

class CallbackScopeEventHandler extends CallbackEventHandler implements ScopeEventHandler
{
    private $scope;

    public function setScope(EventScope $scope)
    {
        $this->scope = $scope;
    }

    public function handle(AsyncEvent $event)
    {
        call_user_func($this->callback, $event, $this->scope);
    }
}