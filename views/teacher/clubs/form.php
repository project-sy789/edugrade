<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - <?php echo $action === 'create' ? 'เพิ่มชุมนุม' : 'แก้ไขชุมนุม'; ?></title>
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
                <h2><?php echo $action === 'create' ? '➕ เพิ่มชุมนุม' : '✏️ แก้ไขชุมนุม'; ?></h2>
            </div>
            
            <form method="POST" action="<?php echo $action === 'create' ? '/teacher/clubs/store' : '/teacher/clubs/' . $club['id'] . '/update'; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                
                <div class="form-group">
                    <label for="club_name">ชื่อชุมนุม <span style="color: red;">*</span></label>
                    <input type="text" id="club_name" name="club_name" class="form-control" 
                           value="<?php echo htmlspecialchars($club['club_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">รายละเอียด</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($club['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="teacher_id">ครูผู้สอน <span style="color: red;">*</span></label>
                    <select id="teacher_id" name="teacher_id" class="form-control" required>
                        <option value="">-- เลือกครูผู้สอน --</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>" 
                                    <?php echo ($club['teacher_id'] ?? '') == $teacher['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($teacher['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="academic_year">ปีการศึกษา <span style="color: red;">*</span></label>
                    <input type="text" id="academic_year" name="academic_year" class="form-control" 
                           value="<?php echo htmlspecialchars($club['academic_year'] ?? (date('Y') + 543)); ?>" 
                           placeholder="2567" required>
                </div>
                
                <div class="form-group">
                    <label for="semester">ภาคเรียน <span style="color: red;">*</span></label>
                    <select id="semester" name="semester" class="form-control" required>
                        <option value="1" <?php echo ($club['semester'] ?? '') == 1 ? 'selected' : ''; ?>>ภาคเรียนที่ 1</option>
                        <option value="2" <?php echo ($club['semester'] ?? '') == 2 ? 'selected' : ''; ?>>ภาคเรียนที่ 2</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>ชั้นที่รับ <span style="color: red;">*</span></label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                        <?php 
                        $selectedLevels = $club['class_levels'] ?? [];
                        for ($i = 1; $i <= 6; $i++): 
                        ?>
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="class_levels[]" value="<?php echo $i; ?>"
                                       <?php echo in_array((string)$i, $selectedLevels) ? 'checked' : ''; ?>>
                                <span>ม.<?php echo $i; ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="max_students">จำนวนที่นั่ง <span style="color: red;">*</span></label>
                    <input type="number" id="max_students" name="max_students" class="form-control" 
                           value="<?php echo htmlspecialchars($club['max_students'] ?? 30); ?>" 
                           min="1" max="100" required>
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'create' ? '💾 บันทึก' : '💾 อัพเดท'; ?>
                    </button>
                    <a href="/teacher/clubs" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
