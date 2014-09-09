<?php

namespace Hart\Architect\Controller;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

use Hart\Architect\Configuration\ArchitectActionCollection;
use Hart\Architect\Filters\FilterCollection;

abstract class ArchitectController extends Controller
{
    /**
     * the eloquent model name
     * @var string
     */
    protected $eloquent_model;

    /**
     * the default query to retrieve data sets
     * @var [type]
     */
    protected $base_query;

    /**
     * collection of custom actions
     * @var array
     */
    protected $custom_actions_collection;

    protected $custom_actions_configuration = array();

    protected $filterCollection = null;

    /**
     * class constructor
     */
    public function __construct()
    {
        $this->eloquent_model = $this->getBaseClassName();
        $this->setupFilters();
        $this->setupCustomActions();

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
        $this->custom_actions_collection->registerRoutes();

        // only setup routing for filters if the admin has some filter
        if (count($this->getFilters())) {
            Route::match(array('get','post'), $this->getRouteNamePrefix().'/filter', array('as' => $this->getRouteNamePrefix().'.filter', 'uses' =>  get_class($this).'@filter'));
        }

        Route::resource($this->getRouteNamePrefix(), get_class($this));

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

    public function setupCustomActions()
    {
        $this->custom_actions_collection = new ArchitectActionCollection($this, $this->custom_actions_configuration);
    }
}
