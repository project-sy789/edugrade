<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <?php if (logoPath()): ?>
                    <img src="<?php echo logoPath(); ?>" alt="Logo" style="max-height: 80px; margin-bottom: 1rem;">
                <?php endif; ?>
                <?php 
                $schoolName = getSetting('school_name');
                if ($schoolName): 
                ?>
                    <h2 style="margin: 0 0 0.5rem 0; color: var(--primary-color); font-size: 1.5rem;">
                        <?php echo htmlspecialchars($schoolName); ?>
                    </h2>
                <?php endif; ?>
                <h1 class="login-title" style="margin: 0;"><?php echo siteName(); ?></h1>
            </div>
            
            <?php
            // Get flash message
            $flash = null;
            if (isset($_SESSION['flash'])) {
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
            }
            
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <form action="/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="form-group">
                    <label class="form-label" for="username">ชื่อผู้ใช้ / รหัสนักเรียน</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus 
                           placeholder="ครู: username | นักเรียน: รหัสนักเรียน">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">รหัสผ่าน / เลขบัตรประชาชน</label>
                    <input type="password" class="form-control" id="password" name="password" required
                           placeholder="ครู: password | นักเรียน: เลขบัตร 13 หลัก">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">เข้าสู่ระบบ</button>
            </form>
            
            <div class="text-center mt-3" style="font-size: 0.875rem; color: var(--text-light);">
                <p>ระบบแจ้งผลคะแนนและบันทึกเวลาเรียน</p>
                <p style="margin-top: 0.5rem; font-size: 0.75rem;">
                    💡 <strong>ครู:</strong> ใช้ username + password<br>
                    💡 <strong>นักเรียน:</strong> ใช้รหัสนักเรียน + เลขบัตรประชาชน
                </p>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
