<?php
session_start();
$pdo = new PDO('sqlite:harrys.db');

// 安全拦截：非管理员不能进
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h1 style='text-align:center;color:red;margin-top:50px;'>Access Denied. You must be an admin to view this page.</h1><p style='text-align:center;'><a href='login.php'>Go to Login</a></p>");
}

// 处理修改订单状态
if (isset($_POST['update_status'])) {
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$_POST['status'], $_POST['order_id']]);
    header("Location: admin.php"); exit;
}

// 处理修改菜单上下架
if (isset($_POST['toggle_item'])) {
    $pdo->prepare("UPDATE menu_items SET available = 1 - available WHERE id = ?")->execute([$_POST['item_id']]);
    header("Location: admin.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Admin Panel</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .header { background: #222; color: white; padding: 15px 30px; display: flex; justify-content: space-between; }
        .header a { color: #ff9900; text-decoration: none; font-weight: bold; }
        .section { background: white; padding: 20px; margin-top: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
        select, button { padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Admin Dashboard</h2>
        <a href="index.php">← Back to Website</a>
    </div>

    <div class="section">
        <h3>Incoming Orders (Order Management)</h3>
        <table>
            <tr><th>Order ID</th><th>Total</th><th>Time</th><th>Status</th><th>Action</th></tr>
            <?php
            $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            $total_revenue = 0;
            foreach ($orders as $o) {
                if ($o['status'] !== 'Cancelled') $total_revenue += $o['total'];
                echo "<tr><td>#{$o['id']}</td><td>$" . number_format($o['total'], 2) . "</td><td>{$o['created_at']}</td><td><b>{$o['status']}</b></td>";
                echo "<td>
                        <form method='POST' style='display:flex;'>
                            <input type='hidden' name='order_id' value='{$o['id']}'>
                            <select name='status'>
                                <option value='Pending'>Pending</option>
                                <option value='Preparing'>Preparing</option>
                                <option value='Ready'>Ready</option>
                                <option value='Completed'>Completed</option>
                                <option value='Cancelled'>Cancelled</option>
                            </select>
                            <button type='submit' name='update_status'>Update</button>
                        </form>
                      </td></tr>";
            }
            ?>
        </table>
        <h3 style="color: green; margin-top: 20px;">Total Revenue: $<?php echo number_format($total_revenue, 2); ?></h3>
    </div>

    <div class="section">
        <h3>Menu Management</h3>
        <table>
            <tr><th>ID</th><th>Item</th><th>Price</th><th>Status</th><th>Action</th></tr>
            <?php
            $items = $pdo->query("SELECT * FROM menu_items")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($items as $i) {
                $status = $i['available'] ? "<span style='color:green;'>Available</span>" : "<span style='color:red;'>Sold Out</span>";
                echo "<tr><td>{$i['id']}</td><td>{$i['name']}</td><td>$" . number_format($i['price'], 2) . "</td><td>$status</td>";
                echo "<td>
                        <form method='POST'>
                            <input type='hidden' name='item_id' value='{$i['id']}'>
                            <button type='submit' name='toggle_item'>Toggle Status</button>
                        </form>
                      </td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>