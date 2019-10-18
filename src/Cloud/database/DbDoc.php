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
     * @param $fields
     * @return array
     */
    public function get($fields = []){
        $query = $this->field($fields)->getQuery()->query();
        return $this->DbPostReqeust(Config::$db['databaseQuery'], [
            'query' => $query,
        ]);
    }

    /**
     * 修改文档
     * @param $data 文档内容
     * @return array
     */
    public function update($data)
    {
        $query = $this->query();
        $query .= '.update({data:' . json_encode($data, JSON_UNESCAPED_UNICODE) . '})';

        return $this->DbPostReqeust(Config::$db['databaseUpdate'], [
            'query' => $query,
        ]);
    }

    /**
     * 修改或新增文档
     * 如果doc(id)不存在，新建
     * @param $data
     * @return array
     */
    public function set($data)
    {
        $query = $this->query();
        $query .= '.set({data:' . json_encode($data, JSON_UNESCAPED_UNICODE) . '})';

        return $this->DbPostReqeust(Config::$db['databaseUpdate'], [
            'query' => $query,
        ]);
    }

    /**
     * 删除文档
     * @param $data
     * @return array
     */
    public function remove()
    {
        $query = $this->query();
        $query .= '.remove()';

        return $this->DbPostReqeust(Config::$db['databaseUpdate'], [
            'query' => $query,
        ]);
    }

}