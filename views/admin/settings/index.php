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
                            <?php if (!empty($settings['site_logo']) && file_exists(__DIR__ . '/../../../public' . $settings['site_logo'])): ?>
                                <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" 
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
                            <?php if (!empty($settings['site_favicon']) && file_exists(__DIR__ . '/../../../public' . $settings['site_favicon'])): ?>
                                <img src="<?php echo htmlspecialchars($settings['site_favicon']); ?>" 
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
            
            <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">
            
            <!-- Club Registration Period Settings -->
            <form method="POST" action="/admin/settings/update-club-registration" id="clubRegistrationForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                
                <h3 style="margin-bottom: 1rem; color: var(--primary);">🎯 การลงทะเบียนชุมนุม</h3>
                
                <div class="form-group">
                    <label class="form-label">โหมดการควบคุม</label>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="club_registration_mode" value="manual" 
                                   <?php echo ($settings['club_registration_mode'] ?? 'manual') === 'manual' ? 'checked' : ''; ?>
                                   onchange="toggleRegistrationMode()">
                            <span style="margin-left: 0.5rem;">📝 Manual (เปิด-ปิดด้วยมือ)</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="club_registration_mode" value="automatic" 
                                   <?php echo ($settings['club_registration_mode'] ?? 'manual') === 'automatic' ? 'checked' : ''; ?>
                                   onchange="toggleRegistrationMode()">
                            <span style="margin-left: 0.5rem;">⏰ Automatic (ตามกำหนดเวลา)</span>
                        </label>
                    </div>
                </div>
                
                <!-- Manual Mode Controls -->
                <div id="manualControls" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">สถานะการรับสมัคร</label>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <label class="switch">
                                <input type="checkbox" name="club_registration_manual_status" value="1"
                                       <?php echo ($settings['club_registration_manual_status'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span id="statusText" style="font-weight: 500;">
                                <?php echo ($settings['club_registration_manual_status'] ?? '0') === '1' ? '🟢 เปิดรับสมัคร' : '🔴 ปิดรับสมัคร'; ?>
                            </span>
                        </div>
                        <small style="color: var(--text-light);">เปิด/ปิดการลงทะเบียนชุมนุมได้ทันที</small>
                    </div>
                </div>
                
                <!-- Automatic Mode Controls -->
                <div id="automaticControls" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="club_registration_start">วันเวลาเริ่มต้น</label>
                        <input type="datetime-local" class="form-control" id="club_registration_start" 
                               name="club_registration_start" 
                               value="<?php echo isset($settings['club_registration_start']) ? date('Y-m-d\TH:i', strtotime($settings['club_registration_start'])) : ''; ?>">
                        <small style="color: var(--text-light);">นักเรียนจะสามารถลงทะเบียนได้ตั้งแต่เวลานี้</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="club_registration_end">วันเวลาสิ้นสุด</label>
                        <input type="datetime-local" class="form-control" id="club_registration_end" 
                               name="club_registration_end" 
                               value="<?php echo isset($settings['club_registration_end']) ? date('Y-m-d\TH:i', strtotime($settings['club_registration_end'])) : ''; ?>">
                        <small style="color: var(--text-light);">นักเรียนจะไม่สามารถลงทะเบียนได้หลังจากเวลานี้</small>
                    </div>
                </div>
                
                <!-- Current Status Display -->
                <div style="background: var(--bg-light); padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                    <strong>สถานะปัจจุบัน:</strong>
                    <?php
                    $regStatus = $settingsModel->getClubRegistrationStatus();
                    $statusColor = $regStatus['open'] ? 'var(--success)' : 'var(--danger)';
                    $statusIcon = $regStatus['open'] ? '✅' : '❌';
                    ?>
                    <div style="margin-top: 0.5rem; color: <?php echo $statusColor; ?>; font-weight: 500;">
                        <?php echo $statusIcon . ' ' . htmlspecialchars($regStatus['message']); ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">💾 บันทึกการตั้งค่า</button>
            </form>
        </div>
    </div>
    
    <style>
    /* Toggle Switch Styles */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: var(--success);
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    </style>
    
    <script>
    // Toggle between manual and automatic mode
    function toggleRegistrationMode() {
        const mode = document.querySelector('input[name="club_registration_mode"]:checked').value;
        const manualControls = document.getElementById('manualControls');
        const automaticControls = document.getElementById('automaticControls');
        
        if (mode === 'manual') {
            manualControls.style.display = 'block';
            automaticControls.style.display = 'none';
        } else {
            manualControls.style.display = 'none';
            automaticControls.style.display = 'block';
        }
    }
    
    // Update status text when toggle changes
    document.addEventListener('DOMContentLoaded', function() {
        toggleRegistrationMode();
        
        const statusToggle = document.querySelector('input[name="club_registration_manual_status"]');
        const statusText = document.getElementById('statusText');
        
        if (statusToggle && statusText) {
            statusToggle.addEventListener('change', function() {
                statusText.textContent = this.checked ? '🟢 เปิดรับสมัคร' : '🔴 ปิดรับสมัคร';
            });
        }
    });
    </script>
    
    <script src="/js/main.js"></script>
</body>
</html>
