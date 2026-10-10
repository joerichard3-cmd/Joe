<?php
require __DIR__ . '/config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter a name, valid email, and password with at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'customer')");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1'); exit;
        } catch (PDOException $ex) { $error = 'That email may already be registered.'; }
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Register | Joe Mart</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="index.php">Home</a><a href="login.php">Login</a></nav></header><main class="auth-wrap"><form class="auth-card" method="post"><p class="eyebrow">JOIN JOE MART</p><h1>Create account</h1><?php if($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><label>Full name</label><input name="name" required maxlength="100"><label>Email</label><input type="email" name="email" required><label>Password (min. 8 characters)</label><input type="password" name="password" minlength="8" required><button class="btn full">Create account</button><p class="muted">Already registered? <a href="login.php">Login</a></p></form></main></body></html>
