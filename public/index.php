<?php
/**
 * Application Entry Point
 * 
 * Simple router for the student grade and attendance system.
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Manual autoloader (no Composer needed)
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    // App\Controllers\AuthController -> Controllers/AuthController.php
    // App\Models\User -> Models/User.php
    
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$requestUri = strtok($requestUri, '?');

// Remove /public from URI if present
$requestUri = str_replace('/public', '', $requestUri);

// Trim slashes
$requestUri = trim($requestUri, '/');

// Import controllers
use App\Controllers\AuthController;
use App\Controllers\StudentController;
use App\Controllers\CourseController;
use App\Controllers\GradeController;
use App\Controllers\AttendanceController;

use App\Controllers\DashboardController;
use App\Controllers\TeacherManagementController;
use App\Controllers\ClubController;
use App\Controllers\SettingsController;

try {
    // Route handling
    if (empty($requestUri) || $requestUri === 'index.php') {
        // Homepage - redirect to login
        header('Location: /login');
        exit;
    }
    
    // Split URI into parts
    $parts = explode('/', $requestUri); // $requestUri is already trimmed, so no need for trim($path, '/')
    $controller = $parts[0] ?? '';
    $action = $parts[1] ?? ''; // Changed from 'index' to '' to match the instruction's implied behavior for login/logout
    $param = $parts[2] ?? null;
    
    // Authentication routes
    if ($requestUri === 'login') { // Use $requestUri which is already trimmed
        $authController = new AuthController();
        if ($requestMethod === 'POST') {
            $authController->handleLogin();
        } else {
            $authController->login();
        }
        exit;
    }
    
    if ($requestUri === 'logout') { // Use $requestUri which is already trimmed
        $authController = new AuthController();
        $authController->logout();
        exit;
    }
    
    // Teacher routes
    if ($controller === 'teacher') {
        if ($action === 'dashboard') {
            $dashboardController = new DashboardController();
            $dashboardController->teacherDashboard();
            exit;
        }
        
        if ($action === 'students') {
            $studentController = new StudentController();
            
            $subAction = $param ?? 'index';
            
            if ($subAction === 'index' || $subAction === '') {
                $studentController->index();
            } elseif ($subAction === 'create') {
                if ($requestMethod === 'POST') {
                    $studentController->store();
                } else {
                    $studentController->create();
                }
            } elseif ($subAction === 'edit') {
                $id = $parts[3] ?? null;
                if ($requestMethod === 'POST') {
                    $studentController->update($id);
                } else {
                    $studentController->edit($id);
                }
            } elseif ($subAction === 'delete') {
                $id = $parts[3] ?? null;
                $studentController->delete($id);
            } elseif ($subAction === 'bulk-delete') {
                $studentController->bulkDelete();
            } elseif ($subAction === 'upload') {
                if ($requestMethod === 'POST') {
                    $studentController->uploadXlsx();
                } else {
                    $studentController->uploadPage();
                }
            }
            exit;
        }
        
        if ($action === 'teachers') {
            $teacherMgmtController = new TeacherManagementController();
            
            $subAction = $param ?? 'index';
            
            if ($subAction === 'index' || $subAction === '') {
                $teacherMgmtController->index();
            } elseif ($subAction === 'create') {
                $teacherMgmtController->create();
            } elseif ($subAction === 'store') {
                $teacherMgmtController->store();
            } elseif (is_numeric($subAction)) {
                $teacherId = $subAction;
                $nextAction = $parts[3] ?? null;
                
                if ($nextAction === 'edit') {
                    $teacherMgmtController->edit($teacherId);
                } elseif ($nextAction === 'update') {
                    $teacherMgmtController->update($teacherId);
                } elseif ($nextAction === 'delete') {
                    $teacherMgmtController->delete($teacherId);
                }
            }
            exit;
        }
        
        if ($action === 'courses') {
            $courseController = new CourseController();
            
            $subAction = $param ?? 'index';
            
            if ($subAction === 'index' || $subAction === '') {
                $courseController->index();
            } elseif ($subAction === 'create') {
                if ($requestMethod === 'POST') {
                    $courseController->store();
                } else {
                    $courseController->create();
                }
            } elseif ($subAction === 'edit') {
                $id = $parts[3] ?? null;
                if ($requestMethod === 'POST') {
                    $courseController->update($id);
                } else {
                    $courseController->edit($id);
                }
            } elseif (is_numeric($subAction)) {
                // Course ID routes
                $courseId = $subAction;
                $courseAction = $parts[3] ?? '';
                
                if ($courseAction === 'categories') {
                    if ($requestMethod === 'POST') {
                        // Check if it's a delete request
                        $categoryId = $parts[4] ?? null;
                        $deleteAction = $parts[5] ?? null;
                        
                        if ($categoryId && $deleteAction === 'delete') {
                            $courseController->deleteGradeCategory($courseId, $categoryId);
                        } else {
                            $courseController->addGradeCategory($courseId);
                        }
                    } else {
                        $courseController->manageGradeCategories($courseId);
                    }
                } elseif ($courseAction === 'enroll') {
                    if ($requestMethod === 'POST') {
                        $courseController->enrollStudents($courseId);
                    } else {
                        $courseController->enrollStudentsPage($courseId);
                    }
                } elseif ($courseAction === 'grades') {
                    $gradeController = new GradeController();
                    if ($parts[4] ?? '' === 'summary') {
                        $gradeController->summary($courseId);
                    } else {
                        $gradeController->record($courseId);
                    }
                } elseif ($courseAction === 'attendance') {
                    $attendanceController = new AttendanceController();
                    if ($parts[4] ?? '' === 'summary') {
                        $attendanceController->summary($courseId);
                    } else {
                        $attendanceController->record($courseId);
                    }
                }
            }
            exit;
        }
        
        if ($action === 'clubs') {
            $clubController = new ClubController();
            
            $subAction = $param ?? 'index';
            
            if ($subAction === 'index' || $subAction === '') {
                $clubController->index();
            } elseif ($subAction === 'summary') {
                $clubController->summary();
            } elseif ($subAction === 'create') {
                $clubController->create();
            } elseif ($subAction === 'store') {
                $clubController->store();
            } elseif (is_numeric($subAction)) {
                $clubId = $subAction;
                $nextAction = $parts[3] ?? null;
                
                if ($nextAction === 'edit') {
                    $clubController->edit($clubId);
                } elseif ($nextAction === 'update') {
                    $clubController->update($clubId);
                } elseif ($nextAction === 'delete') {
                    $clubController->delete($clubId);
                } elseif ($nextAction === 'members') {
                    $clubController->members($clubId);
                } elseif ($nextAction === 'remove-student') {
                    $clubController->removeStudent($clubId);
                } elseif ($nextAction === 'grades') {
                    $subSubAction = $parts[4] ?? null;
                    if ($subSubAction === 'store') {
                        $clubController->gradeStore($clubId);
                    } else {
                        $clubController->gradeForm($clubId);
                    }
                }
            }
            exit;
        }
    }
    
    // Admin settings routes
    if ($controller === 'admin' && $action === 'settings') {
        $settingsController = new SettingsController();
        
        $subAction = $param ?? 'index';
        
        if ($subAction === 'index' || $subAction === '') {
            $settingsController->index();
        } elseif ($subAction === 'update') {
            $settingsController->update();
        } elseif ($subAction === 'upload-logo') {
            $settingsController->uploadLogo();
        } elseif ($subAction === 'upload-favicon') {
            $settingsController->uploadFavicon();
        }
        exit;
    }
    
    // Student routes
    if ($controller === 'student') {
        if ($action === 'dashboard') {
            $dashboardController = new DashboardController();
            $dashboardController->studentDashboard();
            exit;
        }
        
        if ($action === 'grades') {
            $gradeController = new GradeController();
            $gradeController->studentView();
            exit;
        }
        
        if ($action === 'attendance') {
            $attendanceController = new AttendanceController();
            $attendanceController->studentView();
            exit;
        }
        
        if ($action === 'clubs') {
            $clubController = new ClubController();
            
            $subAction = $param ?? 'index';
            
            if ($subAction === 'index' || $subAction === '') {
                $clubController->studentIndex();
            } elseif ($subAction === 'my') {
                $clubController->studentMyClub();
            } elseif ($subAction === 'unenroll') {
                $clubController->studentUnenroll();
            } elseif (is_numeric($subAction)) {
                $clubId = $subAction;
                $nextAction = $parts[3] ?? null;
                
                if ($nextAction === 'enroll') {
                    $clubController->studentEnroll($clubId);
                }
            }
            exit;
        }
    }
    
    // API routes (for AJAX)
    if ($controller === 'api') {
        if ($action === 'save-grade' && $requestMethod === 'POST') {
            $gradeController = new GradeController();
            $gradeController->save();
            exit;
        }
        
        if ($action === 'save-attendance' && $requestMethod === 'POST') {
            $attendanceController = new AttendanceController();
            $attendanceController->save();
            exit;
        }
    }
    
    // 404 Not Found
    http_response_code(404);
    echo '<h1>404 - ไม่พบหน้าที่ต้องการ</h1>';
    
} catch (Exception $e) {
    // Error handling
    error_log('Application Error: ' . $e->getMessage());
    http_response_code(500);
    echo '<h1>เกิดข้อผิดพลาด</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
