<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/22
 * Time: 5:21 下午
 */

namespace swo\message\http;

use \Swoole\Http\Request as SwooleRequest;

class Request
{
    protected $method;

    protected $uriPath;

    protected $swooleRequest;

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getUriPath()
    {
        return $this->uriPath;
    }

    public static function init(SwooleRequest $request){

        $self = app('httpRequest');
        // 获取swoole server
        $self->swooleRequest = $request;
        $self->sever = $request->server;
        $self->method = $request->server['request_method'] ?? '';
        $self->uriPath = $request->server['request_uri'] ?? '';
        return $self;
    }

}