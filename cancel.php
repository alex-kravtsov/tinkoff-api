<?php

use G24_Payment\Tinkoff_API;

error_reporting(E_ALL);
ini_set('display_errors', '1');

include "tinkoff_api.php";

$PaymentId = '';

$mysqli = new mysqli('localhost', 'test', '', 'test');
if ($mysqli->connect_errno) {
    die(printf("Connect failed: %s\n", $mysqli->connect_error) );
}

$api = new Tinkoff_API();
$response = $api->payment_cancel([
    'PaymentId' => $PaymentId,
]);

if (!$response) {
    die($api->error);
}

if ($response->Success) {
    $mysqli = new mysqli('localhost', 'test', '', 'test');
    $table_references = "`transactions`";
    $set = "`completed_at` = NOW()";
    $set .= !empty($response->Status) ? ", `Status` = '{$response->Status}'" : "";
    $where = "`PaymentId` = {$PaymentId}";
    $query = "UPDATE {$table_references} SET {$set} WHERE {$where}";
    $mysqli->query($query);
}

$mysqli->close();

print_r($response);
