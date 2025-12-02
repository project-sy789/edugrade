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
            $this->db->query(
                'INSERT OR REPLACE INTO settings (setting_key, setting_value, updated_at) 
                 VALUES (:key, :value, datetime("now"))',
                [
                    ':key' => $key,
                    ':value' => $value
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
