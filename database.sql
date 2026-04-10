CREATE DATABASE IF NOT EXISTS hormofit;
USE hormofit;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS health_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    weight FLOAT,
    height FLOAT,
    cycle_length INT,
    symptoms TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS wellness_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    mood_level INT,
    stress_level INT,
    anxiety_level INT,
    sleep_hours FLOAT,
    energy_level INT,
    water_glasses INT,
    exercise_minutes INT,
    cravings_level INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
