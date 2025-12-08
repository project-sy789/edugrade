<?php

namespace App\Controllers;

use App\Models\Course;
use App\Models\GradeCategory;
use App\Models\Student;

/**
 * Course Controller
 * 
 * Handles course management, grade categories, and student enrollment.
 */
class CourseController extends BaseController
{
    private $courseModel;
    private $gradeCategoryModel;
    private $studentModel;
    
    public function __construct()
    {
        $this->courseModel = new Course();
        $this->gradeCategoryModel = new GradeCategory();
        $this->studentModel = new Student();
    }
    
    /**
     * List all courses
     */
    public function index()
    {
        $this->requireTeacher();
        
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Check if user is admin or teacher
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($isAdmin) {
            // Admin sees all courses
            $courses = $this->courseModel->getAll($perPage, $offset);
            $totalCourses = $this->courseModel->count();
        } else {
            // Teacher sees only their own courses
            $courses = $this->courseModel->getByTeacher($userId, $perPage, $offset);
            // Count only teacher's courses
            $totalCourses = count($this->courseModel->getByTeacher($userId));
        }
        
        $totalPages = ceil($totalCourses / $perPage);
        
        $this->render('teacher/courses/index', [
            'courses' => $courses,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCourses' => $totalCourses
        ]);
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        $this->requireTeacher();
        $this->render('teacher/courses/form', ['course' => null]);
    }
    
    /**
     * Store new course
     */
    public function store()
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/courses');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $data = [
                'course_code' => $this->post('course_code'),
                'course_name' => $this->post('course_name'),
                'academic_year' => $this->post('academic_year'),
                'semester' => $this->post('semester')
            ];
            
            $this->courseModel->create($data);
            $this->setFlash('success', 'เพิ่มรายวิชาสำเร็จ');
            $this->redirect('/teacher/courses');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/teacher/courses/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        $this->requireTeacher();
        
        $course = $this->courseModel->findById($id);
        
        if (!$course) {
            $this->setFlash('error', 'ไม่พบรายวิชา');
            $this->redirect('/teacher/courses');
            return;
        }
        
        $this->render('teacher/courses/form', ['course' => $course]);
    }
    
    /**
     * Update course
     */
    public function update($id)
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/courses');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $data = [
                'course_code' => $this->post('course_code'),
                'course_name' => $this->post('course_name'),
                'academic_year' => $this->post('academic_year'),
                'semester' => $this->post('semester')
            ];
            
            $this->courseModel->update($id, $data);
            $this->setFlash('success', 'แก้ไขรายวิชาสำเร็จ');
            $this->redirect('/teacher/courses');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/teacher/courses/edit/' . $id);
        }
    }
    
    /**
     * Delete course
     */
    public function delete($id)
    {
        $this->requireTeacher();
        $this->requireCsrfToken();
        
        try {
            $this->courseModel->delete($id);
            $this->jsonResponse(['success' => true, 'message' => 'ลบรายวิชาสำเร็จ']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Manage grade categories for a course
     */
    public function manageGradeCategories($courseId)
    {
        $this->requireTeacher();
        
        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            $this->setFlash('error', 'ไม่พบรายวิชา');
            $this->redirect('/teacher/courses');
            return;
        }
        
        $categories = $this->gradeCategoryModel->findByCourse($courseId);
        
        $this->render('teacher/courses/grade_categories', [
            'course' => $course,
            'categories' => $categories
        ]);
    }
    
    /**
     * Add or update grade category
     */
    public function addGradeCategory($courseId)
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/courses/' . $courseId . '/categories');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $data = [
                'category_name' => $this->post('category_name'),
                'max_score' => $this->post('max_score'),
                'weight' => $this->post('weight', 0)
            ];
            
            $categoryId = $this->post('category_id');
            
            if ($categoryId) {
                // Update existing category
                $this->gradeCategoryModel->update($categoryId, $data);
                $this->setFlash('success', 'แก้ไขหมวดคะแนนสำเร็จ');
            } else {
                // Create new category
                $this->gradeCategoryModel->create($courseId, $data);
                $this->setFlash('success', 'เพิ่มหมวดคะแนนสำเร็จ');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/teacher/courses/' . $courseId . '/categories');
    }
    
    /**
     * Delete grade category
     */
    public function deleteGradeCategory($courseId, $categoryId)
    {
        $this->requireTeacher();
        $this->requireCsrfToken();
        
        try {
            $this->gradeCategoryModel->delete($categoryId);
            $this->setFlash('success', 'ลบหมวดคะแนนสำเร็จ');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/teacher/courses/' . $courseId . '/categories');
    }
    
    /**
     * Enroll students page
     */
    public function enrollStudentsPage($courseId)
    {
        $this->requireTeacher();
        
        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            $this->setFlash('error', 'ไม่พบรายวิชา');
            $this->redirect('/teacher/courses');
            return;
        }
        
        $enrolledStudents = $this->courseModel->getEnrolledStudents($courseId);
        $allStudents = $this->studentModel->getAll();
        
        $this->render('teacher/courses/enroll', [
            'course' => $course,
            'enrolledStudents' => $enrolledStudents,
            'allStudents' => $allStudents
        ]);
    }
    
    /**
     * Enroll students
     */
    public function enrollStudents($courseId)
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/courses/' . $courseId . '/enroll');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $studentIds = $this->post('student_ids', []);
            
            if (empty($studentIds)) {
                throw new \Exception('กรุณาเลือกนักเรียนอย่างน้อย 1 คน');
            }
            
            $result = $this->courseModel->enrollStudents($courseId, $studentIds);
            $this->setFlash('success', "ลงทะเบียนสำเร็จ {$result['success']} คน");
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/teacher/courses/' . $courseId . '/enroll');
    }
    
    /**
     * Remove student from course
     */
    public function removeEnrollment($courseId)
    {
        $this->requireTeacher();
        $this->requireCsrfToken();
        
        try {
            $enrollmentIdRaw = $this->post('enrollment_id');
            $enrollmentId = (int)$enrollmentIdRaw;
            
            if ($enrollmentId <= 0) {
                throw new \Exception('ไม่พบข้อมูลการลงทะเบียน');
            }
            
            // Delete enrollment using exec to avoid parameter binding issues
            $db = \App\Models\Database::getInstance();
            $conn = $db->getConnection();
            
            // Use exec with integer (safe from SQL injection since we cast to int)
            $sql = "DELETE FROM course_enrollments WHERE id = " . $enrollmentId;
            $rowCount = $conn->exec($sql);
            
            if ($rowCount === 0) {
                throw new \Exception('ไม่พบข้อมูลการลงทะเบียนที่ต้องการลบ');
            }
            
            $this->jsonResponse(['success' => true, 'message' => 'ลบนักเรียนออกจากรายวิชาสำเร็จ']);
        } catch (\PDOException $e) {
            error_log("PDO Error in removeEnrollment: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()], 400);
        } catch (\Exception $e) {
            error_log("Error in removeEnrollment: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
