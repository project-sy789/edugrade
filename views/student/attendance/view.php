<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo siteName(); ?> - การเข้าเรียนของฉัน</title>
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
        <div class="card">
            <div class="card-header">
                <h2>การเข้าเรียนของฉัน</h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    รหัสนักเรียน: <?php echo htmlspecialchars($_SESSION['student_code'] ?? ''); ?> | 
                    ชื่อ: <?php echo htmlspecialchars($_SESSION['student_name'] ?? ''); ?>
                </p>
            </div>
            
            <?php if (empty($attendanceData)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                    ยังไม่มีข้อมูลการเข้าเรียน
                </p>
            <?php else: ?>
                <?php foreach ($attendanceData as $data): 
                    $course = $data['course'];
                    $records = $data['records'];
                    $stats = $data['stats'];
                ?>
                    <div style="margin-bottom: 2rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.375rem;">
                        <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        <p style="color: var(--text-light); margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($course['course_code']); ?> | 
                            ปีการศึกษา <?php echo htmlspecialchars($course['academic_year']); ?> 
                            ภาคเรียนที่ <?php echo htmlspecialchars($course['semester']); ?>
                        </p>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div style="padding: 1rem; background: #D1FAE5; border-radius: 0.375rem;">
                                <div style="font-size: 0.875rem; color: #065F46;">มา</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: #065F46;"><?php echo $stats['present']; ?></div>
                            </div>
                            <div style="padding: 1rem; background: #FEE2E2; border-radius: 0.375rem;">
                                <div style="font-size: 0.875rem; color: #991B1B;">ขาด</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: #991B1B;"><?php echo $stats['absent']; ?></div>
                            </div>
                            <div style="padding: 1rem; background: #DBEAFE; border-radius: 0.375rem;">
                                <div style="font-size: 0.875rem; color: #1E40AF;">ป่วย</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: #1E40AF;"><?php echo $stats['sick']; ?></div>
                            </div>
                            <div style="padding: 1rem; background: #FEF3C7; border-radius: 0.375rem;">
                                <div style="font-size: 0.875rem; color: #92400E;">ลา</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: #92400E;"><?php echo $stats['leave']; ?></div>
                            </div>
                            <div style="padding: 1rem; background: #E0E7FF; border-radius: 0.375rem;">
                                <div style="font-size: 0.875rem; color: #3730A3;">สาย</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: #3730A3;"><?php echo $stats['late']; ?></div>
                            </div>
                            <div style="padding: 1rem; background: var(--bg-color); border-radius: 0.375rem;">
                                <div style="font-size: 0.875rem; color: var(--text-color);">เปอร์เซ็นต์การมา</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-color);"><?php echo number_format($stats['percentage'], 1); ?>%</div>
                            </div>
                        </div>
                        
                        <?php if (!empty($records)): ?>
                            <details style="margin-top: 1rem;">
                                <summary style="cursor: pointer; font-weight: 500; padding: 0.5rem; background: var(--bg-color); border-radius: 0.375rem;">
                                    ดูรายละเอียดการเข้าเรียน (<?php echo count($records); ?> วัน)
                                </summary>
                                <table class="table" style="margin-top: 1rem;">
                                    <thead>
                                        <tr>
                                            <th>วันที่</th>
                                            <th style="text-align: center;">สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $record): 
                                            $statusText = [
                                                'present' => 'มา',
                                                'absent' => 'ขาด',
                                                'sick' => 'ป่วย',
                                                'leave' => 'ลา',
                                                'late' => 'สาย'
                                            ];
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($record['date']))); ?></td>
                                                <td style="text-align: center;"><?php echo $statusText[$record['status']] ?? $record['status']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
