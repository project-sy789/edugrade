-- Add max_score column to grade_categories table if not exists

-- Add max_score column
ALTER TABLE `grade_categories` 
ADD COLUMN `max_score` DECIMAL(10,2) NOT NULL DEFAULT 100 AFTER `category_name`;

-- Verify
SELECT 'max_score column added successfully!' AS status;
DESCRIBE `grade_categories`;
