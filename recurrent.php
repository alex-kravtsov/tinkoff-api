<?php

use G24_Payment\Tinkoff_API;

error_reporting(E_ALL);
ini_set('display_errors', '1');

include "tinkoff_api.php";

$api = new Tinkoff_API();

$PaymentId = '';
$RebillId = '';

$response = $api->payment_charge([
    'PaymentId' => $PaymentId,
    'RebillId' => $RebillId,
]);

if (!$response) {
    die($api->error);
}

print_r($response);
