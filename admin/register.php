<?php
require_once __DIR__ . '/../includes/functions.php';

// Якщо вже залогінений
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('dashboard.php');
    } else {
        redirect('../index.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Перевірки
    if (empty($username) || empty($email) || empty($password)) {
        $error = "❌ Всі поля обов'язкові для заповнення!";
    } elseif ($password !== $password_confirm) {
        $error = "❌ Паролі не співпадають!";
    } elseif (strlen($password) < 6) {
        $error = "❌ Пароль повинен містити мінімум 6 символів!";
    } else {
        // Перевіряємо чи існує користувач
        $check_sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error = "❌ Користувач з таким логіном або email вже існує!";
        } else {
            // Створюємо користувача (звичайного, не адміна)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', 'user')";
            
            if ($conn->query($sql)) {
                $success = "✅ Реєстрація успішна! Зараз ви будете перенаправлені...";
                
                // Автоматично логінимо
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';
                
                header("Refresh: 2; url=../index.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація - COLIBRI UA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #E0F7F5 0%, #81D8D0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }
        
        .register-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .register-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header .logo {
            font-size: 48px;
            color: #81D8D0;
            margin-bottom: 15px;
        }
        
        .register-header h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #81D8D0;
            font-size: 18px;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .input-with-icon input:focus {
            outline: none;
            border-color: #81D8D0;
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            background: #81D8D0;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            background: #0ABAB5;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .alert-error {
            background: #FFE0E0;
            color: #D32F2F;
            border: 1px solid #FFCDD2;
        }
        
        .alert-success {
            background: #E0F7F5;
            color: #0ABAB5;
            border: 1px solid #81D8D0;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .login-link a {
            color: #81D8D0;
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="css\style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <div class="logo">
                    <i class="fas fa-hummingbird"></i>
                </div>
                <h2>COLIBRI UA</h2>
                <p>Створіть новий акаунт</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Логін</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Придумайте логін" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Введіть email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Пароль</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Мінімум 6 символів" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Підтвердження паролю</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password_confirm" placeholder="Повторіть пароль" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Зареєструватися
                </button>
            </form>
            
            <div class="login-link">
                Вже є акаунт? <a href="login.php">Увійти</a>
            </div>
        </div>
        
        <a href="../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Повернутися на сайт
        </a>
    </div>
</body>
</html>