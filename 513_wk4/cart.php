<?php
session_start();
$pdo = new PDO('sqlite:harrys.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $id = $_POST['item_id'];
    $_SESSION['cart'][$id] = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id] + 1 : 1;
    header("Location: cart.php"); exit;
}
if (isset($_GET['remove'])) { unset($_SESSION['cart'][$_GET['remove']]); header("Location: cart.php"); exit; }

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Your Cart</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; margin: 0;}
        .header { background: #333; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: #ff9900; text-decoration: none; font-weight: bold; margin-left:15px;}
        .cart-container { max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px;}
        .cart-item { display: flex; justify-content: space-between; border-bottom: 1px solid #ccc; padding: 15px 0; }
        .total { font-size: 24px; font-weight: bold; text-align: right; margin-top: 20px; }
        .checkout-btn { background: #28a745; color: white; padding: 15px; text-align: center; text-decoration: none; display: block; margin-top: 20px; border-radius: 5px; font-weight: bold;}
        .remove-btn { color: red; text-decoration: none; font-size: 14px; margin-left: 10px;}
    </style>
</head>
<body>
    <div class="header">
        <h2>Your Cart</h2>
        <div><a href="index.php">← Back to Menu</a><a href="cart.php">🛒 Cart (<?php echo $cart_count; ?>)</a></div>
    </div>
    <div class="cart-container">
        <?php
        $total_price = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $qty) {
                $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?"); $stmt->execute([$id]); $item = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($item) {
                    $sub = $item['price'] * $qty; $total_price += $sub;
                    echo "<div class='cart-item'><div><b>{$item['name']}</b> (x $qty)</div><div>$" . number_format($sub, 2) . " <a href='cart.php?remove=$id' class='remove-btn'>Remove</a></div></div>";
                }
            }
        } else { echo "<p style='text-align:center;'>Your cart is empty.</p>"; }
        ?>
        <div class="total">Total: $<?php echo number_format($total_price, 2); ?></div>
        <?php if ($total_price > 0): ?><a href="checkout.php" class="checkout-btn">Proceed to Checkout</a><?php endif; ?>
    </div>
</body>
</html>