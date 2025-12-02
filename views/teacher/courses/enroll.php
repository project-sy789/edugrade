<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - ลงทะเบียนนักเรียน</title>
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
        
        <div class="card">
            <div class="card-header">
                <h2>ลงทะเบียนนักเรียน: <?php echo htmlspecialchars($course['course_name']); ?></h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($course['course_code']); ?> | 
                    ปีการศึกษา <?php echo htmlspecialchars($course['academic_year']); ?> ภาคเรียนที่ <?php echo htmlspecialchars($course['semester']); ?>
                </p>
            </div>
            
            <h3>นักเรียนที่ลงทะเบียนแล้ว (<?php echo count($enrolledStudents); ?> คน)</h3>
            <?php if (empty($enrolledStudents)): ?>
                <p style="color: var(--text-light);">ยังไม่มีนักเรียนลงทะเบียน</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสนักเรียน</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ชั้น/ห้อง</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrolledStudents as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['class_level'] . '/' . $student['classroom']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <hr style="margin: 2rem 0;">
            
            <h3>เพิ่มนักเรียนเข้าเรียน</h3>
            <form method="POST" action="/teacher/courses/<?php echo $course['id']; ?>/enroll" id="enrollForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 0.5rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filterClass">กรองตามชั้น</label>
                        <select class="form-control" id="filterClass" onchange="filterStudents()">
                            <option value="">ทุกชั้น</option>
                            <?php
                            $enrolledIds = array_column($enrolledStudents, 'id');
                            $availableStudents = array_filter($allStudents, function($s) use ($enrolledIds) {
                                return !in_array($s['id'], $enrolledIds);
                            });
                            $classes = array_unique(array_column($availableStudents, 'class_level'));
                            sort($classes);
                            foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filterRoom">กรองตามห้อง</label>
                        <select class="form-control" id="filterRoom" onchange="filterStudents()">
                            <option value="">ทุกห้อง</option>
                            <?php
                            $rooms = array_unique(array_column($availableStudents, 'classroom'));
                            sort($rooms);
                            foreach ($rooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room); ?>"><?php echo htmlspecialchars($room); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="selectAll()">เลือกทั้งหมด</button>
                    </div>
                    
                    <div style="display: flex; align-items: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="selectNone()">ยกเลิกทั้งหมด</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">เลือกนักเรียน (<span id="selectedCount">0</span> คน)</label>
                    <div style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 0.375rem; padding: 1rem;" id="studentList">
                        <?php 
                        foreach ($allStudents as $student): 
                            if (in_array($student['id'], $enrolledIds)) continue;
                        ?>
                            <div class="student-item" 
                                 data-class="<?php echo htmlspecialchars($student['class_level']); ?>"
                                 data-room="<?php echo htmlspecialchars($student['classroom']); ?>"
                                 style="margin-bottom: 0.5rem;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" 
                                           class="student-checkbox"
                                           onchange="updateCount()"
                                           style="margin-right: 0.5rem;">
                                    <span><?php echo htmlspecialchars($student['student_code'] . ' - ' . $student['name'] . ' (' . $student['class_level'] . '/' . $student['classroom'] . ')'); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($allStudents) === count($enrolledIds)): ?>
                            <p style="color: var(--text-light);">นักเรียนทุกคนลงทะเบียนแล้ว</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
                    <a href="/teacher/courses" class="btn btn-secondary">กลับ</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        function filterStudents() {
            const classFilter = document.getElementById('filterClass').value;
            const roomFilter = document.getElementById('filterRoom').value;
            const items = document.querySelectorAll('.student-item');
            
            items.forEach(item => {
                const itemClass = item.dataset.class;
                const itemRoom = item.dataset.room;
                
                const classMatch = !classFilter || itemClass === classFilter;
                const roomMatch = !roomFilter || itemRoom === roomFilter;
                
                if (classMatch && roomMatch) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
            
            updateCount();
        }
        
        function selectAll() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                if (cb.closest('.student-item').style.display !== 'none') {
                    cb.checked = true;
                }
            });
            updateCount();
        }
        
        function selectNone() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                if (cb.closest('.student-item').style.display !== 'none') {
                    cb.checked = false;
                }
            });
            updateCount();
        }
        
        function updateCount() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            const visibleChecked = Array.from(checkboxes).filter(cb => 
                cb.closest('.student-item').style.display !== 'none'
            );
            document.getElementById('selectedCount').textContent = checkboxes.length;
        }
        
        // Initialize count on page load
        updateCount();
    </script>
</body>
</html>
