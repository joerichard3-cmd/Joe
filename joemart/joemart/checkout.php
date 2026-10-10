<?php
require __DIR__ . '/config.php';
require_login();
$cart = $_SESSION['cart'] ?? [];
if (!$cart) { header('Location: cart.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $address = trim($_POST['address'] ?? '');
    if (strlen($address) < 8) $error = 'Please enter your full delivery address.';
    else {
        try {
            $pdo->beginTransaction();
            $total = 0; $rows = [];
            foreach ($cart as $id => $qty) {
                $stmt = $pdo->prepare("SELECT id,name,price,stock FROM products WHERE id=? FOR UPDATE");
                $stmt->execute([(int)$id]); $p = $stmt->fetch();
                if (!$p || (int)$p['stock'] < (int)$qty || (int)$qty < 1) throw new RuntimeException('Some items are unavailable or out of stock. Please update your cart.');
                $p['qty'] = (int)$qty; $total += (float)$p['price'] * $p['qty']; $rows[] = $p;
            }
            $stmt = $pdo->prepare("INSERT INTO orders(customer_id,total,address,payment_method,payment_status) VALUES(?,?,?,'COD','Pending')");
            $stmt->execute([$_SESSION['user']['id'],$total,$address]); $orderId=(int)$pdo->lastInsertId();
            foreach($rows as $p) {
                $stmt=$pdo->prepare("INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?,?,?,?)");
                $stmt->execute([$orderId,$p['id'],$p['qty'],$p['price']]);
                $stmt=$pdo->prepare("UPDATE products SET stock=stock-? WHERE id=?"); $stmt->execute([$p['qty'],$p['id']]);
            }
            $pdo->commit(); unset($_SESSION['cart']);
            header('Location: orders.php?placed=1'); exit;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $ex instanceof RuntimeException ? $ex->getMessage() : 'Could not place order. Please try again.';
        }
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Checkout | Joe Mart</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="cart.php">Cart</a><a href="orders.php">My Orders</a></nav></header><main class="auth-wrap"><form class="auth-card" method="post"><p class="eyebrow">ALMOST THERE</p><h1>Checkout</h1><p>Payment method: Cash on Delivery (COD)</p><?php if($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><label>Delivery address</label><textarea name="address" rows="4" required minlength="8"></textarea><button class="btn full">Place order</button><p class="muted">Online payments are not enabled in this starter version. Add a payment gateway before accepting online payments.</p></form></main></body></html>
