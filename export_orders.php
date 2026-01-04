<?php
require("yakuzaconnection.php"); //Used online sources for exporting code-->
// ONLINE SOURCES WERE USED TO HELP CONSTRUCTING THIS CODE...

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=orders.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Order ID', 'Buyer Name', 'Product ID', 'Message', 'Status', 'Location', 'Phone', 'Created At']);

$res = $conn->query("
    SELECT o.id, u.full_name AS buyer_name, o.product_id, o.message, o.status, o.location, o.phone_number, o.created_at
    FROM orders o
    JOIN users u ON o.buyer_id = u.id
    ORDER BY o.created_at DESC
");
while ($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>