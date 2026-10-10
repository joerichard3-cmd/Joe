<?php
require __DIR__ . '/config.php';
require_role(['admin','seller']);
csrf_check();
$role=$_SESSION['user']['role']; $sellerId=(int)$_SESSION['user']['id']; $message='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if($action==='save') {
        $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $description=trim($_POST['description']??'');
        $price=(float)($_POST['price']??0); $stock=(int)($_POST['stock']??0); $category=(int)($_POST['category_id']??0) ?: null;
        if($name===''||$price<0||$stock<0) $message='Check product name, price and stock.';
        elseif($id>0) {
            if($role==='admin') $stmt=$pdo->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,category_id=? WHERE id=?");
            else $stmt=$pdo->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,category_id=? WHERE id=? AND seller_id=?");
            $args=[$name,$description,$price,$stock,$category,$id]; if($role!=='admin') $args[]=$sellerId;
            $stmt->execute($args); $message='Product updated.';
        } else {
            $stmt=$pdo->prepare("INSERT INTO products(seller_id,name,description,price,stock,category_id) VALUES(?,?,?,?,?,?)");
            $stmt->execute([$role==='admin'?null:$sellerId,$name,$description,$price,$stock,$category]); $message='Product added.';
        }
    } elseif($action==='delete') {
        $id=(int)($_POST['id']??0);
        if($role==='admin') $stmt=$pdo->prepare("DELETE FROM products WHERE id=?");
        else $stmt=$pdo->prepare("DELETE FROM products WHERE id=? AND seller_id=?");
        $args=[$id]; if($role!=='admin') $args[]=$sellerId; $stmt->execute($args); $message='Product removed if you had permission.';
    }
}
$cats=$pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
if($role==='admin') $products=$pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
else { $stmt=$pdo->prepare("SELECT * FROM products WHERE seller_id=? ORDER BY id DESC"); $stmt->execute([$sellerId]); $products=$stmt->fetchAll(); }
$edit=null; if(isset($_GET['edit'])) { $id=(int)$_GET['edit']; foreach($products as $p) if((int)$p['id']===$id) $edit=$p; }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Products | Joe Mart</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php">🛒 Joe Mart</a><nav><a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a></nav></header><main class="container page"><h1>Product management</h1><?php if($message): ?><p class="success"><?= e($message) ?></p><?php endif; ?><div class="panel form-panel"><h2><?= $edit?'Edit product':'Add a product' ?></h2><form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><label>Product name</label><input name="name" required value="<?= e($edit['name']??'') ?>"><label>Description</label><textarea name="description" rows="2"><?= e($edit['description']??'') ?></textarea><div class="form-row"><div><label>Price (₹)</label><input type="number" name="price" min="0" step="0.01" required value="<?= e(isset($edit['price'])?(string)$edit['price']:'') ?>"></div><div><label>Stock</label><input type="number" name="stock" min="0" required value="<?= e(isset($edit['stock'])?(string)$edit['stock']:'0') ?>"></div></div><label>Category</label><select name="category_id"><option value="">No category</option><?php foreach($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= isset($edit['category_id'])&&(int)$edit['category_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><button class="btn">Save product</button></form></div><h2>Products</h2><div class="table-wrap panel"><table><tr><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th></tr><?php foreach($products as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= money($p['price']) ?></td><td><?= (int)$p['stock'] ?></td><td><a href="?edit=<?= (int)$p['id'] ?>">Edit</a> <form class="inline" method="post" onsubmit="return confirm('Delete this product?')"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="link-button">Delete</button></form></td></tr><?php endforeach; ?></table></div></main></body></html>
