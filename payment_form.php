<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$mysqli = new mysqli('localhost', 'test', '', 'test');
if ($mysqli->connect_errno) {
    die(printf("Connect failed: %s\n", $mysqli->connect_error) );
}
if (!$mysqli->set_charset("utf8")) {
    die(printf("Error loading character set utf8: %s\n", $mysqli->error) );
}

// Get customer
$columns = "*";
$table_references = "`test_customers`";
$limit = 1;
$query = "SELECT {$columns} FROM {$table_references} LIMIT {$limit}";
if (!$result = $mysqli->query($query) ) {
    die(printf("Error: %s\n", $mysqli->error) );
}

$customer = null;
while ($row = $result->fetch_assoc() ) {
    $customer = $row;
}
$result->free();

if (!$customer) {
    die("Cannot get customer.");
}

// Create order
$price = 10; // roubles
$description = "Тестовый заказ";
$table = "`test_orders`";
$columns = "`customer_id`, `price`, `description`";
$values = "{$customer['id']}, {$price}, '{$description}'";
$query = "INSERT INTO {$table} ({$columns}) VALUES ($values)";
if (!$mysqli->query($query) ) {
    die(printf("Error: %s\n", $mysqli->error) );
}

$TEMPLATE_VARS = [];
$TEMPLATE_VARS['order_id'] = sprintf("t%d", $mysqli->insert_id);
$TEMPLATE_VARS['customer_key'] = sprintf("u%d", $customer['id']);
$TEMPLATE_VARS['email'] = htmlspecialchars($customer['email']);
$TEMPLATE_VARS['price'] = number_format($price, 2, ".", "");
$TEMPLATE_VARS['amount'] = $price * 100;
$TEMPLATE_VARS['description'] = htmlspecialchars($description);

$mysqli->close();

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Тестирование платежей Тинькофф</title>
    </head>
    <body>
        <style>
         .order-layout {
             border-collapse: collapse;
             background: Gainsboro;
         }

         .order-layout th,
         .order-layout td {
             padding: 10px;
         }

         .order-layout footer td {
             padding-top: 20px;
             text-align: right;
         }
        </style>

        <h1>Тестирование платежей Тинькофф</h1>

        <h3>Тестовый заказ с опцией автоплатежа</h3>
        <form method="post" action="payment_init.php">
            <table class="order-layout">
                <tr>
                    <th>ID Заказа</th>
                    <td><?= $TEMPLATE_VARS['order_id'] ?></td>
                </tr>
                <tr>
                    <th>Описание</th>
                    <td><?= $TEMPLATE_VARS['description'] ?></td>
                </tr>
                <tr>
                    <th>Цена</th>
                    <td><?= $TEMPLATE_VARS['price'] ?> руб.</td>
                </tr>
                <tr>
                    <th>Включить автоплатёж.</th>
                    <td><input type="checkbox" checked="checked" disabled="disabled" /></td>
                </tr>
                <tfoot>
                    <tr>
                        <td colspan="2"><input type="submit" value="Перейти к оплате" /></td>
                    </tr>
                </tfoot>
            </table>
            <input type="hidden" name="OrderId" value="<?= $TEMPLATE_VARS['order_id'] ?>" />
            <input type="hidden" name="CustomerKey" value="<?= $TEMPLATE_VARS['customer_key'] ?>" />
            <input type="hidden" name="Email" value="<?= $TEMPLATE_VARS['email'] ?>" />
            <input type="hidden" name="Description" value="<?= $TEMPLATE_VARS['description'] ?>" />
            <input type="hidden" name="Amount" value="<?= $TEMPLATE_VARS['amount'] ?>" />
            <input type="hidden" name="Recurrent" value="Y" />
        </form>
    </body>
</html>
