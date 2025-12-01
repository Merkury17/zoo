<?php
require 'includes/db.php';
require 'includes/functions.php';
checkAuth();
include 'includes/header.php';

$role = $_SESSION['role'];
$user_name = $_SESSION['user_name'];


$total_animals = $pdo->query("SELECT COUNT(*) FROM animals")->fetchColumn();
$sick_animals = $pdo->query("SELECT COUNT(*) FROM animals WHERE health_status != 'Healthy'")->fetchColumn();
$feedings_left = $pdo->query("SELECT COUNT(*) FROM feedings WHERE is_done = 0")->fetchColumn();
$species_stats = $pdo->query("SELECT s.name, COUNT(a.id) as count FROM species s LEFT JOIN animals a ON s.id = a.species_id GROUP BY s.name HAVING count > 0")->fetchAll();


$alerts = getAlerts($pdo);
?>

<?php if ($role == 'visitor'): ?>
    
    <?php
        $user_id = $_SESSION['user_id'];
        
        $stmt_active = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? AND visit_date >= CURDATE() ORDER BY visit_date ASC");
        $stmt_active->execute([$user_id]);
        $active_tickets = $stmt_active->fetchAll();

        $stmt_history = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? AND visit_date < CURDATE() ORDER BY visit_date DESC LIMIT 5");
        $stmt_history->execute([$user_id]);
        $history_tickets = $stmt_history->fetchAll();
    ?>

    <div class="glass-panel fade-in" style="text-align: center; padding: 30px;">
        <h1 style="margin-bottom: 10px;">👋 Вітаємо, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p style="color: #dfe6e9;">Плануєте похід у зоопарк?</p>
        
        <div style="margin-top: 20px; display: flex; justify-content: center; gap: 15px;">
            <a href="buy_ticket.php" class="btn" style="background: #6c5ce7; padding: 12px 25px;">🎟️ Купити новий квиток</a>
            <a href="map.php" class="btn" style="background: #e17055; padding: 12px 25px;">🗺️ Карта</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <div>
            <h3 style="color: #00b894; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                🎫 Ваші активні квитки
            </h3>
            
            <?php if(count($active_tickets) > 0): ?>
                <?php foreach($active_tickets as $t): ?>
                    <div class="ticket-card ticket-active fade-in">
                        <div class="ticket-left">
                            <div style="font-size: 0.8em; color: #636e72; text-transform: uppercase;">
                                <?php echo ($t['type']=='adult' ? 'Дорослий квиток' : 'Дитячий квиток'); ?>
                            </div>
                            <div class="ticket-date"><?php echo date('d.m.Y', strtotime($t['visit_date'])); ?></div>
                            <div style="color: #00b894; font-weight: bold; margin-top: 5px;">🟢 Дійсний</div>
                            <div style="font-family: monospace; margin-top: 5px; color: #333;">#<?php echo $t['ticket_code']; ?></div>
                        </div>
                        <div class="ticket-right">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo $t['ticket_code']; ?>" style="width: 60px;">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #b2bec3;">У вас немає активних квитків. Час це виправити!</p>
            <?php endif; ?>
        </div>

        <div>
            <h3 style="color: #bdc3c7; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                📜 Історія відвідувань
            </h3>
            
            <?php if(count($history_tickets) > 0): ?>
                <?php foreach($history_tickets as $t): ?>
                    <div class="ticket-card ticket-expired fade-in">
                        <div class="ticket-left">
                            <div style="font-size: 0.8em; color: #636e72;">
                                <?php echo ($t['type']=='adult' ? 'Дорослий' : 'Дитячий'); ?>
                            </div>
                            <div class="ticket-date" style="color: #636e72;">
                                <?php echo date('d.m.Y', strtotime($t['visit_date'])); ?>
                            </div>
                            <div style="color: #636e72; font-weight: bold; margin-top: 5px;">🔴 Використано</div>
                        </div>
                        <div class="ticket-right">
                            <i class="fas fa-history" style="font-size: 24px; color: #b2bec3;"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #b2bec3;">Історія порожня.</p>
            <?php endif; ?>
        </div>

    </div>

<?php elseif ($role == 'keeper'): ?>
    <div class="glass-panel fade-in">
        <h1>👋 Привіт, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>Твоя зміна розпочалася.</p>
    </div>
    <div class="dashboard-grid">
        <div class="glass-panel stat-card fade-in delay-1">
            <div>
                <p style="text-transform: uppercase; font-size: 0.8em; color: #e17055;">Залишилось нагодувати</p>
                <p class="stat-number" style="color: #fab1a0;"><?php echo $feedings_left; ?></p>
            </div>
            <i class="fas fa-utensils" style="color: #fab1a0;"></i>
        </div>
        <a href="feedings.php" class="glass-panel fade-in delay-2" style="display: flex; align-items: center; justify-content: center; text-decoration: none; background: rgba(9, 132, 227, 0.3);">
            <h2 style="color: white;"><i class="fas fa-arrow-right"></i> Перейти до графіку</h2>
        </a>
    </div>

