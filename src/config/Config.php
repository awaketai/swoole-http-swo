<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/23
 * Time: 5:06 下午
 */

namespace swo\config;


class Config
{
    protected $items = [];

    protected static $config = [];

    public function __construct()
    {
        $this->configPath = app()->getBasePath() . '/frame/config';
        $this->items = $this->loadConfig();
    }

    public function loadConfig(){
        $files = scandir($this->configPath);
        $data = [];
        foreach ($files as $key => $file){
            if($file == '.' || $file == '..'){
                continue;
            }
            // 返回文件名称
            $filename = stristr($file,'.php',true);
            $data[$filename] = include $this->configPath .'/'.$file;
        }
        // 返回配置
        return $data;
    }

    /**
     * 配置获取 http.host 方式
     * @param $key
     * @return array|mixed
     */
    public function get($key){
        $config = explode('.',$key);
        $data = $this->items;
        foreach ($config as $key => $val){
            $data = $data[$val];
        }
        return $data;
    }
}