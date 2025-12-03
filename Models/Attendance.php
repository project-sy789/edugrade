<?php

namespace App\Models;

/**
 * Attendance Model
 * 
 * Handles student attendance management.
 */
class Attendance
{
    private $db;
    
    // Attendance status constants
    const STATUS_PRESENT = 'present';  // มา
    const STATUS_ABSENT = 'absent';    // ขาด
    const STATUS_SICK = 'sick';        // ป่วย
    const STATUS_LEAVE = 'leave';      // ลา
    const STATUS_LATE = 'late';        // สาย
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get database instance
     * 
     * @return Database
     */
    public function getDb()
    {
        return $this->db;
    }
    
    /**
     * Record attendance
     * 
     * @param int $studentId Student ID
     * @param int $courseId Course ID
     * @param string $date Date (YYYY-MM-DD)
     * @param string $status Attendance status
     * @param int $period Period number (1-8, default 1)
     * @return int Attendance ID
     * @throws \Exception if validation fails
     */
    public function record($studentId, $courseId, $date, $status, $period = 1)
    {
        $this->validateStatus($status);
        
        // Cast to safe types
        $studentId = (int)$studentId;
        $courseId = (int)$courseId;
        $period = (int)$period;
        $date = $this->db->getConnection()->quote($date);
        $status = $this->db->getConnection()->quote($status);
        
        // Check if attendance already exists for this date and period
        $checkSql = "SELECT id FROM attendance 
                     WHERE student_id = $studentId 
                       AND course_id = $courseId 
                       AND date = $date
                       AND period = $period";
        
        $stmt = $this->db->getConnection()->query($checkSql);
        $existing = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
        
        if ($existing) {
            // Update existing attendance
            $updateSql = "UPDATE attendance 
                         SET status = $status 
                         WHERE id = " . (int)$existing['id'];
            $this->db->getConnection()->exec($updateSql);
            return $existing['id'];
        } else {
            // Insert new attendance
            $insertSql = "INSERT INTO attendance 
                         (student_id, course_id, date, period, status) 
                         VALUES ($studentId, $courseId, $date, $period, $status)";
            $this->db->getConnection()->exec($insertSql);
            return $this->db->getConnection()->lastInsertId();
        }
    }
    
    /**
     * Update attendance status
     * 
     * @param int $id Attendance ID
     * @param string $status New status
     * @return int Number of affected rows
     */
    public function update($id, $status)
    {
        $this->validateStatus($status);
        
        return $this->db->update(
            'attendance',
            ['status' => $status],
            'id = :id',
            [':id' => $id]
        );
    }
    
    /**
     * Get student attendance for a course
     * 
     * @param int $studentId Student ID
     * @param int $courseId Course ID
     * @return array Array of attendance records
     */
    public function getStudentAttendance($studentId, $courseId)
    {
        return $this->db->fetchAll(
            'SELECT * FROM attendance 
             WHERE student_id = :student_id AND course_id = :course_id 
             ORDER BY date DESC',
            [':student_id' => $studentId, ':course_id' => $courseId]
        );
    }
    
    /**
     * Get student attendance in a date range
     * 
     * @param int $studentId Student ID
     * @param int $courseId Course ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Array of attendance records
     */
    public function getStudentAttendanceInRange($studentId, $courseId, $startDate, $endDate)
    {
        // Use direct PDO to avoid parameter binding issues
        $studentId = (int)$studentId;
        $courseId = (int)$courseId;
        $startDate = $this->db->getConnection()->quote($startDate);
        $endDate = $this->db->getConnection()->quote($endDate);
        
        $sql = "SELECT * FROM attendance 
                WHERE student_id = $studentId 
                  AND course_id = $courseId 
                  AND date BETWEEN $startDate AND $endDate
                ORDER BY date DESC, period";
        
        $stmt = $this->db->getConnection()->query($sql);
        return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
    }
    
