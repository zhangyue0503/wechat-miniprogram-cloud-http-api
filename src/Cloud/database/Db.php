<?php

namespace zyblog\wxMpCloudHttpApi\database;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use zyblog\wxMpCloudHttpApi\database\tools\DbCondition;
use zyblog\wxMpCloudHttpApi\database\tools\DbField;

class Db
{

    protected $env;
    protected $accessToken;

    protected $queryString;
    protected $collectionName;

    // 静态实例
    private static $dbField; // 数据字段处理类
    private static $dbConditiion; // 数据条件组合类
    private static $client; // 接口请求组件

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

    /**
     * 初始化查询语句
     * @param $collectionName
     * @return $this
     */
    public function collectionQuery($collectionName)
    {
        $this->collectionName = $collectionName;
        $this->queryString = 'db.collection("' . $this->collectionName . '")';
        return $this;
    }

    /**
     * 设置文档查询
     * @param $id 文档id
     * @return $this
     */
    protected function setDocQuery($id)
    {
        $this->queryString .= '.doc("' . $id . '")';
        return $this;
    }

    /**
     * 设置get查询
     * @return $this
     */
    protected function getQuery()
    {
        $this->queryString .= '.get()';
        return $this;
    }

    /**
     * 返回拼装好的查询语句
     * @return mixed
     */
    public function query()
    {
        return $this->queryString;
    }

    public function setQuery($queryString){
        $this->queryString = $queryString;
        return $this;
    }

    public function execute($url){
        return $this->DbPostReqeust($url, [
            'query' => $this->queryString,
        ],[]);
    }

    public function where($where = [], $orWhere = [])
    {
        $condition = $this->getDbConditionInstance();
        if (is_array($where) && count($where) > 0) {
            $this->queryString .= ".where({" . $condition->Where($where) . "})";
        } else if (is_string($where) && $where) {
            $this->queryString .= ".where({" . $where . "})";
        }
        if (is_array($where) && count($orWhere) > 0) {
            $this->queryString .= ".where(_.or(";
            $orString = [];
            foreach ($orWhere as $orw) {
                $orString[] = '{' . $condition->Where([$orw]) . '}';
            }
            $this->queryString .= implode(',', $orString) . "))";
        } else if (is_string($orWhere) && $orWhere) {
            $this->queryString .= ".where(_.or(" . $orWhere . "))";
        }

        return $this;
    }

    public function field($fields = [])
    {
        if (is_string($fields) && $fields) {
            $this->queryString .= ".field({" . $fields . "})";
        } else if (count($fields) > 0) {
            $dbField = $this->getDbFieldInstance();
            $this->queryString .= '.field({' . $dbField->Field($fields) . '})';
        }
        return $this;
    }

    public function limit($limit)
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

    public function orderBy($orderBys = [])
    {
        if (is_array($orderBys) && count($orderBys) > 0) {
            foreach ($orderBys as $o) {
                if (is_array($o) && count($o) == 2) {
                    list($fieldName, $order) = explode(' ', $o);
                    if (!$order) $order = 'asc';
                    $this->queryString .= '.orderBy("' . $fieldName . '", "' . $order . '")';
                }
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
            $client = $this->getClient();
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

    /**
     * 创建字段处理类实例
     * @return DbField 字段处理类
     */
    private function getDbFieldInstance()
    {
        if (self::$dbField == NULL) {
            self::$dbField = new DbField();
        }
        return self::$dbField;
    }

    /**
     * 创建查询条件组合处理类实例
     * @return DbCondition
     */
    private function getDbConditionInstance()
    {
        if (self::$dbConditiion == NULL) {
            self::$dbConditiion = new DbCondition();
        }
        return self::$dbConditiion;
    }

    private function getClient()
    {
        if (self::$client == NULL) {
            self::$client = new Client();
        }
        return self::$client;
    }
}