<?php

use G24_Payment\Tinkoff_API;

error_reporting(E_ALL);
ini_set('display_errors', '1');

include "tinkoff_api.php";

$api = new Tinkoff_API();
$response = $api->resend();

if (!$response) {
    die($api->error);
}

print_r($response);