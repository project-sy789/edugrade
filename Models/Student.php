<?php

namespace App\Models;

/**
 * Student Model
 * 
 * Handles all student-related database operations.
 */
class Student
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new student
     * 
     * @param array $data Student data
     * @return int Student ID
     * @throws \Exception if validation fails or duplicate exists
     */
    public function create($data)
    {
        // Validate required fields
        $this->validate($data);
        
        // Check for duplicates
        if ($this->isDuplicate($data['student_code'], $data['id_card'])) {
            throw new \Exception('รหัสนักเรียนหรือเลขบัตรประชาชนซ้ำกับข้อมูลที่มีอยู่แล้ว');
        }
        
        return $this->db->insert('students', [
            'student_code' => $data['student_code'],
            'id_card' => $data['id_card'],
            'name' => $data['name'],
            'class_level' => $data['class_level'],
            'classroom' => $data['classroom'],
            'notes' => $data['notes'] ?? null
        ]);
    }
    
    /**
     * Update student information
     * 
     * @param int $id Student ID
     * @param array $data Student data
     * @return int Number of affected rows
     * @throws \Exception if validation fails or duplicate exists
     */
    public function update($id, $data)
    {
        // Validate required fields
        $this->validate($data);
        
        // Check for duplicates (excluding current student)
        if ($this->isDuplicate($data['student_code'], $data['id_card'], $id)) {
            throw new \Exception('รหัสนักเรียนหรือเลขบัตรประชาชนซ้ำกับข้อมูลที่มีอยู่แล้ว');
        }
        
        return $this->db->update('students', [
            'student_code' => $data['student_code'],
            'id_card' => $data['id_card'],
            'name' => $data['name'],
            'class_level' => $data['class_level'],
            'classroom' => $data['classroom'],
            'notes' => $data['notes'] ?? null
        ], 'id = :id', [':id' => $id]);
    }
    
    /**
     * Delete a student
     * 
     * @param int $id Student ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        return $this->db->delete('students', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Find student by ID
     * 
     * @param int $id Student ID
     * @return array|false Student data or false if not found
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            'SELECT * FROM students WHERE id = :id',
            [':id' => $id]
        );
    }
    
    /**
     * Find student by ID card
     * 
     * @param string $idCard ID card number
     * @return array|false Student data or false if not found
     */
    public function findByIdCard($idCard)
    {
        return $this->db->fetchOne(
            'SELECT * FROM students WHERE id_card = :id_card',
            [':id_card' => $idCard]
        );
    }
    
    /**
     * Find student by student code
     * 
     * @param string $studentCode Student code
     * @return array|false Student data or false if not found
     */
    public function findByStudentCode($studentCode)
    {
        return $this->db->fetchOne(
            'SELECT * FROM students WHERE student_code = :student_code',
            [':student_code' => $studentCode]
        );
    }
    
    /**
     * Search students by keyword
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit number of results (0 = no limit)
     * @param int $offset Offset for pagination
     * @return array Array of students
     */
    public function search($keyword, $limit = 0, $offset = 0)
    {
        $searchTerm = '%' . $keyword . '%';
        $sql = 'SELECT * FROM students 
         WHERE student_code LIKE :keyword 
            OR id_card LIKE :keyword 
            OR name LIKE :keyword 
         ORDER BY class_level, classroom, student_code';
        
        if ($limit > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':keyword', $searchTerm);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        
        return $this->db->fetchAll($sql, [':keyword' => $searchTerm]);
    }
    
    /**
     * Count students matching search keyword
     * 
     * @param string $keyword Search keyword
     * @return int Count of matching students
     */
    public function countSearch($keyword)
    {
        $searchTerm = '%' . $keyword . '%';
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM students 
             WHERE student_code LIKE :keyword 
                OR id_card LIKE :keyword 
                OR name LIKE :keyword',
            [':keyword' => $searchTerm]
        );
        return (int) $result['count'];
    }
    
    /**
     * Get all students
     * 
     * @param int $limit Limit number of results (0 = no limit)
     * @param int $offset Offset for pagination
     * @return array Array of students
     */
    public function getAll($limit = 0, $offset = 0)
    {
        $sql = 'SELECT * FROM students ORDER BY class_level, classroom, student_code';
        
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
     * Get students by class
     * 
     * @param string $classLevel Class level (e.g., "ม.1")
     * @param string $classroom Classroom (e.g., "1")
     * @return array Array of students
     */
    public function getByClass($classLevel, $classroom = null)
    {
        if ($classroom !== null) {
            return $this->db->fetchAll(
                'SELECT * FROM students 
                 WHERE class_level = :class_level AND classroom = :classroom 
                 ORDER BY student_code',
                [':class_level' => $classLevel, ':classroom' => $classroom]
            );
        }
        
        return $this->db->fetchAll(
            'SELECT * FROM students 
             WHERE class_level = :class_level 
             ORDER BY classroom, student_code',
            [':class_level' => $classLevel]
        );
    }
    
    /**
     * Bulk insert students (for XLSX import)
     * 
     * @param array $students Array of student data
     * @return array Result with success count and errors
     */
    public function bulkInsert($students)
    {
        $successCount = 0;
        $errors = [];
        
        $this->db->beginTransaction();
        
        try {
            foreach ($students as $index => $student) {
                try {
                    $this->create($student);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'data' => $student,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        
        return [
            'success' => $successCount,
            'errors' => $errors,
            'total' => count($students)
        ];
    }
    
    /**
     * Count total students
     * 
     * @return int Total number of students
     */
    public function count()
    {
        $result = $this->db->fetchOne('SELECT COUNT(*) as total FROM students');
        return (int) $result['total'];
    }
    
    /**
     * Validate student data
     * 
     * @param array $data Student data
     * @throws \Exception if validation fails
     */
    private function validate($data)
    {
        $required = ['student_code', 'id_card', 'name', 'class_level', 'classroom'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("ฟิลด์ {$field} จำเป็นต้องกรอก");
            }
        }
        
        // Validate ID card format (13 digits)
        if (!preg_match('/^\d{13}$/', $data['id_card'])) {
            throw new \Exception('เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก');
        }
    }
    
    /**
     * Check if student code or ID card already exists
     * 
     * @param string $studentCode Student code
     * @param string $idCard ID card number
     * @param int|null $excludeId Student ID to exclude from check (for updates)
     * @return bool True if duplicate exists
     */
    private function isDuplicate($studentCode, $idCard, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*) as count FROM students 
                WHERE (student_code = :student_code OR id_card = :id_card)';
        
        $params = [
            ':student_code' => $studentCode,
            ':id_card' => $idCard
        ];
        
        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Authenticate student with ID card and student code
     * 
     * @param string $idCard ID card number
     * @param string $studentCode Student code
     * @return array|false Student data if authenticated, false otherwise
     */
    public function authenticate($idCard, $studentCode)
    {
        return $this->db->fetchOne(
            'SELECT * FROM students WHERE id_card = :id_card AND student_code = :student_code',
            [':id_card' => $idCard, ':student_code' => $studentCode]
        );
    }
}
