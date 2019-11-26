# 微信小程序云服务HTTP API封装

根据微信官方文档，使用PHP对HTTP API相关操作进行的封装。

云开发文档：[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/basis/getting-started.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/basis/getting-started.html)

Composer安装：

```php



```

# 返回值说明

所有接口的返回值都按微信HTTP API的返回值原样返回，并在原始返回值的基础上增加了两个字段，如：

```php
Array
(
    [errcode] => 0
    [errmsg] => ok
    [resp_data] => {"sum":3}
    [wmc_request_url] => https://api.weixin.qq.com/tcb/invokecloudfunction
    [wmc_request_params] => Array
        (
            [body_params] => {"a":1,"b":2}
            [query_params] => Array
                (
                    [access_token] =>  27_vUmnwaBNAfsgHCEnjnXj5C41vTIH7tNrZpzLk0rNKHSyPZ75fhYmYKDXlDWBUcmiNhhXSU4YcsfRSMgLBspV8yaKcO2GQ-2nwDIaP-ICKPZ6e_vLzaQWMaTsZw8TldUlPnN254xZp0NgfkDcQXYeAJAWDE
                    [name] => add
                    [env] => test-app
                )

        )

)
```

上述返回值的内容为调用云函数测试的返回值，在微信原返回值的基础上增加了：

名称 | 说明
--- | ---
wmc_request_url | 请求的接口URL地址
wmc_request_params | 请求参数
&nbsp;&nbsp;&nbsp;&nbsp; body_params | POST BODY参数
&nbsp;&nbsp;&nbsp;&nbsp; query_params | 链接上的Query参数  

另外，表示参数有问题直接拦截时，只返回errcode（-100000或-100001）和errmsg，如：

```php
Array
(
    [errcode] => -100000
    [errmsg] => 方法名不能为空，空参数不能为''空字符串，默认为'{}'！
)
```

# 使用方式

&nbsp;
> ### 初始化调用api

```php
$cloudApi = new CloudApi($env, $accessToken);
```

名称 | 说明
--- | --- 
$env | 云开发环境ID
$accessToken | 接口调用凭证

