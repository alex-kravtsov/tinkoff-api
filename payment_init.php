<?php

use G24_Payment\Tinkoff_API;

error_reporting(E_ALL);
ini_set('display_errors', '1');

include "tinkoff_api.php";

$api = new Tinkoff_API();

$OrderId = !empty($_POST['OrderId']) ? $_POST['OrderId'] : null;
$CustomerKey = !empty($_POST['CustomerKey']) ? $_POST['CustomerKey'] : null;
$Email = !empty($_POST['Email']) ? $_POST['Email'] : null;
$Amount = !empty($_POST['Amount']) ? $_POST['Amount'] : null;
$Description = !empty($_POST['Description']) ? $_POST['Description'] : null;
$Recurrent = !empty($_POST['Recurrent']) ? $_POST['Recurrent'] : null;

$Receipt = [
    'Email' => $Email,
    'Taxation' => 'osn',
    'Items' => [
        [
            'Name' => "Выписка ЕГРН",
            'Price' => $Amount,
            'Quantity' => 1,
            'Amount' => $Amount,
            'Tax' => 'none',
        ],
    ],
];

$response = $api->payment_init([
    'OrderId' => $OrderId,
    'CustomerKey' => $CustomerKey,
    'Amount' => $Amount,
    'Description' => $Description,
    //'Recurrent' => $Recurrent,
    'Receipt' => $Receipt,
]);

if (!$response) {
    die($api->error);
}

$mysqli = new mysqli('localhost', 'test', '', 'test');
if ($mysqli->connect_errno) {
    die(printf("Connect failed: %s\n", $mysqli->connect_error) );
}
if (!$mysqli->set_charset("utf8")) {
    die(printf("Error loading character set utf8: %s\n", $mysqli->error) );
}

$table = "`transactions`";
$columns = "`CustomerKey`, `Description`, `Amount`, `OrderId`, `Status`, `PaymentId`, `PaymentURL`, `initialized_at`";
$values = "'{$CustomerKey}', '" . $mysqli->escape_string($Description) . "'";
$values .= ", '{$response->Amount}', '{$response->OrderId}', '{$response->Status}'";
$values .= ", '{$response->PaymentId}', '{$response->PaymentURL}', NOW()";
$query = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
if (!$mysqli->query($query) ) {
    die(printf("Error: %s\n", $mysqli->error) );
}

header("Location: {$response->PaymentURL}");