<?php elseif ($role == 'worker'): ?>
    <?php 
        $dirty_count = $pdo->query("SELECT COUNT(*) FROM enclosures WHERE cleanliness = 'Dirty'")->fetchColumn();
        $repair_count = $pdo->query("SELECT COUNT(*) FROM enclosures WHERE cleanliness = 'Repair'")->fetchColumn();
    ?>
    <div class="glass-panel fade-in">
        <h1>👋 Привіт, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>Час навести лад у зоопарку.</p>
    </div>
    <div class="dashboard-grid">
        <div class="glass-panel stat-card fade-in delay-1">
            <div><p style="text-transform: uppercase; font-size: 0.8em; color: #e17055;">Брудно</p><p class="stat-number" style="color: #fab1a0;"><?php echo $dirty_count; ?></p></div>
            <i class="fas fa-broom" style="color: #fab1a0;"></i>
        </div>
        <a href="maintenance.php" class="glass-panel fade-in delay-2" style="display: flex; align-items: center; justify-content: center; text-decoration: none; background: rgba(0, 184, 148, 0.3);">
            <h2 style="color: white;"><i class="fas fa-arrow-right"></i> До списку робіт</h2>
        </a>
    </div>

<?php else: ?>
    <div class="dashboard-grid">
        <div class="glass-panel fade-in" style="display: flex; flex-direction: column; justify-content: center;">
            <h1 style="margin: 0;">👋 Вітаємо, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p style="color: #b2bec3; margin: 5px 0;">Роль: <?php echo strtoupper($role); ?></p>
        </div>
        
        <div class="quick-actions fade-in">
            <?php if ($role == 'admin'): ?>
                <a href="add_animal.php" class="action-btn" style="background: linear-gradient(135deg, #00b894, #0984e3);">
                    <i class="fas fa-plus-circle"></i> Додати тварину
                </a>
            <?php endif; ?>
            
            <a href="animals.php" class="action-btn" style="background: linear-gradient(135deg, #6c5ce7, #a29bfe);">
                <i class="fas fa-list"></i> Список тварин
            </a>

            <a href="feedings.php" class="action-btn" style="background: linear-gradient(135deg, #e17055, #fdcb6e);">
                <i class="fas fa-drumstick-bite"></i> Графік годування
            </a>
        </div>
    </div>

    <?php if (!empty($alerts['vet']) || !empty($alerts['feeding'])): ?>
        <div class="glass-panel fade-in delay-1" style="border-left: 5px solid #e74c3c; margin-bottom: 20px; background: rgba(231, 76, 60, 0.15);">
            <h3 style="color: #ff7675; margin-top: 0; margin-bottom: 15px;">🔔 Термінові сповіщення</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <h4 style="color: #fab1a0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; margin-top: 0;">🚑 Вет-контроль</h4>
                    <?php if (!empty($alerts['vet'])): ?>
                        <?php foreach($alerts['vet'] as $msg): ?>
                            <div style="padding: 8px 0; color: #ecf0f1; font-size: 0.95em;"><i class="fas fa-stethoscope" style="color: #e74c3c; margin-right: 5px;"></i><?php echo $msg; ?></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #00b894; font-size: 0.9em;">✅ Всі здорові</p>
                    <?php endif; ?>
                </div>
                <div style="border-left: 1px solid rgba(255,255,255,0.1); padding-left: 30px;">
                    <h4 style="color: #74b9ff; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; margin-top: 0;">🍖 Годування</h4>
                    <?php if (!empty($alerts['feeding'])): ?>
                        <?php foreach($alerts['feeding'] as $msg): ?>
                            <div style="padding: 8px 0; color: #ecf0f1; font-size: 0.95em;"><i class="fas fa-drumstick-bite" style="color: #e17055; margin-right: 5px;"></i><?php echo $msg; ?></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #00b894; font-size: 0.9em;">✅ Всі нагодовані</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="glass-panel stat-card fade-in delay-2">
            <div>
                <p style="text-transform: uppercase; font-size: 0.8em; color: #00b894;">Всього тварин</p>
                <p class="stat-number" style="color: #55efc4;"><?php echo $total_animals; ?></p>
            </div>
            <i class="fas fa-paw" style="color: #55efc4;"></i>
        </div>
        <div class="glass-panel stat-card fade-in delay-2">
            <div>
                <p style="text-transform: uppercase; font-size: 0.8em; color: #e74c3c;">Хворих</p>
                <p class="stat-number" style="color: #ff7675;"><?php echo $sick_animals; ?></p>
            </div>
            <i class="fas fa-briefcase-medical" style="color: #ff7675;"></i>
        </div>
        <?php if ($role == 'admin'): ?>
        <div class="glass-panel stat-card fade-in delay-2">
            <div>
                <p style="text-transform: uppercase; font-size: 0.8em; color: #0984e3;">Не погодовано</p>
                <p class="stat-number" style="color: #74b9ff;"><?php echo $feedings_left; ?></p>
            </div>
            <i class="fas fa-drumstick-bite" style="color: #74b9ff;"></i>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($role == 'admin'): ?>
    <div class="glass-panel fade-in delay-3" style="margin-top: 20px;">
        <h3>📊 Популяція за видами</h3>
        <div style="height: 300px;"><canvas id="speciesChart"></canvas></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('speciesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($species_stats, 'name')); ?>,
                datasets: [{
                    label: 'Кількість',
                    data: <?php echo json_encode(array_column($species_stats, 'count')); ?>,
                    backgroundColor: 'rgba(0, 184, 148, 0.6)', borderColor: '#00b894', borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { color: 'white' } }, x: { ticks: { color: 'white' } } }, plugins: { legend: { display: false } } }
        });
    </script>
    <?php endif; ?>

<?php endif; ?>
<?php include 'includes/footer.php'; ?>