<?php
require("yakuzaconnection.php");
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM users WHERE id = $id AND email != 'overseer@campuscollective.com'");
}
header("Location: admin_dashboard.php");
exit();
?>
