-- Migration script to add missing columns to clubs table
-- Run this in phpMyAdmin or MySQL command line

-- Add semester column
ALTER TABLE `clubs` 
ADD COLUMN `semester` ENUM('1', '2') NOT NULL DEFAULT '1' AFTER `academic_year`;

-- Add class_levels column (JSON type)
ALTER TABLE `clubs` 
ADD COLUMN `class_levels` JSON AFTER `semester`;

-- Add max_students column
ALTER TABLE `clubs` 
ADD COLUMN `max_students` INT DEFAULT 30 AFTER `class_levels`;

-- Verify the changes
DESCRIBE `clubs`;
