<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/23
 * Time: 6:06 下午
 */

namespace swo\rpc;


class Rpc
{
    protected $host;
    protected $port;

    public function __construct(\Swoole\Server $server,$config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        // 监听多端口
        $server->listen($this->host,$this->port,SWOOLE_SOCK_TCP);
        $server->on('connect',[$this,'connect']);
        $server->on('receive',[$this,'receive']);
        $server->on('close',[$this,'close']);
    }

    public function connect($server,$fd){

    }

    public function receive($server,$fd,$fromId,$data){

    }

    public function close($server,$fd){

    }

}