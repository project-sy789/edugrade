<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo siteName(); ?> - สรุปการเข้าเรียน</title>
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
                <h2>สรุปการเข้าเรียน<?php echo $type === 'month' ? 'รายเดือน' : 'รายเทอม'; ?>: <?php echo htmlspecialchars($course['course_name']); ?></h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($course['course_code']); ?> | 
                    ปีการศึกษา <?php echo htmlspecialchars($course['academic_year']); ?> ภาคเรียนที่ <?php echo htmlspecialchars($course['semester']); ?>
                </p>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-end;">
                <?php if ($type === 'month'): ?>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="month">เลือกเดือน</label>
                        <input type="month" class="form-control" id="month" name="month" 
                               value="<?php echo htmlspecialchars($selectedMonth); ?>"
                               onchange="window.location.href='/teacher/courses/<?php echo $course['id']; ?>/attendance/summary?type=month&month=' + this.value"
                               style="max-width: 200px;">
                    </div>
                <?php else: ?>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="start_date">วันที่เริ่มต้น</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo htmlspecialchars($startDate); ?>"
                               style="max-width: 200px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="end_date">วันที่สิ้นสุด</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo htmlspecialchars($endDate); ?>"
                               style="max-width: 200px;">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="updateDateRange()">ค้นหา</button>
                <?php endif; ?>
            </div>
            
            <script>
                function updateDateRange() {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    window.location.href = '/teacher/courses/<?php echo $course['id']; ?>/attendance/summary?type=semester&start_date=' + startDate + '&end_date=' + endDate;
                }
            </script>
            
            <?php if (empty($students)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                    ไม่มีข้อมูลการเข้าเรียน
                </p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">เลขที่</th>
                                <th>รหัสนักเรียน</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th style="text-align: center;">มา</th>
                                <th style="text-align: center;">ขาด</th>
                                <th style="text-align: center;">ป่วย</th>
                                <th style="text-align: center;">ลา</th>
                                <th style="text-align: center;">สาย</th>
                                <th style="text-align: center;">รวม</th>
                                <th style="text-align: center;">% มาเรียน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($students as $student): 
                                $stats = $attendanceStats[$student['id']] ?? [
                                    'present' => 0,
                                    'absent' => 0,
                                    'sick' => 0,
                                    'leave' => 0,
                                    'late' => 0,
                                    'total' => 0,
                                    'percentage' => 0
                                ];
                            ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td style="text-align: center; background: #D1FAE5;"><?php echo $stats['present']; ?></td>
                                    <td style="text-align: center; background: #FEE2E2;"><?php echo $stats['absent']; ?></td>
                                    <td style="text-align: center; background: #DBEAFE;"><?php echo $stats['sick']; ?></td>
                                    <td style="text-align: center; background: #FEF3C7;"><?php echo $stats['leave']; ?></td>
                                    <td style="text-align: center; background: #E0E7FF;"><?php echo $stats['late']; ?></td>
                                    <td style="text-align: center; font-weight: 600;"><?php echo $stats['total']; ?></td>
                                    <td style="text-align: center; font-weight: 600; color: var(--primary-color);">
                                        <?php echo number_format($stats['percentage'], 1); ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <p style="margin-top: 1rem; color: var(--text-light);">
                    ทั้งหมด <?php echo count($students); ?> คน | 
                    ช่วงเวลา: <?php echo htmlspecialchars($periodText); ?>
                </p>
            <?php endif; ?>
            
            <div style="margin-top: 1.5rem;">
                <a href="/teacher/courses/<?php echo $course['id']; ?>/attendance" class="btn btn-secondary">กลับ</a>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
