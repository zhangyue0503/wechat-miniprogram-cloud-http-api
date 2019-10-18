<?php
require_once dirname(__DIR__) . "/vendor/autoload.php";
require_once 'test-config.php';

use zyblog\wxMpCloudHttpApi\CloudApi;

$cloudApi = new CloudApi($env, $accessToken);


print_r($cloudApi->collection('test-20191016')->doc('5579d6915da6c73d0000189705ca8cbf')->update([
    'content'=>'aaaaa',
]));
print_r($cloudApi->collection('test-20191016')->doc('5579d6915da6c73d0000189705ca8cbf')->set([
    'content'=>'aaaaabb',
]));

print_r($cloudApi->collection('test-20191016')->doc('11')->set([
    'content'=>'fff',
]));

print_r($cloudApi->collection('test-20191016')->doc('5579d6915da6c73d0000189705ca8cbf')->get());


