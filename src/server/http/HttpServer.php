<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/18
 * Time: 11:43 下午
 */

namespace swo\server\http;


use swo\message\http\Request;
use swo\server\Server;
use Swoole\Http\Server as SwooleHttpServer;

class HttpServer extends Server
{

    /**
     * @inheritDoc
     */
    protected function createServer()
    {
        $this->setHost('http');
        // TODO: Implement createServer() method.
        dd('http:'.$this->host.':'.$this->port);
        $this->swooleServer = new SwooleHttpServer($this->host,$this->port);
    }

    /**
     * @inheritDoc
     */
    protected function initEvent()
    {
        // TODO: Implement initEvent() method.
        // 设置子类回调
        $this->setEvent('sub',[
            'request' => 'onRequest'
        ]);
    }

    public function onRequest($request,$response){
        $uri = $request->server['request_uri'];
        if ($uri == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return;
        }

        $httpRequest = Request::init($request);
        // 获取请求方法和uri
        // 设置当前请求方式,执行请求，并返回结果
        $res = app('route')->setNamespaceMark('Http')->setMethod($httpRequest->getMethod())->match($httpRequest->getUriPath());

        // 响应的封装处理 ...
        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->end($res);
    }
}