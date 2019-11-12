<?php


namespace zyblog\wxMpCloudHttpApi\qCloudToken;


use zyblog\wxMpCloudHttpApi\Common;
use zyblog\wxMpCloudHttpApi\Config;

/**
 * 获取腾讯云API调用凭证相关操作
 * Class QqToken
 * @package zyblog\wxMpCloudHttpApi\qCloudToken
 */
class QqToken
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
     * 获取腾讯云API调用凭证
     * @param $lifespan
     * @return array
     */
    public function getToken($lifespan = 7200)
    {
        if (!is_int($lifespan) || $lifespan <= 0 || $lifespan > 7200) {
            $lifespan = 7200;
        }

        return $this->postReqeust(Config::$Q_CLOUD_TOKEN, [
            'lifespan' => $lifespan,
        ], [], FALSE);
    }

}