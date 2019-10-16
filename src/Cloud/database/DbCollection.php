<?php


namespace zyblog\wxMpCloudHttpApi\database;

use zyblog\wxMpCloudHttpApi\Config;

class DbCollection extends Db
{
    /**
     * 添加集合
     * @param $name 集合名称
     * @return array 参考：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionAdd.html
     */
    public function createConnections($name)
    {
        if (!$name) {
            return $this->error('-100001', '参数错误：集合名称不能为空');
        }
        return $this->DbPostReqeust(Config::$db['databaseCollectionAdd'], [
            'collection_name' => $name,
        ]);
    }

    /**
     * 删除集合
     * @param $name 集合名称
     * @return array 参考：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionDelete.html
     */
    public function deleteConnections($name)
    {
        if (!$name) {
            return $this->error('-100001', '参数错误：集合名称不能为空');
        }
        return $this->DbPostReqeust(Config::$db['databaseCollectionDelete'], [
            'collection_name' => $name,
        ]);
    }

    /**
     * 获取集合列表
     * @param $limit 数量
     * @param $offset 位移
     * @return array 参考：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionGet.html
     */
    public function getConnections($limit, $offset)
    {
        if (!is_int($limit) || $limit <= 0) {
            return $this->error('-100001', '参数错误：数量限制必须为数字且不能小于等于0');
        }
        if (!is_int($offset) || $offset < 0) {
            return $this->error('-100001', '参数错误：偏移量必须为数字且不能小于0');
        }
        return $this->DbPostReqeust(Config::$db['databaseCollectionGet'], [
            'limit'  => (int)$limit,
            'offset' => (int)$offset,
        ]);
    }

    /**
     *
     * @param $connectionName
     * @param array $where
     * @param array $orWhere
     * @param array $limit
     * @return mixed
     */
    public function getDocList($where = [], $orWhere = [], $limit = [])
    {
        $query = $this->where($where, $orWhere)->limit($limit)->get()->query();
        return $this->DbPostReqeust(Config::$db['databaseQuery'], [
            'query' => $query,
        ]);
    }

    public function addDoc($connectionName, $data)
    {
        $query = $this->query();
        $query .= '.add({data:' . json_encode($data, JSON_UNESCAPED_UNICODE) . '})';

        return $this->DbPostReqeust(Config::$db['databaseAdd'], [
            'query' => $query,
        ]);
    }

    public function updateDoc($data, $where = [], $orWhere = [])
    {
        $query = $this->where($where, $orWhere)->query();
        echo $query;
        $query .= '.update({data:' . json_encode($data, JSON_UNESCAPED_UNICODE) . '})';

        return $this->DbPostReqeust(Config::$db['databaseUpdate'], [
            'query' => $query,
        ]);
    }

    /**
     * 设置doc对象
     * @param $docId
     * @return DbDoc
     */
    public function doc($docId){
        return (new DbDoc($this->env,$this->accessToken))->collectionQuery($this->collectionName)->setDocQuery($docId);
    }


}