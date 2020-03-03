<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/28
 * Time: 1:55 下午
 */

namespace swo\event;

/**
 * key => val
 * 事件标识 => 事件执行的回调函数
 * Class Event
 * @package swo\event
 */
class Event
{
    protected $events = [];

    /**
     * @param string $event 事件名称
     * @param callable $callback 事件触发的回调函数
     */
    public function register($event,$callback){
        $event = strtolower($event);
        $this->events[$event] = ['callback' => $callback];
    }

    /**
     * 触发事件
     * @param string $event 事件名称
     * @param array $param
     * @return bool
     */
    public function trigger($event,$param = []){
        $event = strtolower($event);
        if(isset($this->events[$event])){
            ($this->events[$event]['callback'])(...$param);
        }
        // 事件不存在
        return false;
    }

    public function getEvents($event = null){

        return empty($event) ? $this->events : $this->events[$event];
    }
}