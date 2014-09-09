<?php
namespace Hart\Architect\Configuration;

use Hart\Architect\BaseAdmin;

class ArchitectActionCollection
{
    protected   $routes = array(),
                $admin;

    public function __construct(BaseAdmin $admin,$actions_configuration)
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
        foreach($actions_configuration as $name => $params)
        {
            if(!is_array($params))
            {
                $name = $params;
                $params = array();
            }

            $params['route_name_prefix'] = $this->admin->getRouteNamePrefix();

            if(!isset($params['callable']))
            {
                $params['callable'] =  get_class($this->admin)."@".$name;
            }

            $this->routes[$name] = new ArchitectAction($name,$params);
        }
    }

    public function registerRoutes()
    {
        if(count($this->routes))
        {
            $routes = $this->routes;
            \Route::group(array('prefix' => $this->admin->getRouteNamePrefix().$this->admin->getCustomActionsPathPrefix() ), function () use ($routes) {
                foreach($routes as $name => $action)
                {
                    $action->registerRoute();
                }
            });
        }
    }

}