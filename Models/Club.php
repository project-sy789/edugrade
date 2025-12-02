<?php

namespace App\Models;

use App\Models\Database;

/**
 * Club Model
 * 
 * Handles club management and student enrollments
 */
class Club
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new club
     * 
     * @param array $data Club data
     * @return int Club ID
     */
    public function create($data)
    {
        return $this->db->insert('clubs', [
            'club_name' => $data['club_name'],
            'description' => $data['description'] ?? null,
            'teacher_id' => $data['teacher_id'],
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'],
            'class_levels' => json_encode($data['class_levels']),
            'max_students' => $data['max_students'] ?? 30
        ]);
    }
    
    /**
     * Update club
     * 
     * @param int $id Club ID
     * @param array $data Club data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        $updateData = [];
        
        if (isset($data['club_name'])) {
            $updateData['club_name'] = $data['club_name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['teacher_id'])) {
            $updateData['teacher_id'] = $data['teacher_id'];
        }
        if (isset($data['academic_year'])) {
            $updateData['academic_year'] = $data['academic_year'];
        }
        if (isset($data['semester'])) {
            $updateData['semester'] = $data['semester'];
        }
        if (isset($data['class_levels'])) {
            $updateData['class_levels'] = json_encode($data['class_levels']);
        }
        if (isset($data['max_students'])) {
            $updateData['max_students'] = $data['max_students'];
        }
        
        if (empty($updateData)) {
            return 0;
        }
        
        return $this->db->update('clubs', $updateData, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Delete club
     * 
     * @param int $id Club ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        return $this->db->delete('clubs', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Find club by ID
     * 
     * @param int $id Club ID
     * @return array|null Club data
     */
    public function findById($id)
    {
        $club = $this->db->fetchOne(
            'SELECT c.*, u.name as teacher_name 
             FROM clubs c
             LEFT JOIN users u ON c.teacher_id = u.id
             WHERE c.id = :id',
            [':id' => $id]
        );
        
        if ($club) {
            $club['class_levels'] = json_decode($club['class_levels'], true);
        }
        
        return $club;
    }
    
    /**
     * Get all clubs
     * 
     * @param int $limit Limit (0 = no limit)
     * @param int $offset Offset
     * @return array Array of clubs
     */
    public function getAll($limit = 0, $offset = 0)
    {
        $sql = 'SELECT c.*, u.name as teacher_name 
                FROM clubs c
                LEFT JOIN users u ON c.teacher_id = u.id
                ORDER BY c.academic_year DESC, c.club_name';
        
        if ($limit > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $clubs = $stmt->fetchAll();
        } else {
            $clubs = $this->db->fetchAll($sql);
        }
        
        return $clubs;
    }
    
    /**
     * Count total clubs
     * 
     * @return int Total clubs
     */
    public function count()
    {
        $result = $this->db->fetchOne('SELECT COUNT(*) as total FROM clubs');
        return (int) $result['total'];
    }
    
    /**
     * Get clubs by academic year and semester
     * 
     * @param string $academicYear Academic year
     * @param int $semester Semester
     * @return array Array of clubs
     */
    public function getByAcademicYear($academicYear, $semester)
    {
        $clubs = $this->db->fetchAll(
            'SELECT c.*, u.name as teacher_name 
             FROM clubs c
             LEFT JOIN users u ON c.teacher_id = u.id
             WHERE c.academic_year = :year AND c.semester = :semester
             ORDER BY c.club_name',
            [
                ':year' => $academicYear,
                ':semester' => $semester
            ]
        );
        
        foreach ($clubs as &$club) {
            $club['class_levels'] = json_decode($club['class_levels'], true);
        }
        
        return $clubs;
    }
    
    /**
     * Get available clubs for student
     * 
     * @param int $studentId Student ID
     * @param string $classLevel Student's class level
     * @param string $academicYear Academic year
     * @param int $semester Semester
     * @return array Array of clubs
     */
    public function getAvailableForStudent($studentId, $classLevel, $academicYear, $semester)
    {
        $clubs = $this->getByAcademicYear($academicYear, $semester);
        $availableClubs = [];
        
        // Extract number from class level (e.g., 'ม.1' -> '1')
        $classLevelNumber = preg_replace('/[^0-9]/', '', $classLevel);
        
        foreach ($clubs as $club) {
            // Check if student's class level is accepted
            $accepted = false;
            foreach ($club['class_levels'] as $level) {
                if ($level == $classLevelNumber || $level == $classLevel) {
                    $accepted = true;
                    break;
                }
            }
            
            if (!$accepted) {
                continue;
            }
            
            // Check if club is full
            $enrolled = $this->countEnrolledStudents($club['id']);
            if ($enrolled >= $club['max_students']) {
                continue;
            }
            
            $club['enrolled_count'] = $enrolled;
            $club['available_seats'] = $club['max_students'] - $enrolled;
            
            // Check if student already enrolled
            $club['is_enrolled'] = $this->isStudentEnrolled($studentId, $club['id']);
            
            $availableClubs[] = $club;
        }
        
        return $availableClubs;
    }
    
    /**
     * Enroll student in club
     * 
     * @param int $clubId Club ID
     * @param int $studentId Student ID
     * @return int Enrollment ID
     * @throws \Exception if enrollment fails
     */
    public function enrollStudent($clubId, $studentId)
    {
        // Check if already enrolled
        if ($this->isStudentEnrolled($studentId, $clubId)) {
            throw new \Exception('นักเรียนลงทะเบียนชุมนุมนี้แล้ว');
        }
        
        // Check if club is full
        $club = $this->findById($clubId);
        $enrolled = $this->countEnrolledStudents($clubId);
        
        if ($enrolled >= $club['max_students']) {
            throw new \Exception('ชุมนุมเต็มแล้ว');
        }
        
        error_log("DEBUG enrollStudent: clubId=$clubId, studentId=$studentId");
        
        $result = $this->db->insert('club_enrollments', [
            'club_id' => $clubId,
            'student_id' => $studentId,
            'status' => 'active'
        ]);
        
        error_log("DEBUG enrollStudent result: " . ($result ? "success (ID: $result)" : 'failed'));
        
        return $result;
    }
    
    /**
     * Unenroll student from club
     * 
     * @param int $clubId Club ID
     * @param int $studentId Student ID
     * @return int Number of affected rows
     */
    public function unenrollStudent($clubId, $studentId)
    {
        return $this->db->delete(
            'club_enrollments',
            'club_id = :club_id AND student_id = :student_id',
            [
                ':club_id' => $clubId,
                ':student_id' => $studentId
            ]
        );
    }
    
    /**
     * Check if student is enrolled in club
     * 
     * @param int $studentId Student ID
     * @param int $clubId Club ID
     * @return bool
     */
    public function isStudentEnrolled($studentId, $clubId)
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM club_enrollments 
             WHERE student_id = :student_id AND club_id = :club_id AND status = "active"',
            [
                ':student_id' => $studentId,
                ':club_id' => $clubId
            ]
        );
        
        return $result['count'] > 0;
    }
    
    /**
     * Get enrolled students in club
     * 
     * @param int $clubId Club ID
     * @return array Array of students
     */
    public function getEnrolledStudents($clubId)
    {
        return $this->db->fetchAll(
            'SELECT s.*, ce.grade, ce.enrolled_at, ce.status
             FROM club_enrollments ce
             JOIN students s ON ce.student_id = s.id
             WHERE ce.club_id = :club_id AND ce.status = "active"
             ORDER BY s.class_level, s.classroom, s.student_code',
            [':club_id' => $clubId]
        );
    }
    
    /**
     * Count enrolled students
     * 
     * @param int $clubId Club ID
     * @return int Count
     */
    public function countEnrolledStudents($clubId)
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM club_enrollments 
             WHERE club_id = :club_id AND status = "active"',
            [':club_id' => $clubId]
        );
        
        return (int) $result['count'];
    }
    
    /**
     * Get student's club for semester
     * 
     * @param int $studentId Student ID
     * @param string $academicYear Academic year
     * @param int $semester Semester
     * @return array|null Club data or null
     */
    public function getStudentClub($studentId, $academicYear, $semester)
    {
        error_log("DEBUG getStudentClub: studentId=$studentId, year=$academicYear, semester=$semester");
        
        $club = $this->db->fetchOne(
            'SELECT c.*, u.name as teacher_name, ce.grade, ce.enrolled_at
             FROM club_enrollments ce
             JOIN clubs c ON ce.club_id = c.id
             LEFT JOIN users u ON c.teacher_id = u.id
             WHERE ce.student_id = :student_id 
               AND c.academic_year = :year 
               AND c.semester = :semester',
            [
                ':student_id' => $studentId,
                ':year' => $academicYear,
                ':semester' => $semester
            ]
        );
        
        error_log("DEBUG getStudentClub result: " . ($club ? json_encode($club) : 'NULL'));
        
        if ($club) {
            $club['class_levels'] = json_decode($club['class_levels'], true);
        }
        
        return $club;
    }
    
    /**
     * Update student's grade
     * 
     * @param int $clubId Club ID
     * @param int $studentId Student ID
     * @param float $grade Grade
     * @return int Number of affected rows
     */
    public function updateGrade($clubId, $studentId, $grade)
    {
        return $this->db->update(
            'club_enrollments',
            ['grade' => $grade],
            'club_id = :club_id AND student_id = :student_id',
            [
                ':club_id' => $clubId,
                ':student_id' => $studentId
            ]
        );
    }
    
    /**
     * Check if student can enroll in semester
     * 
     * @param int $studentId Student ID
     * @param string $academicYear Academic year
     * @param int $semester Semester
     * @return bool
     */
    public function canStudentEnroll($studentId, $academicYear, $semester)
    {
        $existing = $this->getStudentClub($studentId, $academicYear, $semester);
        $canEnroll = empty($existing); // Handle both null and false
        
        error_log("DEBUG canStudentEnroll: studentId=$studentId, year=$academicYear, semester=$semester, existing=" . json_encode($existing) . ", canEnroll=" . ($canEnroll ? 'YES' : 'NO'));
        
        return $canEnroll;
    }
    
    /**
     * Get enrollment summary for all students
     * 
     * @param string $academicYear Academic year
     * @param int $semester Semester
     * @param string $classLevel Optional class level filter
     * @param string $classroom Optional classroom filter
     * @return array Array of students with enrollment status
     */
    public function getEnrollmentSummary($academicYear, $semester, $classLevel = '', $classroom = '')
    {
        $sql = "SELECT s.id, s.student_code, s.name, s.class_level, s.classroom,
                       c.club_name, u.name as teacher_name
                FROM students s
                LEFT JOIN club_enrollments ce ON s.id = ce.student_id AND ce.status = 'active'
                LEFT JOIN clubs c ON ce.club_id = c.id 
                    AND c.academic_year = :academic_year 
                    AND c.semester = :semester
                LEFT JOIN users u ON c.teacher_id = u.id
                WHERE 1=1";
        
        $params = [
            ':academic_year' => $academicYear,
            ':semester' => $semester
        ];
        
        if ($classLevel !== '') {
            $sql .= " AND s.class_level LIKE :class_level";
            $params[':class_level'] = '%' . $classLevel . '%';
        }
        
        if ($classroom !== '') {
            $sql .= " AND s.classroom = :classroom";
            $params[':classroom'] = $classroom;
        }
        
        $sql .= " ORDER BY s.class_level, s.classroom, s.student_code";
        
        return $this->db->fetchAll($sql, $params);
    }
}
