<?php
namespace Hart\Architect\Configuration;

use Illuminate\Support\Facades\Route;

class ArchitectAction
{

    protected     $action_name,
                $route_name_prefix,
                $callable,
                $method,
                $options;


    public function __construct($action_name,$options)
    {
        $this->action_name = $action_name;
        $this->options = $options;
        $this->setup();
        $this->registerRoute();
    }

    public function registerRoute()
    {
        Route::{$this->method}('/'.$this->action_name, array(
            'as'     => $this->route_name_prefix.".".$this->action_name,
            'uses'    => $this->callable
        ));
    }

    protected function setup()
    {
        $this->route_name_prefix = $this->getOption('route_name_prefix');
        $this->callable = $this->getOption('callable',false);
        $this->method = $this->getOption('method','GET');

        if(!$this->route_name_prefix)
        {
            throw new \Exception("Missing route_name_prefix parameter", 1);
        }
    }

    protected function getOption($name,$default = false)
    {
        return isset($this->options[$name])? $this->options[$name] : $default;
    }

}