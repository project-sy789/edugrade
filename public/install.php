<?php
/**
 * WordPress-style Installation Wizard
 * Simple one-page installer for EduGrade
 */

// Start session
session_start();

// Check if already installed
$configFile = __DIR__ . '/config/database.php';
$isInstalled = file_exists($configFile) && filesize($configFile) > 100;

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isInstalled) {
    $dbHost = $_POST['db_host'] ?? '';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    
    // Validate inputs
    if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {
        try {
            // Test database connection
            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Import schema
            $schemaFile = __DIR__ . '/database/schema_mysql.sql';
            if (!file_exists($schemaFile)) {
                throw new Exception('ไม่พบไฟล์ schema_mysql.sql');
            }
            
            $schema = file_get_contents($schemaFile);
            $pdo->exec($schema);
            
            // Create config file
            $configContent = <<<PHP
<?php

return [
    'driver' => 'mysql',
    'host' => '$dbHost',
    'database' => '$dbName',
    'username' => '$dbUser',
    'password' => '$dbPass',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

PHP;
            
            // Create config directory if not exists
            if (!file_exists(__DIR__ . '/config')) {
                mkdir(__DIR__ . '/config', 0755, true);
            }
            
            file_put_contents($configFile, $configContent);
            
            // Create other necessary folders
            $folders = ['uploads', 'uploads/logos', 'sessions', 'logs'];
            foreach ($folders as $folder) {
                if (!file_exists(__DIR__ . '/' . $folder)) {
                    @mkdir(__DIR__ . '/' . $folder, 0755, true);
                }
            }
            
            $success = true;
            
        } catch (PDOException $e) {
            $error = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตั้ง EduGrade</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f0f0f1;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #2271b1;
            color: white;
            padding: 30px;
            text-align: center;
        }
        h1 { font-size: 24px; margin-bottom: 10px; }
        .content { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #2271b1;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #2271b1;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn:hover { background: #135e96; }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .help-text {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        code {
            background: #f0f0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .info-box {
            background: #f0f6fc;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #2271b1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 ติดตั้ง EduGrade</h1>
            <p>ระบบจัดการคะแนนและเวลาเรียน</p>
        </div>
        
        <div class="content">
            <?php if ($isInstalled): ?>
                <div class="alert alert-success">
                    <h2 style="margin-bottom: 10px;">✅ ติดตั้งเรียบร้อยแล้ว!</h2>
                    <p>ระบบได้รับการติดตั้งเรียบร้อยแล้ว</p>
                </div>
                <a href="public/" class="btn" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">
                    เข้าสู่ระบบ →
                </a>
                <div class="info-box" style="margin-top: 20px;">
                    <strong>ข้อมูลเข้าสู่ระบบ:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>password</code><br>
                    <p style="margin-top: 10px; color: #991b1b;">⚠️ กรุณาเปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบ!</p>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <h2 style="margin-bottom: 10px;">🎉 ติดตั้งสำเร็จ!</h2>
                    <p>ระบบได้รับการติดตั้งเรียบร้อยแล้ว</p>
                </div>
                <a href="public/" class="btn" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">
                    เข้าสู่ระบบ →
                </a>
                <div class="info-box" style="margin-top: 20px;">
                    <strong>ข้อมูลเข้าสู่ระบบ:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>password</code><br>
                    <p style="margin-top: 10px; color: #991b1b;">⚠️ กรุณาเปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบ!</p>
                    <p style="margin-top: 10px; font-size: 13px;">💡 ลบไฟล์ <code>install.php</code> เพื่อความปลอดภัย</p>
                </div>
            <?php else: ?>
                <h2 style="margin-bottom: 20px;">ข้อมูลฐานข้อมูล MySQL</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <strong>❌ เกิดข้อผิดพลาด:</strong><br>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="info-box">
                    <strong>📋 ก่อนเริ่มติดตั้ง:</strong><br>
                    1. สร้างฐานข้อมูล MySQL ใน DirectAdmin → MySQL Management<br>
                    2. จดข้อมูลฐานข้อมูลที่สร้าง<br>
                    3. กรอกข้อมูลด้านล่าง
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                        <div class="help-text">มักจะเป็น localhost</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" placeholder="subyaisite_edugrade" required>
                        <div class="help-text">ชื่อฐานข้อมูลที่สร้างใน MySQL Management</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" placeholder="subyaisite_user" required>
                        <div class="help-text">ชื่อผู้ใช้ฐานข้อมูล</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass" required>
                        <div class="help-text">รหัสผ่านฐานข้อมูล</div>
                    </div>
                    
                    <button type="submit" class="btn">🚀 เริ่มติดตั้ง</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
