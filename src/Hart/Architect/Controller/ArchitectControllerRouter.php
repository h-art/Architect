<?php
namespace Hart\Architect\Controller;

use Hart\Architect\BaseAdmin;
use Illuminate\Routing\ControllerInspector;
use Illuminate\Support\Facades\Route;

class ArchitectControllerRouter
{
    protected $controller = null;
    protected $controllerInspector = null;

    public function __construct(BaseAdmin $controller)
    {
        $this->controller = $controller;
    }

    public function registerRoutes($routes_domain, $routes_url_prefix)
    {

        Route::group(array('domain' => $routes_domain,'prefix' => $routes_url_prefix), function () {

            $this->registerCustomObjectActions();
            $this->registerCustomListActions();
            // only setup routing for filters if the admin has some filter
            if (count($this->controller->getFilters())) {
                Route::match(array('get', 'post'), $this->controller->getRouteNamePrefix() . '/filter', array('as' => $this->controller->getRouteNamePrefix() . '.filter', 'uses' =>  get_class($this->controller) . '@filter'));
            }

            Route::resource($this->controller->getRouteNamePrefix(), get_class($this->controller));
        });


    }

    /**
     *
     * @return Illuminate\Routing\ControllerInspector
     */
    protected function getInspector()
    {
        if (!$this->controllerInspector) {
            $this->controllerInspector = new ControllerInspector;
        }

        return $this->controllerInspector;
    }

    protected function registerInspected($route, $controller, $method, &$names)
    {
        $action = array('uses' => $controller.'@'.$method);

        // If a given controller method has been named, we will assign the name to the
        // controller action array, which provides for a short-cut to method naming
        // so you don't have to define an individual route for these controllers.
        $action['as'] = array_pull($names, $method);


        Route::{$route['verb']}($route['uri'], $action);
    }

    protected function registerCustomListActions()
    {
        $routables = $this->getInspector()->getRoutable($this->controller, $this->controller->getRouteNamePrefix());
        $names = array();
        foreach ($routables as $name => $routes) {
            $matches = array();
            /**
             * Matches will contain
             * 0 => full method (ex: getListActionExport)
             * 1 => HTTP method (ex: get )
             * 2 => route name  (ex: ListActionExport )
             * 3 => action name (ex: Export)
             */
            preg_match('/(get|post)(ListAction(.*))/', $name, $matches);

            // if is a custom action
            if (isset($matches[0])) {
                $route_configuration = array(
                    'controller_method' => $matches[0],
                    'http_method' => $matches[1],
                    'full_name' => $matches[2],
                    'short_name' => $matches[3]
                );

                $route_name = $this->controller->getRouteNamePrefix().'.'.strtolower($route_configuration['full_name']);

                $names[$route_configuration['controller_method']] = $route_name;
                $this->listActions[$route_name] = $route_configuration;

                foreach ($routes as $config) {
                    $this->registerInspected($config, get_class($this->controller), $matches[0], $names);
                }
            }
        }

    }

    protected function registerCustomObjectActions()
    {
        $routables = $this->getInspector()->getRoutable($this->controller, $this->controller->getRouteNamePrefix());
        $names = array();
        foreach ($routables as $name => $routes) {
            $matches = array();
            /**
             * Matches will contain
             * 0 => full method (ex: getObjectActionExport)
             * 1 => HTTP method (ex: get )
             * 2 => route name  (ex: ObjectActionExport )
             * 3 => action name (ex: Export)
             */
            preg_match('/(get|post)(ObjectAction(.*))/', $name, $matches);

            // if is a custom action
            if (isset($matches[0])) {
                //dd($matches);
                $route_name = strtolower($matches[2]);
                $names[$matches[0]] = $this->controller->getRouteNamePrefix().'.'.$route_name;

                foreach ($routes as $config) {
                    $edited_configuration = $config;
                    $edited_configuration['uri'] = $edited_configuration['plain'].'/{id}';
                    $this->registerInspected($edited_configuration, get_class($this->controller), $matches[0], $names);
                }
            }
        }
    }
}
