<?php

namespace App\Models;

/**
 * Settings Model
 * 
 * Manages website configuration settings
 */
class Settings
{
    private $db;
    private static $cache = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }
    
    /**
     * Load all settings into cache
     */
    private function loadSettings()
    {
        if (empty(self::$cache)) {
            $settings = $this->db->fetchAll('SELECT setting_key, setting_value FROM settings');
            foreach ($settings as $setting) {
                self::$cache[$setting['setting_key']] = $setting['setting_value'];
            }
        }
    }
    
    /**
     * Get setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    public function get($key, $default = null)
    {
        return self::$cache[$key] ?? $default;
    }
    
    /**
     * Set setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success
     */
    public function set($key, $value)
    {
        try {
            // Use MySQL syntax instead of SQLite
            $this->db->query(
                'INSERT INTO settings (setting_key, setting_value, updated_at) 
                 VALUES (:key, :value, NOW())
                 ON DUPLICATE KEY UPDATE 
                 setting_value = :value2, 
                 updated_at = NOW()',
                [
                    ':key' => $key,
                    ':value' => $value,
                    ':value2' => $value
                ]
            );
            
            self::$cache[$key] = $value;
            return true;
        } catch (\Exception $e) {
            error_log('Settings::set() failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all settings
     * 
     * @return array All settings
     */
    public function getAll()
    {
        return self::$cache;
    }
    
    /**
     * Check if club registration is currently open
     * 
     * @return bool True if registration is open
     */
    public function isClubRegistrationOpen()
    {
        $mode = $this->get('club_registration_mode') ?: 'manual';
        
        if ($mode === 'manual') {
            // Manual mode: check manual status
            return (bool)$this->get('club_registration_manual_status');
        }
        
        // Automatic mode: check date/time range
        $start = $this->get('club_registration_start');
        $end = $this->get('club_registration_end');
        $now = date('Y-m-d H:i:s');
        
        if ($start && $now < $start) {
            return false;
        }
        
        if ($end && $now > $end) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get club registration status message
     * 
     * @return array Status info with 'open', 'message', 'start', 'end'
     */
    public function getClubRegistrationStatus()
    {
        $mode = $this->get('club_registration_mode') ?: 'manual';
        $isOpen = $this->isClubRegistrationOpen();
        
        $status = [
            'open' => $isOpen,
            'mode' => $mode,
            'message' => '',
            'start' => null,
            'end' => null
        ];
        
        if ($mode === 'manual') {
            $status['message'] = $isOpen ? 'เปิดรับสมัครชุมนุม' : 'ปิดรับสมัครชุมนุม';
        } else {
            $start = $this->get('club_registration_start');
            $end = $this->get('club_registration_end');
            $now = date('Y-m-d H:i:s');
            
            $status['start'] = $start;
            $status['end'] = $end;
            
            if ($start && $now < $start) {
                $status['message'] = 'การลงทะเบียนจะเปิดในวันที่ ' . date('d/m/Y H:i', strtotime($start));
            } elseif ($end && $now > $end) {
                $status['message'] = 'การลงทะเบียนปิดแล้วเมื่อวันที่ ' . date('d/m/Y H:i', strtotime($end));
            } else {
                $status['message'] = 'เปิดรับสมัครถึงวันที่ ' . date('d/m/Y H:i', strtotime($end));
            }
        }
        
        return $status;
    }
    
    /**
     * Upload file (logo or favicon)
     * 
     * @param array $file Uploaded file from $_FILES
     * @param string $type Type: 'logo' or 'favicon'
     * @return string|false File path or false on error
     */
    public function uploadFile($file, $type = 'logo')
    {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        // Define allowed types and sizes
        $config = [
            'logo' => [
                'types' => ['image/png', 'image/jpeg', 'image/jpg'],
                'extensions' => ['png', 'jpg', 'jpeg'],
                'max_size' => 2 * 1024 * 1024, // 2MB
                'dir' => 'logos'
            ],
            'favicon' => [
                'types' => ['image/x-icon', 'image/png', 'image/vnd.microsoft.icon'],
                'extensions' => ['ico', 'png'],
                'max_size' => 500 * 1024, // 500KB
                'dir' => 'favicons'
            ]
        ];
        
        if (!isset($config[$type])) {
            return false;
        }
        
        $cfg = $config[$type];
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $cfg['types'])) {
            return false;
        }
        
        // Validate file size
        if ($file['size'] > $cfg['max_size']) {
            return false;
        }
        
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $cfg['extensions'])) {
            return false;
        }
        
        // Create upload directory if not exists
        $uploadDir = __DIR__ . '/../public/uploads/' . $cfg['dir'];
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = $type . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/uploads/' . $cfg['dir'] . '/' . $filename;
        }
        
        return false;
    }
    
    /**
     * Delete old file
     * 
     * @param string $path File path
     * @return bool Success
     */
    public function deleteFile($path)
    {
        if (empty($path) || !file_exists(__DIR__ . '/../public' . $path)) {
            return false;
        }
        
        return unlink(__DIR__ . '/../public' . $path);
    }
}
