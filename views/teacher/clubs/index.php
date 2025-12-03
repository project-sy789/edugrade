<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - จัดการชุมนุม</title>
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
                <h2>🎯 จัดการชุมนุม</h2>
            </div>
            
            <div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem;">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/teacher/clubs/create" class="btn btn-primary">+ สร้างชุมนุมใหม่</a>
                <?php endif; ?>
                <a href="/teacher/clubs/summary" class="btn btn-secondary">📊 สรุปการลงทะเบียน</a>
            </div>
            
            <?php if (empty($clubs)): ?>
                <div class="alert alert-info">
                    ยังไม่มีชุมนุม
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>ชื่อชุมนุม</th>
                            <th>ครูผู้สอน</th>
                            <th>ปีการศึกษา</th>
                            <th>ภาคเรียน</th>
                            <th>จำนวนนักเรียน</th>
                            <th style="width: 200px; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = ($currentPage - 1) * 20 + 1;
                        foreach ($clubs as $club): 
                        ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($club['club_name']); ?></td>
                                <td><?php echo htmlspecialchars($club['teacher_name']); ?></td>
                                <td><?php echo htmlspecialchars($club['academic_year']); ?></td>
                                <td><?php echo htmlspecialchars($club['semester'] ?? '1'); ?></td>
                                <td><?php echo ($club['enrolled_count'] ?? 0); ?> / <?php echo ($club['max_students'] ?? 30); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 0.25rem; justify-content: center; flex-wrap: wrap;">
                                        <a href="/teacher/clubs/<?php echo $club['id']; ?>/members" class="btn btn-sm btn-primary">รายชื่อ</a>
                                        <a href="/teacher/clubs/<?php echo $club['id']; ?>/grades" class="btn btn-sm btn-primary">คะแนน</a>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                            <a href="/teacher/clubs/<?php echo $club['id']; ?>/edit" class="btn btn-sm btn-secondary">แก้ไข</a>
                                            <button onclick="deleteClub(<?php echo $club['id']; ?>)" class="btn btn-sm btn-danger">ลบ</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: var(--text-light); margin: 0;">
                        แสดง <?php echo count($clubs); ?> รายการ จากทั้งหมด <?php echo $totalClubs; ?> ชุมนุม
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
        function deleteClub(id) {
            if (!confirm('คุณแน่ใจหรือไม่ที่จะลบชุมนุมนี้? ข้อมูลการลงทะเบียนทั้งหมดจะถูกลบด้วย')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            
            fetch('/teacher/clubs/' + id + '/delete', {
                method: 'POST',
                body: formData
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
