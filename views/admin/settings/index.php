<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - ตั้งค่าเว็บไซต์</title>
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
                        <li><a href="/admin/settings" class="active">ตั้งค่า</a></li>
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
                <h2>⚙️ ตั้งค่าเว็บไซต์</h2>
            </div>
            
            <!-- Website Information -->
            <form method="POST" action="/admin/settings/update" style="margin-bottom: 2rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                
                <h3 style="margin-bottom: 1rem; color: var(--primary);">📝 ข้อมูลเว็บไซต์</h3>
                
                <div class="form-group">
                    <label class="form-label" for="site_name">ชื่อเว็บไซต์</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" 
                           value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                    <small style="color: var(--text-light);">ชื่อที่แสดงบน header และ title ของเว็บไซต์</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="school_name">ชื่อโรงเรียน</label>
                    <input type="text" class="form-control" id="school_name" name="school_name" 
                           value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
                    <small style="color: var(--text-light);">ชื่อโรงเรียนที่แสดงในเอกสารและรายงาน</small>
                </div>
                
                <button type="submit" class="btn btn-primary">💾 บันทึกข้อมูล</button>
            </form>
            
            <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">
            
            <!-- Logo Upload -->
            <form method="POST" action="/admin/settings/upload-logo" enctype="multipart/form-data" style="margin-bottom: 2rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                
                <h3 style="margin-bottom: 1rem; color: var(--primary);">🖼️ Logo</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                    <div>
                        <div style="border: 2px dashed var(--border); border-radius: 8px; padding: 1rem; text-align: center; background: var(--bg-light);">
                            <?php if (!empty($settings['logo_path']) && file_exists(__DIR__ . '/../../../public' . $settings['logo_path'])): ?>
                                <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" 
                                     alt="Logo" 
                                     style="max-width: 100%; max-height: 150px; object-fit: contain;">
                            <?php else: ?>
                                <div style="padding: 2rem; color: var(--text-light);">
                                    <p style="margin: 0;">ยังไม่มี Logo</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--text-light); text-align: center;">
                            Logo ปัจจุบัน
                        </p>
                    </div>
                    
                    <div>
                        <div class="form-group">
                            <label class="form-label" for="logo">เลือกไฟล์ Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg" required>
                            <small style="color: var(--text-light);">
                                รองรับ: PNG, JPG, JPEG | ขนาดสูงสุด: 2 MB
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">📤 อัปโหลด Logo</button>
                    </div>
                </div>
            </form>
            
            <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">
            
            <!-- Favicon Upload -->
            <form method="POST" action="/admin/settings/upload-favicon" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                
                <h3 style="margin-bottom: 1rem; color: var(--primary);">⭐ Favicon</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                    <div>
                        <div style="border: 2px dashed var(--border); border-radius: 8px; padding: 1rem; text-align: center; background: var(--bg-light);">
                            <?php if (!empty($settings['favicon_path']) && file_exists(__DIR__ . '/../../../public' . $settings['favicon_path'])): ?>
                                <img src="<?php echo htmlspecialchars($settings['favicon_path']); ?>" 
                                     alt="Favicon" 
                                     style="max-width: 64px; max-height: 64px; object-fit: contain;">
                            <?php else: ?>
                                <div style="padding: 1.5rem; color: var(--text-light);">
                                    <p style="margin: 0;">ยังไม่มี Favicon</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--text-light); text-align: center;">
                            Favicon ปัจจุบัน
                        </p>
                    </div>
                    
                    <div>
                        <div class="form-group">
                            <label class="form-label" for="favicon">เลือกไฟล์ Favicon</label>
                            <input type="file" class="form-control" id="favicon" name="favicon" accept="image/x-icon,image/png,.ico" required>
                            <small style="color: var(--text-light);">
                                รองรับ: ICO, PNG | ขนาดสูงสุด: 500 KB | แนะนำ: 32x32 หรือ 64x64 px
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">📤 อัปโหลด Favicon</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
