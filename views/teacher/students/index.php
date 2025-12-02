<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - จัดการนักเรียน</title>
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
                <h2>จัดการนักเรียน</h2>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <form method="GET" action="/teacher/students" style="flex: 1; max-width: 400px;">
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="search" class="form-control" placeholder="ค้นหา (รหัส, ชื่อ, เลขบัตร)" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <button type="submit" class="btn btn-primary">ค้นหา</button>
                    </div>
                </form>
                
                <div style="display: flex; gap: 0.5rem;">
                    <a href="/teacher/students/upload" class="btn btn-secondary">นำเข้า XLSX</a>
                    <a href="/teacher/students/create" class="btn btn-primary">เพิ่มนักเรียน</a>
                </div>
            </div>
            
            <?php if (empty($students)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                    ไม่พบข้อมูลนักเรียน
                </p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสนักเรียน</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ชั้น</th>
                            <th>ห้อง</th>
                            <th>เลขบัตรประชาชน</th>
                            <th>หมายเหตุ</th>
                            <th style="text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['class_level']); ?></td>
                                <td><?php echo htmlspecialchars($student['classroom']); ?></td>
                                <td><?php echo htmlspecialchars($student['id_card']); ?></td>
                                <td><?php echo htmlspecialchars($student['notes'] ?? '-'); ?></td>
                                <td style="text-align: center;">
                                    <a href="/teacher/students/edit/<?php echo $student['id']; ?>" class="btn btn-sm btn-primary">แก้ไข</a>
                                    <button onclick="deleteStudent(<?php echo $student['id']; ?>)" class="btn btn-sm btn-danger">ลบ</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: var(--text-light); margin: 0;">
                        แสดง <?php echo count($students); ?> รายการ จากทั้งหมด <?php echo $totalStudents; ?> คน
                    </p>
                    
                    <?php if ($totalPages > 1): ?>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <?php if ($currentPage > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" class="btn btn-sm btn-secondary">« ก่อนหน้า</a>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            if ($startPage > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="btn btn-sm btn-secondary">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span style="padding: 0 0.5rem;">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="btn btn-sm btn-primary" style="cursor: default;"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="btn btn-sm btn-secondary"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span style="padding: 0 0.5rem;">...</span>
                                <?php endif; ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>" class="btn btn-sm btn-secondary"><?php echo $totalPages; ?></a>
                            <?php endif; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" class="btn btn-sm btn-secondary">ถัดไป »</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        function deleteStudent(id) {
            if (!confirm('คุณแน่ใจหรือไม่ที่จะลบนักเรียนคนนี้?')) {
                return;
            }
            
            fetch('/teacher/students/delete/' + id, {
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
                    alert(data.message || 'ลบนักเรียนสำเร็จ');
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบนักเรียนได้'));
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('เกิดข้อผิดพลาด: ' + (error.message || 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์'));
            });
        }
    </script>
</body>
</html>
