<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - นักเรียน</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <a href="/student/dashboard" class="logo">
                
                    <img src="<?php echo logoPath(); ?>" alt="Logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                
                <?php echo siteName(); ?>
            </a>
            <nav>
                <ul class="nav">
                    <li><a href="/student/dashboard">หน้าหลัก</a></li>
                    <li><a href="/student/grades">ผลคะแนน</a></li>
                    <li><a href="/student/attendance">เวลาเรียน</a></li>
                    <li><a href="/student/clubs">ชุมนุม</a></li>
                    <li><span>สวัสดี, <?php echo htmlspecialchars($_SESSION['student_name'] ?? ''); ?></span></li>
                    <li><a href="/logout">ออกจากระบบ</a></li>
                </ul>
            </nav>
        </div>
    </div>
    
    <div class="container">
        <?php
        // Get flash message
        $flash = null;
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        
        if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>หน้าหลักนักเรียน</h2>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <p><strong>รหัสนักเรียน:</strong> <?php echo htmlspecialchars($_SESSION['student_code'] ?? ''); ?></p>
                <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($_SESSION['student_name'] ?? ''); ?></p>
                <p><strong>ชั้น:</strong> <?php echo htmlspecialchars($_SESSION['class_level'] ?? ''); ?>/<?php echo htmlspecialchars($_SESSION['classroom'] ?? ''); ?></p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <a href="/student/grades" class="card" style="text-decoration: none; color: inherit;">
                    <h3>ดูผลคะแนน</h3>
                    <p style="color: var(--text-light);">ตรวจสอบคะแนนของคุณในแต่ละรายวิชา</p>
                </a>
                
                <a href="/student/attendance" class="card" style="text-decoration: none; color: inherit;">
                    <h3>เวลาเรียน</h3>
                    <p style="color: var(--text-light);">ดูบันทึกเวลาเรียนของคุณ</p>
                </a>
                
                <a href="/student/clubs" class="card" style="text-decoration: none; color: inherit;">
                    <h3>🎯 ลงทะเบียนชุมนุม</h3>
                    <p style="color: var(--text-light);">เลือกชุมนุมที่สนใจ</p>
                </a>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
