<?php

namespace Hart\Architect\Filters;

class BooleanFilter extends ChoiceFilter
{
    public function __construct($column,$options = array())
    {
        $options = array_merge($options,array(
            'choices'=> array('0'=>'False/No','1'=>'True/Yes'),
            'with_empty'=> 'Both',
            'filterMethod'=>'equals'
        ));

        parent::__construct($column,$options);
        $this->options = $options;
        $this->setupChoices();

    }

}