这两个参数是必须要有的，在微信开发者工具的云开发管理中获取云开发环境ID，通过小程序的appid和appsecret获得接口调用凭证，见[获取AccessToken](#获取AccessToken)。

&nbsp;
> ### 获取AccessToken

很多框架都已经有这个功能，这里就有简单的封装了一下。

```php
$cloudApi->token()->getWxAccessToken($appId, $appSecret);
```

名称 | 说明
--- | --- 
$appId | 小程序唯一凭证，即 AppID
$appSecret | 小程序唯一凭证密钥，即 AppSecret

参考微信文档：[https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/access-token/auth.getAccessToken.html](https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/access-token/auth.getAccessToken.html)

&nbsp;
> ### 触发云函数

```php
$cloudApi->callFunction()->call($name, $postBody = '{}');
```

名称 | 说明
--- | --- 
$name | 云函数名称
$postBody | 云函数的传入参数，具体结构由开发者定义，默认为空{}

例如我们有一个云函数，就像微信提供的示例一样，做两个数相加操作，如：

```javascript
// 云函数入口文件
const cloud = require('wx-server-sdk')

cloud.init()

// 云函数入口函数
exports.main = async (event, context) => {
  return {
    sum: event.a + event.b
  }
}
```

在使用接口时就可以这样调用：

```php
$cloudApi->callFunction()->call('add', ["a"=>1, "b"=>2]);
```

当然，$postBody是支持字段串的，用于复杂的参数格式，如：

```php
$cloudApi->callFunction()->call('add', '{"a":1, "b":2}');
```

需要注意的是，$postBody如果是字符串，必须是json格式，传递数组进来将自动进行json转换。

如果云函数没有参数，需要传递{}。在封装的框架中，$postBody是可选参数。如下面例子给云函数传递空的参数：

```php
$cloudApi->callFunction()->call('add');
$cloudApi->callFunction()->call('add', '{}');
$cloudApi->callFunction()->call('add', []);
```

微信HTTP API文档：[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/functions/invokeCloudFunction.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/functions/invokeCloudFunction.html)

&nbsp;
> ### 获取腾讯云API调用凭证

```php
$cloudApi->qToken()->getToken($lifespan = 7200);
```

名称 | 说明
--- | --- 
$lifespan | 有效期（单位为秒，最大7200，默认7200）

微信HTTP API文档：[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/utils/getQcloudToken.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/utils/getQcloudToken.html)

&nbsp;
> ### 文件操作

#### **上传文件**

```php
$cloudApi->store()->upload($path, $file);
```

名称 | 说明
--- | --- 
$path | 上传路径，要带文件名，如：test/aaa/a.jpg
$file | 文件二进制流数据，如：file_get_contents('a.jpg')

文件上传的上传路径中的文件目录需要在微信开发者工具云开发管理中创建，路径错误无法上传。

文件上传根据文档会提交两次请求，第一次请求获得凭证及file_id，第二次请求正式上传文件。在系统或本地存储中，应保存file_id用于后续的文件下载链接获取及删除操作。

上传路径需要有文件名，例如：

```php
$cloudApi->store()->upload('test/aaa/a.jpg', file_get_contents('a.jpg')); // 上传到test/aaa/目录下
$cloudApi->store()->upload('a.jpg', file_get_contents('a.jpg')); // 上传到根目录下
```

上传成功后返回的结果为：

```php

Array
(
    [errcode] => 0
    [errmsg] => ok
    [url] => https://cos.ap-shanghai.myqcloud.com……
    [token] => ……
    [authorization] => ……
    [file_id] => cloud://xxxxxxxxx/ass.txt    // 重要，需要保存
    [cos_file_id] => ……
    [wmc_request_url] => https://api.weixin.qq.com/tcb/uploadfile
    [wmc_request_params] => Array
        (
            [body_params] => {"env":"xxxxx","path":"ass.txt"}
            [query_params] => Array
                (
                    [access_token] => xxxxxx
                )

        )

)

```

微信HTTP API文档：
[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/uploadFile.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/uploadFile.html)

#### **获取文件下载链接**

```php
$cloudApi->store()->download($fileList);
```

名称 | 说明
--- | --- 
$fileList | 文件列表，内部结构格式如下

```php
$fileList = [
    [
        "fileid"=>'xxxxx',
        "max_age"=>7200
    ],
    ……
];
```

名称 | 说明
--- | --- 
fileid | 文件file_id
max_age | 下载链接有效期，最大7200

```php
$cloudApi->store()->download([
    [
        "fileid"=>"cloud://xxxxxxx/ass.txt",
        "max_age"=>7200
    ]
]);
```

返回值参考微信文档。

微信HTTP API文档：
[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/batchDownloadFile.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/batchDownloadFile.html)

#### **删除文件**

```php
$cloudApi->store()->delete($fileIdList);
```

名称 | 说明
--- | --- 
$fileIdList | 文件ID列表，简单数组格式，['id1', 'id2']

```php
$cloudApi->store()->delete([
    "cloud://xxxxxxxxxx/as.jpg",
]);
```

微信HTTP API文档：
[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/batchDeleteFile.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/batchDeleteFile.html)

&nbsp;
> ### 数据库操作

重头戏来了，微信云使用的是类似于MongoDb的文档式数据库，但HTTP API提供的能力并不完全，比如不支持聚合等一些函数，所以相关函数的使用请尝试使用云函数进行开发。

#### **集合操作**

```php
// 获取集合列表
$cloudApi->db()->getConnections($limit, $offset);
```
名称 | 说明
--- | --- 
$limit | 集合数量，可选，默认10
$offset | 偏移量，可选

微信HTTP API文档：
[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionGet.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionGet.html)

```php
// 添加集合
$cloudApi->collection()->createCollections($name);
```
名称 | 说明
--- | --- 
$name | 集合名称，必填

微信HTTP API文档：
[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionAdd.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionAdd.html)

```php
// 删除集合
$cloudApi->collection()->deleteCollections($name);
```

名称 | 说明
--- | --- 
$name | 集合名称，必填

微信HTTP API文档：
[https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionDelete.html](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/database/databaseCollectionDelete.html)

#### **集合添加操作**

```php
$cloudApi->collection($collectionName)->add($data);
```

名称 | 说明
--- | --- 
$collectionName | 集合名称
$data | 添加的内容，可以是数组键值对形式内容，也可以是自己准备好的字符串

```php
$data = [
[
    'title'=>'测试1',
    'sort' => 1,
    'content'=>'测试1的内容',
    'class' => [
        'cid' => 1,
        'name' => '文章',
    ]
],
……
]
```

如上代码所示，可以多条同时插入。

```php
$data = '{title:\"测试4\",sort:4,content:\"测试4的内容\",class:{cid:2,name:\"百科\"}}';
```

也可以是自己组装好的字符串，如果是多条插入，不用加[]，如下所示：

```php
$data = '{xxxx},{xxxx}';
```

示例：

```php
// 数组形式添加多条数据
$cloudApi->collection('test-2019')->add([
    [
        'title'=>'测试1',
        'sort' => 1,
        'content'=>'测试1的内容',
        'class' => [
            'cid' => 1,
            'name' => '文章',
        ]
    ],
    [
        'title'=>'测试2',
        'sort' => 2,
        'content'=>'测试2的内容',
        'class' => [
            'cid' => 2,
            'name' => '百科',
        ]
    ],
]);

// 数组形式添加单条数据
$cloudApi->collection('test-2019')->add([
    [
        'title'=>'测试3',
        'sort' => 3,
        'content'=>'测试3的内容',
        'class' => [
            'cid' => 2,
            'name' => '百科',
        ]
    ],
]);

// 字符串形式添加数据
$cloudApi->collection('test-2019')->add('{title:\"测试4\",sort:4,content:\"测试4的内容\",class:{cid:2,name:\"百科\"}}');

```

#### **集合修改操作**

```php
$cloudApi->collection($collectionName)->update($data, $where = [], $orWhere = []);
```

名称 | 说明
--- | --- 
$collectionName | 集合名称
$data | 添加的内容，可以是数组键值对形式内容，也可以是自己准备好的字符串
$where | where条件，支持语法与查询相同，参考查询操作
$orWhere | orWhere条件，支持语法与查询，参考查询操作

下例将title=测试3的数据的sort改为4，并把class里面的cid改为1，name改为文章

```php
$cloudApi->collection('test-20191119')->update([
    'sort' => 4,
    'class' => [
            'cid' => 1,
            'name' => '文章',
        ]
], [
    'title'=>'测试3',
]);
```

如果不给where条件，则集合中所有数据都更新，与MySQL类似

**更新操作支持函数操作**

- set

```php
$cloudApi->collection('test-20191119')->update([
    'class [set]' => [
            'cid' => 3,
            'name' => '文章2',
        ]
], [
    'title'=>'测试3',
]);
```

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.set.html)

