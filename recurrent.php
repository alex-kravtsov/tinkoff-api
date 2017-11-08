<?php

use G24_Payment\Tinkoff_API;

error_reporting(E_ALL);
ini_set('display_errors', '1');

include "tinkoff_api.php";

$RebillId = '1510047881330';

$mysqli = new mysqli('localhost', 'test', '', 'test');
if ($mysqli->connect_errno) {
    die(printf("Connect failed: %s\n", $mysqli->connect_error) );
}

// Create order
$customer_id = 1;
$price = 10; // roubles
$description = "Тестовый заказ";
$table = "`test_orders`";
$columns = "`customer_id`, `price`, `description`";
$values = "{$customer_id}, {$price}, '{$description}'";
$query = "INSERT INTO {$table} ({$columns}) VALUES ($values)";
if (!$mysqli->query($query) ) {
    die(printf("Error: %s\n", $mysqli->error) );
}

$OrderId = sprintf("t%d", $mysqli->insert_id);
$CustomerKey = sprintf("u%d", $customer_id);
$Amount = $price * 100;
$Description = $description;

$api = new Tinkoff_API();
$response = $api->payment_init([
    'OrderId' => $OrderId,
    'CustomerKey' => $CustomerKey,
    'Amount' => $Amount,
    'Description' => $Description,
]);

if (!$response) {
    die($api->error);
}

$table = "`transactions`";
$columns = "`CustomerKey`, `Description`, `Amount`, `OrderId`, `Status`, `PaymentId`, `initialized_at`";
$values = "'{$CustomerKey}', '" . $mysqli->escape_string($Description) . "'";
$values .= ", '{$response->Amount}', '{$response->OrderId}', '{$response->Status}'";
$values .= ", '{$response->PaymentId}', NOW()";
$query = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
if (!$mysqli->query($query) ) {
    die(printf("Error: %s\n", $mysqli->error) );
}

$PaymentId = $response->PaymentId;

$response = $api->payment_charge([
    'PaymentId' => $PaymentId,
    'RebillId' => $RebillId,
]);

if (!$response) {
    die($api->error);
}

if ($response->Success) {
    $mysqli = new mysqli('localhost', 'test', '', 'test');
    $table_references = "`transactions`";
    $set = "`completed_at` = NOW()";
    $set .= !empty($response->Status) ? ", `Status` = '{$response->Status}'" : "";
    $query = "UPDATE {$table_references} SET {$set} WHERE {$where}";
    $mysqli->query($query);
}

$mysqli->close();

print_r($response);
