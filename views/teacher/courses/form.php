<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - <?php echo isset($course) ? 'แก้ไขรายวิชา' : 'เพิ่มรายวิชา'; ?></title>
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
                <h2><?php echo isset($course) ? 'แก้ไขรายวิชา' : 'เพิ่มรายวิชา'; ?></h2>
            </div>
            
            <form method="POST" action="<?php echo isset($course) ? '/teacher/courses/edit/' . $course['id'] : '/teacher/courses/create'; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="form-group">
                    <label class="form-label" for="course_code">รหัสวิชา *</label>
                    <input type="text" class="form-control" id="course_code" name="course_code" 
                           value="<?php echo htmlspecialchars($course['course_code'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="course_name">ชื่อวิชา *</label>
                    <input type="text" class="form-control" id="course_name" name="course_name" 
                           value="<?php echo htmlspecialchars($course['course_name'] ?? ''); ?>" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="academic_year">ปีการศึกษา *</label>
                        <input type="text" class="form-control" id="academic_year" name="academic_year" 
                               placeholder="เช่น 2567"
                               value="<?php echo htmlspecialchars($course['academic_year'] ?? date('Y') + 543); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="semester">ภาคเรียน *</label>
                        <select class="form-control" id="semester" name="semester" required>
                            <option value="">เลือกภาคเรียน</option>
                            <option value="1" <?php echo (isset($course) && $course['semester'] == 1) ? 'selected' : ''; ?>>ภาคเรียนที่ 1</option>
                            <option value="2" <?php echo (isset($course) && $course['semester'] == 2) ? 'selected' : ''; ?>>ภาคเรียนที่ 2</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="/teacher/courses" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
