<?php

namespace App\Models;

/**
 * Course Model
 * 
 * Handles all course-related database operations.
 */
class Course
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new course
     * 
     * @param array $data Course data
     * @return int Course ID
     * @throws \Exception if validation fails or duplicate exists
     */
    public function create($data)
    {
        $this->validate($data);
        
        // Check for duplicate course in same academic year/semester
        if ($this->isDuplicate($data['course_code'], $data['academic_year'], $data['semester'])) {
            throw new \Exception('รายวิชานี้มีอยู่แล้วในปีการศึกษาและภาคเรียนที่ระบุ');
        }
        
        return $this->db->insert('courses', [
            'course_code' => $data['course_code'],
            'course_name' => $data['course_name'],
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'],
            'teacher_id' => $data['teacher_id'] ?? null
        ]);
    }
    
    /**
     * Update course information
     * 
     * @param int $id Course ID
     * @param array $data Course data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        $this->validate($data);
        
        if ($this->isDuplicate($data['course_code'], $data['academic_year'], $data['semester'], $id)) {
            throw new \Exception('รายวิชานี้มีอยู่แล้วในปีการศึกษาและภาคเรียนที่ระบุ');
        }
        
        return $this->db->update('courses', [
            'course_code' => $data['course_code'],
            'course_name' => $data['course_name'],
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'],
            'teacher_id' => $data['teacher_id'] ?? null
        ], 'id = :id', [':id' => $id]);
    }
    
    /**
     * Delete a course
     * 
     * @param int $id Course ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        return $this->db->delete('courses', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Find course by ID
     * 
     * @param int $id Course ID
     * @return array|false Course data or false if not found
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            'SELECT * FROM courses WHERE id = :id',
            [':id' => $id]
        );
    }
    
    /**
     * Get courses by academic year and semester
     * 
     * @param string $year Academic year
     * @param int|null $semester Semester (null = all semesters)
     * @return array Array of courses
     */
    public function findByAcademicYear($year, $semester = null)
    {
        if ($semester !== null) {
            return $this->db->fetchAll(
                'SELECT * FROM courses 
                 WHERE academic_year = :year AND semester = :semester 
                 ORDER BY course_code',
                [':year' => $year, ':semester' => $semester]
            );
        }
        
        return $this->db->fetchAll(
            'SELECT * FROM courses 
             WHERE academic_year = :year 
             ORDER BY semester, course_code',
            [':year' => $year]
        );
    }
    
    /**
     * Get all courses
     * 
     * @param int $limit Limit number of results (0 = no limit)
     * @param int $offset Offset for pagination
     * @return array Array of courses
     */
    public function getAll($limit = 0, $offset = 0)
    {
        $sql = 'SELECT c.*, u.name as teacher_name 
             FROM courses c
             LEFT JOIN users u ON c.teacher_id = u.id
             ORDER BY c.academic_year DESC, c.semester DESC, c.course_code';
        
        if ($limit > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get courses by teacher ID
     * 
     * @param int $teacherId Teacher ID
     * @param int $limit Limit number of results (0 = no limit)
     * @param int $offset Offset for pagination
     * @return array Array of courses
     */
    public function getByTeacher($teacherId, $limit = 0, $offset = 0)
    {
        $sql = 'SELECT * FROM courses 
             WHERE teacher_id = :teacher_id
             ORDER BY academic_year DESC, semester DESC, course_code';
        
        $params = [':teacher_id' => $teacherId];
        
        if ($limit > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':teacher_id', $teacherId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Enroll a student in a course
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @return int Enrollment ID
     * @throws \Exception if already enrolled
     */
    public function enrollStudent($courseId, $studentId)
    {
        // Check if already enrolled
        $existing = $this->db->fetchOne(
            'SELECT id FROM course_enrollments 
             WHERE course_id = :course_id AND student_id = :student_id',
            [':course_id' => $courseId, ':student_id' => $studentId]
        );
        
        if ($existing) {
            throw new \Exception('นักเรียนลงทะเบียนรายวิชานี้แล้ว');
        }
        
        return $this->db->insert('course_enrollments', [
            'course_id' => $courseId,
            'student_id' => $studentId
        ]);
    }
    
    /**
     * Enroll multiple students in a course
     * 
     * @param int $courseId Course ID
     * @param array $studentIds Array of student IDs
     * @return array Result with success count and errors
     */
    public function enrollStudents($courseId, $studentIds)
    {
        $successCount = 0;
        $errors = [];
        
        foreach ($studentIds as $studentId) {
            try {
                $this->enrollStudent($courseId, $studentId);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'student_id' => $studentId,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => $successCount,
            'errors' => $errors,
            'total' => count($studentIds)
        ];
    }
    
    /**
     * Unenroll a student from a course
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @return int Number of affected rows
     */
    public function unenrollStudent($courseId, $studentId)
    {
        return $this->db->delete(
            'course_enrollments',
            'course_id = :course_id AND student_id = :student_id',
            [':course_id' => $courseId, ':student_id' => $studentId]
        );
    }
    
    /**
     * Get enrolled students in a course
     * 
     * @param int $courseId Course ID
     * @return array Array of students with enrollment info
     */
    public function getEnrolledStudents($courseId)
    {
        return $this->db->fetchAll(
            'SELECT s.*, ce.id as enrollment_id, ce.enrolled_at 
             FROM students s
             INNER JOIN course_enrollments ce ON s.id = ce.student_id
             WHERE ce.course_id = :course_id
             ORDER BY s.class_level, s.classroom, s.student_code',
            [':course_id' => $courseId]
        );
    }
    
    /**
     * Get courses that a student is enrolled in
     * 
     * @param int $studentId Student ID
     * @return array Array of courses
     */
    public function getStudentCourses($studentId)
    {
        return $this->db->fetchAll(
            'SELECT c.*, ce.enrolled_at 
             FROM courses c
             INNER JOIN course_enrollments ce ON c.id = ce.course_id
             WHERE ce.student_id = :student_id
             ORDER BY c.academic_year DESC, c.semester DESC, c.course_code',
            [':student_id' => $studentId]
        );
    }
    
    /**
     * Count enrolled students in a course
     * 
     * @param int $courseId Course ID
     * @return int Number of enrolled students
     */
    public function countEnrolledStudents($courseId)
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as total FROM course_enrollments WHERE course_id = :course_id',
            [':course_id' => $courseId]
        );
        return (int) $result['total'];
    }
    
    /**
     * Validate course data
     * 
     * @param array $data Course data
     * @throws \Exception if validation fails
     */
    private function validate($data)
    {
        $required = ['course_code', 'course_name', 'academic_year', 'semester'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("ฟิลด์ {$field} จำเป็นต้องกรอก");
            }
        }
        
        // Validate semester (1 or 2)
        if (!in_array($data['semester'], [1, 2])) {
            throw new \Exception('ภาคเรียนต้องเป็น 1 หรือ 2');
        }
    }
    
    /**
     * Check if course already exists in the same academic year/semester
     * 
     * @param string $courseCode Course code
     * @param string $academicYear Academic year
     * @param int $semester Semester
     * @param int|null $excludeId Course ID to exclude from check
     * @return bool True if duplicate exists
     */
    private function isDuplicate($courseCode, $academicYear, $semester, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*) as count FROM courses 
                WHERE course_code = :course_code 
                  AND academic_year = :academic_year 
                  AND semester = :semester';
        
        $params = [
            ':course_code' => $courseCode,
            ':academic_year' => $academicYear,
            ':semester' => $semester
        ];
        
        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Count total courses
     * 
     * @return int Total number of courses
     */
    public function count()
    {
        $result = $this->db->fetchOne('SELECT COUNT(*) as total FROM courses');
        return (int) $result['total'];
    }
}
