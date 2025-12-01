<?php 
require 'includes/db.php'; 
include 'includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID не вказано");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    

    if (isset($_POST['enclosure_id'])) {
        $enclosure = $_POST['enclosure_id'];
        $sql = "UPDATE animals SET name=?, enclosure_id=? WHERE id=?";
        $params = [$name, $enclosure, $id];
    } else {
   
        $sql = "UPDATE animals SET name=? WHERE id=?";
        $params = [$name, $id];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

  
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = 'assets/img/animals/';
        $photo_name = time() . '_' . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name)) {
            $stmt = $pdo->prepare("UPDATE animals SET photo_path=? WHERE id=?");
            $stmt->execute([$photo_name, $id]);
        }
    }

    echo "<script>alert('Дані оновлено!'); window.location='animals.php';</script>";
}


$stmt = $pdo->prepare("SELECT * FROM animals WHERE id = ?");
$stmt->execute([$id]);
$animal = $stmt->fetch();


$enclosures_list = $pdo->query("SELECT * FROM enclosures WHERE zone_type != 'Closed' AND name != 'Головний Вхід'")->fetchAll();
?>

<div style="display: flex; justify-content: center; padding-top: 20px;">
    <div class="glass-panel" style="width: 100%; max-width: 500px;">
        <h2 style="text-align: center;">✏️ Редагування: <?php echo htmlspecialchars($animal['name']); ?></h2>
        <p style="text-align: center; color: #b2bec3; font-size: 0.9em;">Зміна основної інформації</p>
        
        <form method="POST" enctype="multipart/form-data">
            
            <label>Ім'я:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($animal['name']); ?>" required>
            
            <label>Вольєр:</label>

            <?php if ($animal['health_status'] == 'Sick'): ?>
                <div style="display: flex; align-items: center; gap: 10px; background: rgba(231, 76, 60, 0.2); padding: 10px; border-radius: 8px; border: 1px solid #e74c3c;">
                    <i class="fas fa-lock" style="color: #e74c3c;"></i>
                    <span style="color: #ecf0f1;">Тварина в <b>Карантині</b>. Переміщення заборонено до одужання.</span>
                </div>
                <?php else: ?>
                <select name="enclosure_id">
                    <?php foreach($enclosures_list as $e): ?>
                        <option value="<?php echo $e['id']; ?>" <?php if($e['id'] == $animal['enclosure_id']) echo 'selected'; ?>>
                            <?php echo $e['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <label>Змінити фото (необов'язково):</label>
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="assets/img/animals/<?php echo $animal['photo_path']; ?>" style="width: 100px; border-radius: 10px; object-fit: cover;">
            </div>
            <input type="file" name="photo" accept="image/*">

            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">Зберегти зміни</button>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="animals.php" style="color: #bdc3c7;">Скасувати</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>