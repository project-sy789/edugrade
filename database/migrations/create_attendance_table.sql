-- Simplified migration - Run each step one by one if needed
-- Step 1: Create attendance table WITHOUT foreign keys first

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

-- Done! The table is ready to use
SELECT 'Attendance table created successfully!' AS status;
