<?php
require __DIR__ . '/config.php';
require_role(['admin']);
$users=$pdo->query("SELECT id,name,email,role,created_at FROM users ORDER BY id DESC")->fetchAll();
$products=(int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orders=(int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
if($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check(); $id=(int)($_POST['id']??0); $role=$_POST['role']??'customer';
    if(in_array($role,['admin','seller','customer'],true) && $id!==(int)$_SESSION['user']['id']) {
        $stmt=$pdo->prepare("UPDATE users SET role=? WHERE id=?"); $stmt->execute([$role,$id]);
        header('Location: admin.php'); exit;
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin | Joe Mart</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="dashboard.php">Dashboard</a><a href="products_manage.php">Products</a><a href="manage_orders.php">Orders</a></nav></header><main class="container page"><p class="eyebrow">ADMIN CONSOLE</p><h1>Store overview</h1><div class="stats"><div class="stat"><span>Products</span><strong><?= $products ?></strong></div><div class="stat"><span>Orders</span><strong><?= $orders ?></strong></div><div class="stat"><span>Users</span><strong><?= count($users) ?></strong></div></div><h2>User and role management</h2><p class="muted">Assign seller/admin roles only to trusted accounts. You cannot change your own role here.</p><div class="table-wrap panel"><table><tr><th>Name</th><th>Email</th><th>Role</th><th>Change role</th></tr><?php foreach($users as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td><td><?php if((int)$u['id']===(int)$_SESSION['user']['id']): ?>Current account<?php else: ?><form method="post" class="inline"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><select name="role"><?php foreach(['customer','seller','admin'] as $r): ?><option <?= $r===$u['role']?'selected':'' ?>><?= e($r) ?></option><?php endforeach; ?></select><button class="btn small">Update</button></form><?php endif; ?></td></tr><?php endforeach; ?></table></div></main></body></html>
