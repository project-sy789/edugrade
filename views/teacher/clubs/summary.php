<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo siteName(); ?> - สรุปการลงทะเบียนชุมนุม</title>
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
                <h2>📊 สรุปการลงทะเบียนชุมนุม</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">
                    ปีการศึกษา: <?php echo $academicYear; ?>/<?php echo $semester; ?>
                </p>
            </div>
            
            <!-- Filter by class -->
            <div style="margin-bottom: 1.5rem;">
                <form method="GET" action="/teacher/clubs/summary" style="display: flex; gap: 1rem; align-items: end;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">ชั้น:</label>
                        <select name="class_level" class="form-control" style="width: 150px;">
                            <option value="">ทุกชั้น</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($selectedClass == $i) ? 'selected' : ''; ?>>
                                    ม.<?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">ห้อง:</label>
                        <input type="text" name="classroom" class="form-control" style="width: 100px;" 
                               value="<?php echo htmlspecialchars($selectedClassroom); ?>" placeholder="เช่น 1">
                    </div>
                    <button type="submit" class="btn btn-primary">🔍 ค้นหา</button>
                    <a href="/teacher/clubs/summary" class="btn btn-secondary">ล้างตัวกรอง</a>
                </form>
            </div>
            
            <!-- Summary Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo $stats['total']; ?></h3>
                    <p style="margin: 0; opacity: 0.9;">นักเรียนทั้งหมด</p>
                </div>
                <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo $stats['enrolled']; ?></h3>
                    <p style="margin: 0; opacity: 0.9;">ลงทะเบียนแล้ว</p>
                </div>
                <div class="card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo $stats['not_enrolled']; ?></h3>
                    <p style="margin: 0; opacity: 0.9;">ยังไม่ลงทะเบียน</p>
                </div>
                <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo $stats['percentage']; ?>%</h3>
                    <p style="margin: 0; opacity: 0.9;">อัตราการลงทะเบียน</p>
                </div>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    ไม่พบข้อมูลนักเรียน
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>รหัสนักเรียน</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ชั้น</th>
                            <th>ห้อง</th>
                            <th>สถานะ</th>
                            <th>ชุมนุม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($students as $student): 
                        ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['class_level']); ?></td>
                                <td><?php echo htmlspecialchars($student['classroom']); ?></td>
                                <td>
                                    <?php if ($student['club_name']): ?>
                                        <span class="badge" style="background: #10b981; color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">
                                            ✅ ลงทะเบียนแล้ว
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #ef4444; color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">
                                            ❌ ยังไม่ลงทะเบียน
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['club_name']): ?>
                                        <?php echo htmlspecialchars($student['club_name']); ?>
                                        <br><small style="color: var(--text-light);">ครู: <?php echo htmlspecialchars($student['teacher_name']); ?></small>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p style="margin-top: 1rem; color: var(--text-light);">
                    แสดง <?php echo count($students); ?> รายการ
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
