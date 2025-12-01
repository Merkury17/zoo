<?php 
require 'includes/db.php'; 
include 'includes/header.php';

$is_vet = (isset($_SESSION['role']) && $_SESSION['role'] === 'vet');

// Переклад назв зон
$zone_names = [
    'Savanna' => '☀️ Савана', 'Jungle' => '🌴 Джунглі', 'Aquarium' => '💧 Акваріум',
    'Predator' => '🐾 Сектор Хижаків', 'Birds' => '🦜 Птахи', 'Closed' => '🔒 Карантин / Ізолятор'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $species_id = $_POST['species_id'];
    $enclosure_id = $_POST['enclosure_id'];
    $status = $_POST['health_status'];

    // Валідація
    $stmt_e = $pdo->prepare("SELECT zone_type, name FROM enclosures WHERE id = ?");
    $stmt_e->execute([$enclosure_id]);
    $enclosure_data = $stmt_e->fetch();
    $enc_type = $enclosure_data['zone_type'];

    if ($status === 'Sick' && $enc_type !== 'Closed') {
        echo "<script>alert('ПОМИЛКА! Хвора тварина має бути в Карантині!'); window.history.back();</script>"; exit;
    }
    if ($status === 'Healthy' && $enc_type === 'Closed') {
        echo "<script>alert('ПОМИЛКА! Здорова тварина не може бути в Карантині.'); window.history.back();</script>"; exit;
    }

    // Фото
    $photo_name = 'default.png'; 
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = 'assets/img/animals/';
        $generated_name = time() . '_' . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $generated_name)) {
            $photo_name = $generated_name;
        }
    }

    // Запис
    $sql = "INSERT INTO animals (name, species_id, enclosure_id, health_status, photo_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $species_id, $enclosure_id, $status, $photo_name]);
    
    // Авто-годування
    $new_id = $pdo->lastInsertId();
    $food_ration = $_POST['food_ration'] ?? 'Стандартний корм'; 
    $pdo->prepare("INSERT INTO feedings (animal_id, food_type, feed_time) VALUES (?, ?, '09:00')")->execute([$new_id, $food_ration]);
    
    echo "<script>alert('Тварину успішно додано!'); window.location='animals.php';</script>";
}

$species_list = $pdo->query("SELECT * FROM species ORDER BY name")->fetchAll();
$sql_enc = "SELECT zone_type, id, name FROM enclosures WHERE name != 'Головний Вхід' ORDER BY zone_type";
$enclosures_list = $pdo->query($sql_enc)->fetchAll(PDO::FETCH_GROUP);
?>

<div style="display: flex; justify-content: center; padding-top: 20px;">
    <div class="glass-panel" style="width: 100%; max-width: 500px;">
        <h2 style="text-align: center; margin-bottom: 20px;">➕ Новий мешканець</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <label>📝 Ім'я тварини:</label>
            <input type="text" name="name" required placeholder="Наприклад: Сімба">
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label style="margin: 0;">🦁 Вид:</label>
                <a href="add_species.php" style="font-size: 0.8em; color: #00b894; text-decoration: none;">+ Створити новий</a>
            </div>
            <select name="species_id" required>
                <option value="" disabled selected>Оберіть вид...</option>
                <?php foreach($species_list as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>🥩 Раціон харчування:</label>
            <input type="text" name="food_ration" required placeholder="Напр: М'ясо 5кг, Сіно..." style="border: 1px solid #fab1a0;">

            <label>❤️ Стан здоров'я:</label>
            <?php if ($is_vet): ?>
                <select name="health_status" required style="background: rgba(0,0,0,0.4); border: 1px solid #fab1a0;">
                    <option value="Healthy" selected>🟢 Здоровий</option>
                    <option value="Sick">🔴 Хворий (В Карантин!)</option>
                </select>
            <?php else: ?>
                <select name="health_status" style="background: rgba(0,0,0,0.2); color: gray; pointer-events: none;">
                    <option value="Healthy" selected>🟢 Здоровий (За замовчуванням)</option>
                </select>
            <?php endif; ?>

            <label>🏡 Вольєр:</label>
            <select name="enclosure_id" required>
                <option value="" disabled selected>Оберіть вольєр...</option>
                <?php foreach($enclosures_list as $type => $items): ?>
                    <optgroup label="<?php echo $zone_names[$type] ?? $type; ?>">
                        <?php foreach($items as $e): ?>
                            <option value="<?php echo $e['id']; ?>"><?php echo $e['name']; ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>

            <label>📸 Фотографія (необов'язково):</label>
            <input type="file" name="photo" accept="image/*">

            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Зберегти</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>