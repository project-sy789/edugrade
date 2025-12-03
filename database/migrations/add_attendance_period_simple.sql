-- Simple migration script to add period column to attendance table
-- Run this in phpMyAdmin SQL tab

-- Step 1: Create attendance table if it doesn't exist
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

-- Step 2: Add period column (will show error if already exists, that's OK)
ALTER TABLE `attendance` 
ADD COLUMN `period` TINYINT UNSIGNED DEFAULT 1 AFTER `date`;

-- Step 3: Drop old unique key (will show error if doesn't exist, that's OK)
ALTER TABLE `attendance` 
DROP INDEX `unique_attendance`;

-- Step 4: Add new unique key with period
ALTER TABLE `attendance` 
ADD UNIQUE KEY `unique_attendance` (`student_id`, `course_id`, `date`, `period`);

-- Step 5: Update existing records to have period = 1
UPDATE `attendance` SET `period` = 1 WHERE `period` IS NULL OR `period` = 0;

-- Done! Check the result:
SELECT 'Migration completed!' AS status;
DESCRIBE `attendance`;
