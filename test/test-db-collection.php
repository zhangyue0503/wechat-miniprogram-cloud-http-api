<?php
require_once dirname(__DIR__) . "/vendor/autoload.php";
require_once 'test-config.php';

use zyblog\wxMpCloudHttpApi\CloudApi;

$cloudApi = new CloudApi($env, $accessToken);

// 添加
//print_r($cloudApi->db()->createConnections('test-' . date('Ymd')));

// 查询
//print_r($cloudApi->db()->getConnections(10, 0));

// 删除
//print_r($cloudApi->db()->deleteConnections('test-' . date('Ymd')));

// 查询
//print_r($cloudApi->db()->getConnections(10, 0));


// 添加doc
//$cloudApi->db()->add('test-20191016', [
//    [
//        'title'=>'试试',
//        'sort' => 22,
//        'content'=>'卡夫卡快说快说考试啉',
//        'class' => [
//            'cid' => 1,
//            'name' => '空空',
//        ]
//    ],
//    [
//        'title'=>'试试1',
//        'sort' => 12,
//        'content'=>'卡夫卡快说快说考试啉21',
//        'class' => [
//            'cid' => 2,
//            'name' => '咳咳',
//        ]
//    ]
//]);

// 列表查询
//print_r($cloudApi->collection('test-20191016')->get());

// 更新
print_r($cloudApi->collection('test-20191016')->update([
    'mem' => [
        'foo' => 'aa',
        'baz' => 'bb',
        'cad' => 'cc',
    ]
], [
    ['title', '试试']
]));

print_r($cloudApi->collection('test-20191016')->get([],[],[],[],[
    'content',
    ['mem', [1]]
]));