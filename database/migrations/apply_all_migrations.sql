-- All-in-One Migration Script for EduGrade System
-- Run this script to apply all pending migrations at once
-- Last updated: 2025-12-08

-- 1. Add settings table (if not exists)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add period column to attendance table
ALTER TABLE `attendance` 
ADD COLUMN IF NOT EXISTS `period` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Period number (1-8)' AFTER `date`;

-- Drop old unique key if exists
ALTER TABLE `attendance` DROP INDEX IF EXISTS `student_course_date`;

-- Add new unique key with period
ALTER TABLE `attendance` 
ADD UNIQUE KEY IF NOT EXISTS `student_course_date_period` (`student_id`, `course_id`, `date`, `period`);

-- 3. Add max_score column to grade_categories table
ALTER TABLE `grade_categories` 
ADD COLUMN IF NOT EXISTS `max_score` DECIMAL(10,2) NOT NULL DEFAULT 100 COMMENT 'Maximum score for this category' AFTER `weight`;

-- 4. Add club system columns (if not exists)
ALTER TABLE `clubs` 
ADD COLUMN IF NOT EXISTS `class_levels` VARCHAR(255) DEFAULT NULL COMMENT 'Comma-separated class levels' AFTER `description`,
ADD COLUMN IF NOT EXISTS `max_students` INT DEFAULT NULL COMMENT 'Maximum number of students' AFTER `class_levels`;

-- Migration complete!
SELECT 'All migrations applied successfully!' AS status;
