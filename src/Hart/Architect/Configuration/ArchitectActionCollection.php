<?php

namespace Hart\Architect\Configuration;

use Hart\Architect\BaseAdmin;
use Illuminate\Support\Facades\Route;

/**
 * [OBSOLETE?]
 */
class ArchitectActionCollection
{
    protected $routes = array();
    protected $admin;

    public function __construct(BaseAdmin $admin, $actions_configuration)
    {
        $this->admin = $admin;
        $this->setupCustomActions($actions_configuration);
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    protected function setupCustomActions($actions_configuration)
    {
        //$this->createActionsFromArray($actions_configuration['list_actions']);
        $this->createActionsFromArray($actions_configuration['object_actions']);
    }

    protected function createActionsFromArray($actions)
    {
        foreach ($actions as $name => $params) {
            if (!is_array($params)) {
                $name = $params;
                $params = array();
            }

            $url = isset($params['url'])? $params['url'] : $name;

            $params['route_name_prefix'] = $this->admin->getRouteNamePrefix();

            if (!isset($params['callable'])) {
                $params['callable'] = get_class($this->admin) . "@" . $name;
            }

            $this->routes[$name] = new ArchitectAction($url, $name, $params);
        }
    }

    public function registerRoutes()
    {
        if (count($this->routes)) {
            $routes = $this->routes;
            Route::group(array('prefix' => $this->admin->getRouteNamePrefix() . $this->admin->getCustomActionsPathPrefix()), function () use ($routes) {
                foreach ($routes as $name => $action) {
                    $action->registerRoute();
                }
            });
        }
    }
}
