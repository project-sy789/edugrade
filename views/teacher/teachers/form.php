<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - <?php echo $action === 'create' ? 'เพิ่มครู/ผู้ดูแลระบบ' : 'แก้ไขข้อมูล'; ?></title>
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
                <h2><?php echo $action === 'create' ? '➕ เพิ่มครู/ผู้ดูแลระบบ' : '✏️ แก้ไขข้อมูล'; ?></h2>
            </div>
            
            <form method="POST" action="<?php echo $action === 'create' ? '/teacher/teachers/store' : '/teacher/teachers/' . $teacher['id'] . '/update'; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                
                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้ <span style="color: red;">*</span></label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($teacher['username'] ?? ''); ?>" 
                           required>
                    <small class="form-text">ใช้สำหรับเข้าสู่ระบบ (ห้ามซ้ำ)</small>
                </div>
                
                <div class="form-group">
                    <label for="password">รหัสผ่าน <?php echo $action === 'create' ? '<span style="color: red;">*</span>' : ''; ?></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           <?php echo $action === 'create' ? 'required' : ''; ?>>
                    <small class="form-text">
                        <?php if ($action === 'create'): ?>
                            รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร
                        <?php else: ?>
                            เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน
                        <?php endif; ?>
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="name">ชื่อ-นามสกุล <span style="color: red;">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($teacher['name'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="role">บทบาท <span style="color: red;">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="teacher" <?php echo ($teacher['role'] ?? 'teacher') === 'teacher' ? 'selected' : ''; ?>>👨‍🏫 ครู (Teacher)</option>
                        <option value="admin" <?php echo ($teacher['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>👑 ผู้ดูแลระบบ (Admin)</option>
                    </select>
                    <small class="form-text">ครู: จัดการนักเรียน รายวิชา คะแนน | Admin: จัดการครูและดูข้อมูลทั้งหมด</small>
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'create' ? '💾 บันทึก' : '💾 อัพเดท'; ?>
                    </button>
                    <a href="/teacher/teachers" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
