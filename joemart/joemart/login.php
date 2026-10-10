<?php
require __DIR__ . '/config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT id,name,email,password,role FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Incorrect email or password.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login | Joe Mart</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="index.php">Home</a><a href="register.php">Register</a></nav></header><main class="auth-wrap"><form class="auth-card" method="post"><p class="eyebrow">WELCOME BACK</p><h1>Login</h1><?php if(isset($_GET['registered'])): ?><p class="success">Account created. Please log in.</p><?php endif; ?><?php if($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><label>Email</label><input type="email" name="email" required><label>Password</label><input type="password" name="password" required><button class="btn full">Login</button><p class="muted">New customer? <a href="register.php">Create account</a></p></form></main></body></html>
