<?php
require __DIR__ . '/config.php';
$q = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id";
$params = [];
if ($q !== '') {
    $sql .= " WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Joe Mart | Fresh Groceries</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="index.php">Home</a><a href="cart.php">Cart (<?= array_sum($_SESSION['cart'] ?? []) ?>)</a><?php if (!empty($_SESSION['user'])): ?><a href="orders.php">My Orders</a><a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a><?php else: ?><a href="login.php">Login</a><a href="register.php">Register</a><?php endif; ?></nav></header>
<section class="hero"><div><p class="eyebrow">FRESH • LOCAL • EVERYDAY</p><h1>Fresh groceries,<br><span>delivered simply.</span></h1><p>Everyday essentials at friendly prices.</p><form class="search" method="get"><input name="q" placeholder="Search vegetables, fruits, milk..." value="<?= e($q) ?>"><button>Search</button></form></div><div class="hero-art">🥦🍎🥕<br>🥛🍞🍌</div></section>
<main class="container"><div class="section-heading"><div><p class="eyebrow">SHOP THE ESSENTIALS</p><h2><?= $q !== '' ? 'Search results' : 'Popular groceries' ?></h2></div><span><?= count($products) ?> products</span></div>
<div class="products"><?php foreach ($products as $p): ?><article class="product-card"><div class="product-image"><?php if (!empty($p['image_url'])): ?><img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>"><?php else: ?><span>🥬</span><?php endif; ?></div><div class="product-info"><small><?= e($p['category'] ?? 'Grocery') ?></small><h3><?= e($p['name']) ?></h3><p class="muted"><?= e($p['description'] ?? '') ?></p><div class="product-bottom"><strong><?= money($p['price']) ?></strong><span class="stock"><?= (int)$p['stock'] > 0 ? 'In stock' : 'Out of stock' ?></span></div><form action="cart.php" method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="action" value="add"><button class="btn full" <?= (int)$p['stock'] < 1 ? 'disabled' : '' ?>>Add to cart</button></form></div></article><?php endforeach; ?></div>
<?php if (!$products): ?><p class="empty">No products found. Try another search.</p><?php endif; ?></main><footer>© <?= date('Y') ?> Joe Mart · Freshness for every home</footer></body></html>
