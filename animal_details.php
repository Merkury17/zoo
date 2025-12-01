<?php 
require 'includes/db.php'; 
include 'includes/header.php';

$id = $_GET['id'] ?? 0;

// Отримуємо роль користувача
$role = $_SESSION['role'] ?? 'visitor';
// Визначаємо, хто може бачити історію (Тільки Admin і Vet)
$can_see_history = ($role == 'admin' || $role == 'vet');

// === ЛОГІКА ДЛЯ ЛІКАРЯ ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['diagnosis'])) {
    if ($role !== 'vet') {
        die("<script>alert('Тільки ветеринар має право!'); window.history.back();</script>");
    }
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $new_status = $_POST['new_status'];
    $next_date = $_POST['next_check_date'] ?: NULL;
    $doctor_name = $_SESSION['user_name'] ?? 'Ветеринар';

    $pdo->prepare("INSERT INTO vet_checks (animal_id, check_date, diagnosis, treatment, doctor_name, next_check_date) VALUES (?, CURDATE(), ?, ?, ?, ?)")
        ->execute([$id, $diagnosis, $treatment, $doctor_name, $next_date]);

    // Авто-переміщення
    $q_stmt = $pdo->prepare("SELECT id FROM enclosures WHERE zone_type = 'Closed' LIMIT 1");
    $q_stmt->execute();
    $quarantine_id = $q_stmt->fetchColumn();
    $curr_stmt = $pdo->prepare("SELECT enclosure_id, original_enclosure_id FROM animals WHERE id = ?");
    $curr_stmt->execute([$id]);
    $current_data = $curr_stmt->fetch();

    if ($quarantine_id) {
        if ($new_status != 'Healthy' && $current_data['enclosure_id'] != $quarantine_id) {
            $pdo->prepare("UPDATE animals SET health_status = ?, original_enclosure_id = ?, enclosure_id = ? WHERE id = ?")->execute([$new_status, $current_data['enclosure_id'], $quarantine_id, $id]);
            echo "<script>alert('Тварину переміщено в КАРАНТИН.');</script>";
        } elseif ($new_status == 'Healthy' && !empty($current_data['original_enclosure_id'])) {
            $pdo->prepare("UPDATE animals SET health_status = ?, enclosure_id = ?, original_enclosure_id = NULL WHERE id = ?")->execute([$new_status, $current_data['original_enclosure_id'], $id]);
            echo "<script>alert('Тварина повернулась у свій вольєр.');</script>";
        } else {
            $pdo->prepare("UPDATE animals SET health_status = ? WHERE id = ?")->execute([$new_status, $id]);
        }
    } else {
        $pdo->prepare("UPDATE animals SET health_status = ? WHERE id = ?")->execute([$new_status, $id]);
    }
    echo "<script>window.location='animal_details.php?id=$id';</script>";
    exit;
}

// === ОТРИМАННЯ ДАНИХ ===
$sql = "SELECT a.*, s.name as species_name, s.scientific_name, s.description, s.id as species_id, e.name as enclosure_name FROM animals a LEFT JOIN species s ON a.species_id = s.id LEFT JOIN enclosures e ON a.enclosure_id = e.id WHERE a.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$animal = $stmt->fetch();

if (!$animal) { echo "Тварину не знайдено"; exit; }

// Історія (потрібна тільки якщо користувач має права)
$checks = [];
if ($can_see_history) {
    $history = $pdo->prepare("SELECT * FROM vet_checks WHERE animal_id = ? ORDER BY check_date DESC");
    $history->execute([$id]);
    $checks = $history->fetchAll();
}
?>

