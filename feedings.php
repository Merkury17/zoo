<?php 
require 'includes/db.php'; 
include 'includes/header.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] == 'visitor') {
    die("<div class='glass-panel' style='text-align:center;'>⛔ Доступ заборонено</div>");
}

$role = $_SESSION['role'];
$can_edit = ($role == 'keeper'); 


if ($can_edit) {

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feeding_id'])) {
        $stmt = $pdo->prepare("UPDATE feedings SET feed_time = ? WHERE id = ?");
        $stmt->execute([$_POST['new_time'], $_POST['feeding_id']]);
        header("Location: feedings.php");
        exit;
    }
    if (isset($_GET['done_id'])) {
        $stmt = $pdo->prepare("UPDATE feedings SET is_done = 1 WHERE id = ?");
        $stmt->execute([$_GET['done_id']]);
        header("Location: feedings.php");
        exit;
    }
}


if ($role == 'admin' && isset($_GET['reset'])) {
    $pdo->query("UPDATE feedings SET is_done = 0");
    header("Location: feedings.php");
    exit;
}

$feedings = $pdo->query("SELECT f.*, a.name as animal_name, a.photo_path, s.name as species FROM feedings f JOIN animals a ON f.animal_id = a.id JOIN species s ON a.species_id = s.id ORDER BY f.feed_time ASC")->fetchAll();
?>

<div class="glass-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">🥩 Графік годування</h1>
        
        <?php if($role == 'admin'): ?>
            <a href="feedings.php?reset=1" class="btn" style="background: #636e72; font-size: 0.8em;">🔄 Новий день (Скид)</a>
        <?php endif; ?>
    </div>

    <table style="width: 100%; border-collapse: collapse; color: white;">
        <thead>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.2); text-align: left;">
                <th style="padding: 15px;">Час</th> 
                <th style="padding: 15px;">Тварина</th> 
                <th style="padding: 15px;">Раціон</th> 
                <th style="padding: 15px;">Статус</th> 
                <th style="padding: 15px;">Дія</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($feedings as $row): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); background: <?php echo $row['is_done']?'rgba(0,184,148,0.1)':'transparent'; ?>;">
                    
                    <td style="padding: 15px; font-weight: bold; width: 120px;">
                        <?php if ($can_edit): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="feeding_id" value="<?php echo $row['id']; ?>">
                                <input type="time" name="new_time" value="<?php echo date('H:i', strtotime($row['feed_time'])); ?>" onchange="this.form.submit()" style="background: rgba(0,0,0,0.3); border: 1px solid #fab1a0; color: white; border-radius: 5px; padding: 5px;">
                            </form>
                        <?php else: ?>
                            <?php echo date('H:i', strtotime($row['feed_time'])); ?>
                        <?php endif; ?>
                    </td>
                    
                    <td style="padding: 15px; display: flex; align-items: center; gap: 10px;">
                        <img src="assets/img/animals/<?php echo $row['photo_path']; ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div><div style="font-weight: bold;"><?php echo $row['animal_name']; ?></div><div style="font-size: 0.8em; color: #bdc3c7;"><?php echo $row['species']; ?></div></div>
                    </td>
                    <td style="padding: 15px;"><?php echo $row['food_type']; ?></td>
                    <td style="padding: 15px;"><?php echo $row['is_done'] ? '<span style="color:#00b894;">✅ Виконано</span>' : '<span style="color:#e74c3c;">⏳ Очікує</span>'; ?></td>
                    
                    <td style="padding: 15px;">
                        <?php if(!$row['is_done'] && $can_edit): ?>
                            <a href="feedings.php?done_id=<?php echo $row['id']; ?>" class="btn" style="padding: 5px 15px; font-size: 0.8em;">Нагодувати</a>
                        <?php else: ?>
                            <span style="opacity: 0.5;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>