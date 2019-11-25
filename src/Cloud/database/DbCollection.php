<?php


namespace zyblog\wxMpCloudHttpApi\database;

use zyblog\wxMpCloudHttpApi\Config;

/**
 * 集合操作
 * Class DbCollection
 * @package zyblog\wxMpCloudHttpApi\database
 */
class DbCollection extends Db
{
    /**
     * 添加集合
     * @param $name 集合名称
     * @return array 参考：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionAdd.html
     */
    public function createCollections($name)
    {
        if (!$name) {
            return $this->error('-100001', '参数错误：集合名称不能为空');
        }
        return $this->postReqeust(Config::$DATABASE_COLLECTION_ADD, [
            'collection_name' => $name,
        ]);
    }

    /**
     * 删除集合
     * @param $name 集合名称
     * @return array 参考：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionDelete.html
     */
    public function deleteCollections($name)
    {
        if (!$name) {
            return $this->error('-100001', '参数错误：集合名称不能为空');
        }
        return $this->postReqeust(Config::$DATABASE_COLLECTION_DELETE, [
            'collection_name' => $name,
        ]);
    }

    /**
     * 获取集合列表
     * @param $limit 数量
     * @param $offset 位移
     * @return array 参考：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionGet.html
     */
    public function getCollections($limit = 10, $offset = 0)
    {
        if (!is_int($limit)) {
            return $this->error('-100001', '参数错误：数量限制必须为数字');
        }
        if (!is_int($offset)) {
            return $this->error('-100001', '参数错误：偏移量必须为数字');
        }
        $page = [];
        if($limit > 0){
            $page['limit'] = $limit;
        }
        if($offset > 0){
            $page['offset'] = $offset;
        }
        return $this->postReqeust(Config::$DATABASE_COLLECTION_GET, $page);
    }

    /**
     * 集合查询
     * @param array $where ["key [...]" => (value=array,string,number),……]
     * @param array $orWhere [["key [...]" => (value=array,string,number)],……]
     * @param array $limit [10, 1]
     * @param array $orderBy ["id desc",……]
     * @param array $field [……]
     * @return mixed
     */
    public function get($where = [], $orWhere = [], $limit = [], $orderBy = [], $field = [])
    {
        $query = $this->where($where, $orWhere)->orderBy($orderBy)->limit($limit)->field($field)->getQuery()->query();
        return $this->postReqeust(Config::$DATABASE_QUERY, [
            'query' => $query,
        ]);
    }

    /**
     * 添加文档
     * @param $data 文档内容
     * @return array
     */
    public function add($data)
    {
        $query = $this->query();
        $query .= '.add({data:[' . (is_string($data) ? $data : $this->data($data)) . ']})';

        return $this->postReqeust(Config::$DATABASE_ADD, [
            'query' => $query,
        ]);
    }

    /**
     * 修改文档
     * @param $data 文档内容
     * @param array $where
     * @param array $orWhere
     * @return array
     */
    public function update($data, $where = [], $orWhere = [])
    {
        $query = $this->where($where, $orWhere)->query();
        $query .= '.update({data:{' . (is_string($data) ? $data : $this->data($data)) . '}})';

        return $this->postReqeust(Config::$DATABASE_UPDATE, [
            'query' => $query,
        ]);
    }

    /**
     * 删除文档
     * @param array $where
     * @param array $orWhere
     * @return array
     */
    public function delete($where = [], $orWhere = [])
    {
        $query = $this->where($where, $orWhere)->query();
        $query .= '.remove()';

        return $this->postReqeust(Config::$DATABASE_DELETE, [
            'query' => $query,
        ]);
    }

    /**
     * 查询数量
     * @param array $where
     * @param array $orWhere
     * @return array
     */
    public function count($where = [], $orWhere = [])
    {
        $query = $this->where($where, $orWhere)->query();
        $query .= '.count()';

        return $this->postReqeust(Config::$DATABASE_COUNT, [
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