<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/18
 * Time: 11:40 下午
 */

namespace swo\server;

use swo\rpc\Rpc;
use swo\supper\Inotify;
use swo\foundation\Application;
use Swoole\Server as SwooleServer;

/**
 * base class for all server
 * Class Server
 * @package Swo\server
 */
abstract class Server
{
    // property
    public $swooleServer;
    protected $port;
    protected $host;

    protected $mod = SWOOLE_PROCESS;
    protected $sockType = SWOOLE_SOCK_TCP;

    protected $inotify = null;

    protected $serverType = 'TCP';
    protected $watchFile = false;

    protected $pidFile = '/runtime/swo.pid';

    protected $redis; // redis连接对象

    /**
     * swoole run pid info
     * @var array
     */
    protected $pidMap = [
        'masterPid' => 0,
        'managerPid' => 0,
        'workerPids' => [],
        'taskPids' => [],
    ];

    /**
     * custom function callback
     * @var array
     */
    protected $events = [
        // all server will be registered
        'server' => [
            'start' => 'onStart',
            'workerStart' => 'onWorkerStart',
            'workerStop' => 'onWorkerStop',
            'workerError' => 'onTask',
            'managerStart' => 'onFinish',
            'managerStop' => 'onManagerStop',
            'shutdown' => 'onShutdown',
        ],
        // sub server register
        'sub' => [],
        // extra function callback
        'ext' => [],
    ];

    protected $config = [
        'task_worker_num' => 0,
    ];

    // common function

    /**
     * create server
     * @return mixed
     */
    protected abstract function createServer();

    /**
     * init event will be listen
     * @return mixed
     */
    protected abstract function initEvent();

    public function __construct(Application $app)
    {
//        $this->setHost('http');
        $this->app = $app;
    }

    public function init()
    {
        // 1.创建server
        $this->createServer();
        // 2.设置swoole配置
        $this->swooleServer->set($this->config);
        // 3.设置需要注册的回调函数
        $this->initEvent();
        // 4.设置swoole的回调函数
        $this->setSwooleEvent();
        // server start
        $this->rpcInit();
        $this->swooleServer->start();
    }

    public function rpcInit(){
        $config = app('config');
        if($config->get('server.tcp_enable') === 1){
            // 监听rpc服务
//            dd('tcp:'.$config->get('server.rpc.host').':'.$config->get('server.rpc.port'));
            (new Rpc($this->swooleServer,$config->get('server.rpc')));
        }
    }

    // callback function

    public function setSwooleEvent(){

        array_map([$this,'eventBind'],$this->events);
    }

    /**
     * swoole event bind
     * @param $events
     */
    public function eventBind($events){
        foreach ($events as $event => $func){

            $this->swooleServer->on($event,[$this,$func]);
        }
    }

    public function setHost($type){
        var_dump(static::class);
        $config = app('config');
        $this->host = $config->get('server.'.$type.'.host');
        $this->port = $config->get('server.'.$type.'.port');
        dd($type.':'.$this->host.':---'.$this->port);
    }

    public function onStart(SwooleServer $server)
    {
        $this->pidMap['masterPid'] = $server->master_pid;
        $this->pidMap['managerPid'] = $server->manager_pid;

        $pidStr = sprintf('%s,%s',$server->master_pid,$server->manager_pid);
        if(file_exists(app()->getBasePath().$this->pidFile)){

            file_put_contents(app()->getBasePath().$this->pidFile,$pidStr);
        }

        if($this->watchFile){
            // 热重启
            $this->inotify = new Inotify($this->app->getBasePath(),[$this,'watchEvent']);
        }
        // 触发监听事件
        $this->app->make('event')->trigger('start');

    }

    public function onShutdown()
    {

    }

    public function onWorkerStart(SwooleServer $server,int $workerId)
    {
        $this->pidMap['workerPids'] = [
            'id' => $workerId,
            'pid' => $server->worker_id,
        ];
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1',6379);
    }

    public function onWorkerStop()
    {

    }

    public function onConnect(\Swoole\Server $server,$fd)
    {

    }

    public function onTask()
    {

    }

    public function onFinish()
    {

    }

    public function onWorkerError()
    {

    }

    public function onManagerStart()
    {

    }

    public function onManagerStop()
    {

    }

    /**
     * 子类回调设置
     * @param $type
     * @param $event
     * @return $this
     */
    public function setEvent($type,$event){
        if($type == 'server'){
            return $this;
        }
        $this->events[$type] = $event;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig() :array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 监控文件变化，进行热重启
     * @param $event
     */
    public function watchEvent($event){
        $action = 'file:';
        switch ($event){
            case IN_CREATE ;
                $action = 'IN_CREATE';
                break;
            case IN_DELETE ;
                $action = 'IN_DELETE';
                break;
            case IN_MODIFY ;
                $action = 'IN_MODIFY';
                break;
            case IN_MOVE ;
                $action = 'IN_MOVE';
                break;
        }
        echo ('worker reload by :'.$action.' file :'.$event['name']);
//        $masterPid = $this->pidMap['masterPid'];
//        posix_kill((int)$masterPid,SIGUSR1);
        $this->swooleServer->reload();
    }
}