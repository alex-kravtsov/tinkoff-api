<?php

define('TINKOFF_TERMINAL_KEY', getenv('TINKOFF_TERMINAL_KEY') );
define('TINKOFF_SECRET_KEY', getenv('TINKOFF_SECRET_KEY') );

if (empty($_POST['Token']) ) {
    die('NOTOK');
}

$_POST['Password'] = TINKOFF_SECRET_KEY;
ksort($_POST);
$sorted = $_POST;
$original_token = $sorted['Token'];
unset($sorted['Token']);
$values = implode('', array_values($sorted));
$token = hash('sha256', $values);
if ($token != $original_token) {
    die('NOTOK');
}

$TerminalKey = !empty($_POST['TerminalKey']) ? $_POST['TerminalKey'] : null;
$OrderId = !empty($_POST['OrderId']) ? $_POST['OrderId'] : null;
$Success = !empty($_POST['Success']) ? $_POST['Success'] : null;
$Status = !empty($_POST['Status']) ? $_POST['Status'] : null;
$PaymentId = !empty($_POST['PaymentId']) ? $_POST['PaymentId'] : null;
$ErrorCode = !empty($_POST['ErrorCode']) ? $_POST['ErrorCode'] : null;
$Amount = !empty($_POST['Amount']) ? $_POST['Amount'] : null;
$RebillId = !empty($_POST['RebillId']) ? $_POST['RebillId'] : null;
$CardId = !empty($_POST['CardId']) ? $_POST['CardId'] : null;
$Pan = !empty($_POST['Pan']) ? $_POST['Pan'] : null;
$ExpDate = !empty($_POST['ExpDate']) ? $_POST['ExpDate'] : null;

if ($Success == 'true') {
    $mysqli = new mysqli('localhost', 'test', '', 'test');
    $table_references = "`transactions`";
    $set = "`completed_at` = NOW()";
    $set .= $Status ? ", `Status` = '{$Status}'" : "";
    $set .= $RebillId ? ", `RebillId` = '{$RebillId}'" : "";
    $set .= $CardId ? ", `CardId` = '{$CardId}'" : "";
    $set .= $Pan ? ", `Pan` = '{$Pan}'" : "";
    $set .= $ExpDate ? ", `ExpDate` = '{$ExpDate}'" : "";
    $where = "`PaymentId` = '{$PaymentId}'";
    $query = "UPDATE {$table_references} SET {$set} WHERE {$where}";
    $mysqli->query($query);
}

header("Content-Type: text/plain; charset=utf-8");
echo "OK";
