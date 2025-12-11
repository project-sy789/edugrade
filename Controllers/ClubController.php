<?php

namespace App\Controllers;

use App\Models\Club;
use App\Models\User;
use App\Models\Student;
use App\Models\Settings;

/**
 * Club Controller
 * 
 * Handles club management for admin and student enrollment
 */
class ClubController extends BaseController
{
    private $clubModel;
    private $userModel;
    private $studentModel;
    
    public function __construct()
    {
        $this->clubModel = new Club();
        $this->userModel = new User();
        $this->studentModel = new Student();
        $this->settings = new Settings();
    }
    
    // ==================== ADMIN FUNCTIONS ====================
    
    /**
     * List all clubs (admin/teacher)
     */
    public function index()
    {
        $this->requireTeacher();
        
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $clubs = $this->clubModel->getAll($perPage, $offset);
        $totalClubs = $this->clubModel->count();
        $totalPages = ceil($totalClubs / $perPage);
        
        // Add enrolled count for each club
        foreach ($clubs as &$club) {
            $club['enrolled_count'] = $this->clubModel->countEnrolledStudents($club['id']);
        }
        
        $this->render('teacher/clubs/index', [
            'clubs' => $clubs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalClubs' => $totalClubs
        ]);
    }
    
    /**
     * Show create club form (admin only)
     */
    public function create()
    {
        $this->requireAdmin();
        
        $teachers = $this->userModel->getAll();
        
        $this->render('teacher/clubs/form', [
            'club' => null,
            'teachers' => $teachers,
            'action' => 'create'
        ]);
    }
    
    /**
     * Store new club (admin only)
     */
    public function store()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/clubs');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $clubName = $this->post('club_name');
            $description = $this->post('description');
            $teacherId = $this->post('teacher_id');
            $academicYear = $this->post('academic_year');
            $semester = $this->post('semester');
            $classLevels = $this->post('class_levels', []);
            $maxStudents = $this->post('max_students', 30);
            
            if (empty($clubName) || empty($teacherId) || empty($academicYear) || empty($semester)) {
                $this->setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
                $this->redirect('/teacher/clubs/create');
                return;
            }
            
            if (empty($classLevels)) {
                $this->setFlash('error', 'กรุณาเลือกชั้นที่รับอย่างน้อย 1 ชั้น');
                $this->redirect('/teacher/clubs/create');
                return;
            }
            
            $clubId = $this->clubModel->create([
                'club_name' => $clubName,
                'description' => $description,
                'teacher_id' => $teacherId,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'class_levels' => $classLevels,
                'max_students' => $maxStudents
            ]);
            
