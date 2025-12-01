<?php
/**
 * Web-based Installation Wizard for EduGrade
 * Access this file via browser to install the system
 */

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Get installation step
$step = $_GET['step'] ?? 1;

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตั้ง EduGrade - ระบบจัดการคะแนนและเวลาเรียน</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }
        .step.active { background: #667eea; color: white; }
        .step.completed { background: #10b981; color: white; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover { background: #5568d3; }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
            margin-right: 10px;
        }
        .btn-secondary:hover { background: #d0d0d0; }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-warning { background: #fef3c7; color: #92400e; }
        .check-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f9fafb;
        }
        .check-item.success { background: #d1fae5; }
        .check-item.error { background: #fee2e2; }
        .check-icon {
            width: 24px;
            height: 24px;
            margin-right: 10px;
            font-size: 18px;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 EduGrade</h1>
            <p>ระบบจัดการคะแนนและเวลาเรียนนักเรียน</p>
        </div>
        
        <div class="content">
            <?php
            // Step 1: System Check
            if ($step == 1) {
                $checks = [
                    'php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),
                    'sqlite3' => extension_loaded('sqlite3'),
                    'mbstring' => extension_loaded('mbstring'),
                    'writable_database' => is_writable(__DIR__ . '/database') || is_writable(__DIR__),
                    'writable_uploads' => is_writable(__DIR__ . '/uploads') || is_writable(__DIR__),
                ];
                
                $allPassed = !in_array(false, $checks, true);
                ?>
                
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                    <div class="step">3</div>
                    <div class="step">4</div>
                </div>
                
                <h2 style="margin-bottom: 20px;">ขั้นตอนที่ 1: ตรวจสอบระบบ</h2>
                
                <div class="check-item <?php echo $checks['php_version'] ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $checks['php_version'] ? '✅' : '❌'; ?></span>
                    <div>
                        <strong>PHP Version</strong><br>
                        ต้องการ PHP 8.0+, ปัจจุบัน: <?php echo PHP_VERSION; ?>
                    </div>
                </div>
                
                <div class="check-item <?php echo $checks['sqlite3'] ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $checks['sqlite3'] ? '✅' : '❌'; ?></span>
                    <div><strong>SQLite3 Extension</strong></div>
                </div>
                
                <div class="check-item <?php echo $checks['mbstring'] ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $checks['mbstring'] ? '✅' : '❌'; ?></span>
                    <div><strong>Mbstring Extension</strong></div>
                </div>
                
                <div class="check-item <?php echo $checks['writable_database'] ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $checks['writable_database'] ? '✅' : '❌'; ?></span>
                    <div><strong>Database Folder Writable</strong></div>
                </div>
                
                <div class="check-item <?php echo $checks['writable_uploads'] ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $checks['writable_uploads'] ? '✅' : '❌'; ?></span>
                    <div><strong>Uploads Folder Writable</strong></div>
                </div>
                
                <?php if (!$allPassed): ?>
                    <div class="alert alert-error" style="margin-top: 20px;">
                        <strong>⚠️ พบปัญหา!</strong><br>
                        กรุณาแก้ไขปัญหาที่พบก่อนดำเนินการต่อ
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button class="btn btn-primary" <?php echo !$allPassed ? 'disabled' : ''; ?> 
                            onclick="location.href='?step=2'">
                        ถัดไป →
                    </button>
                </div>
                
            <?php } elseif ($step == 2) { ?>
                
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step active">2</div>
                    <div class="step">3</div>
                    <div class="step">4</div>
                </div>
                
                <h2 style="margin-bottom: 20px;">ขั้นตอนที่ 2: สร้างโฟลเดอร์</h2>
                
                <?php
                $folders = ['database', 'uploads', 'uploads/logos', 'sessions', 'logs'];
                $created = [];
                $errors = [];
                
                foreach ($folders as $folder) {
                    if (!file_exists($folder)) {
                        if (@mkdir($folder, 0755, true)) {
                            $created[] = $folder;
                        } else {
                            $errors[] = $folder;
                        }
                    }
                    @chmod($folder, 0755);
                }
                ?>
                
                <?php if (!empty($created)): ?>
                    <div class="alert alert-success">
                        <strong>✅ สร้างโฟลเดอร์สำเร็จ:</strong><br>
                        <?php echo implode(', ', $created); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <strong>❌ ไม่สามารถสร้างโฟลเดอร์:</strong><br>
                        <?php echo implode(', ', $errors); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <strong>✅ โฟลเดอร์ทั้งหมดพร้อมใช้งาน</strong>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button class="btn btn-secondary" onclick="location.href='?step=1'">← ย้อนกลับ</button>
                    <button class="btn btn-primary" onclick="location.href='?step=3'">ถัดไป →</button>
                </div>
                
            <?php } elseif ($step == 3) { ?>
                
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step completed">2</div>
                    <div class="step active">3</div>
                    <div class="step">4</div>
                </div>
                
                <h2 style="margin-bottom: 20px;">ขั้นตอนที่ 3: สร้างฐานข้อมูล</h2>
                
                <?php
                $dbFile = 'database/score.db';
                $schemaFile = 'database/schema_sqlite.sql';
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_db'])) {
                    try {
                        if (file_exists($dbFile)) {
                            unlink($dbFile);
                        }
                        
                        if (!file_exists($schemaFile)) {
                            throw new Exception("ไม่พบไฟล์ schema: $schemaFile");
                        }
                        
                        $schema = file_get_contents($schemaFile);
                        $db = new SQLite3($dbFile);
                        $db->exec($schema);
                        $db->close();
                        
                        echo '<div class="alert alert-success"><strong>✅ สร้างฐานข้อมูลสำเร็จ!</strong></div>';
                        $_SESSION['db_created'] = true;
                    } catch (Exception $e) {
                        echo '<div class="alert alert-error"><strong>❌ เกิดข้อผิดพลาด:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                
                $dbExists = file_exists($dbFile);
                ?>
                
                <?php if ($dbExists && !isset($_POST['create_db'])): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ ฐานข้อมูลมีอยู่แล้ว</strong><br>
                        การสร้างใหม่จะลบข้อมูลเดิมทั้งหมด
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div style="margin-top: 30px; text-align: right;">
                        <button class="btn btn-secondary" type="button" onclick="location.href='?step=2'">← ย้อนกลับ</button>
                        <?php if (!isset($_SESSION['db_created'])): ?>
                            <button class="btn btn-primary" type="submit" name="create_db">
                                <?php echo $dbExists ? 'สร้างใหม่' : 'สร้างฐานข้อมูล'; ?>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary" type="button" onclick="location.href='?step=4'">ถัดไป →</button>
                        <?php endif; ?>
                    </div>
                </form>
                
            <?php } elseif ($step == 4) { ?>
                
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step completed">2</div>
                    <div class="step completed">3</div>
                    <div class="step active">4</div>
                </div>
                
                <h2 style="margin-bottom: 20px;">ขั้นตอนที่ 4: สร้างผู้ดูแลระบบ</h2>
                
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
                    try {
                        $db = new SQLite3('database/score.db');
                        
                        // Check if admin exists
                        $result = $db->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
                        $row = $result->fetchArray(SQLITE3_ASSOC);
                        
                        if ($row['count'] == 0) {
                            $password = password_hash('password', PASSWORD_BCRYPT);
                            $stmt = $db->prepare("INSERT INTO users (username, password, name, role) VALUES (:username, :password, :name, :role)");
                            $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
                            $stmt->bindValue(':password', $password, SQLITE3_TEXT);
                            $stmt->bindValue(':name', 'ผู้ดูแลระบบ', SQLITE3_TEXT);
                            $stmt->bindValue(':role', 'admin', SQLITE3_TEXT);
                            $stmt->execute();
                            
                            echo '<div class="alert alert-success"><strong>✅ สร้างผู้ดูแลระบบสำเร็จ!</strong></div>';
                            $_SESSION['admin_created'] = true;
                        } else {
                            echo '<div class="alert alert-warning"><strong>⚠️ ผู้ดูแลระบบมีอยู่แล้ว</strong></div>';
                            $_SESSION['admin_created'] = true;
                        }
                        
                        $db->close();
                    } catch (Exception $e) {
                        echo '<div class="alert alert-error"><strong>❌ เกิดข้อผิดพลาด:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                ?>
                
                <?php if (isset($_SESSION['admin_created'])): ?>
                    <div class="alert alert-success">
                        <h3 style="margin-bottom: 15px;">🎉 ติดตั้งเสร็จสมบูรณ์!</h3>
                        <p><strong>ข้อมูลเข้าสู่ระบบ:</strong></p>
                        <p>Username: <code>admin</code></p>
                        <p>Password: <code>password</code></p>
                        <p style="margin-top: 15px; color: #991b1b;">
                            <strong>⚠️ สำคัญ:</strong> กรุณาเปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบ!
                        </p>
                    </div>
                    
                    <div style="margin-top: 30px; text-align: center;">
                        <a href="public/" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
                            เข้าสู่ระบบ →
                        </a>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px; font-size: 14px;">
                        <strong>💡 คำแนะนำ:</strong><br>
                        1. ลบไฟล์ <code>web-install.php</code> เพื่อความปลอดภัย<br>
                        2. เปลี่ยนรหัสผ่าน admin ทันที<br>
                        3. เริ่มเพิ่มข้อมูลนักเรียนและรายวิชา
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div style="margin-top: 30px; text-align: right;">
                            <button class="btn btn-secondary" type="button" onclick="location.href='?step=3'">← ย้อนกลับ</button>
                            <button class="btn btn-primary" type="submit" name="create_admin">สร้างผู้ดูแลระบบ</button>
                        </div>
                    </form>
                <?php endif; ?>
                
            <?php } ?>
        </div>
    </div>
</body>
</html>
