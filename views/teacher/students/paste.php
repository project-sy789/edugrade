<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title>นำเข้าข้อมูลนักเรียน - <?php echo siteName(); ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="container">
                <a href="/teacher/dashboard" class="logo">
                    <?php if (logoPath()): ?>
                        <img src="<?php echo logoPath(); ?>" alt="Logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                    <?php endif; ?>
                    <?php echo siteName(); ?>
                </a>
                <nav>
                    <ul class="nav">
                        <li><a href="/teacher/dashboard">หน้าหลัก</a></li>
                        <li><a href="/teacher/students" class="active">นักเรียน</a></li>
                        <li><a href="/logout">ออกจากระบบ</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        
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
                <h2>📋 นำเข้าข้อมูลนักเรียนแบบ Copy-Paste</h2>
            </div>
            
            <div style="padding: 1.5rem;">
                <div class="alert alert-info" style="margin-bottom: 1.5rem;">
                    <h3 style="margin-top: 0;">📝 วิธีใช้งาน:</h3>
                    <ol style="margin: 0.5rem 0;">
                        <li>เปิดไฟล์ Excel ของคุณ</li>
                        <li>เลือกข้อมูลนักเรียน (รหัส, เลขบัตร, ชื่อ, ชั้น, ห้อง, หมายเหตุ)</li>
                        <li>Copy (Ctrl+C หรือ Cmd+C)</li>
                        <li>Paste (Ctrl+V หรือ Cmd+V) ลงในช่องด้านล่าง</li>
                        <li>คลิก "นำเข้าข้อมูล"</li>
                    </ol>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">
                        <strong>รูปแบบ:</strong> รหัสนักเรียน [Tab] เลขบัตรประชาชน [Tab] ชื่อ-นามสกุล [Tab] ชั้น [Tab] ห้อง [Tab] หมายเหตุ
                    </p>
                </div>
                
                <form method="POST" action="/teacher/students/paste-import">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="pasteData">วางข้อมูลที่ Copy จาก Excel:</label>
                        <textarea 
                            id="pasteData" 
                            name="paste_data" 
                            class="form-control" 
                            rows="15" 
                            placeholder="วางข้อมูลที่ Copy จาก Excel ที่นี่...&#10;&#10;ตัวอย่าง:&#10;S001	1234567890123	สมชาย ใจดี	ม.1	1	-&#10;S002	1234567890124	สมหญิง รักเรียน	ม.1	1	-&#10;S003	1234567890125	สมศักดิ์ ขยัน	ม.1	2	-"
                            required
                            style="font-family: monospace; font-size: 0.9rem;"></textarea>
                        <small style="color: var(--text-light);">
                            💡 คอลัมน์แต่ละคอลัมน์ควรแยกด้วย Tab (กด Tab ใน Excel แล้ว Copy มาได้เลย)
                        </small>
                    </div>
                    
                    <div id="preview" style="display: none; margin-top: 1.5rem;">
                        <h3>👀 ตัวอย่างข้อมูลที่จะนำเข้า:</h3>
                        <div id="previewContent" style="max-height: 300px; overflow-y: auto; border: 1px solid var(--border); border-radius: 4px; padding: 1rem; background: var(--bg-light);"></div>
                        <p id="previewCount" style="margin-top: 0.5rem; font-weight: 500;"></p>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                        <button type="button" onclick="previewData()" class="btn btn-secondary">👁️ ดูตัวอย่าง</button>
                        <button type="submit" class="btn btn-primary">✅ นำเข้าข้อมูล</button>
                        <a href="/teacher/students" class="btn btn-secondary">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function previewData() {
            const textarea = document.getElementById('pasteData');
            const preview = document.getElementById('preview');
            const previewContent = document.getElementById('previewContent');
            const previewCount = document.getElementById('previewCount');
            
            const data = textarea.value.trim();
            if (!data) {
                alert('กรุณาวางข้อมูลก่อน');
                return;
            }
            
            const lines = data.split('\n').filter(line => line.trim());
            let html = '<table class="table" style="margin: 0;"><thead><tr><th>รหัส</th><th>เลขบัตร</th><th>ชื่อ</th><th>ชั้น</th><th>ห้อง</th><th>หมายเหตุ</th></tr></thead><tbody>';
            
            let validCount = 0;
            lines.forEach((line, index) => {
                const cols = line.split('\t');
                if (cols.length >= 5) {
                    validCount++;
                    html += '<tr>';
                    html += '<td>' + (cols[0] || '-') + '</td>';
                    html += '<td>' + (cols[1] || '-') + '</td>';
                    html += '<td>' + (cols[2] || '-') + '</td>';
                    html += '<td>' + (cols[3] || '-') + '</td>';
                    html += '<td>' + (cols[4] || '-') + '</td>';
                    html += '<td>' + (cols[5] || '-') + '</td>';
                    html += '</tr>';
                }
            });
            
            html += '</tbody></table>';
            
            if (validCount === 0) {
                alert('ไม่พบข้อมูลที่ถูกต้อง กรุณาตรวจสอบรูปแบบข้อมูล');
                return;
            }
            
            previewContent.innerHTML = html;
            previewCount.textContent = `พบข้อมูล ${validCount} รายการ`;
            preview.style.display = 'block';
        }
    </script>
</body>
</html>
