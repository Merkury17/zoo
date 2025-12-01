<?php 
require 'includes/db.php'; 
include 'includes/header.php'; 


$enclosures = $pdo->query("SELECT * FROM enclosures WHERE name != 'Головний Вхід'")->fetchAll();


$zoneConfig = [
    'Savanna'  => ['icon' => 'fa-sun', 'color' => '#f1c40f', 'label' => 'Савана'],
    'Jungle'   => ['icon' => 'fa-tree', 'color' => '#2ecc71', 'label' => 'Джунглі'],
    'Aquarium' => ['icon' => 'fa-water', 'color' => '#3498db', 'label' => 'Аквазона'],
    
   
    'Predator' => ['icon' => 'fa-paw', 'color' => '#e74c3c', 'label' => 'Хижаки'],
    

    'Closed'   => ['icon' => 'fa-lock', 'color' => '#95a5a6', 'label' => 'Карантин']
    

];
?>

<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="color: white; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">🗺️ Карта Зоопарку</h1>
    <p style="color: #dfe6e9;">Інтерактивний план території</p>
</div>

<div class="map-wrapper" style="position: relative; max-width: 1000px; margin: 0 auto; border-radius: 25px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); border: 4px solid rgba(255,255,255,0.2);">
    
    <img src="assets/img/zoo_map_bg.png" alt="Карта" style="width: 100%; display: block;">

    <?php foreach($enclosures as $enc): ?>
        <?php 
            $type = $enc['zone_type'] ?? 'Savanna';
            $config = $zoneConfig[$type] ?? $zoneConfig['Savanna'];
            $left = $enc['x_coord'] ?? 50;
            $top = $enc['y_coord'] ?? 50;
        ?>

        <a href="animals.php?enclosure_id=<?php echo $enc['id']; ?>" 
           class="map-pin zone-<?php echo $type; ?>"
           data-zone="<?php echo $type; ?>"
           style="left: <?php echo $left; ?>%; top: <?php echo $top; ?>%;">
            
            <div class="pin-pulse" style="background: <?php echo $config['color']; ?>;"></div>
            
            <div class="pin-icon" style="background: <?php echo $config['color']; ?>;">
                <i class="fas <?php echo $config['icon']; ?>"></i>
            </div>
            
            <div class="pin-card">
                <h4 style="color: #333;"><?php echo htmlspecialchars($enc['name']); ?></h4>
                <span><i class="fas fa-paw"></i> Місць: <?php echo $enc['capacity']; ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div style="max-width: 1000px; margin: 0 auto;">
    <div class="map-legend">
        <?php foreach($zoneConfig as $key => $conf): ?>
            <div class="legend-item" onmouseover="highlightZone('<?php echo $key; ?>')" onmouseout="resetZones()">
                <i class="fas <?php echo $conf['icon']; ?>" style="color: <?php echo $conf['color']; ?>;"></i>
                <span><?php echo $conf['label']; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #bdc3c7; font-size: 0.9em;">
        <i class="fas fa-info-circle"></i> Наведіть на кнопку зони, щоб підсвітити вольєри
    </div>
</div>

<div style="height: 50px;"></div>

<script>
    function highlightZone(zoneType) {
        const pins = document.querySelectorAll('.map-pin');
        
        pins.forEach(pin => {
            if (pin.getAttribute('data-zone') === zoneType) {
                pin.classList.add('highlight-pin');
                pin.style.opacity = '1';
            } else {
                pin.style.opacity = '0.3';
            }
        });
    }

    function resetZones() {
        const pins = document.querySelectorAll('.map-pin');
        pins.forEach(pin => {
            pin.classList.remove('highlight-pin');
            pin.style.opacity = '1';
        });
    }
</script>

<?php include 'includes/footer.php'; ?>