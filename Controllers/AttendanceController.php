<?php

namespace App\Controllers;

use App\Models\Course;
use App\Models\Attendance;

/**
 * Attendance Controller
 * 
 * Handles attendance recording and viewing.
 */
class AttendanceController extends BaseController
{
    private $attendanceModel;
    private $courseModel;
    
    public function __construct()
    {
        $this->attendanceModel = new Attendance();
        $this->courseModel = new Course();
    }
    
    /**
     * Show attendance recording page
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
        
        $date = $this->get('date', date('Y-m-d'));
        $period = (int)$this->get('period', 1);
        $students = $this->courseModel->getEnrolledStudents($courseId);
        $attendance = $this->attendanceModel->getCourseAttendance($courseId, $date, $period);
        
        // Create attendance map for easy lookup
        $attendanceMap = [];
        foreach ($attendance as $record) {
            $attendanceMap[$record['student_id']] = $record;
        }
        
        $this->render('teacher/attendance/record', [
            'course' => $course,
            'date' => $date,
            'period' => $period,
            'students' => $students,
            'attendanceMap' => $attendanceMap,
            'db' => $this->attendanceModel->getDb()
        ]);
    }
    
    /**
     * Save attendance
     */
    public function save($courseId)
    {
        $this->requireTeacher();
        $this->requireCsrfToken();
        
        try {
            $date = $this->post('date');
            $period = (int)$this->post('period', 1);
            $attendance = $this->post('attendance', []);
            
            if (empty($date)) {
                throw new \Exception('กรุณาระบุวันที่');
            }
            
            foreach ($attendance as $studentId => $status) {
                if (!empty($status)) {
                    $this->attendanceModel->record($studentId, $courseId, $date, $status, $period);
                }
            }
            
            $this->setFlash('success', 'บันทึกการเข้าเรียนสำเร็จ (คาบที่ ' . $period . ')');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/teacher/courses/' . $courseId . '/attendance?date=' . ($date ?? date('Y-m-d')) . '&period=' . $period);
    }
    
    /**
     * Save attendance via AJAX (for individual student updates)
     */
    public function saveAjax()
    {
        $this->requireTeacher();
        
        try {
            $studentId = $this->post('student_id');
            $courseId = $this->post('course_id');
            $date = $this->post('date');
            $period = (int)$this->post('period', 1);
            $status = $this->post('status');
            
            if (empty($studentId) || empty($courseId) || empty($date) || empty($status)) {
                throw new \Exception('ข้อมูลไม่ครบถ้วน');
            }
            
            $this->attendanceModel->record($studentId, $courseId, $date, $status, $period);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'บันทึกการเข้าเรียนสำเร็จ'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Show attendance summary for a course (teacher view)
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
        
        $type = $_GET['type'] ?? 'month'; // month or semester
        $selectedMonth = $_GET['month'] ?? date('Y-m');
        
        // Get enrolled students
        $students = $this->courseModel->getEnrolledStudents($courseId);
        
        // Calculate date range
        if ($type === 'month') {
            $startDate = $selectedMonth . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            $periodText = date('F Y', strtotime($startDate));
        } else {
            // Semester: use manual date range from user input
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $periodText = date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate));
        }
        
        
        // Get attendance statistics for each student
        $attendanceStats = [];
        foreach ($students as $student) {
            $records = $this->attendanceModel->getStudentAttendanceInRange(
                $student['id'],
                $courseId,
                $startDate,
                $endDate
            );
            
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
            
            $stats['percentage'] = $stats['total'] > 0 
                ? ($stats['present'] / $stats['total']) * 100 
                : 0;
            
            $attendanceStats[$student['id']] = $stats;
        }
        
        $this->render('teacher/attendance/summary', [
            'course' => $course,
            'students' => $students,
            'attendanceStats' => $attendanceStats,
            'type' => $type,
            'selectedMonth' => $selectedMonth,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodText' => $periodText
        ]);
    }
    
    /**
     * Show student's own attendance
     */
    public function studentView()
    {
        $this->requireStudent();
        
        $studentId = $this->getStudentId();
        $courses = $this->courseModel->getStudentCourses($studentId);
        
        $attendanceData = [];
        foreach ($courses as $course) {
            $records = $this->attendanceModel->getStudentAttendance($studentId, $course['id']);
            $stats = $this->attendanceModel->calculateStatistics($studentId, $course['id']);
            
            $attendanceData[] = [
                'course' => $course,
                'records' => $records,
                'stats' => $stats
            ];
        }
        
        $this->render('student/attendance/view', ['attendanceData' => $attendanceData]);
    }
}
