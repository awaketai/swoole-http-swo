<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/28
 * Time: 2:40 下午
 */

namespace swo\event;


use swo\foundation\Application;

abstract class Listener
{
    protected $name = 'listener';

    protected $app;
    public abstract function handler();

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    public function getName(){

        return $this->name;
    }

}