    /**
     * Get all attendance for a course
     * 
     * @param int $courseId Course ID
     * @param string|null $date Specific date (optional)
     * @param int|null $period Period number (default 1, null for all periods)
     * @return array Array of attendance records
     */
    public function getCourseAttendance($courseId, $date = null, $period = 1)
    {
        if ($date !== null) {
            if ($period === null) {
                // Get all periods for this date
                return $this->db->fetchAll(
                    'SELECT a.*, s.student_code, s.name, s.class_level, s.classroom
                     FROM attendance a
                     INNER JOIN students s ON a.student_id = s.id
                     WHERE a.course_id = :course_id AND a.date = :date
                     ORDER BY a.period, s.class_level, s.classroom, s.student_code',
                    [':course_id' => $courseId, ':date' => $date]
                );
            } else {
                // Get specific period
                return $this->db->fetchAll(
                    'SELECT a.*, s.student_code, s.name, s.class_level, s.classroom
                     FROM attendance a
                     INNER JOIN students s ON a.student_id = s.id
                     WHERE a.course_id = :course_id AND a.date = :date AND a.period = :period
                     ORDER BY s.class_level, s.classroom, s.student_code',
                    [':course_id' => $courseId, ':date' => $date, ':period' => $period]
                );
            }
        }
        
        return $this->db->fetchAll(
            'SELECT a.*, s.student_code, s.name, s.class_level, s.classroom
             FROM attendance a
             INNER JOIN students s ON a.student_id = s.id
             WHERE a.course_id = :course_id
             ORDER BY a.date DESC, a.period, s.class_level, s.classroom, s.student_code',
            [':course_id' => $courseId]
        );
    }
    
    /**
     * Calculate attendance statistics for a student in a course
     * 
     * @param int $studentId Student ID
     * @param int $courseId Course ID
     * @return array Statistics (present, absent, sick, leave, late counts and percentage)
     */
    public function calculateStatistics($studentId, $courseId)
    {
        $records = $this->getStudentAttendance($studentId, $courseId);
        
        $stats = [
            'present' => 0,
            'absent' => 0,
            'sick' => 0,
            'leave' => 0,
            'late' => 0,
            'total' => count($records)
        ];
        
        foreach ($records as $record) {
            $stats[$record['status']]++;
        }
        
        // Calculate attendance percentage (present + late / total)
        $stats['percentage'] = $stats['total'] > 0 
            ? (($stats['present'] + $stats['late']) / $stats['total']) * 100 
            : 0;
        
        return $stats;
    }
    
    /**
     * Get attendance summary for all students in a course
     * 
     * @param int $courseId Course ID
     * @return array Array of student statistics
     */
    public function getCourseSummary($courseId)
    {
        // Get all enrolled students
        $courseModel = new Course();
        $students = $courseModel->getEnrolledStudents($courseId);
        
        $summary = [];
        foreach ($students as $student) {
            $stats = $this->calculateStatistics($student['id'], $courseId);
            $summary[] = array_merge([
                'student_id' => $student['id'],
                'student_code' => $student['student_code'],
                'name' => $student['name'],
                'class_level' => $student['class_level'],
                'classroom' => $student['classroom']
            ], $stats);
        }
        
        return $summary;
    }
    
    /**
     * Get attendance dates for a course
     * 
     * @param int $courseId Course ID
     * @return array Array of unique dates
     */
    public function getCourseDates($courseId)
    {
        return $this->db->fetchAll(
            'SELECT DISTINCT date FROM attendance 
             WHERE course_id = :course_id 
             ORDER BY date DESC',
            [':course_id' => $courseId]
        );
    }
    
    /**
     * Delete attendance record
     * 
     * @param int $id Attendance ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        return $this->db->delete('attendance', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Validate attendance status
     * 
     * @param string $status Status to validate
     * @throws \Exception if status is invalid
     */
    private function validateStatus($status)
    {
        $validStatuses = [
            self::STATUS_PRESENT,
            self::STATUS_ABSENT,
            self::STATUS_SICK,
            self::STATUS_LEAVE,
            self::STATUS_LATE
        ];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('สถานะการเข้าเรียนไม่ถูกต้อง');
        }
    }
    
    /**
     * Get status label in Thai
     * 
     * @param string $status Status code
     * @return string Thai label
     */
    public static function getStatusLabel($status)
    {
        $labels = [
            self::STATUS_PRESENT => 'มา',
            self::STATUS_ABSENT => 'ขาด',
            self::STATUS_SICK => 'ป่วย',
            self::STATUS_LEAVE => 'ลา',
            self::STATUS_LATE => 'สาย'
        ];
        
        return $labels[$status] ?? $status;
    }
}
