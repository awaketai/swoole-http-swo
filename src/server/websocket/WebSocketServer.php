<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/24
 * Time: 11:13 上午
 */

namespace swo\server\websocket;

// web socket server 继承自swoole\http\server
use http\Env\Request;
use swo\server\http\HttpServer;
use \Swoole\WebSocket\Server as SwooleWebSocketServer;

class WebSocketServer extends HttpServer
{
    public function createServer()
    {
        $this->setHost('websocket');
        $this->swooleServer = new SwooleWebSocketServer($this->host,$this->port);
    }

    public function initEvent()
    {
        $event = [
            'request' => 'onRequest',
            'open' => 'onOPen',
            'message' => 'onMessage',
            'close' => 'onClose',
        ];
        // 是否自定义websocket握手事件
        $isHandshake = app('config')->get('server.ws.is_handshake');
        if($isHandshake === 1){
            $event['handshake'] = 'onHandshake';
        }
        $this->setEvent('sub',$event);
    }

    //监听WebSocket连接打开事件
    public function onOpen(\Swoole\WebSocket\Server $ws, $request) {
        dd($request->server['path_info'],'path-info');
        // 连接信息保存
        Connections::init($request->fd,$request->server['path_info']);
        $res = app('route')->setNamespaceMark('WebSocket')->setMethod('open')->match($request->server['path_info'],[$ws,$request]);
        var_export($res);
//        var_dump($request->fd, $request->get, $request->server);
//        $ws->push($request->fd, "hello, welcome\n");
    }

    //监听WebSocket消息事件
    public function onMessage(\Swoole\WebSocket\Server $ws, $frame) {
        // 获取对应连接信息，发送消息
        $path = Connections::get($frame->fd)['path'];
        $res = app('route')->setNamespaceMark('WebSocket')->setMethod('message')->match($path,[$ws,$frame]);
        echo "Message: {$frame->data}\n";
        $ws->push($frame->fd, "server: {$frame->data}");
    }

    //监听WebSocket连接关闭事件
    public function onClose(\Swoole\WebSocket\Server $ws, $fd) {
        $path = Connections::get($fd)['path'];
        $res = app('route')->setNamespaceMark('WebSocket')->setMethod('close')->match($path,[$ws,$fd]);
        echo "client-{$fd} is closed\n";
        Connections::del($fd);
    }

    public function onHandshake(\Swoole\Http\Request $request, \Swoole\Http\Response $response){

        echo 6566;
        $this->app->make('event')->trigger('ws.hand',[$this,$request,$response]);
    }

}