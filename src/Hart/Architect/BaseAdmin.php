<?php

namespace Hart\Architect;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller;

class BaseAdmin extends Controller
{
    /**
     * the eloquent model name
     * @var string
     */
    private $eloquent_model;

    function __construct()
    {
        $this->eloquent_model = $this->getBaseClassName();
    }

    /**
     * render the list of resources
     * @return Response
     */
    public function index()
    {
        $eloquent_model = $this->eloquent_model;
        $rows = $eloquent_model::all();

        return View::make('architect::index', [
            'controller' => $this,
            'eloquent_model' => $eloquent_model,
            'fields' => $this->getFields(),
            'rows' => $rows
        ]);
    }

    /**
     * displays a single resource
     * @return Response
     */
    public function show($id)
    {
        $eloquent_model = $this->eloquent_model;
        $row = $eloquent_model::findOrFail($id);

        return View::make('architect::show', [
            'controller' => $this,
            'eloquent_model' => $eloquent_model,
            'fields' => $this->getFields(),
            'row' => $row
        ]);
    }

    /**
     * shows creation form for one resource
     * @return Response
     */
    public function create()
    {
        return View::make('architect::create', [
            'controller' => $this,
            'eloquent_model' => $this->eloquent_model,
            'fields' => $this->getFields()
        ]);
    }

    /**
     * stores newly created resource
     * @return Response
     */
    public function store()
    {
        $eloquent_model = $this->eloquent_model;
        $instance = new $eloquent_model();
        $instance->save();

        return $this->update($instance->id);
    }

    /**
     * edit a resource
     * @return Response
     */
    public function edit($id)
    {
        $eloquent_model = $this->eloquent_model;
        $row = $eloquent_model::findOrFail($id);

        return View::make('architect::edit', [
            'controller' => $this,
            'eloquent_model' => $eloquent_model,
            'fields' => $this->getFields(),
            'row' => $row
        ]);
    }

    /**
     * updates a resource
     * @return Response
     */
    public function update($id)
    {
        $input = Input::except(['_token', '_method']);
        $eloquent_model = $this->eloquent_model;
        $row = $eloquent_model::findOrFail($id);

        foreach ( $input as $key => $value )
        {
            // guess the relationship
            if ( is_array($value) )
            {
                if ( preg_match('/HasMany/', get_class($row->$key())) )
                {
                    $related = array();
                    $related_model = get_class($row->$key()->getRelated());
                    $relation_results = $row->$key()->get();

                    // find the related models to associate
                    for ( $i = 0; $i < count($value); $i++ )
                    {
                        array_push($related, $related_model::find($value[$i]));
                    }

                    foreach ( $relation_results as $relation_result )
                    {
                        if ( ! in_array($relation_result, $related) )
                        {
                            echo $relation_result->id . ' ';
                        }
                    }

                    // call the saveMany method
                    $row->$key()->saveMany($related);
                }

                if ( preg_match('/BelongsToMany/', get_class($row->$key())) )
                {
                    $row->$key()->sync($value);
                }
            }
            // assign non-relation value
            else
            {
                $row->$key = $value;
            }
        }

        if ( $row->save() )
        {
            return Redirect::route(strtolower($eloquent_model) . '.index');
        }
    }

    /**
     * destroy a single resource
     * @return Response
     */
    public function destroy($id)
    {
        $eloquent_model = $this->eloquent_model;

        if ( $eloquent_model::destroy($id) )
        {
            return Redirect::route(strtolower($eloquent_model) . '.index');
        }
    }

    /**
     * get the list of fields handled by Architect
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * render the label for the field
     * @param  string $field_name the field name as the one in Eloquent model
     * @return string
     */
    public function renderLabel($field_name)
    {
        if ( isset($this->labels[$field_name]) )
        {
            // return the label specified by the user
            return $this->labels[$field_name];
        }

        // try to prettify label name
        return ucfirst(str_replace('_', ' ', $field_name));
    }

    /**
     * render a field in index action
     * @param  string $action_name the action name 'index', 'show', etc.
     * @param  Object $row the Eloquent row
     * @param  string $field_name the name of the field
     * @param  mixed $field the value of the field
     * @return mixed
     */
    public function renderField($action_name, $row, $field_name, $field)
    {
        if ( method_exists($this, $action_name . ucfirst($field_name)) )
        {
            $method_name = $action_name . ucfirst($field_name);
            return $this->$method_name($row, $field_name, $field);
        }

        switch ( $action_name )
        {
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
        Route::resource(strtolower($this->getBaseClassName()), get_class($this));
    }
}