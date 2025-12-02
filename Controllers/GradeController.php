<?php

namespace App\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeCategory;

/**
 * Grade Controller
 * 
 * Handles grade entry and viewing.
 */
class GradeController extends BaseController
{
    private $gradeModel;
    private $courseModel;
    private $gradeCategoryModel;
    
    public function __construct()
    {
        $this->gradeModel = new Grade();
        $this->courseModel = new Course();
        $this->gradeCategoryModel = new GradeCategory();
    }
    
    /**
     * Show grade entry page for a course
     */
    public function record($courseId)
    {
        $this->requireTeacher();
        
        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            $this->setFlash('error', 'ไม่พบรายวิชา');
            $this->redirect('/teacher/courses');
            return;
        }
        
        $categories = $this->gradeCategoryModel->findByCourse($courseId);
        $students = $this->courseModel->getEnrolledStudents($courseId);
        $gradesData = $this->gradeModel->getCourseGrades($courseId);
        
        // Restructure grades array for easier access
        $grades = [];
        foreach ($gradesData as $grade) {
            $key = $grade['student_id'] . '_' . $grade['category_id'];
            $grades[$key] = $grade;
        }
        
        $this->render('teacher/grades/record', [
            'course' => $course,
            'categories' => $categories,
            'students' => $students,
            'grades' => $grades
        ]);
    }
    
    /**
     * Save grades
     */
    public function save()
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $studentId = $this->post('student_id');
            $courseId = $this->post('course_id');
            $categoryId = $this->post('category_id');
            $score = $this->post('score');
            
            // Allow null score (not graded yet)
            if ($score === '' || $score === null) {
                $score = null;
            } else {
                $score = floatval($score);
            }
            
            $this->gradeModel->save($studentId, $courseId, $categoryId, $score);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'บันทึกคะแนนสำเร็จ'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Show grade summary for a course (teacher view)
     */
    public function summary($courseId)
    {
        $this->requireTeacher();
        
        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            $this->setFlash('error', 'ไม่พบรายวิชา');
            $this->redirect('/teacher/courses');
            return;
        }
        
        $summary = $this->gradeModel->getCourseSummary($courseId);
        $categories = $this->gradeCategoryModel->findByCourse($courseId);
        
        $this->render('teacher/grades/summary', [
            'course' => $course,
            'summary' => $summary,
            'categories' => $categories
        ]);
    }
    
    /**
     * Show student's own grades
     */
    public function studentView()
    {
        $this->requireStudent();
        
        $studentId = $this->getStudentId();
        $courses = $this->courseModel->getStudentCourses($studentId);
        
        $gradesData = [];
        foreach ($courses as $course) {
            $grades = $this->gradeModel->getStudentGrades($studentId, $course['id']);
            $total = $this->gradeModel->calculateTotal($studentId, $course['id']);
            
            $gradesData[] = [
                'course' => $course,
                'grades' => $grades,
                'total' => $total
            ];
        }
        
        $this->render('student/grades/view', ['gradesData' => $gradesData]);
    }
}
