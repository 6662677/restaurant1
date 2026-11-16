<?php
session_start();
$pdo = new PDO('sqlite:harrys.db');
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>My Orders</title>
    <style>body{font-family:Arial;padding:20px;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #ddd;padding:10px;text-align:left;}</style>
</head>
<body>
    <h2>My Order History</h2>
    <a href="index.php">¡û Back to Menu</a>
    <table>
        <tr><th>Order ID</th><th>Total</th><th>Date</th><th>Status</th></tr>
        <?php foreach($orders as $o): ?>
            <tr><td>#<?php echo $o['id']; ?></td><td>$<?php echo number_format($o['total'], 2); ?></td><td><?php echo $o['created_at']; ?></td><td><b><?php echo $o['status']; ?></b></td></tr>
        <?php endforeach; ?>
    </table>
</body>
</html>                                                                                                                                                                                                      