-- Migration script to add period column to attendance table
-- This script checks if table exists first

-- Check if attendance table exists, if not create it
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('present', 'absent', 'sick', 'leave', 'late') NOT NULL,
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_att_student_course` (`student_id`, `course_id`),
    INDEX `idx_att_course_date` (`course_id`, `date`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add period column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'attendance' 
  AND COLUMN_NAME = 'period';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE `attendance` ADD COLUMN `period` TINYINT UNSIGNED DEFAULT 1 AFTER `date`',
    'SELECT "Column period already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop old unique key if exists
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists 
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'attendance' 
  AND INDEX_NAME = 'unique_attendance';

SET @query = IF(@index_exists > 0,
    'ALTER TABLE `attendance` DROP INDEX `unique_attendance`',
    'SELECT "Index unique_attendance does not exist" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add new unique key with period
ALTER TABLE `attendance` 
ADD UNIQUE KEY `unique_attendance` (`student_id`, `course_id`, `date`, `period`);

-- Update existing records to have period = 1
UPDATE `attendance` SET `period` = 1 WHERE `period` IS NULL OR `period` = 0;

-- Verify the changes
SELECT 'Migration completed successfully!' AS status;
DESCRIBE `attendance`;
