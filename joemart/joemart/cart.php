<?php
require __DIR__ . '/config.php';
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['product_id'] ?? 0);
    if ($action === 'add' && $id > 0) {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id=?"); $stmt->execute([$id]); $p = $stmt->fetch();
        if ($p && (int)$p['stock'] > 0) $_SESSION['cart'][$id] = min((int)$p['stock'], (int)($_SESSION['cart'][$id] ?? 0) + 1);
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
    } elseif ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $pid => $qty) {
            $pid = (int)$pid; $qty = max(0, (int)$qty);
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id=?"); $stmt->execute([$pid]); $p = $stmt->fetch();
            if (!$p || $qty === 0) unset($_SESSION['cart'][$pid]);
            else $_SESSION['cart'][$pid] = min($qty, (int)$p['stock']);
        }
    }
    header('Location: cart.php'); exit;
}
$items=[]; $total=0;
if ($_SESSION['cart']) {
    $ids=array_map('intval', array_keys($_SESSION['cart']));
    $marks=implode(',', array_fill(0,count($ids),'?'));
    $stmt=$pdo->prepare("SELECT * FROM products WHERE id IN ($marks)"); $stmt->execute($ids);
    foreach($stmt->fetchAll() as $p) { $p['quantity']=(int)$_SESSION['cart'][$p['id']]; $p['line_total']=$p['quantity']*(float)$p['price']; $total += $p['line_total']; $items[]=$p; }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Your Cart | Joe Mart</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="index.php">Continue shopping</a><a href="dashboard.php">Dashboard</a></nav></header><main class="container page"><h1>Your shopping cart</h1><?php if(!$items): ?><p class="empty">Your cart is empty. <a href="index.php">Browse groceries</a>.</p><?php else: ?><form method="post" class="panel"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="update"><div class="table-wrap"><table><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr><?php foreach($items as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= money($p['price']) ?></td><td><input class="qty" type="number" min="0" max="<?= (int)$p['stock'] ?>" name="qty[<?= (int)$p['id'] ?>]" value="<?= (int)$p['quantity'] ?>"></td><td><?= money($p['line_total']) ?></td><td><button class="link-button" formaction="cart.php" formmethod="post" name="remove_id" value="<?= (int)$p['id'] ?>" onclick="this.form.action.value='remove';this.form.product_id.value=this.value">Remove</button></td></tr><?php endforeach; ?></table></div><div class="cart-summary"><strong>Total: <?= money($total) ?></strong><button class="btn">Update cart</button></div><input type="hidden" name="product_id" value=""></form><p><a class="btn" href="checkout.php">Proceed to checkout</a></p><?php endif; ?></main></body></html>
