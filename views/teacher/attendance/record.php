<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - บันทึกการเข้าเรียน</title>
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
        <div class="card">
            <div class="card-header">
                <h2>บันทึกการเข้าเรียน: <?php echo htmlspecialchars($course['course_name']); ?></h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($course['course_code']); ?> | 
                    ปีการศึกษา <?php echo htmlspecialchars($course['academic_year']); ?> ภาคเรียนที่ <?php echo htmlspecialchars($course['semester']); ?>
                </p>
            </div>
            
            <?php
            // Get recorded dates for this course
            $recordedDates = [];
            if (!empty($students)) {
                $pdo = $db->getConnection();
                $stmt = $pdo->prepare('SELECT DISTINCT date FROM attendance WHERE course_id = :course_id ORDER BY date DESC');
                $stmt->execute([':course_id' => $course['id']]);
                $recordedDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
            
            // Prepare calendar data
            $currentMonth = date('Y-m', strtotime($date));
            $firstDay = date('Y-m-01', strtotime($date));
            $lastDay = date('Y-m-t', strtotime($date));
            $daysInMonth = date('t', strtotime($date));
            $startDayOfWeek = date('w', strtotime($firstDay)); // 0 = Sunday
            ?>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1.5rem;">
                <div style="flex: 1; max-width: 600px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0;">เลือกวันที่บันทึกการเข้าเรียน</h3>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="changeMonth(-1)">◀ เดือนก่อน</button>
                            <span style="min-width: 150px; text-align: center; font-weight: 600;">
                                <?php 
                                $thaiMonths = ['01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', 
                                               '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
                                               '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
                                               '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'];
                                echo $thaiMonths[date('m', strtotime($date))] . ' ' . (date('Y', strtotime($date)) + 543);
                                ?>
                            </span>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="changeMonth(1)">เดือนถัดไป ▶</button>
                        </div>
                    </div>
                    
                    <!-- Calendar Grid -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; background: #f5f5f5; padding: 8px; border-radius: 8px;">
                        <!-- Day headers -->
                        <?php 
                        $dayHeaders = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
                        foreach ($dayHeaders as $dayHeader): 
                        ?>
                            <div style="text-align: center; font-weight: 600; padding: 8px; color: #666;">
                                <?php echo $dayHeader; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Empty cells before first day -->
                        <?php for ($i = 0; $i < $startDayOfWeek; $i++): ?>
                            <div style="padding: 12px;"></div>
                        <?php endfor; ?>
                        
                        <!-- Calendar days -->
                        <?php 
                        for ($day = 1; $day <= $daysInMonth; $day++):
                            $currentDate = sprintf('%s-%02d', $currentMonth, $day);
                            $isRecorded = in_array($currentDate, $recordedDates);
                            $isSelected = $currentDate === $date;
                            $isToday = $currentDate === date('Y-m-d');
                            
                            $bgColor = $isSelected ? '#2563eb' : ($isRecorded ? '#10b981' : '#fff');
                            $textColor = ($isSelected || $isRecorded) ? '#fff' : '#333';
                            $border = $isToday ? '2px solid #f59e0b' : '1px solid #e5e7eb';
                            $cursor = 'pointer';
                            $fontWeight = $isSelected ? '700' : ($isRecorded ? '600' : '400');
                        ?>
                            <div onclick="selectDate('<?php echo $currentDate; ?>')"
                                 style="padding: 12px; text-align: center; background: <?php echo $bgColor; ?>; 
                                        color: <?php echo $textColor; ?>; border: <?php echo $border; ?>; 
                                        border-radius: 6px; cursor: <?php echo $cursor; ?>; 
                                        font-weight: <?php echo $fontWeight; ?>; transition: all 0.2s;
                                        position: relative;"
                                 onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)';"
                                 onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                <?php echo $day; ?>
                                <?php if ($isRecorded && !$isSelected): ?>
                                    <div style="position: absolute; top: 2px; right: 2px; width: 6px; height: 6px; background: #fff; border-radius: 50%;"></div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Legend -->
                    <div style="display: flex; gap: 1.5rem; margin-top: 1rem; font-size: 0.875rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 20px; height: 20px; background: #10b981; border-radius: 4px;"></div>
                            <span>บันทึกแล้ว</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 20px; height: 20px; background: #2563eb; border-radius: 4px;"></div>
                            <span>วันที่เลือก</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 20px; height: 20px; background: #fff; border: 2px solid #f59e0b; border-radius: 4px;"></div>
                            <span>วันนี้</span>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <a href="/teacher/courses/<?php echo $course['id']; ?>/attendance/summary?type=month" class="btn btn-secondary">สรุปรายเดือน</a>
                    <a href="/teacher/courses/<?php echo $course['id']; ?>/attendance/summary?type=semester" class="btn btn-secondary">สรุปรายเทอม</a>
                </div>
            </div>
            
            <script>
                function selectDate(date) {
                    window.location.href = '/teacher/courses/<?php echo $course['id']; ?>/attendance?date=' + date;
                }
                
                function changeMonth(offset) {
                    const currentDate = new Date('<?php echo $date; ?>');
                    currentDate.setMonth(currentDate.getMonth() + offset);
                    const newDate = currentDate.toISOString().split('T')[0];
                    window.location.href = '/teacher/courses/<?php echo $course['id']; ?>/attendance?date=' + newDate;
                }
            </script>
            
            <?php if (empty($students)): ?>
                <div class="alert alert-warning">
                    ยังไม่มีนักเรียนลงทะเบียน <a href="/teacher/courses/<?php echo $course['id']; ?>/enroll">คลิกที่นี่เพื่อลงทะเบียน</a>
                </div>
            <?php else: ?>
                <?php
                // Calculate summary
                $summary = [
                    'present' => 0,
                    'absent' => 0,
                    'sick' => 0,
                    'leave' => 0,
                    'late' => 0
                ];
                
                foreach ($students as $student) {
                    $attendance = $attendanceMap[$student['id']] ?? null;
                    $status = $attendance['status'] ?? 'present';
                    $summary[$status]++;
                }
                
                $total = count($students);
                $percentage = $total > 0 ? round(($summary['present'] / $total) * 100, 1) : 0;
                ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem;">
                    <div style="padding: 0.75rem; background: #D1FAE5; border-radius: 0.375rem; text-align: center;">
                        <div style="font-size: 0.75rem; color: #065F46;">มา</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: #065F46;"><?php echo $summary['present']; ?></div>
                    </div>
                    <div style="padding: 0.75rem; background: #FEE2E2; border-radius: 0.375rem; text-align: center;">
                        <div style="font-size: 0.75rem; color: #991B1B;">ขาด</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: #991B1B;"><?php echo $summary['absent']; ?></div>
                    </div>
                    <div style="padding: 0.75rem; background: #DBEAFE; border-radius: 0.375rem; text-align: center;">
                        <div style="font-size: 0.75rem; color: #1E40AF;">ป่วย</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: #1E40AF;"><?php echo $summary['sick']; ?></div>
                    </div>
                    <div style="padding: 0.75rem; background: #FEF3C7; border-radius: 0.375rem; text-align: center;">
                        <div style="font-size: 0.75rem; color: #92400E;">ลา</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: #92400E;"><?php echo $summary['leave']; ?></div>
                    </div>
                    <div style="padding: 0.75rem; background: #E0E7FF; border-radius: 0.375rem; text-align: center;">
                        <div style="font-size: 0.75rem; color: #3730A3;">สาย</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: #3730A3;"><?php echo $summary['late']; ?></div>
                    </div>
                    <div style="padding: 0.75rem; background: var(--bg-color); border-radius: 0.375rem; text-align: center;">
                        <div style="font-size: 0.75rem; color: var(--text-color);">% มาเรียน</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-color);"><?php echo $percentage; ?>%</div>
                    </div>
                </div>
                
                <h3 style="margin-bottom: 1rem;">รายชื่อนักเรียน</h3>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">เลขที่</th>
                            <th>รหัสนักเรียน</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th style="text-align: center;">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($students as $student): 
                            $attendance = $attendanceMap[$student['id']] ?? null;
                            $status = $attendance['status'] ?? 'present';
                        ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td style="text-align: center;">
                                    <select class="form-control" 
                                            data-student-id="<?php echo $student['id']; ?>"
                                            onchange="saveAttendance(<?php echo $student['id']; ?>, <?php echo $course['id']; ?>, '<?php echo $date; ?>', this.value)"
                                            style="max-width: 150px; margin: 0 auto;">
                                        <option value="present" <?php echo $status === 'present' ? 'selected' : ''; ?>>มา</option>
                                        <option value="absent" <?php echo $status === 'absent' ? 'selected' : ''; ?>>ขาด</option>
                                        <option value="sick" <?php echo $status === 'sick' ? 'selected' : ''; ?>>ป่วย</option>
                                        <option value="leave" <?php echo $status === 'leave' ? 'selected' : ''; ?>>ลา</option>
                                        <option value="late" <?php echo $status === 'late' ? 'selected' : ''; ?>>สาย</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: var(--text-light); margin: 0;">
                        <small>💡 การเข้าเรียนจะถูกบันทึกอัตโนมัติเมื่อเลือกสถานะ</small>
                    </p>
                    <button type="button" class="btn btn-primary" onclick="saveAllAttendance(event)">
                        💾 บันทึกทั้งหมด
                    </button>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 1.5rem;">
                <a href="/teacher/courses" class="btn btn-secondary">กลับ</a>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        function saveAttendance(studentId, courseId, date, status) {
            console.log(`saveAttendance: student=${studentId}, course=${courseId}, date=${date}, status=${status}`);
            
            const formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('course_id', courseId);
            formData.append('date', date);
            formData.append('status', status);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            
            fetch('/api/save-attendance', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin' // Ensure cookies are sent
            })
            .then(response => {
                console.log(`saveAttendance response status: ${response.status}`);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('saveAttendance response:', data);
                if (!data.success) {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                } else {
                    console.log('✓ Attendance saved successfully');
                }
            })
            .catch(error => {
                console.error('saveAttendance error:', error);
                alert('เกิดข้อผิดพลาดในการบันทึก: ' + error.message);
            });
        }
        
        function saveAllAttendance(event) {
            event.preventDefault(); // Prevent form submission
            event.stopPropagation(); // Stop event bubbling
            
            console.log('=== saveAllAttendance called ===');
            
            const selects = document.querySelectorAll('select.form-control');
            const courseId = <?php echo $course['id']; ?>;
            const date = '<?php echo $date; ?>';
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            console.log('Students to save:', selects.length);
            console.log('Course ID:', courseId);
            console.log('Date:', date);
            console.log('CSRF Token:', csrfToken ? 'Present' : 'Missing');
            
            if (selects.length === 0) {
                alert('ไม่มีนักเรียนให้บันทึก');
                return;
            }
            
            // Proceed directly with saving (removed confirm dialog due to browser issues)
            console.log('Starting save process...');
            
            const button = event.target;
            button.disabled = true;
            button.textContent = 'กำลังบันทึก...';
            
            let completed = 0;
            let errors = 0;
            let errorMessages = [];
            
            // Save each student's attendance
            const promises = [];
            selects.forEach((select, index) => {
                const studentId = select.getAttribute('data-student-id');
                const status = select.value;
                
                console.log(`[${index + 1}/${selects.length}] Saving student ${studentId}: ${status}`);
                
                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('course_id', courseId);
                formData.append('date', date);
                formData.append('status', status);
                formData.append('csrf_token', csrfToken);
                
                const promise = fetch('/api/save-attendance', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin' // Ensure cookies are sent
                })
                .then(response => {
                    console.log(`Student ${studentId} response status:`, response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(`Student ${studentId} response:`, data);
                    if (data.success) {
                        completed++;
                    } else {
                        errors++;
                        errorMessages.push(`Student ${studentId}: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error(`Error saving student ${studentId}:`, error);
                    errors++;
                    errorMessages.push(`Student ${studentId}: ${error.message}`);
                });
                
                promises.push(promise);
            });
            
            Promise.all(promises).then(() => {
                console.log('=== All saves completed ===');
                console.log('Successful:', completed);
                console.log('Failed:', errors);
                
                button.disabled = false;
                button.textContent = '💾 บันทึกทั้งหมด';
                
                if (errors === 0) {
                    alert('✓ บันทึกสำเร็จทั้งหมด ' + completed + ' คน');
                    console.log('Reloading page to update calendar...');
                    // Delay reload slightly to ensure alert is visible
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    const errorMsg = 'บันทึกสำเร็จ ' + completed + ' คน, ล้มเหลว ' + errors + ' คน\n\n' + 
                                   'รายละเอียด:\n' + errorMessages.join('\n');
                    alert(errorMsg);
                    console.error('Save errors:', errorMessages);
                }
            });
        }
    </script>
</body>
</html>
