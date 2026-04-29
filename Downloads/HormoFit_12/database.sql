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

CREATE TABLE IF NOT EXISTS user_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    water_goal INT DEFAULT 8,
    sleep_goal FLOAT DEFAULT 8,
    exercise_goal INT DEFAULT 30,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prediction_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    age FLOAT NOT NULL,
    weight FLOAT NOT NULL,
    height FLOAT NOT NULL,
    cycle_length FLOAT NOT NULL,
    bmi FLOAT NOT NULL,
    probability FLOAT NOT NULL,
    prediction TINYINT(1) NOT NULL,
    accuracy FLOAT NOT NULL,
    risk_level VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pcos_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    age INT NOT NULL,
    cycle_pattern VARCHAR(30) NOT NULL,
    skipped_periods VARCHAR(20) NOT NULL,
    facial_hair TINYINT(1) NOT NULL DEFAULT 0,
    persistent_acne TINYINT(1) NOT NULL DEFAULT 0,
    scalp_hair_thinning TINYINT(1) NOT NULL DEFAULT 0,
    unexplained_weight_gain TINYINT(1) NOT NULL DEFAULT 0,
    dark_skin_patches TINYINT(1) NOT NULL DEFAULT 0,
    high_sugar_cravings TINYINT(1) NOT NULL DEFAULT 0,
    family_history TINYINT(1) NOT NULL DEFAULT 0,
    trying_to_conceive TINYINT(1) NOT NULL DEFAULT 0,
    pelvic_pain TINYINT(1) NOT NULL DEFAULT 0,
    activity_level VARCHAR(20) NOT NULL,
    stress_load VARCHAR(20) NOT NULL,
    risk_score INT NOT NULL,
    risk_band VARCHAR(20) NOT NULL,
    ai_recommendation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
