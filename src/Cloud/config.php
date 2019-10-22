<?php

namespace zyblog\wxMpCloudHttpApi;

class Config
{
    public static $callFunctionApi = 'https://api.weixin.qq.com/cgi-bin/token';

    public static $DATABASE_COLLECTION_GET    = 'https://api.weixin.qq.com/tcb/databasecollectionget';
    public static $DATABASE_COLLECTION_ADD    = 'https://api.weixin.qq.com/tcb/databasecollectionadd';
    public static $DATABASE_COLLECTION_DELETE = 'https://api.weixin.qq.com/tcb/databasecollectiondelete';

    public static $DATABASE_QUERY  = 'https://api.weixin.qq.com/tcb/databasequery';
    public static $DATABASE_ADD    = 'https://api.weixin.qq.com/tcb/databaseadd';
    public static $DATABASE_UPDATE = 'https://api.weixin.qq.com/tcb/databaseupdate';
    public static $DATABASE_DELETE = 'https://api.weixin.qq.com/tcb/databasedelete';
    public static $DATABASE_COUNT  = 'https://api.weixin.qq.com/tcb/databasecount';

}