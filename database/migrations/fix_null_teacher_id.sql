-- Fix existing courses without teacher_id
-- This script updates courses that have NULL teacher_id
-- Run this ONCE in phpMyAdmin after uploading new code

-- Option 1: If you know the admin user ID (usually 1), assign all NULL courses to admin
-- UPDATE courses SET teacher_id = 1 WHERE teacher_id IS NULL;

-- Option 2: Check which courses need updating first
SELECT id, course_code, course_name, teacher_id 
FROM courses 
WHERE teacher_id IS NULL;

-- After reviewing, you can update specific courses:
-- UPDATE courses SET teacher_id = YOUR_ADMIN_ID WHERE id = COURSE_ID;

-- Or update all at once (replace 1 with your actual admin user ID):
-- UPDATE courses SET teacher_id = 1 WHERE teacher_id IS NULL;
