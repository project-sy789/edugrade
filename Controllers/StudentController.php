<?php

namespace App\Controllers;

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Student Controller
 * 
 * Handles student management including XLSX import.
 */
class StudentController extends BaseController
{
    private $studentModel;
    
    public function __construct()
    {
        $this->studentModel = new Student();
    }
    
    /**
     * List all students
     */
    public function index()
    {
        $this->requireTeacher();
        
        $search = $this->get('search', '');
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        if (!empty($search)) {
            $students = $this->studentModel->search($search, $perPage, $offset);
            $totalStudents = $this->studentModel->countSearch($search);
        } else {
            $students = $this->studentModel->getAll($perPage, $offset);
            $totalStudents = $this->studentModel->count();
        }
        
        $totalPages = ceil($totalStudents / $perPage);
        
        $this->render('teacher/students/index', [
            'students' => $students,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalStudents' => $totalStudents
        ]);
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        $this->requireTeacher();
        $this->render('teacher/students/form', ['student' => null]);
    }
    
    /**
     * Store new student
     */
    public function store()
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/students');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $data = [
                'student_code' => $this->post('student_code'),
                'id_card' => $this->post('id_card'),
                'name' => $this->post('name'),
                'class_level' => $this->post('class_level'),
                'classroom' => $this->post('classroom'),
                'notes' => $this->post('notes')
            ];
            
            $this->studentModel->create($data);
            $this->setFlash('success', 'เพิ่มนักเรียนสำเร็จ');
            $this->redirect('/teacher/students');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/teacher/students/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        $this->requireTeacher();
        
        $student = $this->studentModel->findById($id);
        
        if (!$student) {
            $this->setFlash('error', 'ไม่พบนักเรียน');
            $this->redirect('/teacher/students');
            return;
        }
        
        $this->render('teacher/students/form', ['student' => $student]);
    }
    
    /**
     * Update student
     */
    public function update($id)
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/students');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $data = [
                'student_code' => $this->post('student_code'),
                'id_card' => $this->post('id_card'),
                'name' => $this->post('name'),
                'class_level' => $this->post('class_level'),
                'classroom' => $this->post('classroom'),
                'notes' => $this->post('notes')
            ];
            
            $this->studentModel->update($id, $data);
            $this->setFlash('success', 'แก้ไขข้อมูลนักเรียนสำเร็จ');
            $this->redirect('/teacher/students');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/teacher/students/edit/' . $id);
        }
    }
    
    /**
     * Delete student
     */
    public function delete($id)
    {
        $this->requireTeacher();
        $this->requireCsrfToken();
        
        try {
            $this->studentModel->delete($id);
            $this->jsonResponse(['success' => true, 'message' => 'ลบนักเรียนสำเร็จ']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Show XLSX upload page
     */
    public function uploadPage()
    {
        $this->requireTeacher();
        $this->render('teacher/students/upload');
    }
    
    /**
     * Handle XLSX file upload and import
     */
    public function uploadXlsx()
    {
        $this->requireTeacher();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/teacher/students/upload');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            // Check if file was uploaded
            if (!isset($_FILES['xlsx_file']) || $_FILES['xlsx_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('กรุณาเลือกไฟล์ XLSX');
            }
            
            $file = $_FILES['xlsx_file'];
            
            // Validate file extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'xlsx') {
                throw new \Exception('ไฟล์ต้องเป็นนามสกุล .xlsx เท่านั้น');
            }
            
            // Validate file size (max 10MB)
            if ($file['size'] > MAX_UPLOAD_SIZE) {
                throw new \Exception('ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 10MB)');
            }
            
            // Load spreadsheet
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Parse data (assuming first row is header)
            $students = [];
            $highestRow = $worksheet->getHighestRow();
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $studentCode = $worksheet->getCell("A{$row}")->getValue();
                $idCard = $worksheet->getCell("B{$row}")->getValue();
                $name = $worksheet->getCell("C{$row}")->getValue();
                $classLevel = $worksheet->getCell("D{$row}")->getValue();
                $classroom = $worksheet->getCell("E{$row}")->getValue();
                $notes = $worksheet->getCell("F{$row}")->getValue();
                
                // Skip empty rows
                if (empty($studentCode) && empty($idCard) && empty($name)) {
                    continue;
                }
                
                $students[] = [
                    'student_code' => $studentCode,
                    'id_card' => $idCard,
                    'name' => $name,
                    'class_level' => $classLevel,
                    'classroom' => $classroom,
                    'notes' => $notes
                ];
            }
            
            if (empty($students)) {
                throw new \Exception('ไม่พบข้อมูลนักเรียนในไฟล์');
            }
            
            // Bulk insert
            $result = $this->studentModel->bulkInsert($students);
            
            // Store result in session for display
            $_SESSION['import_result'] = $result;
            
            $this->setFlash('success', "นำเข้าข้อมูลสำเร็จ {$result['success']} คน จากทั้งหมด {$result['total']} คน");
            $this->redirect('/teacher/students/upload');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->redirect('/teacher/students/upload');
        }
    }
}
