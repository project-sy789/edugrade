<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - จัดการรายวิชา</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
        // Get flash message
        $flash = null;
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        
        if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>จัดการรายวิชา</h2>
            </div>
            
            <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
                <a href="/teacher/courses/create" class="btn btn-primary">เพิ่มรายวิชา</a>
            </div>
            
            <?php if (empty($courses)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                    ยังไม่มีรายวิชา กรุณาเพิ่มรายวิชาใหม่
                </p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสวิชา</th>
                            <th>ชื่อวิชา</th>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <th>ครูผู้สอน</th>
                            <?php endif; ?>
                            <th>ปีการศึกษา</th>
                            <th>ภาคเรียน</th>
                            <th style="text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <td>
                                        <?php 
                                        if (!empty($course['teacher_name'])) {
                                            echo htmlspecialchars($course['teacher_name']);
                                        } else {
                                            echo '<span style="color: var(--text-light);">ยังไม่ระบุ</span>';
                                        }
                                        ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($course['academic_year']); ?></td>
                                <td><?php echo htmlspecialchars($course['semester']); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 0.25rem; justify-content: center; flex-wrap: wrap;">
                                        <a href="/teacher/courses/<?php echo $course['id']; ?>/enroll" class="btn btn-sm btn-secondary" title="ลงทะเบียนนักเรียน">ลงทะเบียน</a>
                                        <a href="/teacher/courses/<?php echo $course['id']; ?>/categories" class="btn btn-sm btn-secondary" title="จัดการหมวดคะแนน">หมวดคะแนน</a>
                                        <a href="/teacher/courses/<?php echo $course['id']; ?>/grades" class="btn btn-sm btn-primary" title="บันทึกคะแนน">คะแนน</a>
                                        <a href="/teacher/courses/<?php echo $course['id']; ?>/attendance" class="btn btn-sm btn-primary" title="บันทึกการเข้าเรียน">เข้าเรียน</a>
                                        <a href="/teacher/courses/edit/<?php echo $course['id']; ?>" class="btn btn-sm btn-primary" title="แก้ไข">แก้ไข</a>
                                        <button onclick="deleteCourse(<?php echo $course['id']; ?>)" class="btn btn-sm btn-danger" title="ลบ">ลบ</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: var(--text-light); margin: 0;">
                        แสดง <?php echo count($courses); ?> รายการ จากทั้งหมด <?php echo $totalCourses; ?> รายวิชา
                    </p>
                    
                    <?php if ($totalPages > 1): ?>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=<?php echo $currentPage - 1; ?>" class="btn btn-sm btn-secondary">« ก่อนหน้า</a>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            if ($startPage > 1): ?>
                                <a href="?page=1" class="btn btn-sm btn-secondary">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span style="padding: 0 0.5rem;">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="btn btn-sm btn-primary" style="cursor: default;"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>" class="btn btn-sm btn-secondary"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span style="padding: 0 0.5rem;">...</span>
                                <?php endif; ?>
                                <a href="?page=<?php echo $totalPages; ?>" class="btn btn-sm btn-secondary"><?php echo $totalPages; ?></a>
                            <?php endif; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?>" class="btn btn-sm btn-secondary">ถัดไป »</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        function deleteCourse(id) {
            if (!confirm('คุณแน่ใจหรือไม่ที่จะลบรายวิชานี้? ข้อมูลคะแนนและการเข้าเรียนทั้งหมดจะถูกลบด้วย')) {
                return;
            }
            
            fetch('/teacher/courses/delete/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                alert('เกิดข้อผิดพลาด: ' + error.message);
            });
        }
    </script>
</body>
</html>
