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
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0;">จัดการนักเรียน</h2>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="/teacher/students/upload" class="btn btn-secondary">📥 นำเข้า XLSX</a>
                    <a href="/teacher/students/create" class="btn btn-primary">➕ เพิ่มนักเรียน</a>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div style="padding: 1.5rem; background: var(--bg-light); border-bottom: 1px solid var(--border);">
                <div style="display: grid; grid-template-columns: 1fr auto auto auto; gap: 0.75rem; align-items: center;">
                    <!-- Search Box -->
                    <form method="GET" action="/teacher/students">
                        <input type="text" 
                               name="search" 
                               placeholder="🔍 ค้นหา (รหัส, ชื่อ, เลขบัตรประชาชน)" 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                               class="form-control"
                               style="width: 100%;">
                    </form>
                    
                    <!-- Class Level Filter -->
                    <form method="GET" action="/teacher/students">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        <?php if (!empty($classroom)): ?>
                            <input type="hidden" name="classroom" value="<?php echo htmlspecialchars($classroom); ?>">
                        <?php endif; ?>
                        
                        <select name="class_level" class="form-control" onchange="this.form.submit()" style="min-width: 140px;">
                            <option value="">📚 ทุกระดับชั้น</option>
                            <option value="ม.1" <?php echo ($classLevel ?? '') === 'ม.1' ? 'selected' : ''; ?>>ม.1</option>
                            <option value="ม.2" <?php echo ($classLevel ?? '') === 'ม.2' ? 'selected' : ''; ?>>ม.2</option>
                            <option value="ม.3" <?php echo ($classLevel ?? '') === 'ม.3' ? 'selected' : ''; ?>>ม.3</option>
                            <option value="ม.4" <?php echo ($classLevel ?? '') === 'ม.4' ? 'selected' : ''; ?>>ม.4</option>
                            <option value="ม.5" <?php echo ($classLevel ?? '') === 'ม.5' ? 'selected' : ''; ?>>ม.5</option>
                            <option value="ม.6" <?php echo ($classLevel ?? '') === 'ม.6' ? 'selected' : ''; ?>>ม.6</option>
                        </select>
                    </form>
                    
                    <!-- Classroom Filter -->
                    <form method="GET" action="/teacher/students">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        <?php if (!empty($classLevel)): ?>
                            <input type="hidden" name="class_level" value="<?php echo htmlspecialchars($classLevel); ?>">
                        <?php endif; ?>
                        
                        <select name="classroom" class="form-control" onchange="this.form.submit()" style="min-width: 120px;">
                            <option value="">🏫 ทุกห้อง</option>
                            <?php for ($i = 1; $i <= 15; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($classroom ?? '') == $i ? 'selected' : ''; ?>>ห้อง <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                    
                    <!-- Clear Filter Button -->
                    <?php if (!empty($classLevel) || !empty($classroom) || !empty($search)): ?>
                        <a href="/teacher/students" 
                           class="btn btn-secondary" 
                           style="white-space: nowrap;">🔄 ล้างตัวกรอง</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Bulk Actions Bar -->
            <div id="bulkActionsBar" style="display: none; padding: 1rem 1.5rem; background: #fff3cd; border-bottom: 1px solid var(--border); align-items: center; justify-content: space-between;">
                <span id="selectedCount" style="font-weight: 500;">เลือก 0 คน</span>
                <button onclick="bulkDelete()" class="btn btn-danger">🗑️ ลบที่เลือก</button>
            </div>
            
            <?php if (empty($students)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                    ไม่พบข้อมูลนักเรียน
                </p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
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
                                <td>
                                    <input type="checkbox" class="student-checkbox" value="<?php echo $student['id']; ?>" onchange="updateBulkActions()">
                                </td>
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
        // Toggle select all checkboxes
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateBulkActions();
        }
        
        // Update bulk actions bar visibility and count
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            const bulkBar = document.getElementById('bulkActionsBar');
            const selectedCount = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAll');
            
            if (checkboxes.length > 0) {
                bulkBar.style.display = 'flex';
                selectedCount.textContent = `เลือก ${checkboxes.length} คน`;
            } else {
                bulkBar.style.display = 'none';
            }
            
            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.student-checkbox');
            selectAll.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
        }
        
        // Bulk delete selected students
        function bulkDelete() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            
            if (ids.length === 0) {
                alert('กรุณาเลือกนักเรียนที่ต้องการลบ');
                return;
            }
            
            if (!confirm(`คุณแน่ใจหรือไม่ที่จะลบนักเรียน ${ids.length} คน?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('ids', JSON.stringify(ids));
            
            fetch('/teacher/students/bulk-delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || `ลบนักเรียน ${ids.length} คนสำเร็จ`);
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบนักเรียนได้'));
                }
            })
            .catch(error => {
                console.error('Bulk delete error:', error);
                alert('เกิดข้อผิดพลาด: ' + (error.message || 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์'));
            });
        }
        
        // Single delete function
        function deleteStudent(id) {
            if (!confirm('คุณแน่ใจหรือไม่ที่จะลบนักเรียนคนนี้?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            
            fetch('/teacher/students/delete/' + id, {
                method: 'POST',
                body: formData
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
