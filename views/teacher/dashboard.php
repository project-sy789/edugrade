<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - ครู</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <a href="/teacher/dashboard" class="logo">
                <img src="<?php echo logoPath(); ?>" alt="Logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                <?php echo siteName(); ?>
            </a>
            <nav>
                <ul class="nav">
                    <li><a href="/teacher/dashboard">หน้าหลัก</a></li>
                    <li><a href="/teacher/students">นักเรียน</a></li>
                    <li><a href="/teacher/courses">รายวิชา</a></li>
                    <li><a href="/teacher/clubs">ชุมนุม</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="/teacher/teachers">จัดการครู</a></li>
                        <li><a href="/admin/settings">⚙️ ตั้งค่า</a></li>
                    <?php endif; ?>
                    <li><span>สวัสดี, <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></span></li>
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
                <h2>หน้าหลักครู</h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <a href="/teacher/students" class="card" style="text-decoration: none; color: inherit;">
                    <h3>จัดการนักเรียน</h3>
                    <p style="color: var(--text-light);">เพิ่ม แก้ไข ลบ และนำเข้าข้อมูลนักเรียน</p>
                </a>
                
                <a href="/teacher/courses" class="card" style="text-decoration: none; color: inherit;">
                    <h3>จัดการรายวิชา</h3>
                    <p style="color: var(--text-light);">สร้างรายวิชา กำหนดหมวดคะแนน และลงทะเบียนนักเรียน</p>
                </a>
                
                <a href="/teacher/students/upload" class="card" style="text-decoration: none; color: inherit;">
                    <h3>นำเข้าข้อมูล XLSX</h3>
                    <p style="color: var(--text-light);">อัปโหลดไฟล์ Excel เพื่อเพิ่มนักเรียนหลายคน</p>
                </a>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/teacher/teachers" class="card" style="text-decoration: none; color: inherit; border: 2px solid #dc2626;">
                        <h3>👑 จัดการครู</h3>
                        <p style="color: var(--text-light);">เพิ่ม แก้ไข ลบบัญชีครู (Admin)</p>
                    </a>
                    
                    <a href="/teacher/clubs" class="card" style="text-decoration: none; color: inherit; border: 2px solid #8b5cf6;">
                        <h3>🎯 จัดการชุมนุม</h3>
                        <p style="color: var(--text-light);">สร้างชุมนุม ดูรายชื่อสมาชิก (Admin)</p>
                    </a>
                <?php else: ?>
                    <a href="/teacher/clubs" class="card" style="text-decoration: none; color: inherit;">
                        <h3>🎯 ชุมนุม</h3>
                        <p style="color: var(--text-light);">ดูรายชื่อสมาชิก บันทึกคะแนน</p>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
