<?php

namespace zyblog\wxMpCloudHttpApi;

trait Config
{
    public static $callFunctionApi = 'https://api.weixin.qq.com/cgi-bin/token';

    public static $db = [
        'databaseCollectionGet'    => 'https://api.weixin.qq.com/tcb/databasecollectionget',
        'databaseCollectionAdd'    => 'https://api.weixin.qq.com/tcb/databasecollectionadd',
        'databaseCollectionDelete' => 'https://api.weixin.qq.com/tcb/databasecollectiondelete',

        'databaseQuery'  => 'https://api.weixin.qq.com/tcb/databasequery',
        'databaseAdd'    => 'https://api.weixin.qq.com/tcb/databaseadd',
        'databaseUpdate' => 'https://api.weixin.qq.com/tcb/databaseupdate',
        'databaseDelete' => 'https://api.weixin.qq.com/tcb/databasedelete',
        'databaseCount'  => 'https://api.weixin.qq.com/tcb/databasecount',
    ];

}