<?php
session_start();
try {
    $pdo = new PDO('sqlite:harrys.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 自动建表：菜单 (加入了 image 字段)
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, description TEXT, price REAL, image TEXT, available INTEGER DEFAULT 1);");
    
    // 自动建表：用户
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE, password TEXT, role TEXT DEFAULT 'customer');");
    
    // 自动建表：订单
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, total REAL, status TEXT DEFAULT 'Pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP);");
    
    // 自动建表：订单明细
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id INTEGER, menu_item_id INTEGER, quantity INTEGER, price REAL);");
    
    // 插入 8 个默认数据（包含图片路径）
    $count = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO menu_items (name, description, price, image) VALUES 
            ('Tiger Pie', 'Classic meat pie with mushy peas', 8.50, 'images/pie1.jpg'), 
            ('Chilli Pie', 'Meat pie with chilli con carne', 9.50, 'images/pie2.jpg'), 
            ('Hot Dog de Wheels', 'Classic hot dog with chilli', 7.00, 'images/hotdog.jpg'),
            ('Coca-Cola', 'Cold can of Coke', 3.00, 'images/coke.jpg'),
            ('Hot Chips', 'Crispy golden potato chips', 4.50, 'images/fries.jpg'),
            ('Milkshake', 'Vanilla, Chocolate or Strawberry', 5.50, 'images/milkshake.jpg'),
            ('Classic Burger', 'Beef patty with cheese and salad', 12.00, 'images/burger.jpg'),
            ('Bottled Water', '500ml chilled water', 2.50, 'images/water.jpg');");
        
        // 预置管理员账号
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@harrys.com', '$hashed', 'admin');");
    }

    // 处理搜索逻辑
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE available = 1 AND (name LIKE ? OR description LIKE ?)");
        $stmt->execute(["%$search%", "%$search%"]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $items = $pdo->query("SELECT * FROM menu_items WHERE available = 1")->fetchAll(PDO::FETCH_ASSOC);
    }

} catch(PDOException $e) { die("DB Error: " . $e->getMessage()); }

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Harry's Cafe de Wheels</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; margin: 0;}
        .header { background: #333; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: #ff9900; text-decoration: none; font-weight: bold; margin-left: 15px;}
        .search-bar { padding: 0 30px; margin-top: 20px; display: flex; gap: 10px;}
        .search-bar input { padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 5px;}
        .search-bar button { padding: 10px 20px; background: #333; color: white; border: none; cursor: pointer; border-radius: 5px;}
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; padding: 0 30px;}
        .card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; flex-direction: column;}
        .card img { width: 100%; height: 180px; object-fit: cover; border-radius: 5px; margin-bottom: 15px;}
        .price { color: green; font-weight: bold; font-size: 18px; margin-top: auto;}
        button { background: #ff9900; border: none; padding: 10px; width: 100%; color: white; font-weight: bold; cursor: pointer; border-radius: 5px; margin-top: 10px;}
        button:hover { background: #e68a00; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Harry's Cafe de Wheels</h2>
        <div>
            <?php if (isset($_SESSION['user_name'])): ?>
                <span style="color:white;">Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php">Logout</a>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php" style="background:#e68a00; padding:5px; border-radius:3px; color:white;">Admin Panel</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php">Login / Register</a>
            <?php endif; ?>
            <a href="cart.php">🛒 Cart (<?php echo $cart_count; ?>)</a>
        </div>
    </div>

    <div class="search-bar">
        <form method="GET" action="index.php" style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search for pies, drinks..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if($search): ?><a href="index.php" style="padding: 10px; color: red; text-decoration: none;">Clear</a><?php endif; ?>
        </form>
    </div>

    <div class="grid">
        <?php if(empty($items)): ?>
            <p style="padding-left: 30px; font-size: 18px;">No items found for "<?php echo htmlspecialchars($search); ?>".</p>
        <?php endif; ?>

        <?php foreach ($items as $item): ?>
            <div class="card">
                <!-- 这行是显示图片的 -->
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                <p style="color: #666; font-size: 14px;"><?php echo htmlspecialchars($item['description']); ?></p>
                <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                <form action="cart.php" method="POST">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>