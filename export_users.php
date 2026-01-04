<?php
// ONLINE SOURCES WERE USED TO HELP CONSTRUCTING THIS CODE...
require("yakuzaconnection.php");

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=users.csv');

// Open the output stream
$output = fopen('php://output', 'w');

// Write the column headings to the CSV
fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Role', 'Joined']);

// Fetch the data from the database and write it to the CSV
$res = $conn->query("SELECT id, firstname, lastname, email, role, created_at FROM users");
while ($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}

// Close the output stream
fclose($output);

// Optional: Provide a message after file download is complete (but it won't be displayed due to header redirection)
exit();
?>