- remove

```php
$cloudApi->collection('test-20191119')->update([
    'content [remove]' => 1
], [
    'title'=>'测试3',
]);
```

值并没有特殊意义，会根据key值判断这个key中是否有remove操作，也就是说这里的值可以给任意内容

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.remove.html)

- inc

```php
$cloudApi->collection('test-20191119')->update([
    'sort [inc]' => 1
], [
    'title'=>'测试3',
]);
```

值表示增加数，必须数字类型，负数表示为减操作，例如当前sort为4，则加1后为5，或使用-2后为2

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.inc.html)

- mul

```php
$cloudApi->collection('test-20191119')->update([
    'sort [mul]' => 2
], [
    'title'=>'测试3',
]);
```

与inc类似，但注意数学运算规则，如果给负数或者本身数据为负数，则乘积都为负数

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.mul.html)

- push

```php
$cloudApi->collection('test-20191119')->update([
    'tags [push]' => [
        "each" =>[["app"=>"aaa", "ppa"=>2], ["app"=>"bbb", "ppa"=>5]],
        "sort" => ["ppa" => 1],
        "position" => 1,
        "slice" => -2
    ],
], [
    'title'=>'测试3',
]);
```

数组操作能力，详情查看文档，支持文档中的参数写法。

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.push.html)

- pop

```php
$cloudApi->collection('test-20191119')->update([
    'tags [pop]' => 1,
], [
    'title'=>'测试3',
]);
```

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.pop.html)

- unshift

```php
$cloudApi->collection('test-20191119')->update([
    'tags [unshift]' => [1122,333,444,'fff'],
], [
    'title'=>'测试3',
]);
```

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.unshift.html)

- shift

```php
$cloudApi->collection('test-20191119')->update([
    'tags [shift]' => 1,
], [
    'title'=>'测试3',
]);
```

[小程序文档](https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-sdk-api/database/command/Command.shift.html)

#### **集合删除操作**

```php
$cloudApi->collection($collectionName)->delete($where = [], $orWhere = []);
```

名称 | 说明
--- | --- 
$collectionName | 集合名称
$where | where条件，支持语法与查询相同，参考查询操作
$orWhere | orWhere条件，支持语法与查询，参考查询操作