            $this->setFlash('success', 'สร้างชุมนุมสำเร็จ');
            $this->redirect('/teacher/clubs');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->redirect('/teacher/clubs/create');
        }
    }
    
    /**
     * Show edit club form (admin only)
     */
    public function edit($id)
    {
        $this->requireAdmin();
        
        $club = $this->clubModel->findById($id);
        
        if (!$club) {
            $this->setFlash('error', 'ไม่พบชุมนุม');
            $this->redirect('/teacher/clubs');
            return;
        }
        
        $teachers = $this->userModel->getAll();
        
        $this->render('teacher/clubs/form', [
            'club' => $club,
            'teachers' => $teachers,
            'action' => 'edit'
        ]);
    }
    
    /**
     * Update club (admin only)
     */
    public function update($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/clubs');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $club = $this->clubModel->findById($id);
            
            if (!$club) {
                $this->setFlash('error', 'ไม่พบชุมนุม');
                $this->redirect('/teacher/clubs');
                return;
            }
            
            $clubName = $this->post('club_name');
            $description = $this->post('description');
            $teacherId = $this->post('teacher_id');
            $academicYear = $this->post('academic_year');
            $semester = $this->post('semester');
            $classLevels = $this->post('class_levels', []);
            $maxStudents = $this->post('max_students', 30);
            
            if (empty($clubName) || empty($teacherId) || empty($academicYear) || empty($semester)) {
                $this->setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
                $this->redirect('/teacher/clubs/' . $id . '/edit');
                return;
            }
            
            if (empty($classLevels)) {
                $this->setFlash('error', 'กรุณาเลือกชั้นที่รับอย่างน้อย 1 ชั้น');
                $this->redirect('/teacher/clubs/' . $id . '/edit');
                return;
            }
            
            $this->clubModel->update($id, [
                'club_name' => $clubName,
                'description' => $description,
                'teacher_id' => $teacherId,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'class_levels' => $classLevels,
                'max_students' => $maxStudents
            ]);
            
            $this->setFlash('success', 'อัพเดทชุมนุมสำเร็จ');
            $this->redirect('/teacher/clubs');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->redirect('/teacher/clubs/' . $id . '/edit');
        }
    }
    
    /**
     * Delete club (admin only)
     */
    public function delete($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/clubs');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $club = $this->clubModel->findById($id);
            
            if (!$club) {
                echo json_encode(['success' => false, 'message' => 'ไม่พบชุมนุม']);
                return;
            }
            
            $this->clubModel->delete($id);
            
            echo json_encode(['success' => true, 'message' => 'ลบชุมนุมสำเร็จ']);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show club members
     */
    public function members($id)
    {
        $this->requireTeacher();
        
        $club = $this->clubModel->findById($id);
        
        if (!$club) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ไม่พบชุมนุม'];
            $this->redirect('/teacher/clubs');
            return;
        }
        
        $students = $this->clubModel->getEnrolledStudents($id);
        
        $this->render('teacher/clubs/members', [
            'club' => $club,
            'students' => $students
        ]);
    }
    
    /**
     * Remove student from club (Admin only)
     */
    public function removeStudent($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/clubs/' . $id . '/members');
            return;
        }
        
        $this->requireCsrfToken();
        
        $studentId = $this->post('student_id');
        
        if (!$studentId) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'];
            $this->redirect('/teacher/clubs/' . $id . '/members');
            return;
        }
        
        try {
            $this->clubModel->unenrollStudent($id, $studentId);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'ลบนักเรียนออกจากชุมนุมสำเร็จ'];
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
        
        $this->redirect('/teacher/clubs/' . $id . '/members');
    }
    
    /**
     * Show grade form
     */
    public function gradeForm($id)
    {
        $this->requireTeacher();
        
        $club = $this->clubModel->findById($id);
        
        if (!$club) {
            $this->setFlash('error', 'ไม่พบชุมนุม');
            $this->redirect('/teacher/clubs');
            return;
        }
        
        $students = $this->clubModel->getEnrolledStudents($id);
        
        $this->render('teacher/clubs/grades', [
            'club' => $club,
            'students' => $students
        ]);
    }
    
    /**
     * Store grades
     */
    public function gradeStore($id)
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/clubs/' . $id . '/members');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $grades = $this->post('grades', []);
            
            foreach ($grades as $studentId => $grade) {
                if ($grade !== '' && $grade !== null) {
                    $this->clubModel->updateGrade($id, $studentId, floatval($grade));
                }
            }
            
            $this->setFlash('success', 'บันทึกคะแนนสำเร็จ');
            $this->redirect('/teacher/clubs/' . $id . '/members');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->redirect('/teacher/clubs/' . $id . '/grades');
        }
    }
    
    // ==================== STUDENT FUNCTIONS ====================
    
    /**
     * Show available clubs for student
     */
    public function studentIndex()
    {
        $this->requireStudent();
        
        $studentId = $this->getStudentId();
        $student = $this->studentModel->findById($studentId);
        
        // Get current academic year and semester (Thai academic calendar)
        // Semester 1: May-October (months 5-10)
        // Semester 2: November-April (months 11-4)
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        if ($currentMonth >= 5 && $currentMonth <= 10) {
            // Semester 1
            $academicYear = $currentYear + 543;
            $semester = 1;
        } else {
            // Semester 2
            if ($currentMonth >= 11) {
                $academicYear = $currentYear + 543;
            } else {
                $academicYear = $currentYear + 543 - 1;
            }
            $semester = 2;
        }
        
        // Allow override via query params
        $academicYear = $this->get('year', $academicYear);
        $semester = $this->get('semester', $semester);
        
        // DEBUG
        error_log("DEBUG: Student ID: $studentId");
        error_log("DEBUG: Student class_level: " . ($student['class_level'] ?? 'NULL'));
        error_log("DEBUG: Academic Year: $academicYear, Semester: $semester");
        
        $clubs = $this->clubModel->getAvailableForStudent(
            $studentId,
            $student['class_level'],
            $academicYear,
            $semester
        );
        
        error_log("DEBUG: Clubs found: " . count($clubs));
        
        // Get student's current club
        $myClub = $this->clubModel->getStudentClub($studentId, $academicYear, $semester);
        
        // Get registration status
        $registrationStatus = $this->settings->getClubRegistrationStatus();
        
        $this->render('student/clubs/index', [
            'clubs' => $clubs,
            'myClub' => $myClub,
            'academicYear' => $academicYear,
            'semester' => $semester,
            'registrationStatus' => $registrationStatus
        ]);
    }
    
    /**
     * Enroll in club
     */
    public function studentEnroll($id)
    {
        $this->requireStudent();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student/clubs');
            return;
        }
        
        // Check if registration is open
        if (!$this->settings->isClubRegistrationOpen()) {
            $status = $this->settings->getClubRegistrationStatus();
            echo json_encode(['success' => false, 'message' => $status['message']]);
            return;
        }
        
        // Note: CSRF token validation removed for AJAX requests
        // Consider implementing proper CSRF handling for AJAX in the future
        
        try {
            $studentId = $this->getStudentId();
            $student = $this->studentModel->findById($studentId);
            $club = $this->clubModel->findById($id);
            
            if (!$club) {
                echo json_encode(['success' => false, 'message' => 'ไม่พบชุมนุม']);
                return;
            }
            
            // Check if student already enrolled in this semester
            if (!$this->clubModel->canStudentEnroll($studentId, $club['academic_year'], $club['semester'])) {
                echo json_encode(['success' => false, 'message' => 'คุณลงทะเบียนชุมนุมในเทอมนี้แล้ว']);
                return;
            }
            
            // Check if student's class level is accepted
            // Extract number from class level (e.g., 'ม.1' -> '1')
            $classLevelNumber = preg_replace('/[^0-9]/', '', $student['class_level']);
            $accepted = false;
            foreach ($club['class_levels'] as $level) {
                if ($level == $classLevelNumber || $level == $student['class_level']) {
                    $accepted = true;
                    break;
                }
            }
            
            if (!$accepted) {
                echo json_encode(['success' => false, 'message' => 'ชุมนุมนี้ไม่รับนักเรียนชั้น ' . $student['class_level']]);
                return;
            }
            
            $this->clubModel->enrollStudent($id, $studentId);
            
            echo json_encode(['success' => true, 'message' => 'ลงทะเบียนชุมนุมสำเร็จ']);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Unenroll from club
     */
    public function studentUnenroll()
    {
        $this->requireStudent();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student/clubs');
            return;
        }
        
        // Note: CSRF token validation removed for AJAX requests
        
        try {
            $clubId = $this->post('club_id');
            $studentId = $this->getStudentId();
            
            $this->clubModel->unenrollStudent($clubId, $studentId);
            
            echo json_encode(['success' => true, 'message' => 'ถอนการลงทะเบียนสำเร็จ']);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show student's club
     */
    public function studentMyClub()
    {
        $this->requireStudent();
        
        $studentId = $this->getStudentId();
        
        // Get current academic year and semester
        $academicYear = $this->get('year', date('Y') + 543);
        $semester = $this->get('semester', (int)date('n') <= 4 ? 2 : 1);
        
        $club = $this->clubModel->getStudentClub($studentId, $academicYear, $semester);
        
        $this->render('student/clubs/my_club', [
            'club' => $club,
            'academicYear' => $academicYear,
            'semester' => $semester
        ]);
    }
    
    /**
     * Show club enrollment summary
     */
    public function summary()
    {
        $this->requireTeacher();
        
        // Get current academic year and semester
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        if ($currentMonth >= 5 && $currentMonth <= 10) {
            $academicYear = $currentYear + 543;
            $semester = 1;
        } else {
            if ($currentMonth >= 11) {
                $academicYear = $currentYear + 543;
            } else {
                $academicYear = $currentYear + 543 - 1;
            }
            $semester = 2;
        }
        
        // Get filter parameters
        $selectedClass = $this->get('class_level', '');
        $selectedClassroom = $this->get('classroom', '');
        
        // Get all students with their club enrollment status
        $students = $this->clubModel->getEnrollmentSummary($academicYear, $semester, $selectedClass, $selectedClassroom);
        
        // Calculate statistics
        $total = count($students);
        $enrolled = 0;
        foreach ($students as $student) {
            if ($student['club_name']) {
                $enrolled++;
            }
        }
        $notEnrolled = $total - $enrolled;
        $percentage = $total > 0 ? round(($enrolled / $total) * 100, 1) : 0;
        
        $this->render('teacher/clubs/summary', [
            'students' => $students,
            'academicYear' => $academicYear,
            'semester' => $semester,
            'selectedClass' => $selectedClass,
            'selectedClassroom' => $selectedClassroom,
            'stats' => [
                'total' => $total,
                'enrolled' => $enrolled,
                'not_enrolled' => $notEnrolled,
                'percentage' => $percentage
            ]
        ]);
    }
}
