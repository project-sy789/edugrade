<?php
/**
 * Helper Functions
 * 
 * Global helper functions for the application
 */

use App\Models\Settings;

// Initialize settings instance
$GLOBALS['settings_instance'] = null;

/**
 * Get settings instance (singleton)
 * 
 * @return Settings
 */
function getSettingsInstance()
{
    if ($GLOBALS['settings_instance'] === null) {
        $GLOBALS['settings_instance'] = new Settings();
    }
    return $GLOBALS['settings_instance'];
}

/**
 * Get setting value
 * 
 * @param string $key Setting key
 * @param mixed $default Default value
 * @return mixed Setting value
 */
function setting($key, $default = null)
{
    return getSettingsInstance()->get($key, $default);
}

/**
 * Get site name
 * 
 * @return string
 */
function siteName()
{
    return setting('site_name', 'ระบบจัดการโรงเรียน');
}

/**
 * Get school name
 * 
 * @return string
 */
function schoolName()
{
    return setting('school_name', 'โรงเรียนตัวอย่าง');
}

/**
 * Get logo path
 * 
 * @return string
 */
function logoPath()
{
    $logoSetting = setting('logo_path');
    
    // If logo is set and file exists, return it
    if ($logoSetting && file_exists(__DIR__ . '/../public' . $logoSetting)) {
        return $logoSetting;
    }
    
    // Return empty string if no logo (avoid 404)
    return '';
}

/**
 * Get favicon path
 * 
 * @return string
 */
function faviconPath()
{
    return setting('favicon_path', '/images/favicon.ico');
}
