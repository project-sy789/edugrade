<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo siteName(); ?> - รายชื่อสมาชิกชุมนุม</title>
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
                <h2>👥 รายชื่อสมาชิก: <?php echo htmlspecialchars($club['club_name']); ?></h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">
                    ครูผู้สอน: <?php echo htmlspecialchars($club['teacher_name']); ?> | 
                    ปีการศึกษา: <?php echo htmlspecialchars($club['academic_year']); ?>/<?php echo $club['semester']; ?>
                </p>
            </div>
            
            <div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem;">
                <a href="/teacher/clubs" class="btn btn-secondary">← กลับ</a>
                <a href="/teacher/clubs/<?php echo $club['id']; ?>/grades" class="btn btn-primary">บันทึกคะแนน</a>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    ยังไม่มีนักเรียนลงทะเบียน
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
                            <th>คะแนน</th>
                            <th>วันที่ลงทะเบียน</th>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <th style="width: 100px;">จัดการ</th>
                            <?php endif; ?>
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
                                <td><?php echo $student['grade'] !== null ? number_format($student['grade'], 2) : '-'; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($student['enrolled_at'])); ?></td>
                                <td>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <button onclick="removeStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')" 
                                                class="btn btn-sm btn-danger">
                                            ลบ
                                        </button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p style="margin-top: 1rem; color: var(--text-light);">
                    ทั้งหมด <?php echo count($students); ?> คน (เต็ม <?php echo $club['max_students']; ?> คน)
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        function removeStudent(studentId, studentName) {
            if (!confirm('ต้องการลบ ' + studentName + ' ออกจากชุมนุมนี้ใช่หรือไม่?\n\nนักเรียนจะสามารถลงทะเบียนชุมนุมใหม่ได้')) {
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/teacher/clubs/<?php echo $club['id']; ?>/remove-student';
            
            const studentIdInput = document.createElement('input');
            studentIdInput.type = 'hidden';
            studentIdInput.name = 'student_id';
            studentIdInput.value = studentId;
            form.appendChild(studentIdInput);
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
