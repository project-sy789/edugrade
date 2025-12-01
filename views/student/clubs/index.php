<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - ลงทะเบียนชุมนุม</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <a href="/student/dashboard" class="logo">
                
                    <img src="<?php echo logoPath(); ?>" alt="Logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                
                <?php echo siteName(); ?>
            </a>
            <nav>
                <ul class="nav">
                    <li><a href="/student/dashboard">หน้าหลัก</a></li>
                    <li><a href="/student/grades">ผลคะแนน</a></li>
                    <li><a href="/student/attendance">เวลาเรียน</a></li>
                    <li><a href="/student/clubs">ชุมนุม</a></li>
                    <li><span>สวัสดี, <?php echo htmlspecialchars($_SESSION['student_name'] ?? ''); ?></span></li>
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
        
        <div class="card">
            <div class="card-header">
                <h2>🎯 ลงทะเบียนชุมนุม</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">
                    ปีการศึกษา: <?php echo $academicYear; ?>/<?php echo $semester; ?>
                </p>
            </div>
            
            <?php if ($myClub): ?>
                <div class="alert alert-success">
                    <strong>✅ คุณลงทะเบียนชุมนุมแล้ว:</strong> <?php echo htmlspecialchars($myClub['club_name']); ?>
                    <br>
                    <small>ครูผู้สอน: <?php echo htmlspecialchars($myClub['teacher_name']); ?></small>
                    <?php if ($myClub['grade'] !== null): ?>
                        <br><small>คะแนน: <?php echo number_format($myClub['grade'], 2); ?></small>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    คุณยังไม่ได้ลงทะเบียนชุมนุมในเทอมนี้ กรุณาเลือกชุมนุมด้านล่าง
                </div>
            <?php endif; ?>
            
            <?php if (empty($clubs)): ?>
                <div class="alert alert-info">
                    ไม่มีชุมนุมที่เปิดให้ลงทะเบียนในขณะนี้
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                    <?php foreach ($clubs as $club): ?>
                        <div class="card" style="border: 2px solid <?php echo $club['is_enrolled'] ? '#10b981' : '#e5e7eb'; ?>;">
                            <h3 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($club['club_name']); ?></h3>
                            
                            <?php if ($club['description']): ?>
                                <p style="color: var(--text-light); margin-bottom: 1rem;"><?php echo nl2br(htmlspecialchars($club['description'])); ?></p>
                            <?php endif; ?>
                            
                            <div style="font-size: 0.875rem; color: var(--text-light); margin-bottom: 1rem;">
                                <div>👨‍🏫 ครูผู้สอน: <?php echo htmlspecialchars($club['teacher_name']); ?></div>
                                <div>👥 ที่นั่งว่าง: <?php echo $club['available_seats']; ?> / <?php echo $club['max_students']; ?></div>
                                <div>📚 รับชั้น: ม.<?php echo implode(', ม.', $club['class_levels']); ?></div>
                            </div>
                            
                            <?php if ($club['is_enrolled']): ?>
                                <span class="btn btn-success" style="cursor: default;">✅ ลงทะเบียนแล้ว</span>
                            <?php elseif ($myClub): ?>
                                <span class="btn btn-secondary" style="cursor: default;">ลงทะเบียนชุมนุมอื่นแล้ว</span>
                            <?php elseif ($club['available_seats'] <= 0): ?>
                                <span class="btn btn-secondary" style="cursor: default;">เต็มแล้ว</span>
                            <?php else: ?>
                                <button onclick="enrollClub(<?php echo $club['id']; ?>)" class="btn btn-primary" id="enroll-btn-<?php echo $club['id']; ?>">ลงทะเบียน</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        let enrolling = false;
        
        function enrollClub(clubId) {
            console.log('enrollClub called with clubId:', clubId);
            
            if (enrolling) {
                console.log('Already enrolling, ignoring duplicate click');
                return;
            }
            
            enrolling = true;
            
            // Show loading
            const btn = document.getElementById('enroll-btn-' + clubId);
            if (!btn) {
                console.error('Button not found');
                enrolling = false;
                return;
            }
            
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = '⏳ กำลังลงทะเบียน...';
            
            console.log('Sending enrollment request...');
            
            fetch('/student/clubs/' + clubId + '/enroll', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    btn.textContent = '✅ สำเร็จ!';
                    alert('✅ ลงทะเบียนชุมนุมสำเร็จ!\n\nหน้าจะรีโหลดเพื่อแสดงผลการลงทะเบียน');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('❌ เกิดข้อผิดพลาด:\n' + (data.message || 'ไม่สามารถลงทะเบียนได้'));
                    btn.disabled = false;
                    btn.textContent = originalText;
                    enrolling = false;
                }
            })
            .catch(error => {
                console.error('Enrollment error:', error);
                alert('❌ เกิดข้อผิดพลาด:\n' + error.message + '\n\nกรุณาลองใหม่อีกครั้ง');
                btn.disabled = false;
                btn.textContent = originalText;
                enrolling = false;
            });
        }
    </script>
</body>
</html>
