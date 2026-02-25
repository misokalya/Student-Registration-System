-- College Registration System Database
CREATE DATABASE IF NOT EXISTS college_registration;
USE college_registration;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    registration_no VARCHAR(50) UNIQUE NOT NULL,
    date_of_birth DATE,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    photo VARCHAR(255) DEFAULT 'default.png',
    course VARCHAR(150) NOT NULL,
    course_type ENUM('fulltime','shortcourse') NOT NULL,
    year_of_study TINYINT DEFAULT 1 COMMENT '1-3 for fulltime',
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') DEFAULT 'student',
    must_change_password TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account (password: Admin@1234)
INSERT INTO users (first_name, last_name, registration_no, email, phone, course, course_type, password, role, must_change_password)
VALUES (
    'System', 'Admin', 'ADM-0001',
    'admin@kiitec.tz',
    '0743864338',
    'Administration',
    'fulltime',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    0
);

USE college_registration;

CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_no VARCHAR(50) NOT NULL,
    subject_code VARCHAR(30) NOT NULL,
    ca_score DECIMAL(5,2) DEFAULT 0,
    se_score DECIMAL(5,2) DEFAULT 0,
    total_score DECIMAL(5,2) DEFAULT 0,
    grade VARCHAR(5) DEFAULT '',
    upload_batch INT DEFAULT 1 COMMENT 'tracks which CSV upload this came from',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reg_no (registration_no),
    UNIQUE KEY unique_result (registration_no, subject_code, upload_batch)
);

CREATE TABLE IF NOT EXISTS result_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    filename VARCHAR(255),
    total_rows INT DEFAULT 0,
    imported_rows INT DEFAULT 0,
    skipped_rows INT DEFAULT 0,
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);