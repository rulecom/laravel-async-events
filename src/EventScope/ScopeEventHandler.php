<?php
namespace Rule\AsyncEvents\EventScope;

use Rule\AsyncEvents\Dispatcher\EventHandler;

interface ScopeEventHandler extends EventHandler
{
    public function setScope(EventScope $scope);
}