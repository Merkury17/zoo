<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'visitor';
$root_path = "/zoo_system"; 

if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: {$root_path}/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoo Pro - Система Управління Зоопарком</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $root_path; ?>/assets/css/style.css?v=<?php echo time(); ?>">

    <style>
        body { margin: 0; padding: 0; display: flex; flex-direction: column; min-height: 100vh; }
        header { width: 100%; background: rgba(0, 0, 0, 0.8); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); position: sticky; top: 0; z-index: 1000; height: 70px; }
        nav { width: 100%; height: 100%; display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; position: relative; }
        .logo { font-size: 1.8em; font-weight: 800; color: #00b894; text-decoration: none; display: flex; align-items: center; gap: 10px; z-index: 2; }
        .center-nav { position: absolute; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 1; }
        .center-nav a { color: #ecf0f1; text-decoration: none; font-weight: 600; font-size: 1em; padding: 8px 15px; border-radius: 8px; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; }
        .center-nav a:hover { background: rgba(255, 255, 255, 0.1); color: #00b894; transform: translateY(-2px); }
        .logout-btn { color: #ff7675; text-decoration: none; font-weight: 600; padding: 8px 15px; border: 1px solid rgba(255, 118, 117, 0.3); border-radius: 8px; transition: 0.3s; z-index: 2; display: inline-flex; align-items: center; gap: 8px; }
        .logout-btn:hover { background: #ff7675; color: white; }
        main { flex: 1; width: 100%; max-width: 1200px; margin: 20px auto; padding: 0 20px; box-sizing: border-box; }
        .user-info { color: #dfe6e9; font-size: 0.9em; border-right: 1px solid rgba(255,255,255,0.2); padding-right: 20px; margin-right: 10px; }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="<?php echo $root_path; ?>/index.php" class="logo">
                <i class="fas fa-paw"></i> Zoo Pro
            </a>
            
            <div class="center-nav">
                <a href="<?php echo $root_path; ?>/index.php"><i class="fas fa-home"></i> Головна</a>
                <a href="<?php echo $root_path; ?>/map.php"><i class="fas fa-map"></i> Карта</a>
                <a href="<?php echo $root_path; ?>/animals.php"><i class="fas fa-hippo"></i> Тварини</a>

                <?php if ($role == 'keeper' || $role == 'admin' || $role == 'vet'): ?>
                    <a href="<?php echo $root_path; ?>/feedings.php">
                        <i class="fas fa-drumstick-bite"></i> Годування
                    </a>
                <?php endif; ?>

                <?php if ($role == 'worker' || $role == 'admin'): ?>
                    <a href="<?php echo $root_path; ?>/maintenance.php">
                        <i class="fas fa-tools"></i> Тех. стан
                    </a>
                <?php endif; ?>
            </div>

            <div style="display: flex; align-items: center; gap: 15px;">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span class="user-info">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                <?php endif; ?>
                
                <a href="<?php echo $root_path; ?>/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Вихід
                </a>
            </div>
        </nav>
    </header>

    <main>