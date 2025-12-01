<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - บันทึกคะแนนชุมนุม</title>
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
                <h2>📝 บันทึกคะแนน: <?php echo htmlspecialchars($club['club_name']); ?></h2>
            </div>
            
            <form method="POST" action="/teacher/clubs/<?php echo $club['id']; ?>/grades/store">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                
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
                                <th style="width: 150px;">คะแนน</th>
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
                                    <td><?php echo htmlspecialchars($student['class_level']); ?>/<?php echo htmlspecialchars($student['classroom']); ?></td>
                                    <td>
                                        <input type="number" 
                                               name="grades[<?php echo $student['id']; ?>]" 
                                               class="form-control" 
                                               value="<?php echo $student['grade'] ?? ''; ?>" 
                                               min="0" 
                                               max="100" 
                                               step="0.01"
                                               placeholder="0-100">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                    <?php if (!empty($students)): ?>
                        <button type="submit" class="btn btn-primary">💾 บันทึกคะแนน</button>
                    <?php endif; ?>
                    <a href="/teacher/clubs/<?php echo $club['id']; ?>/members" class="btn btn-secondary">กลับ</a>
                </div>
            <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
