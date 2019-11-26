<?php

namespace zyblog\wxMpCloudHttpApi\database;

use zyblog\wxMpCloudHttpApi\Common;
use zyblog\wxMpCloudHttpApi\database\tools\DbCondition;
use zyblog\wxMpCloudHttpApi\database\tools\DbData;
use zyblog\wxMpCloudHttpApi\database\tools\DbField;

class Db
{
    use Common;

    protected $env;
    protected $accessToken;

    protected $queryString;
    protected $collectionName;

    // 静态实例
    private static $dbField; // 数据字段处理类
    private static $dbConditiion; // 数据条件组合类
    private static $dbData; // 数据更新修改字段

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

    public function setQuery($queryString)
    {
        $this->queryString = $queryString;
        return $this;
    }

    public function execute($url)
    {
        return $this->postReqeust($url, [
            'query' => $this->queryString,
        ], []);
    }

    public function where($where = [], $orWhere = [])
    {
        $whereString = ".where(";
        $condition = $this->getDbConditionInstance();
        $dot = '';
        $hasWhere = false;
        if (is_array($where) && count($where) > 0) {
            $whereString .= "_.and([{" . $condition->where($where) . "}";
            $dot = ',';
            $hasWhere = true;
        } else if (is_string($where) && $where) {
            $whereString .= "_.and([" . $where;
            $dot = ',';
            $hasWhere = true;
        }
        if (is_array($orWhere) && count($orWhere) > 0) {
            $whereString.= $dot . "_.or([";
            $orString = [];
            foreach ($orWhere as $orw) {
                $orString[] = '{' . $condition->where($orw) . '}';
            }
            $whereString .= implode(',', $orString) . "])";
            $hasWhere = true;
        } else if (is_string($orWhere) && $orWhere) {
            $whereString .= $dot . "_.or(" . $orWhere . ")";
            $hasWhere = true;
        }

        $whereString .= ($hasWhere ? '])' : '') . ")";
        if($hasWhere){
            $this->queryString .= $whereString;
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

    public function data($data)
    {
        $dbData = $this->getDbData();
        return $dbData->data($data);
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
                list($fieldName, $order) = explode(' ', $o);
                if (!$order) $order = 'asc';
                $this->queryString .= '.orderBy("' . $fieldName . '", "' . $order . '")';
            }
        }
        return $this;
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

    private function getDbData()
    {
        if (self::$dbData == NULL) {
            self::$dbData = new DbData();
        }
        return self::$dbData;
    }
}