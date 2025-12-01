<?php 
require 'includes/db.php'; 
include 'includes/header.php'; 

// Список зон (Птахів прибрали)
$valid_zones = [
    'Savanna'  => '☀️ Савана (Відкритий простір)',
    'Jungle'   => '🌴 Джунглі (Багато зелені)',
    'Aquarium' => '💧 Акваріум (Вода)',
    'Predator' => '🐾 Сектор Хижаків (Посилений захист)'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $diet = $_POST['diet_type'];
    $zone = $_POST['type_zone'];
    
    $sci_name = $_POST['scientific_name'] ?? '';
    $desc = $_POST['description'] ?? '';

    // Вставляємо новий вид
    $sql = "INSERT INTO species (name, scientific_name, diet_type, description, type_zone) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $sci_name, $diet, $desc, $zone]);

    echo "<script>alert('Вид успішно створено!'); window.location='add_animal.php';</script>";
}
?>

<div style="display: flex; justify-content: center; padding-top: 20px;">
    <div class="glass-panel" style="width: 100%; max-width: 500px;">
        <h2 style="text-align: center;">🧬 Додати новий вид</h2>
        <p style="text-align: center; color: #b2bec3; font-size: 0.9em;">Вкажіть вимоги до утримання</p>
        
        <form method="POST">
            <label>Назва виду (Укр):</label>
            <input type="text" name="name" required placeholder="Напр: Тигр">
            
            <label>Наукова назва (Лат):</label>
            <input type="text" name="scientific_name" placeholder="Напр: Panthera tigris">
            
            <label>Тип харчування:</label>
            <select name="diet_type">
                <option value="Carnivore">🍖 Хижак</option>
                <option value="Herbivore">🌿 Травоїдний</option>
                <option value="Omnivore">🍎 Всеїдний</option>
            </select>

            <label>Необхідний тип вольєра:</label>
            <select name="type_zone">
                <?php foreach ($valid_zones as $key => $label): ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <small style="color: #fab1a0; display: block; margin-top: 5px; font-size: 0.85em;">
                * Це визначить, які вольєри будуть доступні при заселенні тварин цього виду.
            </small>

            <label>Опис (не обов'язково):</label>
            <textarea name="description" rows="3" style="width: 100%; background: rgba(0,0,0,0.3); border: none; border-radius: 8px; padding: 12px; color: white;"></textarea>

            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">Створити вид</button>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="add_animal.php" style="color: #bdc3c7;">Скасувати</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>