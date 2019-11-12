<?php

namespace zyblog\wxMpCloudHttpApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * 获取AccessToken
 * Class AccessToken
 * @package zyblog\wxMpCloudHttpApi
 */
class AccessToken
{
    use Common;

    /**
     * 获取AccessToken
     * @param $appid 微信Appid
     * @param $secret 微信私钥
     * @return array
     */
    public function getWxAccessToken($appid, $secret)
    {
        if (!$appid || !$secret) {
            return $this->error(-100000, "微信appid及secret不能为空！");
        }
        $query = [
            'grant_type' => 'client_credential',
            'appid'      => $appid,
            'secret'     => $secret,
        ];
        $requestLog = $this->getRequestLog(Config::$ACCESS_TOKEN, ['query' => $query]);

        try {
            $client = new Client();
            $response = $client->request('GET', Config::$ACCESS_TOKEN, [
                'query' => $query,
            ]);
            return array_merge(json_decode($response->getBody()->getContents(), TRUE), $requestLog);
        } catch (GuzzleException $e) {
            return array_merge($this->error(-100001, '请求失败：' . $e->getMessage() . PHP_EOL . $e->getTraceAsString()), $requestLog);
        }
    }
}