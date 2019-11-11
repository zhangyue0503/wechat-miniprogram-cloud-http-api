<?php


namespace zyblog\wxMpCloudHttpApi\store;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use zyblog\wxMpCloudHttpApi\Common;
use zyblog\wxMpCloudHttpApi\Config;

/**
 * 云文件存储相关操作
 * Class Store
 * @package zyblog\wxMpCloudHttpApi\store
 */
class Store
{
    use Common;

    private $env;
    private $accessToken;

    public function __construct($env, $accessToken)
    {
        $this->env = $env;
        $this->accessToken = $accessToken;
    }

    /**
     * 上传文件
     * @param $path 上传路径：对应云存储路径，要带文件名，文件目录需要微信开发者工具云开发中创建，如：a.jpg、test/a.jpg
     * @param $file 文件二进制流数据：file_get_contents、fread等
     * @return array
     */
    public function upload($path, $file)
    {
        if (!$path || !$file) {
            return $this->error(-100000, "上传路径或上传文件不能为空！");
        }

        $bodyParams = [
            'path' => $path,
        ];
        // 第一次请求，获取上传链接及凭证
        $result = $this->postReqeust(Config::$UPLOAD_FILE, $bodyParams);
        // 第一次请求成功后
        if (isset($result['errcode']) && $result['errcode'] == 0) {
            $multipart = [
                ['name' => 'Signature', 'contents' => $result['authorization'],],
                ['name' => 'key', 'contents' => $path,],
                ['name' => 'x-cos-security-token', 'contents' => $result['token'],],
                ['name' => 'x-cos-meta-fileid', 'contents' => $result['cos_file_id'],],
                ['name' => 'file', 'contents' => $file,],
            ];
            $queryParams = ['access_token' => $this->accessToken,];
            $requestLog = $this->getRequestLog($result['url'], [
                'body_params'  => $multipart,
                'query_params' => $queryParams,
            ]);
            try {
                $client = new Client();
                // 第二次请求，正式上传文件
                $response = $client->request('POST', $result['url'], [
                    'query'     => $queryParams,
                    'multipart' => $multipart,
                ]);
                // 正常上传成功不会有任何信息返回，如果有信息，报错
                if ($response->getBody()->getContents()) {
                    return array_merge(json_decode($response->getBody()->getContents(), TRUE), $requestLog);
                }
                return $result;
            } catch (GuzzleException $e) {
                return array_merge($this->error(-100001, $e->getMessage() . PHP_EOL . $e->getTraceAsString()), $requestLog);
            }
        } else {
            return $result;
        }
    }

    /**
     * 批量下载文件链接获取
     * @param array $fileList 文件列表id
     * @return array
     */
    public function download(array $fileList = [])
    {
        if (!$fileList) {
            return $this->error(-100000, "文件列表为空");
        }
        if (!isset($fileList[0]['fileid'])) {
            return $this->error(-100000, "文件列表格式错误，请参考微信文档：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/batchDownloadFile.html");
        }

        $bodyParams = [
            'file_list' => $fileList,
        ];
        return $this->postReqeust(Config::$BATCH_DOWNLOAD_FILE, $bodyParams);
    }

    /**
     * 批量删除文件
     * @param array $fileIdList 文件ID列表
     * @return array
     */
    public function delete(array $fileIdList = [])
    {
        if (!$fileIdList) {
            return $this->error(-100000, "文件ID列表为空");
        }

        if (count($fileIdList) != count($fileIdList, COUNT_RECURSIVE)) {
            return $this->error(-100000, "文件ID列表格式不正确，请参考微信文档：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-http-api/storage/batchDeleteFile.html");
        }

        $bodyParams = [
            'fileid_list' => $fileIdList,
        ];
        return $this->postReqeust(Config::$BATCH_DELETE_FILE, $bodyParams);
    }


}