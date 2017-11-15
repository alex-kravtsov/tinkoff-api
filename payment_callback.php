<?php

define('TINKOFF_TERMINAL_KEY', getenv('TINKOFF_TERMINAL_KEY') );
define('TINKOFF_SECRET_KEY', getenv('TINKOFF_SECRET_KEY') );

if (!empty($_POST['Token']) ) {
    $input = $_POST;
}
else {
    $raw_input = file_get_contents('php://input');
    if (empty($raw_input) ) {
        die('NOTOK');
    }

    $input = json_decode($raw_input, true);
    if (empty($input) || empty($input['Token']) ) {
        die('NOTOK');
    }
}

if (empty($input['Success']) ) {
    $input['Success'] = 'false';
}
elseif ($input['Success'] == 1) {
    $input['Success'] = 'true';
}

$input['Password'] = TINKOFF_SECRET_KEY;
ksort($input);
$original_token = $input['Token'];
unset($input['Token']);
$values = implode('', array_values($input) );
$token = hash('sha256', $values);
if ($token != $original_token) {
    die('NOTOK');
}

$TerminalKey = !empty($input['TerminalKey']) ? $input['TerminalKey'] : null;
$OrderId = !empty($input['OrderId']) ? $input['OrderId'] : null;
$Success = !empty($input['Success']) ? $input['Success'] : null;
$Status = !empty($input['Status']) ? $input['Status'] : null;
$PaymentId = !empty($input['PaymentId']) ? $input['PaymentId'] : null;
$ErrorCode = !empty($input['ErrorCode']) ? $input['ErrorCode'] : null;
$Amount = !empty($input['Amount']) ? $input['Amount'] : null;
$RebillId = !empty($input['RebillId']) ? $input['RebillId'] : null;
$CardId = !empty($input['CardId']) ? $input['CardId'] : null;
$Pan = !empty($input['Pan']) ? $input['Pan'] : null;
$ExpDate = !empty($input['ExpDate']) ? $input['ExpDate'] : null;

$mysqli = new mysqli('localhost', 'test', '', 'test');
$table_references = "`transactions`";
$set = "`updated_at` = NOW()";
$set .= $Status ? ", `Status` = '{$Status}'" : "";
$set .= $RebillId ? ", `RebillId` = '{$RebillId}'" : "";
$set .= $CardId ? ", `CardId` = '{$CardId}'" : "";
$set .= $Pan ? ", `Pan` = '{$Pan}'" : "";
$set .= $ExpDate ? ", `ExpDate` = '{$ExpDate}'" : "";
$where = "`PaymentId` = '{$PaymentId}'";
$query = "UPDATE {$table_references} SET {$set} WHERE {$where}";
$mysqli->query($query);

header("Content-Type: text/plain; charset=utf-8");
echo "OK";
