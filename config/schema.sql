-- Create Database
CREATE DATABASE IF NOT EXISTS bctl_animal_db;
USE bctl_animal_db;

-- Animals table - Store information about individual animals
CREATE TABLE IF NOT EXISTS animals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    species VARCHAR(100) NOT NULL,
    common_name VARCHAR(100),
    scientific_name VARCHAR(150),
    habitat VARCHAR(100),
    status ENUM('Healthy', 'Injured', 'Rescued', 'Released', 'Deceased') DEFAULT 'Healthy',
    location VARCHAR(200),
    date_recorded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes LONGTEXT,
    reporter VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_species (species),
    INDEX idx_status (status),
    INDEX idx_date (date_recorded)
);

-- Conservation Actions table - Track actions taken for animals
CREATE TABLE IF NOT EXISTS conservation_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    action_type ENUM('Rescue', 'Release', 'Patrol', 'Education', 'Habitat Restoration', 'Medical Treatment', 'Other') NOT NULL,
    description LONGTEXT,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    officer_name VARCHAR(100),
    location VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    INDEX idx_animal (animal_id),
    INDEX idx_action_type (action_type),
    INDEX idx_action_date (action_date)
);

-- Blockchain Ledger table - Store blockchain records
CREATE TABLE IF NOT EXISTS blockchain_ledger (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT,
    action_id INT,
    transaction_hash VARCHAR(255) UNIQUE,
    block_index INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data JSON,
    previous_hash VARCHAR(255),
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE SET NULL,
    FOREIGN KEY (action_id) REFERENCES conservation_actions(id) ON DELETE SET NULL,
    INDEX idx_hash (transaction_hash),
    INDEX idx_block (block_index)
);

-- Habitat Data table - Track habitat information
CREATE TABLE IF NOT EXISTS habitats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    habitat_type VARCHAR(100),
    location VARCHAR(200),
    area_sq_km DECIMAL(10, 2),
    animal_count INT DEFAULT 0,
    conservation_status VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- Reports table - Aggregate conservation reports
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_animals INT,
    total_actions INT,
    content LONGTEXT,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_date (report_date)
);
