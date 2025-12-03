-- Migration script to add period column to attendance table
-- This allows multiple attendance records per day (different periods)

-- Step 1: Add period column
ALTER TABLE `attendance` 
ADD COLUMN `period` TINYINT UNSIGNED DEFAULT 1 AFTER `date`;

-- Step 2: Drop old unique key
ALTER TABLE `attendance` 
DROP INDEX `unique_attendance`;

-- Step 3: Add new unique key with period
ALTER TABLE `attendance` 
ADD UNIQUE KEY `unique_attendance` (`student_id`, `course_id`, `date`, `period`);

-- Step 4: Update existing records to have period = 1
UPDATE `attendance` SET `period` = 1 WHERE `period` IS NULL;

-- Verify the changes
DESCRIBE `attendance`;
