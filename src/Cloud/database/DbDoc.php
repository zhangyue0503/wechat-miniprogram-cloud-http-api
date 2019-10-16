<?php


namespace zyblog\wxMpCloudHttpApi\database;

use zyblog\wxMpCloudHttpApi\Config;

class DbDoc extends Db
{

    public function get(){
        $query = $this->getQuery()->query();
        return $this->DbPostReqeust(Config::$db['databaseQuery'], [
            'query' => $query,
        ]);
    }

}