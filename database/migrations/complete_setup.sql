-- Complete Database Setup Script
-- This creates ALL tables needed for EduGrade system
-- Run this ONCE in phpMyAdmin

-- Students table
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_code` VARCHAR(50) UNIQUE NOT NULL,
    `id_card` VARCHAR(13),
    `name` VARCHAR(255) NOT NULL,
    `class_level` VARCHAR(10) NOT NULL,
    `classroom` VARCHAR(10) NOT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_class` (`class_level`, `classroom`),
    INDEX `idx_student_code` (`student_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `role` ENUM('teacher', 'admin') DEFAULT 'teacher',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_code` VARCHAR(50) UNIQUE NOT NULL,
    `course_name` VARCHAR(255) NOT NULL,
    `teacher_id` INT,
    `academic_year` VARCHAR(10) NOT NULL,
    `semester` ENUM('1', '2') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_teacher` (`teacher_id`),
    INDEX `idx_academic_year` (`academic_year`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course enrollments table
CREATE TABLE IF NOT EXISTS `course_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_enrollment` (`student_id`, `course_id`),
    INDEX `idx_student` (`student_id`),
    INDEX `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance table (with period support)
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `period` TINYINT UNSIGNED DEFAULT 1,
    `status` ENUM('present', 'absent', 'sick', 'leave', 'late') NOT NULL,
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_attendance` (`student_id`, `course_id`, `date`, `period`),
    INDEX `idx_att_student_course` (`student_id`, `course_id`),
    INDEX `idx_att_course_date` (`course_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grade categories table
CREATE TABLE IF NOT EXISTS `grade_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `category_name` VARCHAR(255) NOT NULL,
    `weight` DECIMAL(5,2) DEFAULT 0,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_gc_course` (`course_id`),
    INDEX `idx_gc_order` (`course_id`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grades table
CREATE TABLE IF NOT EXISTS `grades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `score` DECIMAL(10,2),
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_grade` (`student_id`, `category_id`),
    INDEX `idx_student_course` (`student_id`, `course_id`),
    INDEX `idx_grade_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clubs table
CREATE TABLE IF NOT EXISTS `clubs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `club_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `teacher_id` INT,
    `academic_year` VARCHAR(10) NOT NULL,
    `semester` ENUM('1', '2') NOT NULL DEFAULT '1',
    `class_levels` JSON,
    `max_students` INT DEFAULT 30,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_teacher` (`teacher_id`),
    INDEX `idx_academic_year` (`academic_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Club enrollments table
CREATE TABLE IF NOT EXISTS `club_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `club_id` INT NOT NULL,
    `grade` DECIMAL(5,2),
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_club_enrollment` (`student_id`, `club_id`),
    INDEX `idx_student` (`student_id`),
    INDEX `idx_club` (`club_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: password)
INSERT IGNORE INTO `users` (`username`, `password`, `name`, `role`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin');

-- Insert default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'ระบบจัดการคะแนนและเวลาเรียน'),
('site_logo', ''),
('site_favicon', '');

SELECT 'Database setup completed successfully!' AS status;
SELECT 'Default login: admin / password' AS info;
