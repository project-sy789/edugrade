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
                
                <?php if (isset($course)): ?>
                    <!-- Show teacher selector when EDITING (admin can change, others can see) -->
                    <div class="form-group">
                        <label class="form-label" for="teacher_id">ครูผู้สอน</label>
                        <select class="form-control" id="teacher_id" name="teacher_id">
                            <option value="">ยังไม่ระบุครูผู้สอน</option>
                            <?php
                            // Get all teachers using Database singleton (already loaded)
                            $db = \App\Models\Database::getInstance();
                            $teachers = $db->fetchAll('SELECT id, name, role FROM users ORDER BY name');
                            
                            // Debug: Show teacher count
                            echo '<!-- DEBUG: Found ' . count($teachers) . ' teachers -->';
                            echo '<!-- DEBUG: Course teacher_id = ' . ($course['teacher_id'] ?? 'NOT SET') . ' -->';
                            
                            foreach ($teachers as $teacher):
                            ?>
                                <option value="<?php echo $teacher['id']; ?>" 
                                    <?php 
                                    // Ensure both values are integers for comparison
                                    $isSelected = isset($course['teacher_id']) && (int)$course['teacher_id'] === (int)$teacher['id'];
                                    echo $isSelected ? 'selected' : ''; 
                                    // Debug output
                                    echo '<!-- Teacher ID: ' . $teacher['id'] . ' | Match: ' . ($isSelected ? 'YES' : 'NO') . ' -->';
                                    ?>>
                                    <?php echo htmlspecialchars($teacher['name']); ?> 
                                    (<?php echo $teacher['role'] === 'admin' ? 'Admin' : 'ครู'; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
                            <small style="color: var(--text-light);">เฉพาะ admin เท่านั้นที่สามารถเปลี่ยนครูผู้สอนได้</small>
                        <?php else: ?>
                            <small style="color: var(--text-light);">เปลี่ยนครูที่รับผิดชอบวิชานี้</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
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
