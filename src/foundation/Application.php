<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/18
 * Time: 11:37 下午
 */

namespace swo\foundation;


use app\http\controllers\IndexController;
use swo\config\Config;
use swo\container\Container;
use swo\event\Event;
use swo\message\http\Request;
use swo\route\Route;
use swo\server\http\HttpServer;
use swo\server\tcp\TcpServer;
use swo\server\websocket\WebSocketServer;

class Application extends Container
{
    public $basePath;

    public function __construct()
    {
        $this->setBasePath();
        $this->register();
        $this->init();
    }

    public function run($argv){
        if(!isset($argv[1]) or empty($argv)){
            $httpServer = new HttpServer($this);
            $httpServer->init();
        }

        switch ($argv[1]){
            case 'http:start':
                $httpServer = new HttpServer($this);
                $httpServer->init();
                break;
            case 'tcp:start':
                $httpServer = new TcpServer($this);
                $httpServer->init();
                break;
            case 'ws:start':
                $server = new WebSocketServer($this);
                $server->init();
                break;
            default:
        }
    }

    public function setBasePath($path = ''){
        if(!$path){
            $path = dirname(dirname(dirname(dirname(__FILE__))));
        }
        $this->basePath = rtrim($path,'\/');
    }

    /**
     * @return mixed
     */
    public function getBasePath():string
    {
        return $this->basePath;
    }

    public function register(){
        // 设置自身为容器对象
        self::setInstance($this);
        // 预先绑定的容器对象
        $binds = [
            'index' => new IndexController(),
            'httpRequest' => new Request(),
            'config' => new Config(),
        ];
        foreach ($binds as $key => $val){
            $this->bind($key,$val);
        }
    }

    public function init(){
        // 路由注册
        $this->bind('route',Route::getInstance()->registerRoute());

        $this->bind('event',$this->registerEvent());
    }

    /**
     * 循环注册事件
     */
    public function registerEvent(){
        $event = new Event();

        $files = scandir($this->getBasePath() . '/frame/app/listener');
        foreach ($files as $key => $file){
            if($file == '.' || $file == '..'){
                continue;
            }
            $class = 'app\\listener\\'.explode('.',$file)[0];
            if(class_exists($class)){
                $listener = new $class($this);
                // 注册事件
                $event->register($listener->getName(),[$listener,'handler']);
            }
        }
        return $event;
    }
}