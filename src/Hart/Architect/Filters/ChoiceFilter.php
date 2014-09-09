<?php

namespace Hart\Architect\Filters;

use  Hart\Architect\Filters\Exceptions\NoChoicesException;

class ChoiceFilter extends BaseFilter
{

    protected $choices = array();

    /**
     * OPTIONS:
     * 'choices'=> array of choices for the <select> tag
     * 'query' => query object to fetch the choices for the <select> tag
     * 'keyMethod'=> method to use on the object to fetch the key for the <option> tag , default getKeyName()
     * 'valueMethod'=> method to use on the object to fetch the key for the <option> tag , default __toString()
     * 'with_empty' => allow empty field. Use a string for the label
     *
     * @param string $column  column for the filter
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
        if ($this->getOption('choices',false)) {
            $empty_entry = array();
            if ($this->getOption('with_empty')) {
                $empty_entry[''] = $this->getOption('with_empty',' ');
            }
            $this->choices = array_merge($empty_entry,$this->getOption('choices'));
        } else {
            if ($query = $this->getOption('query')) {
                $objects = $query->get();
                $this->createChoices($objects);
            } else {
                if ($model = $this->getOption('model')) {
                    $objects = $model::all();
                    $this->createChoices($objects);
                } else {
                    throw new NoChoicesException("No choices!", 1);
                }
            }
        }
    }

    protected function createChoices($objects)
    {

        if ($this->getOption('with_empty')) {
            $this->choices[''] = $this->getOption('with_empty',' ');
        }

        $keyMethod = $this->getOption('keyMethod','getKeyName');
        $valueMethod = $this->getOption('valueMethod','__toString');
        foreach ($objects as $o) {
            $key = $o->$keyMethod();
            $this->choices[$o->$key] = $o->$valueMethod();
        }
    }

    public function getWidget($default = null ,$attributes = array())
    {
        return \Form::Select($this->column,$this->getChoices(),$default,$attributes);
    }

    public function getChoices()
    {
        return $this->choices;
    }

}
