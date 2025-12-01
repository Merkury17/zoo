<?php

function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}


function getAlerts($pdo) {
    $alerts = [
        'vet' => [],     
        'feeding' => []  
    ];
    

    $sql_vet = "SELECT a.name, v.next_check_date 
            FROM vet_checks v 
            JOIN animals a ON v.animal_id = a.id 
            WHERE v.id IN (
                SELECT MAX(id) FROM vet_checks GROUP BY animal_id
            )
            AND v.next_check_date IS NOT NULL 
            AND v.next_check_date < CURDATE()";
            
    $stmt_vet = $pdo->query($sql_vet);
    while ($row = $stmt_vet->fetch()) {
        $date = date('d.m.Y', strtotime($row['next_check_date']));
        $alerts['vet'][] = "Тварина <strong>{$row['name']}</strong> пропустила огляд ($date)!";
    }

    $sql_feed = "SELECT a.name, f.feed_time 
                 FROM feedings f 
                 JOIN animals a ON f.animal_id = a.id 
                 WHERE f.is_done = 0 
                 ORDER BY f.feed_time ASC";

    $stmt_feed = $pdo->query($sql_feed);
    while ($row = $stmt_feed->fetch()) {
        $feed_timestamp = strtotime($row['feed_time']);
        $time_str = date('H:i', $feed_timestamp);
        
        if (time() > $feed_timestamp) {
            $alerts['feeding'][] = "Тварина <strong>{$row['name']}</strong> голодна! (Час вийшов: $time_str)";
        } 
    }

    return $alerts;
}
?>