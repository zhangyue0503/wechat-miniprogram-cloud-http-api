<?php


namespace zyblog\wxMpCloudHttpApi\database;

use zyblog\wxMpCloudHttpApi\Config;

/**
 * 文档操作
 * Class DbDoc
 * @package zyblog\wxMpCloudHttpApi\database
 */
class DbDoc extends Db
{
    /**
     * 获得文档内容
     * @param array $fields 字段数组
     * @return array
     */
    public function get(array $fields = []){
        $query = $this->field($fields)->getQuery()->query();
        return $this->postReqeust(Config::$DATABASE_QUERY, [
            'query' => $query,
        ]);
    }

    /**
     * 修改文档
     * @param $data 文档内容，可以是字符串或字段数组
     * @return array
     */
    public function update($data)
    {
        $query = $this->query();
        $query .= '.update({data:{' . (is_string($data) ? $data : $this->data($data)) . '}})';

        return $this->postReqeust(Config::$DATABASE_UPDATE, [
            'query' => $query,
        ]);
    }

    /**
     * 修改或新增文档
     * 如果doc(id)不存在，新建
     * @param $data 文档内容，可以是字符串或字段数组
     * @return array
     */
    public function set($data)
    {
        $query = $this->query();
        $query .= '.set({data:{' . (is_string($data) ? $data : $this->data($data)) . '}})';

        return $this->postReqeust(Config::$DATABASE_UPDATE, [
            'query' => $query,
        ]);
    }

    /**
     * 删除文档
     * @return array
     */
    public function remove()
    {
        $query = $this->query();
        $query .= '.remove()';

        return $this->postReqeust(Config::$DATABASE_DELETE, [
            'query' => $query,
        ]);
    }

}