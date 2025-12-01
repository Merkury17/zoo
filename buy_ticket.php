<?php 
require 'includes/db.php'; 
include 'includes/header.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    $count = (int)$_POST['count'];
    
   
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        echo "<script>alert('Помилка! Не можна купити квиток у минуле. 🕰️'); window.history.back();</script>";
        exit;
    }


    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, type, visit_date, ticket_code) VALUES (?, ?, ?, ?)");
    
    for ($i = 0; $i < $count; $i++) {
        $code = "ZOO-" . strtoupper(substr(md5(uniqid()), 0, 6)); 
        $stmt->execute([$user_id, $type, $date, $code]);
    }
    
    echo "<script>
        alert('✅ Успішно! Ви придбали $count квитків на $date.');
        window.location='index.php';
    </script>";
}
?>

<div style="display: flex; justify-content: center; padding-top: 50px;">
    <div class="glass-panel" style="width: 100%; max-width: 400px; text-align: center;">
        <h1 style="color: #00b894;">🎟️ Купити квиток</h1>
        <p style="color: #bdc3c7; margin-bottom: 30px;">Оберіть дату вашого візиту</p>
        
        <form method="POST">
            <label style="text-align: left;">Тип квитка:</label>
            <select name="type">
                <option value="adult">Дорослий (200 грн)</option>
                <option value="child">Дитячий (100 грн)</option>
            </select>
            
            <label style="text-align: left;">Дата візиту:</label>
            <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required style="background: rgba(0,0,0,0.3); border: 1px solid #00b894;">
            
            <label style="text-align: left;">Кількість:</label>
            <input type="number" name="count" value="1" min="1" max="5" required>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 20px; background: #6c5ce7;">Оплатити</button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="index.php" style="color: #bdc3c7; font-size: 0.9em;">На головну</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>