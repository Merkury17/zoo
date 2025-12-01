<?php
require 'includes/db.php';

// Перевірка прав (тільки Адмін може видаляти)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<script>alert('Доступ заборонено!'); window.history.back();</script>");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 1. ОТРИМУЄМО ДАНІ ПЕРЕД ВИДАЛЕННЯМ
    // Нам треба знати species_id, щоб перевірити цей вид після видалення
    $stmt = $pdo->prepare("SELECT photo_path, species_id FROM animals WHERE id = ?");
    $stmt->execute([$id]);
    $animal = $stmt->fetch();

    if ($animal) {
        $species_id_to_check = $animal['species_id']; // Запам'ятовуємо ID виду

        // 2. Видаляємо файл фотографії (щоб не засмічувати сервер)
        if ($animal['photo_path'] && $animal['photo_path'] !== 'default.png') {
            $file_path = 'assets/img/animals/' . $animal['photo_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // 3. ВИДАЛЯЄМО САМУ ТВАРИНУ
        $stmt = $pdo->prepare("DELETE FROM animals WHERE id = ?");
        $stmt->execute([$id]);

        // =========================================================
        // 4. АВТОМАТИЧНЕ ВИДАЛЕННЯ ПУСТОГО ВИДУ (Магія тут)
        // =========================================================
        if ($species_id_to_check) {
            // Рахуємо, скільки тварин цього виду залишилось
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM animals WHERE species_id = ?");
            $stmt_count->execute([$species_id_to_check]);
            $remaining_count = $stmt_count->fetchColumn();

            // Якщо тварин 0 -> Видаляємо і сам вид
            if ($remaining_count == 0) {
                $stmt_del_species = $pdo->prepare("DELETE FROM species WHERE id = ?");
                $stmt_del_species->execute([$species_id_to_check]);
            }
        }
        // =========================================================
    }
}

// Повертаємось назад до списку
header("Location: animals.php");
exit;
?>