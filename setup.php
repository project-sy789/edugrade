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
        .content { padding: 30px; }
        h1 { font-size: 24px; margin-bottom: 10px; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        .btn:hover { background: #135e96; }
        .success { background: #00a32a; padding: 15px; border-radius: 4px; color: white; margin: 20px 0; }
        .error { background: #d63638; padding: 15px; border-radius: 4px; color: white; margin: 20px 0; }
        .info { background: #f0f6fc; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #2271b1; }
        code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 ติดตั้ง EduGrade</h1>
            <p>ระบบจัดการคะแนนและเวลาเรียน</p>
        </div>
        <div class="content">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Create folders
                    $folders = ['database', 'uploads', 'uploads/logos', 'sessions', 'logs'];
                    foreach ($folders as $folder) {
                        if (!file_exists($folder)) {
                            @mkdir($folder, 0755, true);
                        }
                        @chmod($folder, 0755);
                    }
                    
                    // Create database
                    $dbFile = 'database/score.db';
                    if (file_exists($dbFile)) {
                        @unlink($dbFile);
                    }
                    
                    $schemaFile = 'database/schema_sqlite.sql';
                    if (!file_exists($schemaFile)) {
                        throw new Exception("ไม่พบไฟล์ schema");
                    }
                    
                    $schema = file_get_contents($schemaFile);
                    $db = new SQLite3($dbFile);
                    $db->exec($schema);
                    
                    // Create admin user
                    $password = password_hash('password', PASSWORD_BCRYPT);
                    $stmt = $db->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
                    $stmt->bindValue(1, 'admin', SQLITE3_TEXT);
                    $stmt->bindValue(2, $password, SQLITE3_TEXT);
                    $stmt->bindValue(3, 'ผู้ดูแลระบบ', SQLITE3_TEXT);
                    $stmt->bindValue(4, 'admin', SQLITE3_TEXT);
                    $stmt->execute();
                    $db->close();
                    
                    echo '<div class="success">';
                    echo '<h2 style="margin-bottom: 15px;">✅ ติดตั้งสำเร็จ!</h2>';
                    echo '<p><strong>ข้อมูลเข้าสู่ระบบ:</strong></p>';
                    echo '<p>Username: <code>admin</code></p>';
                    echo '<p>Password: <code>password</code></p>';
                    echo '<p style="margin-top: 15px; font-size: 14px;">⚠️ กรุณาเปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบ!</p>';
                    echo '</div>';
                    echo '<a href="public/" class="btn">เข้าสู่ระบบ →</a>';
                    echo '<div class="info" style="margin-top: 20px; font-size: 14px;">';
                    echo '<strong>💡 สำคัญ:</strong> ลบไฟล์ <code>setup.php</code> ทันทีเพื่อความปลอดภัย';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    echo '<button class="btn" onclick="location.reload()">ลองอีกครั้ง</button>';
                }
            } else {
                // Show install button
                $dbExists = file_exists('database/score.db');
                ?>
                
                <?php if ($dbExists): ?>
                    <div class="info">
                        <strong>⚠️ พบฐานข้อมูลเดิม</strong><br>
                        การติดตั้งใหม่จะลบข้อมูลเดิมทั้งหมด
                    </div>
                <?php endif; ?>
                
                <div class="info">
                    <h3 style="margin-bottom: 10px;">📋 ข้อมูลที่จะได้รับ:</h3>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Username: <code>admin</code></li>
                        <li>Password: <code>password</code></li>
                    </ul>
                </div>
                
                <form method="POST" style="margin-top: 30px;">
                    <button type="submit" class="btn">
                        <?php echo $dbExists ? '🔄 ติดตั้งใหม่' : '🚀 เริ่มติดตั้ง'; ?>
                    </button>
                </form>
                
                <div class="info" style="margin-top: 20px; font-size: 14px;">
                    <strong>💡 หมายเหตุ:</strong> การติดตั้งจะใช้เวลาประมาณ 5-10 วินาที
                </div>
                
            <?php } ?>
        </div>
    </div>
</body>
</html>
