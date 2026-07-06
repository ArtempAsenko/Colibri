<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "❌ Всі поля обов'язкові!";
    } elseif ($password !== $password_confirm) {
        $error = "❌ Паролі не співпадають!";
    } elseif (strlen($password) < 6) {
        $error = "❌ Пароль має бути мінімум 6 символів!";
    } else {
        global $conn;
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = "❌ Користувач з таким логіном або email вже існує!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $hashed);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';
                $success = "✅ Реєстрація успішна! Перехід на головну...";
                header("Refresh: 2; url=index.php");
            } else {
                $error = "❌ Помилка реєстрації: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Реєстрація - COLIBRI UA</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .register-wrapper { max-width: 450px; margin: 80px auto; padding: 20px; }
        .register-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .register-card h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .register-card .form-group { margin-bottom: 20px; }
        .register-card input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; }
        .register-card input:focus { border-color: #81D8D0; outline: none; }
        .btn-register { width: 100%; padding: 14px; background: #81D8D0; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .btn-register:hover { background: #0ABAB5; }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: #81D8D0; text-decoration: none; font-weight: bold; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .alert-error { background: #FFE0E0; color: #D32F2F; }
        .alert-success { background: #E0F7F5; color: #0ABAB5; }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-card">
            <h2><i class="fas fa-user-plus" style="color: #81D8D0;"></i> Реєстрація</h2>
            <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Логін</label>
                    <input type="text" name="username" required placeholder="Придумайте логін">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Введіть email">
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" required placeholder="Мінімум 6 символів">
                </div>
                <div class="form-group">
                    <label>Підтвердіть пароль</label>
                    <input type="password" name="password_confirm" required placeholder="Повторіть пароль">
                </div>
                <button type="submit" class="btn-register">Зареєструватися</button>
            </form>
            <div class="login-link">Вже є акаунт? <a href="admin/login.php">Увійти</a></div>
        </div>
    </div>
</body>
</html>