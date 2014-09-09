<?php

namespace Hart\Architect;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;

use Hart\Architect\Controller\ArchitectController;

class BaseAdmin extends ArchitectController
{

    /**
     * render the list of resources
     * @return Response
     */
    public function index()
    {
        $rows = $this->getBaseQuery()->get();

        return View::make('architect::index', [
            'controller' => $this,
            'eloquent_model' => $this->eloquent_model,
            'fields' => $this->getFields(),
            'filters' => $this->filterCollection->getForm(),
            'rows' => $rows
        ]);
    }

    /**
     * render the list of resources
     * @return Response
     */
    public function filter()
    {

        $filter_values = Input::except(['_token', '_method']);

        $rows = $this->applyFilters($filter_values)->get();

        return View::make('architect::index', [
            'controller' => $this,
            'eloquent_model' => $this->eloquent_model,
            'fields' => $this->getFields(),
            'filters' => $this->filterCollection->getForm($filter_values),
            'rows' => $rows
        ]);
    }

    /**
     * displays a single resource
     * @return Response
     */
    public function show($id)
    {
        $row = $this->getBaseQuery()->findOrFail($id);

        return View::make('architect::show', [
            'controller' => $this,
            'eloquent_model' => $this->eloquent_model,
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
        $instance = App::make($eloquent_model);
        $instance->save();

        return $this->update($instance->id);
    }

    /**
     * edit a resource
     * @return Response
     */
    public function edit($id)
    {
        $row = $this->getBaseQuery()->findOrFail($id);

        return View::make('architect::edit', [
            'controller' => $this,
            'eloquent_model' => $this->eloquent_model,
            'fields' => $this->getFields(),
            'row' => $row
        ]);
    }

    /**
     * updates an existing resource
     * @return Response
     */
    public function update($id)
    {
        $input = Input::except(['_token', '_method']);
        $eloquent_model = $this->eloquent_model;
        $row = $eloquent_model::findOrFail($id);

        foreach ($input as $key => $value) {
            // guess the relationship type
            if ( is_array($value) ) {
                if ( preg_match('/HasMany/', get_class($row->$key())) ) {
                    $related = array();
                    $related_model = get_class($row->$key()->getRelated());
                    $relation_results = $row->$key()->get();

                    // find the related models to associate
                    for ( $i = 0; $i < count($value); $i++ ) {
                        array_push($related, $related_model::find($value[$i]));
                    }

                    foreach ($relation_results as $relation_result) {
                        if ( ! in_array($relation_result, $related) ) {
                            echo $relation_result->id . ' ';
                        }
                    }

                    // call the saveMany method
                    $row->$key()->saveMany($related);
                }

                if ( preg_match('/BelongsToMany/', get_class($row->$key())) ) {
                    $row->$key()->sync($value);
                }
            }
            // assign non-relation value
            else {
                $row->$key = $value;
            }
        }

        if ( $row->save() ) {
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

        if ( $eloquent_model::destroy($id) ) {
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
     * Overwrite to customize the base query that Architect uses to retrieve data
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function getBaseQuery()
    {
        if (!$this->base_query) {
            $eloquent_model = $this->eloquent_model;
            $this->base_query = $eloquent_model::on();
        }

        return $this->base_query;
    }

    /**
     * Overwrite to customize filters
     * @return array of filters
     */
    protected function getFilters()
    {
        return array();
    }

    /**
     * Returns the prefix of the url used for custom actions
     * @return [type] [description]
     */
    public function getCustomActionsPathPrefix()
    {
        //return '/custom';
        return '';
    }

}
