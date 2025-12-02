-- Student Grade and Attendance System Database Schema
-- MySQL Version

SET FOREIGN_KEY_CHECKS=0;

-- Drop tables if exists (for clean installation)
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `grades`;
DROP TABLE IF EXISTS `grade_categories`;
DROP TABLE IF EXISTS `course_enrollments`;
DROP TABLE IF EXISTS `course_students`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `clubs`;
DROP TABLE IF EXISTS `club_enrollments`;

SET FOREIGN_KEY_CHECKS=1;

-- Students table
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_code` VARCHAR(50) UNIQUE NOT NULL,
    `id_card` VARCHAR(13) UNIQUE NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `class_level` VARCHAR(50) NOT NULL,
    `classroom` VARCHAR(50) NOT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student_code` (`student_code`),
    INDEX `idx_id_card` (`id_card`),
    INDEX `idx_class` (`class_level`, `classroom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_code` VARCHAR(50) NOT NULL,
    `course_name` VARCHAR(255) NOT NULL,
    `academic_year` VARCHAR(10) NOT NULL,
    `semester` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_course` (`course_code`, `academic_year`, `semester`),
    INDEX `idx_academic_year` (`academic_year`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course enrollments table
CREATE TABLE `course_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_enrollment` (`student_id`, `course_id`),
    INDEX `idx_student` (`student_id`),
    INDEX `idx_course` (`course_id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course students table (alias for compatibility)
CREATE TABLE `course_students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_enrollment` (`student_id`, `course_id`),
    INDEX `idx_student` (`student_id`),
    INDEX `idx_course` (`course_id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grade categories table
CREATE TABLE `grade_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `category_name` VARCHAR(255) NOT NULL,
    `max_score` DECIMAL(10,2) NOT NULL,
    `weight` DECIMAL(5,2) DEFAULT 0,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_gc_course` (`course_id`),
    INDEX `idx_gc_order` (`course_id`, `display_order`),
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grades table
CREATE TABLE `grades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `score` DECIMAL(10,2),
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_grade` (`student_id`, `category_id`),
    INDEX `idx_student_course` (`student_id`, `course_id`),
    INDEX `idx_grade_course` (`course_id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `grade_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance table
CREATE TABLE `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('present', 'absent', 'sick', 'leave', 'late') NOT NULL,
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_attendance` (`student_id`, `course_id`, `date`),
    INDEX `idx_att_student_course` (`student_id`, `course_id`),
    INDEX `idx_att_course_date` (`course_id`, `date`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table (for teachers/admins)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `role` ENUM('teacher', 'admin') DEFAULT 'teacher',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clubs table
CREATE TABLE `clubs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `club_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `teacher_id` INT,
    `academic_year` VARCHAR(10) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_teacher` (`teacher_id`),
    INDEX `idx_academic_year` (`academic_year`),
    FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Club enrollments table
CREATE TABLE `club_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `club_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `grade` DECIMAL(5,2),
    `status` VARCHAR(20) DEFAULT 'active',
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_enrollment` (`club_id`, `student_id`),
    INDEX `idx_club` (`club_id`),
    INDEX `idx_student` (`student_id`),
    FOREIGN KEY (`club_id`) REFERENCES `clubs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: password)
INSERT INTO `users` (`username`, `password`, `name`, `role`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin');

-- Sample data for testing
INSERT INTO `students` (`student_code`, `id_card`, `name`, `class_level`, `classroom`) VALUES
('S001', '1234567890123', 'สมชาย ใจดี', 'ม.1', '1'),
('S002', '1234567890124', 'สมหญิง รักเรียน', 'ม.1', '1'),
('S003', '1234567890125', 'สมศักดิ์ ขยัน', 'ม.1', '2');

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'ระบบจัดการคะแนนและเวลาเรียน'),
('site_logo', ''),
('site_favicon', '');
