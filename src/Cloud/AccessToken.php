<?php

namespace zyblog\wxMpCloudHttpApi;

use GuzzleHttp\Client;

/**
 * 获取AccessToken
 * Class AccessToken
 * @package zyblog\wxMpCloudHttpApi
 */
class AccessToken
{
    public static function getWxAccessToken($appid, $secret)
    {
        $returnRes = [
            'err_code'         => '0',
            'access_token' => '',
            'error_msg'    => '',
        ];
        if (!$appid || !$secret) {
            return $returnRes;
        }
        $client = new Client();
        $response = $client->request('GET', 'https://api.weixin.qq.com/cgi-bin/token', [
            'query' => [
                'grant_type' => 'client_credential',
                'appid'      => $appid,
                'secret'     => $secret,
            ],
        ]);
        if ($response->getStatusCode() == 200) {
            $res = json_decode($response->getBody()->getContents(), TRUE);
            if (isset($res['access_token'])) {
                $returnRes['access_token'] = $res['access_token'];
            } else {
                if (isset($res['errmsg']) && isset($res['errcode'])) {
                    $returnRes['err_code'] = $res['errcode'];
                    $returnRes['error_msg'] = $res['errmsg'];
                } else {
                    $returnRes['err_code'] = "-2";
                    $returnRes['error_msg'] = "接口返回值异常：" . $response->getBody()->getContents();
                }
            }
        } else {
            $returnRes['err_code'] = "-1";
            $returnRes['error_msg'] = "请求失败";
        }
        return $returnRes;
    }
}