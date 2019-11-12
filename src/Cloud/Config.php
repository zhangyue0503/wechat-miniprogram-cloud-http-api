<?php

namespace zyblog\wxMpCloudHttpApi;

class Config
{
    public static $ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/token';

    public static $CALL_FUNCTION = 'https://api.weixin.qq.com/tcb/invokecloudfunction';

    public static $Q_CLOUD_TOKEN = 'https://api.weixin.qq.com/tcb/getqcloudtoken';

    public static $UPLOAD_FILE = 'https://api.weixin.qq.com/tcb/uploadfile';
    public static $BATCH_DOWNLOAD_FILE = 'https://api.weixin.qq.com/tcb/batchdownloadfile';
    public static $BATCH_DELETE_FILE = 'https://api.weixin.qq.com/tcb/batchdeletefile';

    public static $DATABASE_COLLECTION_GET    = 'https://api.weixin.qq.com/tcb/databasecollectionget';
    public static $DATABASE_COLLECTION_ADD    = 'https://api.weixin.qq.com/tcb/databasecollectionadd';
    public static $DATABASE_COLLECTION_DELETE = 'https://api.weixin.qq.com/tcb/databasecollectiondelete';

    public static $DATABASE_QUERY  = 'https://api.weixin.qq.com/tcb/databasequery';
    public static $DATABASE_ADD    = 'https://api.weixin.qq.com/tcb/databaseadd';
    public static $DATABASE_UPDATE = 'https://api.weixin.qq.com/tcb/databaseupdate';
    public static $DATABASE_DELETE = 'https://api.weixin.qq.com/tcb/databasedelete';
    public static $DATABASE_COUNT  = 'https://api.weixin.qq.com/tcb/databasecount';

}