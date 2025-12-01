<?php 
require 'includes/db.php'; 
include 'includes/header.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<div class='glass-panel' style='text-align:center;'>⛔ Доступ заборонено</div>");
}

$id = $_GET['id'] ?? null; 
$return_id = $_GET['return_id'] ?? null; 

if (!$id) header("Location: index.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $desc = $_POST['description'];
    $sci_name = $_POST['scientific_name'];
    

    $stmt = $pdo->prepare("UPDATE species SET description = ?, scientific_name = ? WHERE id = ?");
    $stmt->execute([$desc, $sci_name, $id]);
    
    if ($return_id) {
        header("Location: animal_details.php?id={$return_id}");
    } else {
        header("Location: index.php"); 
    }
    exit;
}

$species = $pdo->query("SELECT * FROM species WHERE id = $id")->fetch();
?>

<div style="display: flex; justify-content: center; padding-top: 20px;">
    <div class="glass-panel" style="width: 100%; max-width: 500px;">
        <h2>✏️ Редагувати вид: <?php echo htmlspecialchars($species['name']); ?></h2>
        <p style="color: #fab1a0; font-size: 0.9em;">⚠️ Увага: Цей опис зміниться для ВСІХ тварин цього виду!</p>
        
        <form method="POST">
            <label>Наукова назва (лат.):</label>
            <input type="text" name="scientific_name" value="<?php echo htmlspecialchars($species['scientific_name']); ?>">
            
            <label>Спільний опис:</label>
            <textarea name="description" rows="5" style="width: 100%; background: rgba(0,0,0,0.3); border: none; border-radius: 8px; padding: 12px; color: white;"><?php echo htmlspecialchars($species['description']); ?></textarea>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">Зберегти для всіх</button>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="animal_details.php?id=<?php echo $return_id; ?>" style="color: #bdc3c7;">Скасувати</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>