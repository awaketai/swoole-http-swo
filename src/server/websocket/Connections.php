<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/24
 * Time: 6:57 下午
 */

namespace swo\server\websocket;


class Connections
{
    /**
     * 客户端连接信息
     * [
     *  'fd' => [
     *      'path' => '',
     *      ]
     * ]
     * @var array
     */
    protected static $connections = [];

    public static function init($fd,$path){

        self::$connections[$fd]['path'] = $path;
    }

    public static function get($fd){

        return self::$connections[$fd];
    }

    public static function del($fd){

        unset(self::$connections[$fd]);
    }

}