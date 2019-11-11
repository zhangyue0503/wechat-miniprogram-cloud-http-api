<?php


namespace zyblog\wxMpCloudHttpApi;


use zyblog\wxMpCloudHttpApi\callFunction\Cf;
use zyblog\wxMpCloudHttpApi\database\DbCollection;
use zyblog\wxMpCloudHttpApi\store\Store;

class CloudApi
{
    private $env;
    private $accessToken;

    private static $callFunction;
    private static $collection;
    private static $store;

    /**
     * 微信云服务开发
     * CloudApi constructor.
     * @param $env 云环境ID
     * @param $accessToken AccessToken
     */
    public function __construct($env, $accessToken)
    {
        $this->env = $env;
        $this->accessToken = $accessToken;
    }

    /**
     * 云函数调用
     * @return Cf 云函数调用对象
     */
    public function callFunction(){
        if(self::$callFunction == NULL){
            self::$callFunction = new Cf($this->env, $this->accessToken);
        }
        return self::$callFunction;
    }

    /**
     * 数据库操作
     * @param $collectionName 集合名称
     * @return DbCollection 数据库操作对象
     */
    public function collection($collectionName){
        if(self::$collection == NULL){
            self::$collection = new DbCollection($this->env, $this->accessToken);
        }
        self::$collection->collectionQuery($collectionName);
        return self::$collection;
    }

    /**
     * 文件存储
     * @return Store 文件操作对象
     */
    public function store(){
        if(self::$store == NULL){
            self::$store = new Store($this->env, $this->accessToken);
        }
        return self::$store;
    }
}