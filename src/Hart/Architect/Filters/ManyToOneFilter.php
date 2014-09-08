<?php

namespace Hart\Architect\Filters;

class ManyToOneFilter extends BaseFilter
{

    protected  $choices = array();


    /**
     * OPTIONS:
     * 'choices'=> array of choices for the <select> tag
     * 'query' => query object to fetch the choices for the <select> tag
     * 'keyMethod'=> method to use on the object to fetch the key for the <option> tag , default getKeyName()
     * 'valueMethod'=> method to use on the object to fetch the key for the <option> tag , default __toString()
     *
     * @param string $column column for the filter
     * @param array  $options [description]
     */
    public function __construct($column,$options = array())
    {
        parent::__construct($column,$options);
        $this->options = $options;
        $this->setupChoices();

    }

    public function setupChoices()
    {
        if($this->getOption('choices',false))
        {
            $this->choices = $this->getOption('choices');
        }
        else
        {
            if($query = $this->getOption('query'))
            {


                $objects = $query->get();
                $this->createChoices($objects);

            }
            else
            {
                if($model = $this->getOption('model'))
                {

                    $objects = $model::all();
                    $this->createChoices($objects);

                }
                else
                {
                    throw new \Exception("No choices!", 1);
                }


            }
        }

    }

    protected function createChoices($objects)
    {

        if($this->getOption('with_empty'))
        {
            $this->choices[''] = $this->getOption('empty_label',' ');
        }

        $keyMethod = $this->getOption('keyMethod','getKeyName');
        $valueMethod = $this->getOption('valueMethod','__toString');
        foreach($objects as $o)
        {
            $key = $o->$keyMethod();

            $this->choices[$o->$key] = $o->$valueMethod();
        }

    }

    public function getWidget($default =  null)
    {
        return \Form::Select($this->column,$this->getChoices(),$default);
    }

    public function getChoices()
    {
        return $this->choices;
    }



}