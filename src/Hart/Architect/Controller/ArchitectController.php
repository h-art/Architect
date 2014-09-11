<?php

namespace Hart\Architect\Controller;

use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

use Hart\Architect\Filters\FilterCollection;
use Hart\Architect\Controller\ArchitectControllerRouter;

abstract class ArchitectController extends Controller
{

    /**
     * the default query to retrieve data sets
     * @var [type]
     */
    protected $baseQuery;

    protected $controllerRouter;

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
        $this->controllerRouter = new ArchitectControllerRouter($this);
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

        $this->controllerRouter->registerRoutes($routes_domain, $routes_url_prefix);
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
