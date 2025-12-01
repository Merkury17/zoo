-- Створення БД
CREATE DATABASE IF NOT EXISTS zoo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zoo_db;

-- 1. Таблиця видів (Species)
CREATE TABLE species (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- Назва (Лев)
    scientific_name VARCHAR(150), -- Panthera leo
    diet_type ENUM('Carnivore', 'Herbivore', 'Omnivore') NOT NULL, -- Тип харчування
    description TEXT
);

-- 2. Таблиця вольєрів (Enclosures)
CREATE TABLE enclosures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- "Савана-1"
    type ENUM('Open', 'Closed', 'Aquarium') NOT NULL,
    capacity INT NOT NULL,
    location_x INT DEFAULT 0, -- Координати для карти
    location_y INT DEFAULT 0
);

-- 3. Таблиця співробітників (Staff)
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    position VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- Хеш пароля
    role ENUM('admin', 'viewer', 'vet') DEFAULT 'viewer'
);

-- 4. Таблиця тварин (Animals)
CREATE TABLE animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    species_id INT,
    enclosure_id INT,
    gender ENUM('Male', 'Female') NOT NULL,
    birth_date DATE,
    arrival_date DATE,
    health_status ENUM('Healthy', 'Sick', 'Critical', 'Recovering') DEFAULT 'Healthy',
    photo_url VARCHAR(255) DEFAULT 'default.jpg',
    FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE SET NULL,
    FOREIGN KEY (enclosure_id) REFERENCES enclosures(id) ON DELETE SET NULL
);

-- 5. Таблиця вет. оглядів (VetChecks)
CREATE TABLE vet_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    vet_id INT, -- ID лікаря (співробітника)
    check_date DATE NOT NULL,
    diagnosis VARCHAR(255),
    next_check_date DATE,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (vet_id) REFERENCES staff(id) ON DELETE SET NULL
);

-- 6. Таблиця годувань (Feedings) - для складності
CREATE TABLE feedings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    food_item VARCHAR(100),
    feed_time TIME,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE
);

-- === НАПОВНЕННЯ ДАНИМИ (TEST DATA) ===

INSERT INTO species (name, scientific_name, diet_type, description) VALUES
('Лев Африканський', 'Panthera leo', 'Carnivore', 'Король звірів.'),
('Жирафа', 'Giraffa camelopardalis', 'Herbivore', 'Найвища тварина.'),
('Пінгвін', 'Spheniscidae', 'Carnivore', 'Птах, що не літає.'),
('Слон Індійський', 'Elephas maximus', 'Herbivore', 'Дуже розумна тварина.'),
('Зебра', 'Hippotigris', 'Herbivore', 'Смугаста конячка.');

INSERT INTO enclosures (name, type, capacity, location_x, location_y) VALUES
('Сектор Хижаків', 'Open', 5, 1, 1),
('Савана', 'Open', 10, 2, 1),
('Аквазона', 'Aquarium', 20, 1, 2),
('Слоновник', 'Open', 3, 2, 2),
('Карантин', 'Closed', 2, 3, 3);

-- Пароль: admin123 (хеш)
INSERT INTO staff (full_name, position, email, password_hash, role) VALUES
('Іванов Іван', 'Директор', 'admin@zoo.ua', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Петрова Олена', 'Ветеринар', 'vet@zoo.ua', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vet');

INSERT INTO animals (name, species_id, enclosure_id, gender, birth_date, arrival_date, health_status) VALUES
('Сімба', 1, 1, 'Male', '2018-05-20', '2019-01-10', 'Healthy'),
('Нала', 1, 1, 'Female', '2019-02-15', '2019-06-20', 'Healthy'),
('Мелман', 2, 2, 'Male', '2020-08-10', '2021-03-12', 'Sick'),
('Ковальскі', 3, 3, 'Male', '2021-11-05', '2022-01-15', 'Healthy'),
('Дамбо', 4, 4, 'Male', '2015-01-30', '2016-05-05', 'Healthy'),
('Марті', 5, 2, 'Male', '2019-07-12', '2020-02-20', 'Healthy');

INSERT INTO vet_checks (animal_id, vet_id, check_date, diagnosis, next_check_date) VALUES
(1, 2, '2025-10-01', 'Здоровий', '2025-11-01'),
(3, 2, '2025-10-20', 'Застуда', '2025-10-25'), -- Прострочений огляд! (для тесту алертів)
(5, 2, '2025-11-15', 'Огляд зубів', '2025-12-15');

INSERT INTO `enclosures` (`name`, `type`, `capacity`, `x_coord`, `y_coord`, `zone_type`) 
VALUES ('Головний Вхід', 'Closed', '0', '50', '90', 'Savanna');

-- Додаємо колонку для опису лікування
ALTER TABLE `vet_checks` ADD COLUMN `treatment` TEXT DEFAULT NULL;

-- Додаємо колонку для імені лікаря
ALTER TABLE `vet_checks` ADD COLUMN `doctor_name` VARCHAR(150) DEFAULT 'Черговий лікар';

ALTER TABLE `animals` ADD COLUMN `original_enclosure_id` INT DEFAULT NULL;