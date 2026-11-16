<?php
session_start();
$pdo = new PDO('sqlite:harrys.db');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if (empty($_SESSION['cart'])) { header("Location: index.php"); exit; }

$total_price = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    $stmt = $pdo->prepare("SELECT price FROM menu_items WHERE id = ?"); $stmt->execute([$id]); $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) $total_price += $item['price'] * $qty;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 生成订单
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Pending')");
    $stmt->execute([$_SESSION['user_id'], $total_price]);
    $order_id = $pdo->lastInsertId();
    
    // 记录订单明细
    foreach ($_SESSION['cart'] as $id => $qty) {
        $stmt = $pdo->prepare("SELECT price FROM menu_items WHERE id = ?"); $stmt->execute([$id]); $item = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)")->execute([$order_id, $id, $qty, $item['price']]);
    }
    unset($_SESSION['cart']);
    header("Location: success.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Checkout</title>
    <style>body{font-family:Arial;background:#f4f4f4;padding:20px;}.box{background:#fff;max-width:500px;margin:50px auto;padding:30px;border-radius:8px;text-align:center;}button{width:100%;padding:15px;background:#28a745;color:#fff;border:none;font-size:18px;cursor:pointer;border-radius:5px;margin-top:20px;}</style>
</head>
<body>
    <div class="box">
        <h2>Checkout</h2>
        <p>Simulated payment. No real money charged.</p>
        <h3>Total: <span style="color: green;">$<?php echo number_format($total_price, 2); ?></span></h3>
        <form method="POST"><button type="submit">Confirm Simulated Payment</button></form>
    </div>
</body>
</html>