<div class="glass-panel" style="max-width: 1000px; margin: 20px auto; display: flex; flex-wrap: wrap; gap: 40px;">
    
    <div style="flex: 1; min-width: 300px;">
        <?php $img = $animal['photo_path'] ? $animal['photo_path'] : 'default.png'; ?>
        <div style="border-radius: 20px; overflow: hidden; height: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 4px solid rgba(255,255,255,0.1);">
            <img src="assets/img/animals/<?php echo $img; ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div style="margin-top: 20px; text-align: center; background: white; padding: 15px; border-radius: 15px;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ZOO_ID:<?php echo $id; ?>" alt="QR" style="width: 100px;">
        </div>
    </div>

    <div style="flex: 1.5; min-width: 300px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 2.5em;"><?php echo htmlspecialchars($animal['name']); ?></h1>
            <span style="background: <?php echo ($animal['health_status']=='Healthy'?'#00b894': ($animal['health_status']=='Sick'?'#d63031':'#f1c40f')); ?>; padding: 8px 20px; border-radius: 30px; font-weight: bold;">
                <?php echo $animal['health_status']; ?>
            </span>
        </div>
        <p style="font-size: 1.2em;">🌍 <strong>Вид:</strong> <?php echo htmlspecialchars($animal['species_name']); ?></p>
        <p style="font-size: 1.2em;">🏡 <strong>Вольєр:</strong> <?php echo htmlspecialchars($animal['enclosure_name']); ?></p>

        <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.05); border-left: 4px solid #00b894; border-radius: 5px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="margin: 0; color: #00b894;">📖 Про цей вид:</h4>
                <?php if($role == 'admin'): ?>
                    <a href="edit_species.php?id=<?php echo $animal['species_id']; ?>&return_id=<?php echo $animal['id']; ?>" style="color: #f39c12; text-decoration: none; font-size: 0.9em; border: 1px solid #f39c12; padding: 2px 8px; border-radius: 5px;">✏️ Редагувати</a>
                <?php endif; ?>
            </div>
            <p style="margin: 0; font-style: italic; color: #dfe6e9;"><?php echo !empty($animal['description']) ? nl2br(htmlspecialchars($animal['description'])) : 'Опис ще не додано.'; ?></p>
            <small style="display: block; margin-top: 10px; color: #7f8c8d;">Наукова назва: <i><?php echo htmlspecialchars($animal['scientific_name'] ?? '-'); ?></i></small>
        </div>

        <?php if($can_see_history): ?>
            <div style="margin-top: 40px;">
                <h3 style="border-left: 5px solid #00b894; padding-left: 15px;">🩺 Історія оглядів</h3>
                <div style="background: rgba(0,0,0,0.2); border-radius: 15px; padding: 15px; max-height: 300px; overflow-y: auto;">
                    <?php foreach($checks as $check): ?>
                        <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; margin-bottom: 10px; border: 1px solid rgba(255,255,255,0.1);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; color: #fab1a0;">
                                <strong>📅 <?php echo $check['check_date']; ?></strong>
                                <small>👨‍⚕️ <?php echo htmlspecialchars($check['doctor_name'] ?? 'Не вказано'); ?></small>
                            </div>
                            <div style="font-size: 1.1em;"><strong>Діагноз:</strong> <?php echo htmlspecialchars($check['diagnosis']); ?></div>
                            <div style="color: #bdc3c7; font-style: italic;">💊 <?php echo htmlspecialchars($check['treatment'] ?? '-'); ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($checks)): ?>
                        <p style="text-align: center; color: #7f8c8d;">Історія поки що чиста...</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if($role == 'vet'): ?>
            <div style="margin-top: 30px; background: rgba(255,255,255,0.05); padding: 25px; border-radius: 15px; border: 1px dashed rgba(255,255,255,0.3);">
                <h3 style="margin-top: 0;">💉 Новий запис</h3>
                <form method="POST">
                    <label>Діагноз:</label> <input type="text" name="diagnosis" required>
                    <label>Лікування:</label> <textarea name="treatment" rows="2"></textarea>
                    <label style="color: #74b9ff;">📅 Наступний огляд:</label> <input type="date" name="next_check_date" style="background: rgba(0,0,0,0.3); border: 1px solid #74b9ff;">
                    <label style="margin-top: 15px;">Статус:</label>
                    <select name="new_status">
                        <option value="Healthy" <?php if($animal['health_status']=='Healthy') echo 'selected'; ?>>🟢 Здоровий</option>
                        <option value="Sick" <?php if($animal['health_status']=='Sick') echo 'selected'; ?>>🔴 Хворий</option>
                        <option value="Treatment" <?php if($animal['health_status']=='Treatment') echo 'selected'; ?>>🟡 На лікуванні</option>
                    </select>
                    <button type="submit" class="btn" style="width: 100%; margin-top: 20px; background: #e17055;">Зберегти</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>