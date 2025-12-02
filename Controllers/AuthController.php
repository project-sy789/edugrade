<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Student;

/**
 * Authentication Controller
 * 
 * Handles login, logout, and authentication for both teachers and students.
 */
class AuthController extends BaseController
{
    private $userModel;
    private $studentModel;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->studentModel = new Student();
    }

    /**
     * Show login page
     */
    public function login()
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->isTeacher()) {
            $this->redirect('/teacher/dashboard');
        } elseif ($this->isStudent()) {
            $this->redirect('/student/dashboard');
        }
        
        $this->render('auth/login');
    }
    
    /**
     * Handle unified login (auto-detect teacher or student)
     */
    public function handleLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }
        
        $this->requireCsrfToken();
        
        $username = $this->post('username');
        $password = $this->post('password');
        
        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
            $this->redirect('/login');
            return;
        }
        
        // Try teacher login first
        $user = $this->userModel->findByUsername($username);
        
        error_log("DEBUG LOGIN: username='$username', user found: " . ($user ? 'YES' : 'NO'));
        if ($user) {
            error_log("DEBUG LOGIN: user data: " . json_encode(['id' => $user['id'], 'username' => $user['username'], 'name' => $user['name']]));
            error_log("DEBUG LOGIN: password verify: " . (password_verify($password, $user['password']) ? 'MATCH' : 'NO MATCH'));
        }
        
        if ($user && password_verify($password, $user['password'])) {
            // Teacher login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_type'] = 'teacher';
            
            $this->redirect('/teacher/dashboard');
            return;
        }
        
        // Try student login (username = student_code, password = id_card)
        $student = $this->studentModel->findByStudentCode($username);
        
        error_log("DEBUG LOGIN: student found: " . ($student ? 'YES' : 'NO'));
        if ($student) {
            error_log("DEBUG LOGIN: student data: " . json_encode(['id' => $student['id'], 'student_code' => $student['student_code'], 'name' => $student['name']]));
            error_log("DEBUG LOGIN: id_card match: " . ($student['id_card'] === $password ? 'MATCH' : 'NO MATCH'));
        }
        
        if ($student && $student['id_card'] === $password) {
            // Student login successful
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['student_code'] = $student['student_code'];
            $_SESSION['class_level'] = $student['class_level'];
            $_SESSION['classroom'] = $student['classroom'];
            $_SESSION['user_type'] = 'student';
            
            $this->redirect('/student/dashboard');
            return;
        }
        
        // Login failed
        error_log("DEBUG LOGIN: Login failed for username='$username'");
        $this->setFlash('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        $this->redirect('/login');
    }
    
    /**
     * Authenticate teacher
     */
    public function teacherLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login?type=teacher');
            return;
        }
        
        $this->requireCsrfToken();
        
        $username = $this->post('username');
        $password = $this->post('password');
        
        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
            $this->redirect('/login?type=teacher');
            return;
        }
        
        $userModel = new User();
        $user = $userModel->authenticate($username, $password);
        
        if (!$user) {
            $this->setFlash('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
            $this->redirect('/login?type=teacher');
            return;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        $this->setFlash('success', 'เข้าสู่ระบบสำเร็จ');
        
        // All users (both admin and teacher) go to teacher dashboard
        $this->redirect('/teacher/dashboard');
    }
    
    /**
     * Authenticate student
     */
    public function studentLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login?type=student');
            return;
        }
        
        $this->requireCsrfToken();
        
        $idCard = $this->post('id_card');
        $studentCode = $this->post('student_code');
        
        if (empty($idCard) || empty($studentCode)) {
            $this->setFlash('error', 'กรุณากรอกเลขบัตรประชาชนและรหัสนักเรียน');
            $this->redirect('/login?type=student');
            return;
        }
        
        $studentModel = new Student();
        $student = $studentModel->authenticate($idCard, $studentCode);
        
        if (!$student) {
            $this->setFlash('error', 'เลขบัตรประชาชนหรือรหัสนักเรียนไม่ถูกต้อง');
            $this->redirect('/login?type=student');
            return;
        }
        
        // Set session
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_code'] = $student['student_code'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['class_level'] = $student['class_level'];
        $_SESSION['classroom'] = $student['classroom'];
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        $this->setFlash('success', 'เข้าสู่ระบบสำเร็จ');
        $this->redirect('/student/dashboard');
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        // Determine redirect URL based on current user type
        $redirectUrl = '/login';
        if ($this->isTeacher()) {
            $redirectUrl = '/login?type=teacher';
        } elseif ($this->isStudent()) {
            $redirectUrl = '/login?type=student';
        }
        
        // Clear session
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        // Start new session for flash message
        session_start();
        $this->setFlash('success', 'ออกจากระบบสำเร็จ');
        
        $this->redirect($redirectUrl);
    }
}
