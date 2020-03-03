<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/22
 * Time: 4:05 下午
 */

namespace swo\route;


use http\Exception\InvalidArgumentException;
use swo\exception\InvalidRequestException;

class Route
{
    protected static $instance;

    /**
     * 存储解析之后的路由
     * @var array
     */
    public $routes = [];

    protected $verbs = ['GET','POST','PUT','PATCH','DELETE'];
    
    /**
     * 路由文件地址
     * @var array 
     */
    protected $routeMap = [];

    protected $method = null; // 请求方式

    protected $namespaceMark;// 控制器命名空间标志

    public function __construct()
    {
        $this->routeMap = [
            'Http' => app()->getBasePath() . '/frame/route/http.php',
            'WebSocket' => app()->getBasePath() .'/frame/route/websocket.php',
        ];
    }

    public static function getInstance(){

        if(is_null(self::$instance)){
            self::$instance = new static;
        }
        return self::$instance;
    }

    public function get($uri,$action){

        return $this->addRoute(['GET'],$uri,$action);
    }

    public function post($uri,$action){

        return $this->addRoute(['POST'],$uri,$action);
    }

    public function any($uri,$action){

        return $this->addRoute($this->verbs,$uri,$action);
    }

    /**
     * 注册路由
     * @param array $methods
     * @param string $uri
     * @param string $action
     * @return $this
     */
    public function addRoute($methods,$uri,$action){

        foreach($methods as $method){
            $this->routes[$this->namespaceMark][$method][$uri] = $action;
        }
        return $this;
    }

    /**
     * websocket路由配置
     * @param $uri
     * @param $controller
     */
    public function ws($uri,$controller){
        $action = [
            'open',
            'message',
            'close'
        ];
        foreach ($action as $key => $ac){
            $this->addRoute([$ac],$uri,$controller.'@'.$ac);
        }
    }

    /**
     * 1.获取请求的uripath
     * 2.根据类型获取路由
     * 3.根据请求的uri匹配相应的路由，并返回action
     * 4.判断执行的方法类型是控制器还是Closure
     * 5.执行控制器方法或者Closure
     *
     * websocket怎么接入，扩展
     * websocket的回调事件怎么运用到不同的控制器中
     */
    public function match($path,$param){
        if(empty($path) OR $path == '/'){
            // 默认
            return $this->runAction('IndexController@index');
        }
        $action = '';
        foreach ($this->routes[$this->namespaceMark][$this->method] as $uri => $val){
            if(substr($uri,0,1) != '/'){
                $uri = '/' . $uri;
            }
            // 查找对应的方法
            if($path == $uri){
                $action = $val;
                break;
            }
        }
        if($action){
            return $this->runAction($action,$param);
        }
        throw new InvalidRequestException('The request not found',404);
    }

    /**
     * 执行路由闭包或者控制器方法
     * @param $action
     * @return mixed
     * @throws InvalidRequestException
     */
    private function runAction($action,$param = null){

        if($action instanceof \Closure){
            // 匿名函数 ... 根据传递的参数依次传给调用的方法
            return $action(...$param);
        }else{
            // 控制器方法 IndexController@index
            $namespace = "app\\".strtolower($this->namespaceMark)."\controllers\\";
            $controller = explode('@',$action);
            if(!isset($controller[0])){
                throw new InvalidRequestException('Invalid request controller');
            }
            if(!isset($controller[1])){
                throw new InvalidRequestException('Unknow request method');
            }
            // 执行控制器方法
            $class = $namespace.$controller[0];
            return (new $class)->{$controller[1]}(...$param);
        }
    }

    public function registerRoute(){

        foreach ($this->routeMap as $key => $path){
            $this->namespaceMark = $key;
            // Route的对象实例，将文件包含后，调用的方法的调用方式就无所谓了
            require_once $path;
        }
        return $this;
    }

    /**
     * @param $method
     * @return Route
     */
    public function setMethod($method): Route
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param $namespaceMark
     * @return Route
     */
    public function setNamespaceMark($namespaceMark): Route
    {
        $this->namespaceMark = $namespaceMark;
        return $this;
    }
}