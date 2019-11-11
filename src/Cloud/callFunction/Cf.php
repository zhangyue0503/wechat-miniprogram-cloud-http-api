<?php

namespace zyblog\wxMpCloudHttpApi\callFunction;

use GuzzleHttp\Client;
use zyblog\wxMpCloudHttpApi\Config;

class Cf
{

    private $env;
    private $accessToken;

    public function __construct($env, $accessToken)
    {
        $this->env = $env;
        $this->accessToken = $accessToken;
    }

    public function call($name, $postBody)
    {
        $client = new Client();
        $response = $client->request('POST', Config::$callFunctionApi, [
            'query' => [
                'env'          => $this->env,
                'access_token' => $this->accessToken,
                'name'         => $name,
            ],
            'body'  => is_string($postBody) ? $postBody : json_encode($postBody, JSON_UNESCAPED_UNICODE),
        ]);

    }
}