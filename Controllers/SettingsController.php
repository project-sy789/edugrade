<?php

namespace App\Controllers;

use App\Models\Settings;

/**
 * Settings Controller
 * 
 * Manages website settings (Admin only)
 */
class SettingsController extends BaseController
{
    private $settingsModel;
    
    public function __construct()
    {
        $this->settingsModel = new Settings();
    }
    
    public function index()
    {
        $this->requireAdmin();
        
        $settings = $this->settingsModel->getAll();
        
        $this->render('admin/settings/index', [
            'settings' => $settings,
            'settingsModel' => $this->settingsModel  // Pass model instance to view
        ]);
    }
    
    /**
     * Update settings
     */
    public function update()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $siteName = $this->post('site_name');
            $schoolName = $this->post('school_name');
            
            if (empty($siteName) || empty($schoolName)) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'];
                $this->redirect('/admin/settings');
                return;
            }
            
            $this->settingsModel->set('site_name', $siteName);
            $this->settingsModel->set('school_name', $schoolName);
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'บันทึกการตั้งค่าสำเร็จ'];
            $this->redirect('/admin/settings');
            
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
            $this->redirect('/admin/settings');
        }
    }
    
    /**
     * Upload logo
     */
    public function uploadLogo()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'กรุณาเลือกไฟล์ Logo'];
                $this->redirect('/admin/settings');
                return;
            }
            
            $newPath = $this->settingsModel->uploadFile($_FILES['logo'], 'logo');
            
            if ($newPath === false) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาตรวจสอบประเภทและขนาดไฟล์'];
                $this->redirect('/admin/settings');
                return;
            }
            
            // Delete old logo
            $oldPath = $this->settingsModel->get('site_logo');
            if ($oldPath && $oldPath !== '/images/logo.png') {
                $this->settingsModel->deleteFile($oldPath);
            }
            
            $this->settingsModel->set('site_logo', $newPath);
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'อัปโหลด Logo สำเร็จ'];
            $this->redirect('/admin/settings');
            
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
            $this->redirect('/admin/settings');
        }
    }
    
    /**
     * Upload favicon
     */
    public function uploadFavicon()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            if (!isset($_FILES['favicon']) || $_FILES['favicon']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'กรุณาเลือกไฟล์ Favicon'];
                $this->redirect('/admin/settings');
                return;
            }
            
            $newPath = $this->settingsModel->uploadFile($_FILES['favicon'], 'favicon');
            
            if ($newPath === false) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาตรวจสอบประเภทและขนาดไฟล์'];
                $this->redirect('/admin/settings');
                return;
            }
            
            // Delete old favicon
            $oldPath = $this->settingsModel->get('site_favicon');
            if ($oldPath && $oldPath !== '/images/favicon.ico') {
                $this->settingsModel->deleteFile($oldPath);
            }
            
            $this->settingsModel->set('site_favicon', $newPath);
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'อัปโหลด Favicon สำเร็จ'];
            $this->redirect('/admin/settings');
            
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
            $this->redirect('/admin/settings');
        }
    }
    
    /**
     * Update club registration settings
     */
    public function updateClubRegistration()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }
        
        $this->requireCsrfToken();
        
        try {
            $mode = $this->post('club_registration_mode', 'manual');
            
            // Save mode
            $this->settingsModel->set('club_registration_mode', $mode);
            
            if ($mode === 'manual') {
                // Manual mode: save status
                $status = $this->post('club_registration_manual_status') ? '1' : '0';
                $this->settingsModel->set('club_registration_manual_status', $status);
            } else {
                // Automatic mode: save start and end dates
                $start = $this->post('club_registration_start');
                $end = $this->post('club_registration_end');
                
                // Convert to MySQL datetime format if provided
                if ($start) {
                    $start = date('Y-m-d H:i:s', strtotime($start));
                    $this->settingsModel->set('club_registration_start', $start);
                }
                
                if ($end) {
                    $end = date('Y-m-d H:i:s', strtotime($end));
                    $this->settingsModel->set('club_registration_end', $end);
                }
            }
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'บันทึกการตั้งค่าการลงทะเบียนชุมนุมสำเร็จ'];
            $this->redirect('/admin/settings');
            
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
            $this->redirect('/admin/settings');
        }
    }
}
