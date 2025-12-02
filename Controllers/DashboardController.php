<?php

namespace App\Controllers;

/**
 * Dashboard Controller
 * 
 * Handles dashboard pages for teachers and students.
 */
class DashboardController extends BaseController
{
    /**
     * Teacher dashboard
     */
    public function teacherDashboard()
    {
        $this->requireTeacher();
        $this->render('teacher/dashboard');
    }
    
    /**
     * Student dashboard
     */
    public function studentDashboard()
    {
        $this->requireStudent();
        $this->render('student/dashboard');
    }
}
