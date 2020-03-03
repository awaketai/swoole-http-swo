<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/22
 * Time: 3:26 下午
 */
use swo\foundation\Application;
use swo\console\Input;
if(!function_exists('app')){
    function app($name = null){

        if(!$name){
            return Application::getInstance();
        }
        return Application::getInstance()->make($name);
    }
}

if(!function_exists('dd')){
    function dd($message,$desc = null){

        Input::info($message,$desc);
    }
}