<?php

namespace Hart\Architect\Filters;

class FilterCollection implements \IteratorAggregate
{
    protected $collection = array();

    public function __construct($filters)
    {
        foreach ($filters as $column => $filterType) {
            if ($filterType instanceof BaseFilter) {
                $this->collection[$column] = $filterType;
            } else {
                $this->collection[$column] = $this->setupFilterType($column, $filterType);
            }
        }
    }

    protected function setupFilterType($column, $filterType)
    {
        if (!is_array($filterType)) {
            $class = "Hart\Architect\Filters\\" . $filterType . "Filter";

            return new $class($column);
        }

        $type = $filterType['type'];
        $class = "Hart\Architect\Filters\\" . $type . "Filter";

        return new $class($column, $filterType);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->collection);
    }

    public function apply($values, $query)
    {
        foreach ($values as $column => $value) {
            if ($this->has($column) && ($value !== '')) {
                $this->getFilter($column)->apply($query, $value);
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
        return ($this->has($column)) ? $this->collection[$column] : null;
    }

    public function getForm($defaults = array(), $widget_attributes = array())
    {
        $form = array();
        foreach ($this->collection as $column => $filter) {
            $default_value = isset($defaults[$column]) ? $defaults[$column] : null;
            $current_widget_attributes = isset($widget_attributes[$column]) ? $widget_attributes[$column] : array();

            $form[$column] = $filter->getWidget($default_value, $current_widget_attributes);
        }

        return $form;
    }
}
