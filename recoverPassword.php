<?php
require_once "yakuzaconnection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $security_answer = trim($_POST['security_answer'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        header("Location: forgotPassword.php?msg=nomatch");
        exit;
    }

    $enc_security_ans = maz_encrypts($security_answer);

    // Find user by security question and answer
    $stmt = $conn->prepare("SELECT id FROM users WHERE securityQ = ? AND securityA = ?");
    $stmt->bind_param("ss", $security_question, $enc_security_ans);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param("si", $hashed_password, $user['id']);
        if ($update->execute()) {
            header("Location: forgotPassword.php?msg=reset");
            exit;
        } else {
            header("Location: forgotPassword.php?msg=error");
            exit;
        }
    } else {
        header("Location: forgotPassword.php?msg=notfound");
        exit;
    }
}
?>