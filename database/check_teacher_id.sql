-- Check current teacher_id value for course ID 1
SELECT id, course_code, course_name, teacher_id 
FROM courses 
WHERE id = 1;

-- If you want to manually set it for testing:
-- UPDATE courses SET teacher_id = 2 WHERE id = 1;

-- Then check again:
-- SELECT id, course_code, course_name, teacher_id FROM courses WHERE id = 1;
