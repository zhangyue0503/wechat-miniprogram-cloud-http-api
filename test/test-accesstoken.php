<?php
require_once dirname(__DIR__) . "/vendor/autoload.php";

use zyblog\wxMpCloudHttpApi\AccessToken;

print_r(AccessToken::getWxAccessToken('', ''));