<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo siteName(); ?> - ผลคะแนนของฉัน</title>
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
                <h2>ผลคะแนนของฉัน</h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    รหัสนักเรียน: <?php echo htmlspecialchars($_SESSION['student_code'] ?? ''); ?> | 
                    ชื่อ: <?php echo htmlspecialchars($_SESSION['student_name'] ?? ''); ?>
                </p>
            </div>
            
            <?php if (empty($gradesData)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                    ยังไม่มีข้อมูลคะแนน
                </p>
            <?php else: ?>
                <?php foreach ($gradesData as $data): 
                    $course = $data['course'];
                    $grades = $data['grades'];
                    $total = $data['total'];
                ?>
                    <div style="margin-bottom: 2rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.375rem;">
                        <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        <p style="color: var(--text-light); margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($course['course_code']); ?> | 
                            ปีการศึกษา <?php echo htmlspecialchars($course['academic_year']); ?> 
                            ภาคเรียนที่ <?php echo htmlspecialchars($course['semester']); ?>
                        </p>
                        
                        <?php if (empty($grades)): ?>
                            <p style="color: var(--text-light);">ยังไม่มีคะแนน</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>หมวดคะแนน</th>
                                        <th style="text-align: center;">คะแนนที่ได้</th>
                                        <th style="text-align: center;">คะแนนเต็ม</th>
                                        <th style="text-align: center;">น้ำหนัก (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($grade['category_name']); ?></td>
                                            <td style="text-align: center;">
                                                <?php echo $grade['score'] !== null ? htmlspecialchars($grade['score']) : '-'; ?>
                                            </td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($grade['max_score']); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($grade['weight']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="background: var(--bg-color); font-weight: 600;">
                                        <td>รวม</td>
                                        <td style="text-align: center;"><?php echo number_format($total['total_score'] ?? 0, 2); ?></td>
                                        <td style="text-align: center;">100.00</td>
                                        <td style="text-align: center;"><?php echo number_format($total['total_weight'] ?? 0, 2); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>
