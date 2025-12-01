<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - บันทึกคะแนน</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .grade-table { width: 100%; border-collapse: collapse; }
        .grade-table th, .grade-table td { padding: 0.5rem; border: 1px solid var(--border-color); text-align: center; }
        .grade-table th { background: var(--bg-color); font-weight: 600; }
        .grade-table input { width: 80px; text-align: center; }
        .sticky-col { position: sticky; left: 0; background: white; z-index: 1; }
    </style>
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
                <h2>บันทึกคะแนน: <?php echo htmlspecialchars($course['course_name']); ?></h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($course['course_code']); ?> | 
                    ปีการศึกษา <?php echo htmlspecialchars($course['academic_year']); ?> ภาคเรียนที่ <?php echo htmlspecialchars($course['semester']); ?>
                </p>
            </div>
            
            <?php if (empty($categories)): ?>
                <div class="alert alert-warning">
                    กรุณาสร้างหมวดคะแนนก่อน <a href="/teacher/courses/<?php echo $course['id']; ?>/categories">คลิกที่นี่</a>
                </div>
            <?php elseif (empty($students)): ?>
                <div class="alert alert-warning">
                    ยังไม่มีนักเรียนลงทะเบียน <a href="/teacher/courses/<?php echo $course['id']; ?>/enroll">คลิกที่นี่เพื่อลงทะเบียน</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="grade-table">
                        <thead>
                            <tr>
                                <th class="sticky-col">รหัสนักเรียน</th>
                                <th class="sticky-col" style="left: 100px;">ชื่อ-นามสกุล</th>
                                <?php foreach ($categories as $category): ?>
                                    <th><?php echo htmlspecialchars($category['category_name']); ?><br>
                                        <small>(<?php echo $category['max_score']; ?>)</small>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="sticky-col"><?php echo htmlspecialchars($student['student_code']); ?></td>
                                    <td class="sticky-col" style="left: 100px; text-align: left;"><?php echo htmlspecialchars($student['name']); ?></td>
                                    <?php foreach ($categories as $category): 
                                        $gradeKey = $student['id'] . '_' . $category['id'];
                                        $currentGrade = $grades[$gradeKey] ?? null;
                                    ?>
                                        <td>
                                            <input type="number" 
                                                   class="form-control" 
                                                   step="0.01" 
                                                   min="0" 
                                                   max="<?php echo $category['max_score']; ?>"
                                                   value="<?php echo $currentGrade ? htmlspecialchars($currentGrade['score']) : ''; ?>"
                                                   oninput="console.log('Input changed!'); saveGrade(<?php echo $student['id']; ?>, <?php echo $course['id']; ?>, <?php echo $category['id']; ?>, this.value)"
                                                   placeholder="-">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <p style="margin-top: 1rem; color: var(--text-light);">
                    <small>* คะแนนจะถูกบันทึกอัตโนมัติเมื่อกรอกเสร็จ</small>
                </p>
            <?php endif; ?>
            
            <div style="margin-top: 1.5rem;">
                <a href="/teacher/courses" class="btn btn-secondary">กลับ</a>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        function saveGrade(studentId, courseId, categoryId, score) {
            const formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('course_id', courseId);
            formData.append('category_id', categoryId);
            formData.append('score', score);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            
            fetch('/api/save-grade', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>
