<?php

namespace App\Controllers;

/**
 * Base Controller
 * 
 * Provides common functionality for all controllers.
 */
class BaseController
{
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']) || isset($_SESSION['student_id']);
    }
    
    /**
     * Check if logged in as teacher
     * 
     * @return bool
     */
    protected function isTeacher()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
    }
    
    /**
     * Check if logged in as admin
     * 
     * @return bool
     */
    protected function isAdmin()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Check if logged in as teacher or admin
     * 
     * @return bool
     */
    protected function isTeacherOrAdmin()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']) && 
               in_array($_SESSION['role'], ['teacher', 'admin']);
    }
    
    /**
     * Check if logged in as student
     * 
     * @return bool
     */
    protected function isStudent()
    {
        return isset($_SESSION['student_id']);
    }
    
    /**
     * Require teacher authentication (teacher or admin)
     * 
     * @throws \Exception if not authenticated as teacher
     */
    protected function requireTeacher()
    {
        if (!$this->isTeacherOrAdmin()) {
            $this->redirect('/login?type=teacher');
            exit;
        }
    }
    
    /**
     * Require admin authentication
     * 
     * @throws \Exception if not authenticated as admin
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login?type=teacher');
            exit;
        }
    }
    
    /**
     * Require student authentication
     * 
     * @throws \Exception if not authenticated as student
     */
    protected function requireStudent()
    {
        if (!$this->isStudent()) {
            $this->redirect('/login?type=student');
            exit;
        }
    }
    
    /**
     * Require any authentication (teacher or student)
     */
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            exit;
        }
    }
    
    /**
     * Get current user ID (teacher)
     * 
     * @return int|null
     */
    protected function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current student ID
     * 
     * @return int|null
     */
    protected function getStudentId()
    {
        return $_SESSION['student_id'] ?? null;
    }
    
    /**
     * Get current user role
     * 
     * @return string|null
     */
    protected function getUserRole()
    {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string|null $token Token to verify
     * @return bool
     */
    protected function verifyCsrfToken($token = null)
    {
        if ($token === null) {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        }
        
        return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
    }
    
    /**
     * Require valid CSRF token
     * 
     * @throws \Exception if CSRF token is invalid
     */
    protected function requireCsrfToken()
    {
        if (!$this->verifyCsrfToken()) {
            $this->jsonResponse(['error' => 'Invalid CSRF token'], 403);
            exit;
        }
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Render a view
     * 
     * @param string $view View file path (relative to views/)
     * @param array $data Data to pass to view
     */
    protected function render($view, $data = [])
    {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$view}");
        }
        
        include $viewFile;
        
        // Get buffered content
        $content = ob_get_clean();
        
        // Output content
        echo $content;
    }
    
    /**
     * Render a view with layout
     * 
     * @param string $view View file path
     * @param array $data Data to pass to view
     * @param string $layout Layout file (default: 'layouts/main')
     */
    protected function renderWithLayout($view, $data = [], $layout = 'layouts/main')
    {
        // Extract data
        extract($data);
        
        // Capture view content
        ob_start();
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        }
        $content = ob_get_clean();
        
        // Render layout with content
        $layoutFile = __DIR__ . '/../views/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    /**
     * Send JSON response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Get POST data
     * 
     * @param string|null $key Specific key to get (null = all data)
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     * 
     * @param string|null $key Specific key to get (null = all data)
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get and clear flash message
     * 
     * @return array|null Flash message or null
     */
    protected function getFlash()
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Sanitize output for HTML
     * 
     * @param string $text Text to sanitize
     * @return string
     */
    protected function escape($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
