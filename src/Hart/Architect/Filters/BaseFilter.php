<?php

namespace Hart\Architect\Filters;

use Illuminate\Support\Facades\Form;

use Hart\Architect\Filters\Exceptions\FilterMethodNotCallableException;

abstract class BaseFilter
{

    protected   $baseQuery = null,
                $column = null,
                $options = array();

    public function __construct($column,$options = array())
    {
        $this->column = $column;
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name,$default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function getWidget($default = null ,$attributes = array())
    {
        return Form::Text($this->column,$default,$attributes);
    }

    public function apply($query,$value='')
    {
        $filterMethod = $this->getOption('filterMethod','like');

        if (is_callable(array($this,$filterMethod))) {
            $this->{$filterMethod}($query,$value);
        } else {
            throw new FilterMethodNotCallableException("Method {$this->filterMethod} is not callable", 1);
        }
    }

    public function like($query,$value='')
    {
        $query->where($this->column,'LIKE','%'.$value.'%');
    }

    public function equals($query,$value='')
    {
        $query->where($this->column,'=',$value);
    }

    public function greaterThan($query,$value='')
    {
        $query->where($this->column,'>',$value);
    }

    public function greaterOrEqualThan($query,$value='')
    {
        $query->where($this->column,'>=',$value);
    }

    public function lessThan($query,$value='')
    {
        $query->where($this->column,'<',$value);
    }

    public function lessOrEqualThan($query,$value='')
    {
        $query->where($this->column,'<=',$value);
    }

}
