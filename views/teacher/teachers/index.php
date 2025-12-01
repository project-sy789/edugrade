<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - จัดการครู</title>
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
        $flash = $this->getFlash();
        if ($flash):
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>👥 จัดการครู</h2>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <form method="GET" action="/teacher/teachers" style="flex: 1; max-width: 400px;">
                    <div class="form-group" style="margin: 0;">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="ค้นหาชื่อผู้ใช้หรือชื่อ-นามสกุล..." 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
                </form>
                
                <a href="/teacher/teachers/create" class="btn btn-primary">+ เพิ่มครู/ผู้ดูแลระบบ</a>
            </div>
            
            <?php if (empty($teachers)): ?>
                <div class="alert alert-info">
                    ไม่พบข้อมูลครู
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>บทบาท</th>
                            <th style="width: 180px;">วันที่สร้าง</th>
                            <th style="width: 150px; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($teachers as $teacher): 
                        ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                <td>
                                    <?php if ($teacher['role'] === 'admin'): ?>
                                        <span class="badge" style="background: #dc2626; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">👑 Admin</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #2563eb; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">👨‍🏫 Teacher</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($teacher['created_at'])); ?></td>
                                <td style="text-align: center;">
                                    <a href="/teacher/teachers/<?php echo $teacher['id']; ?>/edit" class="btn btn-sm btn-secondary">แก้ไข</a>
                                    <form method="POST" action="/teacher/teachers/<?php echo $teacher['id']; ?>/delete" style="display: inline;" onsubmit="return confirm('ต้องการลบผู้ใช้นี้ใช่หรือไม่?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div style="margin-top: 1.5rem;">
                <a href="/teacher/dashboard" class="btn btn-secondary">กลับ</a>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
