<?php
session_start();
$pdo = new PDO('sqlite:harrys.db');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']); $password = $_POST['password']; $action = $_POST['action'];
    if ($action === 'register') {
        $name = trim($_POST['name']); $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)")->execute([$name, $email, $hashed]);
            header("Location: login.php?success=1"); exit;
        } catch (Exception $e) { $error = "Email already exists."; }
    } elseif ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?"); $stmt->execute([$email]); $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; $_SESSION['user_name'] = $user['name']; $_SESSION['role'] = $user['role'];
            header("Location: index.php"); exit;
        } else { $error = "Invalid email or password."; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Login</title>
    <style>body{font-family:Arial;background:#f4f4f4;padding:20px;}.box{background:#fff;max-width:400px;margin:50px auto;padding:30px;border-radius:8px;}input{width:100%;padding:10px;margin:10px 0;box-sizing:border-box;}button{width:100%;padding:10px;background:#333;color:#fff;border:none;cursor:pointer;margin-top:10px;}.error{color:red;}.success{color:green;}a{display:block;text-align:center;margin-top:15px;color:#ff9900;text-decoration:none;}</style>
</head>
<body>
    <div class="box">
        <h2>Login</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; if(isset($_GET['success'])) echo "<p class='success'>Registered! Please log in.</p>"; ?>
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <hr style="margin: 30px 0;">
        <h2>Register</h2>
        <form method="POST">
            <input type="hidden" name="action" value="register">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account</button>
        </form>
        <a href="index.php">← Back to Home</a>
    </div>
</body>
</html>