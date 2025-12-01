<?php 
require 'includes/db.php'; 
include 'includes/header.php'; 

$user_role = $_SESSION['role'] ?? 'visitor';
$is_admin = ($user_role === 'admin');
$show_health = ($user_role !== 'visitor'); 

$where = "";
$params = [];
if (isset($_GET['enclosure_id'])) {
    $where = "WHERE enclosure_id = ?";
    $params[] = $_GET['enclosure_id'];
}

$sql = "SELECT * FROM animals $where ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animals = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5); margin: 0;">🐾 Наші Тварини</h1>
    <?php if ($is_admin): ?>
        <a href="add_animal.php" class="btn">➕ Додати тварину</a>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px;">
    <?php foreach($animals as $a): ?>
        <div class="glass-panel" style="text-align: center; padding: 15px; transition: transform 0.3s; display: flex; flex-direction: column;">
            
            <?php $img = $a['photo_path'] ? $a['photo_path'] : 'default.png'; ?>
            <div style="width: 100%; height: 220px; overflow: hidden; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); background: #2d3436;">
                <img src="assets/img/animals/<?php echo $img; ?>" alt="<?php echo htmlspecialchars($a['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.5s;">
            </div>
            
            <h3 style="margin: 5px 0 10px 0; font-size: 1.4em;"><?php echo htmlspecialchars($a['name']); ?></h3>
            
            <?php if ($show_health): ?>
            <div style="margin-bottom: 20px;">
                <span style="
                    background: <?php echo ($a['health_status']=='Healthy'?'#00b894': ($a['health_status']=='Sick'?'#d63031':'#f1c40f')); ?>; 
                    padding: 5px 12px; 
                    border-radius: 20px; 
                    font-size: 0.85em; 
                    font-weight: bold;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                    <?php echo $a['health_status']; ?>
                </span>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: auto; display: flex; gap: 10px; justify-content: center;">
                <a href="animal_details.php?id=<?php echo $a['id']; ?>" class="btn" style="font-size: 0.9em; flex: 1;">Детальніше</a>
                <?php if ($is_admin): ?>
                    <a href="edit_animal.php?id=<?php echo $a['id']; ?>" class="btn" style="background: #f39c12; width: auto; padding: 10px 15px;" title="Редагувати">✏️</a>
                    <a href="delete_animal.php?id=<?php echo $a['id']; ?>" class="btn" style="background: #e74c3c; width: auto; padding: 10px 15px;" onclick="return confirm('Видалити?');" title="Видалити">🗑️</a>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>
</div>

<?php if(count($animals) == 0): ?>
    <div class="glass-panel" style="text-align: center; padding: 50px;">
        <h3>Тут поки що пусто... 🦁</h3>
        <a href="animals.php" class="btn" style="margin-top: 10px;">Показати всіх</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>