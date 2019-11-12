<?php

namespace zyblog\wxMpCloudHttpApi\callFunction;

use zyblog\wxMpCloudHttpApi\Common;
use zyblog\wxMpCloudHttpApi\Config;

/**
 * 云函数调用相关操作
 * Class Cf
 * @package zyblog\wxMpCloudHttpApi\callFunction
 */
class Cf
{
    use Common;

    private $env;
    private $accessToken;

    public function __construct($env, $accessToken)
    {
        $this->env = $env;
        $this->accessToken = $accessToken;
    }

    /**
     * 云函数调用
     * @param $name 云函数名称
     * @param string $postBody 云函数参数 {"a":1, "b":2}
     * @return array
     */
    public function call($name, $postBody = '{}')
    {
        if (!$name || !$postBody) {
            return $this->error(-100000, "方法名不能为空，空参数不能为''空字符串，默认为'{}'！");
        }

        $queryParams = [
            'name' => $name,
            'env'  => $this->env,
        ];

        return $this->postReqeust(Config::$CALL_FUNCTION, $postBody, $queryParams, FALSE);
    }
}