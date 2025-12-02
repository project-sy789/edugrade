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
    
    /**
     * Show settings page
     */
    public function index()
    {
        $this->requireAdmin();
        
        $settings = $this->settingsModel->getAll();
        
        $this->render('admin/settings/index', [
            'settings' => $settings
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
            $oldPath = $this->settingsModel->get('favicon_path');
            if ($oldPath && $oldPath !== '/images/favicon.ico') {
                $this->settingsModel->deleteFile($oldPath);
            }
            
            $this->settingsModel->set('favicon_path', $newPath);
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'อัปโหลด Favicon สำเร็จ'];
            $this->redirect('/admin/settings');
            
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
            $this->redirect('/admin/settings');
        }
    }
}
