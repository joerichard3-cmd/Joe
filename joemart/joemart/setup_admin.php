<?php
// Run this once locally, then DELETE this file.
require __DIR__ . '/config.php';
$email = 'admin@joemart.local';
$password = 'ChangeMe123!';
$name = 'Joe Mart Admin';
$stmt = $pdo->prepare("SELECT id FROM users WHERE email=?"); $stmt->execute([$email]);
if ($stmt->fetch()) {
    $stmt = $pdo->prepare("UPDATE users SET role='admin' WHERE email=?"); $stmt->execute([$email]);
    echo 'Admin role confirmed. Email: '.htmlspecialchars($email).' Password remains unchanged if the account already existed.';
} else {
    $stmt = $pdo->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'admin')");
    $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
    echo 'Admin created. Email: '.htmlspecialchars($email).' Temporary password: '.htmlspecialchars($password).'. Log in and change this password immediately.';
}
