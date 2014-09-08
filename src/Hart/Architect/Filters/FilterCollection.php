<?php

namespace Hart\Architect\Filters;

class FilterCollection implements \IteratorAggregate
{
    protected $collection = array();


    public function __construct($filters)
    {

        foreach ($filters as $column => $filterType)
        {
            if ( $filterType instanceof BaseFilter )
            {
                $this->collection[$column] = $filterType;
            }
            else
            {

                $this->collection[$column] = $this->setupFilterType($column,$filterType);
            }

        }
    }

    protected function setupFilterType($column,$filterType)
    {

        if(!is_array($filterType))
        {
            $class = "Hart\Architect\Filters\\".$filterType."Filter";
            return new $class($column);
        }

        $type = $filterType['type'];
        $model = $filterType['model'];
        $query = $filterType['query'];

        $class = "Hart\Architect\Filters\\".$type."Filter";

        return new $class($column,$filterType);



    }
    public function getIterator()
    {
        return new ArrayIterator($this->collection);
    }

    public function apply($values,$query)
    {

        foreach($values as $column => $value)
        {

            if($this->has($column) && $value)
            {
                $this->getFilter($column)->like($query,$value);
            }
        }

        return $query;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function has($column)
    {
        return isset($this->collection[$column]);
    }

    public function getFilter($column)
    {
        return ($this->has($column))? $this->collection[$column] : null;
    }

    public function getForm($defaults = array())
    {
        $form = array();
        foreach($this->collection as $column => $filter)
        {
            if(isset($defaults[$column]))
            {
                $form[$column] = $filter->getWidget($defaults[$column]);
            }
            else
            {
                $form[$column] = $filter->getWidget();
            }

        }

        return $form;
    }
}