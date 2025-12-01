<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <title><?php echo siteName(); ?> - จัดการหมวดคะแนน</title>
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
                <h2>จัดการหมวดคะแนน: <?php echo htmlspecialchars($course['course_name']); ?></h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($course['course_code']); ?> | 
                    ปีการศึกษา <?php echo htmlspecialchars($course['academic_year']); ?> ภาคเรียนที่ <?php echo htmlspecialchars($course['semester']); ?>
                </p>
            </div>
            
            <h3>หมวดคะแนนที่มีอยู่</h3>
            <?php if (empty($categories)): ?>
                <p style="color: var(--text-light);">ยังไม่มีหมวดคะแนน</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อหมวดคะแนน</th>
                            <th>คะแนนเต็ม</th>
                            <th>น้ำหนัก (%)</th>
                            <th style="width: 150px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['display_order']); ?></td>
                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($category['max_score']); ?></td>
                                <td><?php echo htmlspecialchars($category['weight']); ?></td>
                                <td>
                                    <button onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['category_name'], ENT_QUOTES); ?>', <?php echo $category['max_score']; ?>, <?php echo $category['weight']; ?>)" 
                                            class="btn btn-sm btn-secondary">แก้ไข</button>
                                    <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['category_name'], ENT_QUOTES); ?>')" 
                                            class="btn btn-sm btn-danger">ลบ</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <hr style="margin: 2rem 0;">
            
            <h3 id="form_title">เพิ่มหมวดคะแนนใหม่</h3>
            <form id="category_form" method="POST" action="/teacher/courses/<?php echo $course['id']; ?>/categories">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                <input type="hidden" id="category_id" name="category_id" value="">
                
                <div class="form-group">
                    <label class="form-label" for="category_name">ชื่อหมวดคะแนน *</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" 
                           placeholder="เช่น เก็บคะแนน 1, กลางภาค, ปลายภาค" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="max_score">คะแนนเต็ม *</label>
                        <input type="number" class="form-control" id="max_score" name="max_score" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="weight">น้ำหนัก (%) *</label>
                        <input type="number" class="form-control" id="weight" name="weight" 
                               step="0.01" min="0" max="100" value="0" required>
                    </div>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="submit" id="submit_btn" class="btn btn-primary">เพิ่มหมวดคะแนน</button>
                    <button type="button" id="cancel_btn" class="btn btn-secondary" onclick="cancelEdit()" style="display: none;">ยกเลิก</button>
                    <a href="/teacher/courses" class="btn btn-secondary">กลับ</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
    <script>
        let editingCategoryId = null;
        
        function editCategory(id, name, maxScore, weight) {
            editingCategoryId = id;
            document.getElementById('category_name').value = name;
            document.getElementById('max_score').value = maxScore;
            document.getElementById('weight').value = weight;
            document.getElementById('category_id').value = id;
            document.getElementById('form_title').textContent = 'แก้ไขหมวดคะแนน';
            document.getElementById('submit_btn').textContent = '💾 บันทึกการแก้ไข';
            document.getElementById('cancel_btn').style.display = 'inline-block';
            
            // Scroll to form
            document.getElementById('category_form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function cancelEdit() {
            editingCategoryId = null;
            document.getElementById('category_name').value = '';
            document.getElementById('max_score').value = '';
            document.getElementById('weight').value = '0';
            document.getElementById('category_id').value = '';
            document.getElementById('form_title').textContent = 'เพิ่มหมวดคะแนนใหม่';
            document.getElementById('submit_btn').textContent = 'เพิ่มหมวดคะแนน';
            document.getElementById('cancel_btn').style.display = 'none';
        }
        
        function deleteCategory(id, name) {
            if (!confirm('คุณแน่ใจหรือไม่ที่จะลบหมวดคะแนน "' + name + '"?\n\n⚠️ คะแนนที่บันทึกไว้ทั้งหมดในหมวดนี้จะถูกลบด้วย!')) {
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/teacher/courses/<?php echo $course['id']; ?>/categories/' + id + '/delete';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
