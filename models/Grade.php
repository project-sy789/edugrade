<?php

namespace App\Models;

/**
 * Grade Model
 * 
 * Handles student grade management.
 */
class Grade
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Save or update a grade
     * 
     * @param int $studentId Student ID
     * @param int $courseId Course ID
     * @param int $categoryId Category ID
     * @param float|null $score Score (null = not graded yet)
     * @return int Grade ID
     * @throws \Exception if validation fails
     */
    public function save($studentId, $courseId, $categoryId, $score)
    {
        // Validate score against max_score
        if ($score !== null) {
            $category = $this->db->fetchOne(
                'SELECT max_score FROM grade_categories WHERE id = :id',
                [':id' => $categoryId]
            );
            
            if (!$category) {
                throw new \Exception('ไม่พบหมวดคะแนนที่ระบุ');
            }
            
            if ($score < 0) {
                throw new \Exception('คะแนนต้องไม่ติดลบ');
            }
            
            if ($score > $category['max_score']) {
                throw new \Exception("คะแนนต้องไม่เกิน {$category['max_score']} คะแนน");
            }
        }
        
        // Check if grade already exists
        $existing = $this->db->fetchOne(
            'SELECT id FROM grades 
             WHERE student_id = :student_id AND category_id = :category_id',
            [':student_id' => $studentId, ':category_id' => $categoryId]
        );
        
        if ($existing) {
            // Update existing grade
            $this->db->update(
                'grades',
                ['score' => $score],
                'id = :id',
                [':id' => $existing['id']]
            );
            return $existing['id'];
        } else {
            // Insert new grade
            return $this->db->insert('grades', [
                'student_id' => $studentId,
                'course_id' => $courseId,
                'category_id' => $categoryId,
                'score' => $score
            ]);
        }
    }
    
    /**
     * Update a grade by ID
     * 
     * @param int $id Grade ID
     * @param float|null $score New score
     * @return int Number of affected rows
     */
    public function update($id, $score)
    {
        // Get grade info to validate
        $grade = $this->db->fetchOne('SELECT category_id FROM grades WHERE id = :id', [':id' => $id]);
        
        if (!$grade) {
            throw new \Exception('ไม่พบคะแนนที่ระบุ');
        }
        
        // Validate score
        if ($score !== null) {
            $category = $this->db->fetchOne(
                'SELECT max_score FROM grade_categories WHERE id = :id',
                [':id' => $grade['category_id']]
            );
            
            if ($score < 0 || $score > $category['max_score']) {
                throw new \Exception("คะแนนต้องอยู่ระหว่าง 0 ถึง {$category['max_score']}");
            }
        }
        
        return $this->db->update(
            'grades',
            ['score' => $score],
            'id = :id',
            [':id' => $id]
        );
    }
    
    /**
     * Get student grades for a course
     * 
     * @param int $studentId Student ID
     * @param int $courseId Course ID
     * @return array Array of grades with category info
     */
    public function getStudentGrades($studentId, $courseId)
    {
        return $this->db->fetchAll(
            'SELECT g.*, gc.category_name, gc.max_score, gc.weight, gc.display_order
             FROM grades g
             INNER JOIN grade_categories gc ON g.category_id = gc.id
             WHERE g.student_id = :student_id AND g.course_id = :course_id
             ORDER BY gc.display_order, gc.id',
            [':student_id' => $studentId, ':course_id' => $courseId]
        );
    }
    
    /**
     * Get all grades for a course
     * 
     * @param int $courseId Course ID
     * @return array Array of grades grouped by student
     */
    public function getCourseGrades($courseId)
    {
        return $this->db->fetchAll(
            'SELECT g.*, s.student_code, s.name, gc.category_name, gc.max_score, gc.display_order
             FROM grades g
             INNER JOIN students s ON g.student_id = s.id
             INNER JOIN grade_categories gc ON g.category_id = gc.id
             WHERE g.course_id = :course_id
             ORDER BY s.class_level, s.classroom, s.student_code, gc.display_order',
            [':course_id' => $courseId]
        );
    }
    
    /**
     * Get grades for a specific category
     * 
     * @param int $categoryId Category ID
     * @return array Array of grades
     */
    public function getCategoryGrades($categoryId)
    {
        return $this->db->fetchAll(
            'SELECT g.*, s.student_code, s.name
             FROM grades g
             INNER JOIN students s ON g.student_id = s.id
             WHERE g.category_id = :category_id
             ORDER BY s.class_level, s.classroom, s.student_code',
            [':category_id' => $categoryId]
        );
    }
    
    /**
     * Calculate total grade for a student in a course
     * 
     * @param int $studentId Student ID
     * @param int $courseId Course ID
     * @return array Total score and breakdown
     */
    public function calculateTotal($studentId, $courseId)
    {
        $grades = $this->getStudentGrades($studentId, $courseId);
        
        $total = 0;
        $maxTotal = 0;
        $weightedTotal = 0;
        $totalWeight = 0;
        $breakdown = [];
        
        foreach ($grades as $grade) {
            $score = $grade['score'] ?? 0;
            $maxScore = $grade['max_score'];
            $weight = $grade['weight'];
            
            $total += $score;
            $maxTotal += $maxScore;
            
            // Calculate weighted score
            if ($weight > 0 && $maxScore > 0) {
                $percentage = ($score / $maxScore) * 100;
                $weightedScore = ($percentage * $weight) / 100;
                $weightedTotal += $weightedScore;
                $totalWeight += $weight;
            }
            
            $breakdown[] = [
                'category_name' => $grade['category_name'],
                'score' => $score,
                'max_score' => $maxScore,
                'weight' => $weight
            ];
        }
        
        return [
            'total' => $total,
            'total_score' => $weightedTotal, // Weighted total score
            'max_total' => $maxTotal,
            'total_weight' => $totalWeight,
            'percentage' => $maxTotal > 0 ? ($total / $maxTotal) * 100 : 0,
            'breakdown' => $breakdown
        ];
    }
    
    /**
     * Get grade summary for all students in a course
     * 
     * @param int $courseId Course ID
     * @return array Array of student totals
     */
    public function getCourseSummary($courseId)
    {
        // Get all enrolled students
        $courseModel = new Course();
        $students = $courseModel->getEnrolledStudents($courseId);
        
        $summary = [];
        foreach ($students as $student) {
            $total = $this->calculateTotal($student['id'], $courseId);
            $summary[] = array_merge([
                'student_id' => $student['id'],
                'student_code' => $student['student_code'],
                'name' => $student['name'],
                'class_level' => $student['class_level'],
                'classroom' => $student['classroom']
            ], $total);
        }
        
        return $summary;
    }
    
    /**
     * Delete all grades for a category
     * 
     * @param int $categoryId Category ID
     * @return int Number of affected rows
     */
    public function deleteByCategoryId($categoryId)
    {
        return $this->db->delete('grades', 'category_id = :category_id', [':category_id' => $categoryId]);
    }
}
