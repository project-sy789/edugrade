<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - <?php echo isset($student) ? 'แก้ไขนักเรียน' : 'เพิ่มนักเรียน'; ?></title>
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
        <div class="card">
            <div class="card-header">
                <h2><?php echo isset($student) ? 'แก้ไขข้อมูลนักเรียน' : 'เพิ่มนักเรียน'; ?></h2>
            </div>
            
            <form method="POST" action="<?php echo isset($student) ? '/teacher/students/edit/' . $student['id'] : '/teacher/students/create'; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="form-group">
                    <label class="form-label" for="student_code">รหัสนักเรียน *</label>
                    <input type="text" class="form-control" id="student_code" name="student_code" 
                           value="<?php echo htmlspecialchars($student['student_code'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="id_card">เลขบัตรประชาชน (13 หลัก) *</label>
                    <input type="text" class="form-control" id="id_card" name="id_card" 
                           maxlength="13" pattern="\d{13}"
                           value="<?php echo htmlspecialchars($student['id_card'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="name">ชื่อ-นามสกุล *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="class_level">ชั้น *</label>
                        <input type="text" class="form-control" id="class_level" name="class_level" 
                               placeholder="เช่น ม.1, ม.2"
                               value="<?php echo htmlspecialchars($student['class_level'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="classroom">ห้อง *</label>
                        <input type="text" class="form-control" id="classroom" name="classroom" 
                               placeholder="เช่น 1, 2, 3"
                               value="<?php echo htmlspecialchars($student['classroom'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="notes">หมายเหตุ</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($student['notes'] ?? ''); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="/teacher/students" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
