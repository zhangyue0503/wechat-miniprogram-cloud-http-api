<?php


namespace zyblog\wxMpCloudHttpApi;


use zyblog\wxMpCloudHttpApi\callFunction\CallFunction;
use zyblog\wxMpCloudHttpApi\database\Db;
use zyblog\wxMpCloudHttpApi\database\DbCollection;

class CloudApi
{
    private $env;
    private $accessToken;

    private static $callFunction;
    private static $collection;

    public function __construct($env, $accessToken)
    {
        $this->env = $env;
        $this->accessToken = $accessToken;
    }

    /**
     * 云函数调用
     * @return CallFunction 云函数调用对象
     */
    public function callFunction(){
        if(self::$callFunction == NULL){
            self::$callFunction = new CallFunction($this->env, $this->accessToken);
        }
        return self::$callFunction;
    }


    public function collection($collectionName){
        if(self::$collection == NULL){
            self::$collection = new DbCollection($this->env, $this->accessToken);
        }
        self::$collection->collectionQuery($collectionName);
        return self::$collection;
    }

    // 文件存储
    public static function store(){

    }
}