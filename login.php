<?php
require 'includes/db.php';


if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    

    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name']; 
        $_SESSION['role'] = $user['role'];

        header("Location: index.php");
        exit;
    } else {
        $error = "❌ Невірний Email або пароль!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід у систему</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .error-message {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #ff7675;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
    </style>
</head>
<body style="justify-content: center; align-items: center;">
    
    <div class="glass-panel" style="width: 100%; max-width: 400px; padding: 40px;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="margin: 0;">🦁 ZOO PRO</h1>
            <p style="color: #bdc3c7;">Система управління зоопарком</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Email співробітника:</label>
            <input type="email" name="email" placeholder="example@zoo.ua" required>
            
            <label>Пароль:</label>
            <input type="password" name="password" placeholder="Введіть пароль" required>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 10px; padding: 15px;">Увійти</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 0.8em; color: #7f8c8d;">
            Студент: Юхимчук Назар | Варіант 28
        </p>
    </div>

</body>
</html>