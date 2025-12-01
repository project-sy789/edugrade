<?php
/**
 * Application Configuration
 * 
 * General application settings and constants.
 */

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Application Settings
define('APP_NAME', 'ระบบแจ้งผลคะแนนและบันทึกเวลาเรียน');
define('APP_VERSION', '1.0.0');
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Session lifetime (24 hours)
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);

// File Upload Settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['xlsx']);

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_NAME', 'student_grade_session');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Load helper functions
require_once __DIR__ . '/helpers.php';
