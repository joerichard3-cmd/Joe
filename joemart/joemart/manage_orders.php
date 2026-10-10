<?php
require __DIR__ . '/config.php';
require_role(['admin','seller']);
csrf_check();
$role=$_SESSION['user']['role']; $message='';
$allowed=['Pending','Confirmed','Shipped','Delivered','Cancelled'];
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)($_POST['id']??0); $status=$_POST['status']??'';
    if(in_array($status,$allowed,true)) {
        // Admin can manage all orders. Sellers can see/manage orders containing their products.
        if($role==='admin') { $stmt=$pdo->prepare("UPDATE orders SET status=? WHERE id=?"); $stmt->execute([$status,$id]); }
        else {
            $stmt=$pdo->prepare("UPDATE orders o SET o.status=? WHERE o.id=? AND EXISTS (SELECT 1 FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=o.id AND p.seller_id=?)");
            $stmt->execute([$status,$id,$_SESSION['user']['id']]);
        }
        $message='Order status updated if you had permission.';
    }
}
if($role==='admin') $orders=$pdo->query("SELECT o.*,u.name AS customer FROM orders o JOIN users u ON u.id=o.customer_id ORDER BY o.created_at DESC")->fetchAll();
else { $stmt=$pdo->prepare("SELECT DISTINCT o.*,u.name AS customer FROM orders o JOIN users u ON u.id=o.customer_id JOIN order_items oi ON oi.order_id=o.id JOIN products p ON p.id=oi.product_id WHERE p.seller_id=? ORDER BY o.created_at DESC"); $stmt->execute([$_SESSION['user']['id']]); $orders=$stmt->fetchAll(); }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Manage Orders | Joe Mart</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="dashboard.php">Dashboard</a></nav></header><main class="container page"><h1>Order management</h1><?php if($message): ?><p class="success"><?= e($message) ?></p><?php endif; ?><div class="table-wrap panel"><table><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Update</th></tr><?php foreach($orders as $o): ?><tr><td>#<?= (int)$o['id'] ?><br><small><?= e($o['created_at']) ?></small></td><td><?= e($o['customer']) ?></td><td><?= money($o['total']) ?></td><td><?= e($o['status']) ?></td><td><form method="post" class="inline"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>"><select name="status"><?php foreach($allowed as $s): ?><option <?= $s===$o['status']?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select><button class="btn small">Save</button></form></td></tr><?php endforeach; ?></table></div></main></body></html>
