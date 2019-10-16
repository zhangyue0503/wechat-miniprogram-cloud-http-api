<?php

namespace zyblog\wxMpCloudHttpApi\database;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use zyblog\wxMpCloudHttpApi\database\tools\DbCondition;

class Db
{

    protected $env;
    protected $accessToken;

    protected $queryString;
    protected $collectionName;

    /**
     * Db constructor.
     * @param $env 云环境ID
     * @param $accessToken 接口调用凭证
     */
    public function __construct($env, $accessToken)
    {
        $this->env = $env;
        $this->accessToken = $accessToken;
    }

    public function collectionQuery($collectionName)
    {
        $this->collectionName = $collectionName;
        $this->queryString = 'db.collection("' . $this->collectionName . '")';
        return $this;
    }

    protected function setDocQuery($id)
    {
        $this->queryString .= '.doc("' . $id . '")';
        return $this;
    }

    protected function getQuery()
    {
        $this->queryString .= '.get()';
        return $this;
    }

    public function query()
    {
        return $this->queryString;
    }

    protected function where($where = [], $orWhere = [])
    {
        $condition = new DbCondition();
        if (count($where) > 0) {
            $this->queryString .= ".where({" . $condition->Where($where) . "})";
        }
        if (count($orWhere) > 0) {
            $this->queryString .= ".where(_.or(";
            $orString = [];
            foreach ($orWhere as $orw) {
                $orString[] = '{' . $condition->Where([$orw]) . '}';
            }
            $this->queryString .= implode(',', $orString) . "))";
        }
        return $this;
    }

    protected function limit($limit)
    {
        if (is_array($limit) && count($limit) == 2) {
            if (is_int($limit[0]) && $limit[0] > 0) {
                $this->queryString .= ".limit({$limit[0]})";
            }
            if (is_int($limit[1]) && $limit[1] >= 0) {
                $this->queryString .= ".skip({$limit[1]})";
            }
        }

        return $this;
    }


    protected function DbPostReqeust($url, $bodyParams = [], $queryParams = [])
    {
        $queryParams = array_merge([
            'access_token' => $this->accessToken,
        ], $queryParams);

        $bodyParams = array_merge([
            'env' => $this->env,
        ], $bodyParams);

        if (!$this->accessToken) {
            return $this->error('-100001', '参数错误：access_token接口调用凭证不能为空');
        }
        if (!$this->env) {
            return $this->error('-100001', '参数错误：env云环境ID不能为空');
        }

        $requestLog = [
            'url'    => $url,
            'params' => [
                'body_params'  => json_encode($bodyParams, JSON_UNESCAPED_UNICODE),
                'query_params' => $queryParams,
            ],
        ];
        try {
            $client = new Client();
            $response = $client->request('POST', $url, [
                'query' => $queryParams,
                'json'  => $bodyParams, //'{"env":"acp-4ff2bb","query":"db.collection(\"acp_tt\").get()"}',
//                'debug'        => true,
            ]);

            return array_merge(json_decode($response->getBody()->getContents(), TRUE), $requestLog);
        } catch (GuzzleException $e) {
            return array_merge([
                'errcode' => '-100000',
                'errmsg'  => $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
            ], $requestLog);
        }
    }

    protected function error($errCode, $errMsg)
    {
        return [
            'errcode' => $errCode,
            'errmsg'  => $errMsg,
        ];
    }

}