下例将删除title=测试4

```php
$cloudApi->collection('test-20191119')->delete([
    'title'=>'测试4',
]);
```

如果不给where条件，则集合中所有数据都删除，与MySQL类似

#### **集合查询操作**

```php
$cloudApi->collection($collectionName)->get($where, $orWhere, $limit, $orderBy, $field);
```

名称 | 说明
--- | --- 
$collectionName | 集合名称
$where | where条件，["key [...]" => (value=array,string,number),……]
$orWhere | orWhere条件，[["key [...]" => (value=array,string,number)],……]，注意orWhere是二维数组
$limit | 分页，[$limit, $offset]
$orderBy | 排序，["key asc|desc", ……]
$field | 查询字段名，["key", ……]

查询操作所有的条件都不是必须的，没有任何条件将返回全部的文档列表，在这些参数中主要是where条件的支持语法比较多，我们先看一个实例：

```php

$where = [
    "sort [>]" => 1,
];
$orWhere = [
    ["title" => '测试2'],
    ["title" => '测试3'],
];
$limit = [10, 0];
$orderBy = ['sort asc', 'class.cid desc'];
$field = ["class.name", "content", "sort", "title"];
$cloudApi->collection('test-20191119')->get($where, $orWhere, $limit, $orderBy, $field);

// 组合而成的postBody
// {"env":"acp-xxx","query":"db.collection(\"test-20191119\").where(_.and([{sort:_.gt(10)},_.or([{title:\"测试2\"},{title:\"测试3\"}])])).rBy(\"sort\",\"asc\").orderBy(\"cid\", \"desc\").limit(10).skip(0).field({class:{name:true},content:true,sort:true,title:true}).get()"}

```

上述实例中各项说明为：
- $where：sort 大于 1 的
- $orWhere：title 等于 测试2 或者 测试3 的
- $limit：从0开始的10条数据
- $orderBy：按照 sort 正序并且 class.cid 倒序
- $field：显示指定的 class.name 、 content 、 sort 、 title 字段

**where与orWhere所支持的函数操作**

- [=] [eq] 等于

```php
$where = ['title [=]' => '测试3'];
$where = ['title [eq]' => '测试3'];
$where = ['title' => '测试3'];
```

它们的效果是一致的。

- [_eq] 等于对象

```php
$where = [
    "class [_eq]" => [
        "cid" => 2,
        "name" => "百科",
    ],
];
```

- [gt] [>] 大于

```php
$where = ["sort [>]" => 1];
$where = ["sort [gt]" => 1];
```

- [gte] [>=] 大于等于

```php
$where = ["sort [>=]" => 1];
$where = ["sort [gte]" => 1];
```

- [lt] [<] 小于

```php
$where = ["sort [<]" => 30];
$where = ["sort [lt]" => 30];
```

- [lte] [<=] 小于等于

```php
$where = ["sort [<=]" => 30];
$where = ["sort [lte]" => 30];
```

- [neq] [!=] [<>] 不等于

```php
$where = ["title [neq]" => "测试2"];
$where = ["title [!=]" => "测试2"];
$where = ["title [<>]" => "测试2"];
```

- [in] 在集合中

```php
$where = ["title [in]" => ["测试2", "测试3"]];
```

- [not in] [nin] 不在集合中

```php
$where = ["title [not in]" => ["测试2", "测试3"]];
$where = ["title [nin]" => ["测试2", "测试3"]];
```

- [like] 模糊匹配

```php
$where = ["class.name [like]" => "文"];
```

- [not like] 反向模糊匹配

```php
$where = ["class.name [not like]" => "文"];
```

- [exists] 字段存在

```php
$where = ["tags [exists]" => 1]; // 返回存在tags字段的
$where = ["tags [exists]" => 0]; // 返回不存在tags字段的
```

- [size] 数组字段的查询筛选条件，要求数组长度为给定值

```php
$where = ["tags [size]" => 3]; 
$where = ["tags [size]" => 4]; 
```

- [mod] 给定除数 divisor 和余数 remainder，要求字段作为被除数时 value % divisor = remainder

```php
$where = ["sort [mod]" => "5"]; // 模5为0的
$where = ["sort [mod]" => "3,1"]; // 模3余1的，测试时出错403错误，微信文档没有给出说明
```

- [reg] 正则匹配

```php

```



**关于orWhere条件的问题**

**关于字段名的点操作**

**直接使用自行拼装的字符串**













