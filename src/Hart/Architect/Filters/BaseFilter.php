<?php

namespace Hart\Architect\Filters;


use Illuminate\Support\Facades\Form;

abstract class BaseFilter
{


    protected $baseQuery = null;
    protected $column = null;

    public function __construct($column)
    {
        $this->column = $column;
    }

    public function getWidget($default =  null)
    {
        return Form::Text($this->column,$default);
    }

    public function equals($query,$value='')
    {
        $query->where($this->column,'=',$value);
    }

    public function like($query,$value='')
    {
        $query->where($this->column,'LIKE',$value);
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