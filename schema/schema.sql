-- Create database
CREATE DATABASE IF NOT EXISTS agro_mgmt
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agro_mgmt;

-- Farmers (parent)
CREATE TABLE IF NOT EXISTS farmers (
  farmer_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  address VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Animals (child of farmers; optional self-join for lineage)
CREATE TABLE IF NOT EXISTS animals (
  animal_id INT AUTO_INCREMENT PRIMARY KEY,
  farmer_id INT NOT NULL,
  tag_code VARCHAR(50) UNIQUE NOT NULL,
  species ENUM('Cattle','Goat','Sheep','Poultry','Duck') NOT NULL,
  breed VARCHAR(100),
  sex ENUM('M','F') NOT NULL,
  date_of_birth DATE,
  status ENUM('Active','Sold','Dead') DEFAULT 'Active',
  mother_id INT NULL,
  father_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_animals_farmer FOREIGN KEY (farmer_id)
    REFERENCES farmers(farmer_id) ON DELETE CASCADE,
  CONSTRAINT fk_animals_mother FOREIGN KEY (mother_id)
    REFERENCES animals(animal_id) ON DELETE SET NULL,
  CONSTRAINT fk_animals_father FOREIGN KEY (father_id)
    REFERENCES animals(animal_id) ON DELETE SET NULL
);

-- Feed master
CREATE TABLE IF NOT EXISTS feed (
  feed_id INT AUTO_INCREMENT PRIMARY KEY,
  feed_name VARCHAR(100) NOT NULL,
  feed_type ENUM('Concentrate','Roughage','Mineral','Vitamin','Mixed') NOT NULL,
  unit VARCHAR(20) DEFAULT 'kg',
  energy_kcal_per_kg DECIMAL(10,2) DEFAULT 0.00
);

-- Feeding schedule (animal × feed)
CREATE TABLE IF NOT EXISTS feeding_schedule (
  schedule_id INT AUTO_INCREMENT PRIMARY KEY,
  animal_id INT NOT NULL,
  feed_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  feed_time TIME NOT NULL,
  notes VARCHAR(255),
  CONSTRAINT fk_sched_animal FOREIGN KEY (animal_id)
    REFERENCES animals(animal_id) ON DELETE CASCADE,
  CONSTRAINT fk_sched_feed FOREIGN KEY (feed_id)
    REFERENCES feed(feed_id) ON DELETE RESTRICT
);

-- Production logs
CREATE TABLE IF NOT EXISTS production (
  production_id INT AUTO_INCREMENT PRIMARY KEY,
  animal_id INT NOT NULL,
  prod_date DATE NOT NULL,
  milk_liters DECIMAL(10,2) DEFAULT 0.00,
  eggs_count INT DEFAULT 0,
  weight_gain_kg DECIMAL(10,2) DEFAULT 0.00,
  notes VARCHAR(255),
  CONSTRAINT fk_prod_animal FOREIGN KEY (animal_id)
    REFERENCES animals(animal_id) ON DELETE CASCADE,
  UNIQUE KEY uk_prod_animal_date (animal_id, prod_date)
);

-- Health records
CREATE TABLE IF NOT EXISTS health_record (
  health_id INT AUTO_INCREMENT PRIMARY KEY,
  animal_id INT NOT NULL,
  visit_date DATE NOT NULL,
  diagnosis VARCHAR(255),
  treatment VARCHAR(255),
  vet_name VARCHAR(100),
  cost DECIMAL(10,2) DEFAULT 0.00,
  CONSTRAINT fk_health_animal FOREIGN KEY (animal_id)
    REFERENCES animals(animal_id) ON DELETE CASCADE
);

-- Helpful indexes
CREATE INDEX idx_animals_farmer ON animals(farmer_id);
CREATE INDEX idx_sched_animal ON feeding_schedule(animal_id);
CREATE INDEX idx_prod_date ON production(prod_date);
CREATE INDEX idx_health_date ON health_record(visit_date);

-- Seed data (tiny sample)
INSERT INTO farmers (full_name, phone, address) VALUES
('Abdul Karim', '01711-111111', 'Rajshahi'),
('Fatema Begum', '01822-222222', 'Khulna'),
('Zahid Hasan',  '01933-333333', 'Sylhet')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

INSERT INTO animals (farmer_id, tag_code, species, breed, sex, date_of_birth, status)
VALUES
(1, 'TAG-C1', 'Cattle', 'Local', 'F', '2022-03-10', 'Active'),
(1, 'TAG-G1', 'Goat',   'Black Bengal', 'F', '2023-01-15', 'Active'),
(2, 'TAG-P1', 'Poultry','Lohmann', 'F', '2024-05-01', 'Active')
ON DUPLICATE KEY UPDATE breed=VALUES(breed);

INSERT INTO feed (feed_name, feed_type, unit, energy_kcal_per_kg) VALUES
('Maize', 'Concentrate', 'kg', 3500),
('Rice Straw', 'Roughage', 'kg', 1800),
('Mineral Mix', 'Mineral', 'kg', 0)
ON DUPLICATE KEY UPDATE feed_type=VALUES(feed_type);

INSERT INTO feeding_schedule (animal_id, feed_id, amount, feed_time, notes) VALUES
(1, 1, 3.00, '08:00:00', 'Morning'),
(1, 2, 5.00, '17:00:00', 'Evening'),
(2, 1, 1.50, '08:30:00', 'Morning')
ON DUPLICATE KEY UPDATE amount=VALUES(amount);

INSERT INTO production (animal_id, prod_date, milk_liters, eggs_count, weight_gain_kg, notes) VALUES
(1, '2025-10-20', 6.5, 0, 0.10, 'Good yield'),
(1, '2025-10-21', 7.1, 0, 0.12, 'Improving'),
(2, '2025-10-21', 1.2, 0, 0.05, 'Goat milk'),
(3, '2025-10-21', 0, 12, 0.02, 'Eggs from poultry')
ON DUPLICATE KEY UPDATE milk_liters=VALUES(milk_liters), eggs_count=VALUES(eggs_count);

INSERT INTO health_record (animal_id, visit_date, diagnosis, treatment, vet_name, cost) VALUES
(1, '2025-10-19', 'Mastitis', 'Antibiotic 3 days', 'Dr. Rahman', 1200.00),
(3, '2025-10-21', 'Deworm', 'Albendazole', 'Dr. Akter', 300.00);
