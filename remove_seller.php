<?php
require("yakuzaconnection.php");
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM sellers WHERE id = $id");
}
header("Location: admin_dashboard.php");
exit();
?>
