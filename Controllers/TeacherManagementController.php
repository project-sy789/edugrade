<?php

namespace App\Controllers;

use App\Models\User;

/**
 * Teacher Management Controller
 * 
 * Handles teacher/admin management for admin users.
 */
class TeacherManagementController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new User();
    }
    
    /**
     * List all teachers
     */
    public function index()
    {
        $this->requireAdmin();
        
        $search = $this->get('search', '');
        
        if ($search) {
            $teachers = $this->userModel->search($search);
        } else {
            $teachers = $this->userModel->getAll();
        }
        
        $this->render('teacher/teachers/index', [
            'teachers' => $teachers,
            'search' => $search
        ]);
    }
    
    /**
     * Show create teacher form
     */
    public function create()
    {
        $this->requireAdmin();
        
        $this->render('teacher/teachers/form', [
            'teacher' => null,
            'action' => 'create'
        ]);
    }
    
    /**
     * Store new teacher
     */
    public function store()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/teachers');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $username = $this->post('username');
            $password = $this->post('password');
            $name = $this->post('name');
            $role = $this->post('role', 'teacher');
            
            if (empty($username) || empty($password) || empty($name)) {
                $this->setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
                $this->redirect('/teacher/teachers/create');
                return;
            }
            
            if (!in_array($role, ['teacher', 'admin'])) {
                $this->setFlash('error', 'บทบาทไม่ถูกต้อง');
                $this->redirect('/teacher/teachers/create');
                return;
            }
            
            if ($this->userModel->findByUsername($username)) {
                $this->setFlash('error', 'ชื่อผู้ใช้นี้มีอยู่แล้ว');
                $this->redirect('/teacher/teachers/create');
                return;
            }
            
            $userId = $this->userModel->create([
                'username' => $username,
                'password' => $password,
                'name' => $name,
                'role' => $role
            ]);
            
            $this->setFlash('success', 'เพิ่มผู้ใช้สำเร็จ');
            $this->redirect('/teacher/teachers');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->redirect('/teacher/teachers/create');
        }
    }
    
    /**
     * Show edit teacher form
     */
    public function edit($id)
    {
        $this->requireAdmin();
        
        $teacher = $this->userModel->findById($id);
        
        if (!$teacher) {
            $this->setFlash('error', 'ไม่พบผู้ใช้');
            $this->redirect('/teacher/teachers');
            return;
        }
        
        $this->render('teacher/teachers/form', [
            'teacher' => $teacher,
            'action' => 'edit'
        ]);
    }
    
    /**
     * Update teacher
     */
    public function update($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/teachers');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $teacher = $this->userModel->findById($id);
            
            if (!$teacher) {
                $this->setFlash('error', 'ไม่พบผู้ใช้');
                $this->redirect('/teacher/teachers');
                return;
            }
            
            $username = $this->post('username');
            $password = $this->post('password');
            $name = $this->post('name');
            $role = $this->post('role', 'teacher');
            
            if (empty($username) || empty($name)) {
                $this->setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
                $this->redirect('/teacher/teachers/' . $id . '/edit');
                return;
            }
            
            if (!in_array($role, ['teacher', 'admin'])) {
                $this->setFlash('error', 'บทบาทไม่ถูกต้อง');
                $this->redirect('/teacher/teachers/' . $id . '/edit');
                return;
            }
            
            $existingUser = $this->userModel->findByUsername($username);
            if ($existingUser && $existingUser['id'] != $id) {
                $this->setFlash('error', 'ชื่อผู้ใช้นี้มีอยู่แล้ว');
                $this->redirect('/teacher/teachers/' . $id . '/edit');
                return;
            }
            
            $data = [
                'username' => $username,
                'name' => $name,
                'role' => $role
            ];
            
            if (!empty($password)) {
                $data['password'] = $password;
            }
            
            $this->userModel->update($id, $data);
            
            $this->setFlash('success', 'อัพเดทข้อมูลสำเร็จ');
            $this->redirect('/teacher/teachers');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->redirect('/teacher/teachers/' . $id . '/edit');
        }
    }
    
    /**
     * Delete teacher
     */
    public function delete($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/teachers');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $teacher = $this->userModel->findById($id);
            
            if (!$teacher) {
                $this->setFlash('error', 'ไม่พบผู้ใช้');
                $this->redirect('/teacher/teachers');
                return;
            }
            
            if ($id == $this->getUserId()) {
                $this->setFlash('error', 'ไม่สามารถลบบัญชีของตนเองได้');
                $this->redirect('/teacher/teachers');
                return;
            }
            
            $this->userModel->delete($id);
            
            $this->setFlash('success', 'ลบผู้ใช้สำเร็จ');
            $this->redirect('/teacher/teachers');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->redirect('/teacher/teachers');
        }
    }
}
