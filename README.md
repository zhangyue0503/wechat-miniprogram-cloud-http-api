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

&nbsp;
> ### 数据库操作


