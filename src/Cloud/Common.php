<?php


namespace zyblog\wxMpCloudHttpApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * 公共工具Trait
 * Trait Common
 * @package zyblog\wxMpCloudHttpApi
 */
trait Common
{

    private static $client; // 接口请求组件

    /**
     * 错误信息定制
     * @param $errCode 错误代码
     * @param $errMsg 错误内容
     * @return array 返回错误信息数组
     */
    protected function error($errCode = '', $errMsg = '')
    {
        return [
            'errcode' => $errCode,
            'errmsg'  => $errMsg,
        ];
    }

    /**
     * 接口请求
     * @param $url 请求url
     * @param array $bodyParams 请求体内容
     * @param array $queryParams 请求参数内容
     * @param bool $bodyAddEnv body中是否自动添加env
     * @return array 请求结果
     */
    protected function postReqeust($url, $bodyParams = [], array $queryParams = [], $bodyAddEnv = TRUE)
    {
        $queryParams = array_merge([
            'access_token' => $this->accessToken,
        ], $queryParams);

        $bodyParams = is_string($bodyParams) ? $bodyParams :
            ($bodyAddEnv ? array_merge([
                'env' => $this->env,
            ], $bodyParams) : $bodyParams);

        if (!$this->accessToken) {
            return $this->error('-100001', '参数错误：access_token接口调用凭证不能为空');
        }
        if (!$this->env) {
            return $this->error('-100001', '参数错误：env云环境ID不能为空');
        }

        $requestLog = $this->getRequestLog($url, [
            'body_params'  => json_encode($bodyParams, JSON_UNESCAPED_UNICODE),
            'query_params' => $queryParams,
        ]);
        try {
            $options = is_string($bodyParams) ? [
                'query' => $queryParams,
                'body'  => $bodyParams,
            ] : [
                'query' => $queryParams,
                'json'  => $bodyParams,
            ];
            $client = $this->getClient();
            $response = $client->request('POST', $url, $options);

            return array_merge(json_decode($response->getBody()->getContents(), TRUE), $requestLog);
        } catch (GuzzleException $e) {
            return array_merge($this->error(-100001, '请求失败：' . $e->getMessage() . PHP_EOL . $e->getTraceAsString()), $requestLog);
        }
    }

    /**
     * 请求结果组合信息
     * @param $url 请求链接
     * @param array $params 请求体内容
     * @return array 请求结果信息结合字段
     */
    private function getRequestLog($url, array $params)
    {
        return [
            'wmc_request_url'    => $url,
            'wmc_request_params' => $params,
        ];
    }

    /**
     * 单例实现请求类实例化
     * @return Client
     */
    private function getClient()
    {
        if (self::$client == NULL) {
            self::$client = new Client();
        }
        return self::$client;
    }

}