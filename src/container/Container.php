<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/22
 * Time: 11:59 上午
 */

namespace swo\container;


use swo\exception\ObjectNotFoundException;

class Container
{
    protected static $instance;

    protected $bindings = []; //
    protected $instances = [];

    /**
     * @param string $abstract 容器绑定标识
     * @param callable|object $object 实例对象或者闭包
     */
    public function bind($abstract,$object){

        $this->bindings[$abstract] = $object;
    }

    /**
     * 从容器中解析实例对象或者闭包
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws ObjectNotFoundException
     */
    public function make($abstract,$parameters = []){

        return $this->resolve($abstract,$parameters);
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws ObjectNotFoundException
     */
    public function resolve($abstract,$parameters = []){

        if(isset($this->instances[$abstract])){
            return $this->instances[$abstract];
        }
        if(!$this->has($abstract)){
            throw new ObjectNotFoundException('The container object not found',500);
        }
        $object = $this->bindings[$abstract];
        if($object instanceof  \Closure){
            return $object();
        }
        return $this->instances[$abstract] = (is_object($object)) ? $object : new $object(...$parameters);
    }

    public function has($abstract){

        return isset($this->bindings[$abstract]);
    }

    public static function getInstance(){
        if(is_null(static::$instance)){
            static::$instance = new static;
        }
        return static::$instance;
    }

    public static function setInstance($container = null){

        return static::$instance = $container;
    }
}