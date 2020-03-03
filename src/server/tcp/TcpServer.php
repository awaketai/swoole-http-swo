<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/25
 * Time: 11:57 上午
 */

namespace swo\server\tcp;


use swo\server\Server;
use \Swoole\Server as SwooleTcpServer;

class TcpServer extends Server
{

    /**
     * @inheritDoc
     */
    protected function createServer()
    {
        // TODO: Implement createServer() method.
        $this->setHost('tcp');
        $this->swooleServer = new SwooleTcpServer($this->host,$this->port);
    }

    /**
     * @inheritDoc
     */
    protected function initEvent()
    {
        // TODO: Implement initEvent() method.
        $this->setEvent('sub',[
            'connect' => 'onConnect',
            'receive' => 'onReceive',
            'close' => 'onClose',
        ]);
    }

    public function onConnect(\Swoole\Server $server,$fd){
        echo "Client: Connect.\n";
    }

    public function onReceive(\Swoole\Server $server,$fd,$fromId,$data){
        $server->send($fd, "Server: ".$data);
    }

    public function onClose(\Swoole\Server $server,$fd){
        echo "Client: Close.\n";
    }
}