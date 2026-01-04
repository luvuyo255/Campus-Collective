<?php
require_once "yakuzaconnection.php";

// get the token from URL
$token = $_GET['token'] ?? '';
if (!$token) exit('No token provided.'); // stop if missing

// check DB for this token
$stmt = $conn->prepare("SELECT id, reset_token_expires FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) exit('Invalid token.'); // bad token

$row = $res->fetch_assoc();

// check if token expired
if ($row['reset_token_expires'] < date('Y-m-d H:i:s')) exit('Token expired.');

$userId = $row['id'];

// handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 8) exit('Password too short.'); // require decent length

    // hash it and save in DB, clear token
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
    $update->bind_param("si", $hash, $userId);
    $update->execute();

    exit('Password reset successful.'); // done
}
