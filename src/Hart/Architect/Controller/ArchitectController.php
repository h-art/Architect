<?php

namespace Hart\Architect\Controller;

use Illuminate\Routing\Controller;
use Illuminate\Routing\ControllerInspector;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

use Hart\Architect\Filters\FilterCollection;

abstract class ArchitectController extends Controller
{

    /**
     * the default query to retrieve data sets
     * @var [type]
     */
    protected $baseQuery;

    protected $controllerInspector;

    /**
     * the eloquent model name
     * @var string
     */
    protected $eloquentModel;
    protected $filterCollection = null;

    protected $listActions = array();

    /**
     * class constructor
     */
    public function __construct()
    {
        $this->eloquentModel = $this->getBaseClassName();
        $this->setupFilters();
    }

    /**
     * render the label for the field or column
     * @param  string $field_name the field name as the one in Eloquent model
     * @return string
     */
    public function renderLabel($field_name)
    {
        if (isset($this->labels[$field_name])) {
            // return the label specified by the user
            return $this->labels[$field_name];
        }

        // try to prettify label name
        return ucfirst(str_replace('_', ' ', $field_name));
    }

    /**
     * method to render a field.
     * Rendering depends on the action method. If a valid method is found
     * in user generated admin class, then that method is called.
     * @param  string $action_name the action name 'index', 'show', 'edit', etc.
     * @param  Object $row         the whole Eloquent row as comes from the database
     * @param  string $field_name  the name of the field or column
     * @param  mixed  $field       the value of the field or column
     * @return mixed
     */
    public function renderField($action_name, $row, $field_name, $field)
    {
        // check if <action><Fieldname>() method exsists, and if so, call it
        if (method_exists($this, $action_name . ucfirst($field_name))) {
            $method_name = $action_name . ucfirst($field_name);

            return $this->$method_name($row, $field_name, $field);
        }

        switch ($action_name) {
            case 'create':
                return View::make('architect::inputs/field_create', ['field_name' => $field_name]);

            case 'edit':
                return View::make('architect::inputs/field_edit', [
                'field_name' => $field_name,
                'field' => $field,
            ]);

            default:
                return $field;
        }
    }

    /**
     * get the full class name (with no namespace)
     * @return string
     */
    public function getClassName()
    {
        $full_class_name = get_class($this);
        $class_name_parts = explode('\\', $full_class_name);

        return $class_name_parts[count($class_name_parts) - 1];
    }

    /**
     * get the base name class (without the "Admin" substring)
     * @return [type]
     */
    public function getBaseClassName()
    {
        return str_replace('Admin', '', $this->getClassName());
    }

    /**
     * register the routes
     * @return void
     */
    public function registerRoutes()
    {
        $routes_domain = Config::get('architect::routing.domain', false);
        $routes_url_prefix = Config::get('architect::routing.url_prefix', false);

        Route::group(array('domain' => $routes_domain,'prefix' => $routes_url_prefix), function () {
            $this->registerCustomObjectActions();
            $this->registerCustomListActions();

            // only setup routing for filters if the admin has some filter
            if (count($this->getFilters())) {
                Route::match(array('get', 'post'), $this->getRouteNamePrefix() . '/filter', array('as' => $this->getRouteNamePrefix() . '.filter', 'uses' =>  get_class($this) . '@filter'));
            }

            Route::resource($this->getRouteNamePrefix(), get_class($this));
        });

    }

    protected function registerCustomListActions()
    {
        $routables = $this->getInspector()->getRoutable($this, $this->getRouteNamePrefix());
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

                $route_name = $this->getRouteNamePrefix().'.'.strtolower($route_configuration['full_name']);

                $names[$route_configuration['controller_method']] = $route_name;
                $this->listActions[$route_name] = $route_configuration;

                foreach ($routes as $config) {
                    $this->registerInspected($config, get_class($this), $matches[0], $names);
                }
            }
        }

    }

    protected function registerCustomObjectActions()
    {
        $routables = $this->getInspector()->getRoutable($this, $this->getRouteNamePrefix());
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
                $names[$matches[0]] = $this->getRouteNamePrefix().'.'.$route_name;

                foreach ($routes as $config) {
                    $edited_configuration = $config;
                    $edited_configuration['uri'] = $edited_configuration['plain'].'/{id}';
                    $this->registerInspected($edited_configuration, get_class($this), $matches[0], $names);
                }
            }
        }
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


    public function getRouteNamePrefix()
    {
        return strtolower($this->getBaseClassName());
    }

    protected function setupFilters()
    {
        $this->filterCollection = new FilterCollection($this->getFilters());
    }

    protected function getFiltersForm()
    {
        return $this->filterCollection->getForm();
    }

    protected function applyFilters($values)
    {
        return $this->filterCollection->apply($values, $this->getBaseQuery());
    }

    protected function getListActions()
    {
        return $this->listActions;
    }
}
