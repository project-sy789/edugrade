<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - นำเข้าข้อมูลนักเรียน</title>
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
        
        <?php if (isset($_SESSION['import_result'])): 
            $result = $_SESSION['import_result'];
            unset($_SESSION['import_result']);
        ?>
            <div class="card">
                <div class="card-header">
                    <h3>ผลการนำเข้าข้อมูล</h3>
                </div>
                <p><strong>สำเร็จ:</strong> <?php echo $result['success']; ?> คน</p>
                <p><strong>ล้มเหลว:</strong> <?php echo $result['failed']; ?> คน</p>
                <p><strong>ทั้งหมด:</strong> <?php echo $result['total']; ?> คน</p>
                
                <?php if (!empty($result['errors'])): ?>
                    <h4>รายการที่ล้มเหลว:</h4>
                    <ul>
                        <?php foreach ($result['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>นำเข้าข้อมูลนักเรียนจากไฟล์ XLSX</h2>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h3>รูปแบบไฟล์ Excel</h3>
                <p>ไฟล์ Excel ต้องมีคอลัมน์ดังนี้ (แถวแรกเป็นหัวตาราง):</p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>คอลัมน์ A</th>
                            <th>คอลัมน์ B</th>
                            <th>คอลัมน์ C</th>
                            <th>คอลัมน์ D</th>
                            <th>คอลัมน์ E</th>
                            <th>คอลัมน์ F</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>รหัสนักเรียน</td>
                            <td>เลขบัตรประชาชน</td>
                            <td>ชื่อ-นามสกุล</td>
                            <td>ชั้น</td>
                            <td>ห้อง</td>
                            <td>หมายเหตุ</td>
                        </tr>
                        <tr style="background: var(--bg-color);">
                            <td>S001</td>
                            <td>1234567890123</td>
                            <td>สมชาย ใจดี</td>
                            <td>ม.1</td>
                            <td>1</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <form method="POST" action="/teacher/students/upload" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="form-group">
                    <label class="form-label" for="xlsx_file">เลือกไฟล์ XLSX *</label>
                    <input type="file" class="form-control" id="xlsx_file" name="xlsx_file" 
                           accept=".xlsx" required>
                    <small style="color: var(--text-light);">ไฟล์ต้องเป็นนามสกุล .xlsx เท่านั้น (ขนาดไม่เกิน 10MB)</small>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">อัปโหลดและนำเข้า</button>
                    <a href="/teacher/students" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
