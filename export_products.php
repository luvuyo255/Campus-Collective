<?php
require("yakuzaconnection.php");// ONLINE SOURCES WERE USED TO HELP CONSTRUCTING THIS CODE...

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=products.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Description', 'Price', 'Type', 'Status', 'User ID']);

$res = $conn->query("SELECT id, prodName, prodDesc, prodPrice, prodType, status, user_id FROM products WHERE status != 'deleted'");
while ($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>