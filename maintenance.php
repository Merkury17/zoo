<?php 
require 'includes/db.php'; 
include 'includes/header.php'; 


$user_role = $_SESSION['role'] ?? 'visitor';
$can_edit_maintenance = ($user_role == 'worker');


if (!isset($_SESSION['role']) || ($user_role != 'worker' && $user_role != 'admin')) {
    die("<div class='glass-panel' style='text-align:center;'>⛔ Доступ заборонено</div>");
}


if ($can_edit_maintenance && isset($_GET['action']) && isset($_GET['id'])) {
    $new_status = $_GET['action']; 
    $id = $_GET['id'];
    
    if (in_array($new_status, ['Clean', 'Dirty', 'Repair'])) {
        $stmt = $pdo->prepare("UPDATE enclosures SET cleanliness = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
    }
    header("Location: maintenance.php");
    exit;
}


$enclosures = $pdo->query("SELECT * FROM enclosures WHERE name != 'Головний Вхід'")->fetchAll();


$status_config = [
    'Clean'  => ['text' => '✨ Чисто',   'color' => '#00b894'],
    'Dirty'  => ['text' => '💩 Брудно',  'color' => '#e17055'],
    'Repair' => ['text' => '🔧 Ремонт',  'color' => '#fdcb6e']
];
?>

<div class="glass-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;"><i class="fas fa-broom"></i> Технічний стан вольєрів</h1>
        <span style="color: #bdc3c7;">Управління чистотою та ремонтом</span>
    </div>

    <table style="width: 100%; border-collapse: collapse; color: white;">
        <thead>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.2); text-align: left; color: #fab1a0;">
                <th style="padding: 15px;">Вольєр</th>
                <th style="padding: 15px;">Тип</th>
                <th style="padding: 15px;">Поточний стан</th>
                <th style="padding: 15px;">Дії (Тільки працівник)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($enclosures as $row): ?>
                <?php $st = $status_config[$row['cleanliness']]; ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="padding: 15px; font-weight: bold; font-size: 1.1em;"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="padding: 15px; color: #bdc3c7;"><?php echo $row['zone_type']; ?></td>
                    <td style="padding: 15px;">
                        <span style="background: <?php echo $st['color']; ?>; padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9em; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            <?php echo $st['text']; ?>
                        </span>
                    </td>
                    <td style="padding: 15px;">
                        <?php if($can_edit_maintenance): ?>
                            <div style="display: flex; gap: 10px;">
                                <?php if($row['cleanliness'] == 'Dirty'): ?>
                                    <a href="maintenance.php?id=<?php echo $row['id']; ?>&action=Clean" class="btn" style="background: #00b894; padding: 8px 15px; font-size: 0.8em;">🧹 Прибрати</a>
                                    <a href="maintenance.php?id=<?php echo $row['id']; ?>&action=Repair" class="btn" style="background: #fdcb6e; color: #333; padding: 8px 15px; font-size: 0.8em;">🛠 У ремонт</a>
                                <?php elseif($row['cleanliness'] == 'Repair'): ?>
                                    <a href="maintenance.php?id=<?php echo $row['id']; ?>&action=Clean" class="btn" style="background: #00b894; padding: 8px 15px; font-size: 0.8em;">✅ Завершити ремонт</a>
                                <?php else: ?>
                                    <a href="maintenance.php?id=<?php echo $row['id']; ?>&action=Dirty" style="color: #636e72; font-size: 0.85em; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                        <i class="fas fa-trash"></i> Помітити як брудне
                                    </a>
                                <?php endif; ?>
                            </div>
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