# เอกสารการออกแบบระบบ

## ภาพรวม

ระบบเว็บแจ้งผลคะแนนและบันทึกเวลาเรียนของนักเรียนเป็นระบบที่พัฒนาด้วย PHP และ MySQL เพื่อรองรับ hosting ที่ไม่รองรับ Node.js ระบบประกอบด้วย 2 ส่วนหลัก:

1. **ส่วนของครู (Teacher Portal)** - จัดการข้อมูลนักเรียน รายวิชา คะแนน และการเข้าเรียน
2. **ส่วนของนักเรียน (Student Portal)** - ดูผลคะแนนและสถิติการเข้าเรียนของตนเอง

ระบบใช้สถาปัตยกรรมแบบ MVC (Model-View-Controller) เพื่อแยกส่วนการทำงานให้ชัดเจนและง่ายต่อการบำรุงรักษา

## สถาปัตยกรรม

### เทคโนโลยีที่ใช้

- **Backend**: PHP 7.4+ (รองรับ hosting ทั่วไป)
- **Database**: MySQL 5.7+ หรือ MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla JS)
- **Excel Processing**: PhpSpreadsheet library สำหรับอ่านไฟล์ XLSX
- **Session Management**: PHP Session สำหรับจัดการการเข้าสู่ระบบ

### โครงสร้างระบบ

```
student-grade-system/
├── config/
│   ├── database.php          # การตั้งค่าฐานข้อมูล
│   └── config.php            # การตั้งค่าทั่วไป
├── models/
│   ├── Student.php           # Model สำหรับนักเรียน
│   ├── Course.php            # Model สำหรับรายวิชา
│   ├── Grade.php             # Model สำหรับคะแนน
│   └── Attendance.php        # Model สำหรับการเข้าเรียน
├── controllers/
│   ├── StudentController.php
│   ├── CourseController.php
│   ├── GradeController.php
│   └── AttendanceController.php
```
├── views/
│   ├── teacher/              # หน้าสำหรับครู
│   └── student/              # หน้าสำหรับนักเรียน
├── public/
│   ├── css/
│   ├── js/
│   └── index.php             # Entry point
├── uploads/                  # โฟลเดอร์สำหรับไฟล์ XLSX
└── vendor/                   # PhpSpreadsheet และ dependencies
```

### สถาปัตยกรรมระบบ

```
[Browser] <--> [PHP Application] <--> [MySQL Database]
                     |
                     v
              [PhpSpreadsheet]
              (XLSX Processing)
```

## คอมโพเนนต์และอินเทอร์เฟซ

### 1. Database Layer

**ฐานข้อมูล MySQL** จัดเก็บข้อมูลทั้งหมดของระบบ

**ตาราง:**

- `students` - ข้อมูลนักเรียน
- `courses` - ข้อมูลรายวิชา
- `course_enrollments` - การลงทะเบียนเรียน
- `grade_categories` - หมวดคะแนน (เก็บ, กลางภาค, ปลายภาค)
- `grades` - คะแนนของนักเรียน
- `attendance` - การเข้าเรียน
- `users` - ข้อมูลผู้ใช้ (ครู)

### 2. Model Layer

**Student Model**
- `create($data)` - สร้างนักเรียนใหม่
- `update($id, $data)` - อัปเดตข้อมูลนักเรียน
- `delete($id)` - ลบนักเรียน
- `findById($id)` - ค้นหานักเรียนด้วย ID
- `findByIdCard($idCard)` - ค้นหาด้วยเลขบัตรประชาชน
- `search($keyword)` - ค้นหานักเรียน
- `bulkInsert($students)` - เพิ่มนักเรียนหลายคนพร้อมกัน

**Course Model**
- `create($data)` - สร้างรายวิชาใหม่
- `update($id, $data)` - อัปเดตข้อมูลรายวิชา
- `delete($id)` - ลบรายวิชา
- `findById($id)` - ค้นหารายวิชาด้วย ID
- `findByAcademicYear($year, $semester)` - ค้นหาตามปีการศึกษา
- `enrollStudent($courseId, $studentId)` - ลงทะเบียนนักเรียน
- `getEnrolledStudents($courseId)` - ดึงรายชื่อนักเรียนในวิชา

**GradeCategory Model**
- `create($courseId, $data)` - สร้างหมวดคะแนน
- `update($id, $data)` - อัปเดตหมวดคะแนน
- `delete($id)` - ลบหมวดคะแนน
- `findByCourse($courseId)` - ดึงหมวดคะแนนทั้งหมดของวิชา

**Grade Model**
- `save($studentId, $categoryId, $score)` - บันทึกคะแนน
- `update($id, $score)` - อัปเดตคะแนน
- `getStudentGrades($studentId, $courseId)` - ดึงคะแนนของนักเรียนในวิชา
- `getCourseGrades($courseId)` - ดึงคะแนนทั้งหมดในวิชา
- `calculateTotal($studentId, $courseId)` - คำนวณคะแนนรวม

**Attendance Model**
- `record($studentId, $courseId, $date, $status)` - บันทึกการเข้าเรียน
- `update($id, $status)` - อัปเดตสถานะการเข้าเรียน
- `getStudentAttendance($studentId, $courseId)` - ดึงข้อมูลการเข้าเรียนของนักเรียน
- `getCourseAttendance($courseId)` - ดึงข้อมูลการเข้าเรียนทั้งหมดในวิชา
- `calculateStatistics($studentId, $courseId)` - คำนวณสถิติการเข้าเรียน

### 3. Controller Layer

**StudentController**
- `index()` - แสดงรายชื่อนักเรียนทั้งหมด
- `create()` - แสดงฟอร์มเพิ่มนักเรียน
- `store()` - บันทึกนักเรียนใหม่
- `edit($id)` - แสดงฟอร์มแก้ไขนักเรียน
- `update($id)` - อัปเดตข้อมูลนักเรียน
- `delete($id)` - ลบนักเรียน
- `uploadXlsx()` - อัปโหลดและประมวลผลไฟล์ XLSX

**CourseController**
- `index()` - แสดงรายวิชาทั้งหมด
- `create()` - แสดงฟอร์มสร้างรายวิชา
- `store()` - บันทึกรายวิชาใหม่
- `edit($id)` - แสดงฟอร์มแก้ไขรายวิชา
- `update($id)` - อัปเดตข้อมูลรายวิชา
- `manageGradeCategories($id)` - จัดการหมวดคะแนน
- `enrollStudents($id)` - ลงทะเบียนนักเรียน

**GradeController**
- `index($courseId)` - แสดงหน้าบันทึกคะแนน
- `save()` - บันทึกคะแนน
- `summary($courseId)` - แสดงสรุปผลคะแนนรายวิชา
- `studentSummary($studentId, $courseId)` - แสดงสรุปคะแนนของนักเรียน

**AttendanceController**
- `index($courseId)` - แสดงหน้าบันทึกการเข้าเรียน
- `record()` - บันทึกการเข้าเรียน
- `summary($courseId)` - แสดงสรุปผลการเข้าเรียนรายวิชา
- `studentSummary($studentId, $courseId)` - แสดงสรุปการเข้าเรียนของนักเรียน

**AuthController**
- `login()` - แสดงหน้า login
- `authenticate()` - ตรวจสอบการเข้าสู่ระบบ
- `logout()` - ออกจากระบบ

### 4. View Layer

**Teacher Views**
- `dashboard.php` - หน้าหลักของครู
- `students/index.php` - รายชื่อนักเรียน
- `students/form.php` - ฟอร์มเพิ่ม/แก้ไขนักเรียน
- `students/upload.php` - อัปโหลดไฟล์ XLSX
- `courses/index.php` - รายวิชา
- `courses/form.php` - ฟอร์มสร้าง/แก้ไขรายวิชา
- `courses/grade_categories.php` - จัดการหมวดคะแนน
- `grades/record.php` - บันทึกคะแนน
- `grades/summary.php` - สรุปผลคะแนน
- `attendance/record.php` - บันทึกการเข้าเรียน
- `attendance/summary.php` - สรุปผลการเข้าเรียน

**Student Views**
- `dashboard.php` - หน้าหลักของนักเรียน
- `grades/view.php` - ดูผลคะแนน
- `attendance/view.php` - ดูสถิติการเข้าเรียน

## โมเดลข้อมูล

### ER Diagram

```
students                    courses
├── id (PK)                ├── id (PK)
├── student_code           ├── course_code
├── id_card                ├── course_name
├── name                   ├── academic_year
├── class_level            ├── semester
├── classroom              └── created_at
├── notes
└── created_at
        |                          |
        |                          |
        └──────────┬───────────────┘
                   |
          course_enrollments
          ├── id (PK)
          ├── student_id (FK)
          ├── course_id (FK)
          └── enrolled_at
                   |
        ┌──────────┴──────────┐
        |                     |
    grades              attendance
    ├── id (PK)         ├── id (PK)
    ├── student_id (FK) ├── student_id (FK)
    ├── course_id (FK)  ├── course_id (FK)
    ├── category_id (FK)├── date
    ├── score           ├── status
    └── recorded_at     └── recorded_at
        |
        |
   grade_categories
   ├── id (PK)
   ├── course_id (FK)
   ├── category_name
   ├── max_score
   ├── weight
   └── order
```

### ตารางฐานข้อมูล

**students**
```sql
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_code VARCHAR(20) UNIQUE NOT NULL,
    id_card VARCHAR(13) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    class_level VARCHAR(10) NOT NULL,
    classroom VARCHAR(10) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**courses**
```sql
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_course (course_code, academic_year, semester)
);
```

**course_enrollments**
```sql
CREATE TABLE course_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);
```

**grade_categories**
```sql
CREATE TABLE grade_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    weight DECIMAL(5,2) DEFAULT 0,
    display_order INT DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
```

**grades**
```sql
CREATE TABLE grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    category_id INT NOT NULL,
    score DECIMAL(5,2),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES grade_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade (student_id, category_id)
);
```

**attendance**
```sql
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'sick', 'leave', 'late') NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, course_id, date)
);
```

**users** (สำหรับครู)
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'admin') DEFAULT 'teacher',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## คุณสมบัติความถูกต้อง (Correctness Properties)

*คุณสมบัติ (Property) คือลักษณะหรือพฤติกรรมที่ควรเป็นจริงในทุกการทำงานที่ถูกต้องของระบบ - เป็นข้อความที่แสดงถึงสิ่งที่ระบบควรทำอย่างเป็นทางการ คุณสมบัติทำหน้าที่เป็นสะพานเชื่อมระหว่างข้อกำหนดที่มนุษย์อ่านได้กับการรับประกันความถูกต้องที่เครื่องจักรสามารถตรวจสอบได้*


### Property Reflection

หลังจากวิเคราะห์เกณฑ์การยอมรับทั้งหมด พบว่ามีคุณสมบัติที่ซ้ำซ้อนดังนี้:
- Property 4.5 และ 8.3 ทั้งคู่ทดสอบการคำนวณคะแนนรวม สามารถรวมเป็น property เดียวได้
- Property 7.4, 7.5, 8.4, 8.5 เป็นการทดสอบ authorization ที่คล้ายกัน สามารถรวมเป็น property ทั่วไปได้

### คุณสมบัติความถูกต้อง

**Property 1: การอ่านไฟล์ XLSX ถูกต้อง**
*สำหรับ* ไฟล์ XLSX ใดๆ ที่มีข้อมูลนักเรียนในรูปแบบที่ถูกต้อง เมื่อระบบอ่านไฟล์ ข้อมูลที่ได้ควรตรงกับข้อมูลในไฟล์ทุกฟิลด์ (รหัสนักเรียน เลขบัตรประชาชน ชื่อ ชั้น ห้องเรียน หมายเหตุ)
**Validates: Requirements 1.1**

**Property 2: การตรวจสอบข้อมูลก่อนบันทึก**
*สำหรับ* ข้อมูลนักเรียนใดๆ ที่ไม่ถูกต้อง (เช่น ขาดฟิลด์จำเป็น รูปแบบไม่ถูกต้อง) ระบบควรปฏิเสธการบันทึกและแสดงข้อความแจ้งเตือน
**Validates: Requirements 1.2**

**Property 3: การป้องกันข้อมูลซ้ำ**
*สำหรับ* นักเรียนใดๆ ที่มีรหัสนักเรียนหรือเลขบัตรประชาชนซ้ำกับข้อมูลที่มีอยู่แล้ว ระบบควรปฏิเสธการบันทึกและแสดงข้อความแจ้งเตือน
**Validates: Requirements 1.3, 2.3**

**Property 4: สรุปผลการนำเข้าถูกต้อง**
*สำหรับ* การอัปโหลดไฟล์ XLSX ใดๆ จำนวนนักเรียนที่เพิ่มสำเร็จในสรุปผลควรเท่ากับจำนวนแถวที่ถูกต้องและไม่ซ้ำ และจำนวนที่มีข้อผิดพลาดควรเท่ากับจำนวนแถวที่ไม่ถูกต้องหรือซ้ำ
**Validates: Requirements 1.4**

**Property 5: การบันทึกข้อมูลถาวร**
*สำหรับ* นักเรียนใดๆ ที่เพิ่มผ่านไฟล์ XLSX หรือฟอร์ม หลังจากบันทึกสำเร็จ การค้นหานักเรียนด้วยรหัสนักเรียนควรได้ข้อมูลที่ตรงกับข้อมูลที่บันทึก
**Validates: Requirements 1.5, 2.1**

**Property 6: การอัปเดตข้อมูลนักเรียน**
*สำหรับ* นักเรียนใดๆ ที่มีอยู่แล้ว เมื่อแก้ไขข้อมูลและบันทึก การค้นหานักเรียนควรได้ข้อมูลที่อัปเดตแล้ว
**Validates: Requirements 2.2**

**Property 7: การลบข้อมูล cascade**
*สำหรับ* นักเรียนใดๆ ที่มีข้อมูลที่เกี่ยวข้อง (คะแนน การเข้าเรียน) เมื่อลบนักเรียน ข้อมูลที่เกี่ยวข้องทั้งหมดควรถูกลบด้วย
**Validates: Requirements 2.4**

**Property 8: การค้นหานักเรียน**
*สำหรับ* คำค้นหาใดๆ ผลลัพธ์ที่ได้ควรประกอบด้วยนักเรียนที่มีข้อมูลตรงกับคำค้นหาเท่านั้น
**Validates: Requirements 2.5**

**Property 9: การบันทึกรายวิชา**
*สำหรับ* รายวิชาใดๆ ที่สร้างใหม่ หลังจากบันทึกสำเร็จ การค้นหารายวิชาด้วยรหัสวิชาและปีการศึกษาควรได้ข้อมูลที่ตรงกับข้อมูลที่บันทึก
**Validates: Requirements 3.1**

**Property 10: การเพิ่มหมวดคะแนนหลายประเภท**
*สำหรับ* รายวิชาใดๆ ระบบควรอนุญาตให้เพิ่มหมวดคะแนนได้หลายประเภท และการดึงหมวดคะแนนทั้งหมดควรได้หมวดคะแนนที่เพิ่มทั้งหมด
**Validates: Requirements 3.2, 3.3**

**Property 11: การแก้ไขโครงสร้างคะแนนไม่กระทบคะแนนเดิม**
*สำหรับ* รายวิชาใดๆ ที่มีคะแนนบันทึกไว้แล้ว เมื่อแก้ไขโครงสร้างคะแนน (เช่น เปลี่ยนชื่อหมวด เปลี่ยนคะแนนเต็ม) คะแนนที่บันทึกไว้ควรยังคงอยู่และเชื่อมโยงกับหมวดคะแนนที่ถูกต้อง
**Validates: Requirements 3.4**

**Property 12: การลงทะเบียนเรียน**
*สำหรับ* นักเรียนและรายวิชาใดๆ หลังจากลงทะเบียนเรียน การดึงรายชื่อนักเรียนในรายวิชาควรมีนักเรียนคนนั้นอยู่ในรายชื่อ
**Validates: Requirements 3.5, 4.1, 6.1**

**Property 13: การบันทึกคะแนน**
*สำหรับ* นักเรียน รายวิชา และหมวดคะแนนใดๆ หลังจากบันทึกคะแนน การดึงคะแนนของนักเรียนในหมวดคะแนนนั้นควรได้คะแนนที่บันทึก
**Validates: Requirements 4.2**

**Property 14: การอัปเดตคะแนน**
*สำหรับ* คะแนนใดๆ ที่บันทึกไว้แล้ว เมื่อแก้ไขคะแนน การดึงคะแนนควรได้คะแนนที่อัปเดตแล้ว
**Validates: Requirements 4.3**

**Property 15: การตรวจสอบคะแนนไม่เกินคะแนนเต็ม**
*สำหรับ* คะแนนใดๆ ที่มากกว่าคะแนนเต็มของหมวดคะแนน ระบบควรปฏิเสธการบันทึกและแสดงข้อความแจ้งเตือน
**Validates: Requirements 4.4**

**Property 16: การคำนวณคะแนนรวม**
*สำหรับ* นักเรียนและรายวิชาใดๆ คะแนนรวมควรเท่ากับผลรวมของคะแนนจากทุกหมวดคะแนนในรายวิชานั้น
**Validates: Requirements 4.5, 8.3**

**Property 17: การ authentication นักเรียน**
*สำหรับ* เลขบัตรประชาชนและรหัสนักเรียนใดๆ ที่ตรงกับข้อมูลในฐานข้อมูล ระบบควรอนุญาตให้เข้าสู่ระบบได้ และถ้าไม่ตรงควรปฏิเสธการเข้าสู่ระบบ
**Validates: Requirements 5.1, 5.2, 5.3**

**Property 18: การบันทึกการเข้าเรียน**
*สำหรับ* นักเรียน รายวิชา วันที่ และสถานะใดๆ หลังจากบันทึกการเข้าเรียน การดึงข้อมูลการเข้าเรียนของนักเรียนในวันนั้นควรได้สถานะที่บันทึก
**Validates: Requirements 6.2, 6.3**

**Property 19: การอัปเดตสถานะการเข้าเรียน**
*สำหรับ* การเข้าเรียนใดๆ ที่บันทึกไว้แล้ว เมื่อแก้ไขสถานะ การดึงข้อมูลการเข้าเรียนควรได้สถานะที่อัปเดตแล้ว
**Validates: Requirements 6.4**

**Property 20: การคำนวณสถิติการเข้าเรียน**
*สำหรับ* นักเรียนและรายวิชาใดๆ สถิติการเข้าเรียน (จำนวนมา ขาด ป่วย ลา สาย) ควรเท่ากับจำนวนจริงของแต่ละสถานะที่บันทึกไว้
**Validates: Requirements 6.5, 7.1, 7.2**

**Property 21: การคำนวณเปอร์เซ็นต์การเข้าเรียน**
*สำหรับ* นักเรียนและรายวิชาใดๆ เปอร์เซ็นต์การเข้าเรียนควรเท่ากับ (จำนวนวันที่มา / จำนวนวันทั้งหมด) × 100
**Validates: Requirements 7.3**

**Property 22: การแสดงคะแนนแยกตามหมวด**
*สำหรับ* รายวิชาใดๆ สรุปผลคะแนนควรแสดงคะแนนของแต่ละหมวดคะแนนที่กำหนดไว้ในโครงสร้างคะแนน
**Validates: Requirements 8.1, 8.2**

**Property 23: การควบคุมการเข้าถึงข้อมูล (Authorization)**
*สำหรับ* ผู้ใช้ใดๆ ครูควรเห็นข้อมูลของนักเรียนทุกคน ส่วนนักเรียนควรเห็นเฉพาะข้อมูลของตนเองเท่านั้น
**Validates: Requirements 7.4, 7.5, 8.4, 8.5**


## การจัดการข้อผิดพลาด

### ประเภทของข้อผิดพลาด

1. **Validation Errors** - ข้อมูลไม่ถูกต้องตามรูปแบบที่กำหนด
   - เลขบัตรประชาชนไม่ครบ 13 หลัก
   - คะแนนเกินคะแนนเต็ม
   - ฟิลด์จำเป็นว่างเปล่า

2. **Duplicate Errors** - ข้อมูลซ้ำกับข้อมูลที่มีอยู่
   - รหัสนักเรียนซ้ำ
   - เลขบัตรประชาชนซ้ำ
   - รายวิชาซ้ำในปีการศึกษาเดียวกัน

3. **Authentication Errors** - การเข้าสู่ระบบล้มเหลว
   - เลขบัตรประชาชนหรือรหัสนักเรียนไม่ถูกต้อง
   - Session หมดอายุ

4. **Authorization Errors** - ไม่มีสิทธิ์เข้าถึง
   - นักเรียนพยายามเข้าถึงข้อมูลของนักเรียนคนอื่น
   - ผู้ใช้ที่ไม่ได้ login พยายามเข้าถึงหน้าที่ต้อง login

5. **File Processing Errors** - ปัญหาในการประมวลผลไฟล์
   - ไฟล์ไม่ใช่ XLSX
   - โครงสร้างไฟล์ไม่ถูกต้อง
   - ไฟล์เสียหาย

6. **Database Errors** - ปัญหาในการเชื่อมต่อหรือดำเนินการกับฐานข้อมูล
   - การเชื่อมต่อฐานข้อมูลล้มเหลว
   - Query ล้มเหลว

### กลยุทธ์การจัดการข้อผิดพลาด

1. **Input Validation** - ตรวจสอบข้อมูลก่อนประมวลผล
   - ใช้ HTML5 validation attributes
   - ตรวจสอบซ้ำที่ฝั่ง server ด้วย PHP

2. **Error Messages** - แสดงข้อความที่เข้าใจง่าย
   - ใช้ภาษาไทยที่เข้าใจง่าย
   - ระบุสาเหตุและวิธีแก้ไข

3. **Transaction Management** - ใช้ database transaction สำหรับการดำเนินการที่สำคัญ
   - การอัปโหลดไฟล์ XLSX (rollback ถ้ามีข้อผิดพลาด)
   - การลบนักเรียน (ลบข้อมูลที่เกี่ยวข้องทั้งหมด)

4. **Logging** - บันทึก error log สำหรับการ debug
   - บันทึก error ที่สำคัญลงไฟล์
   - แสดง error ที่เหมาะสมกับผู้ใช้

5. **Graceful Degradation** - ระบบยังใช้งานได้แม้มีข้อผิดพลาดบางส่วน
   - ถ้าอัปโหลดไฟล์ XLSX บางแถวผิด ให้บันทึกแถวที่ถูกต้องและรายงานแถวที่ผิด

## กลยุทธ์การทดสอบ

### Unit Testing

ใช้ PHPUnit สำหรับทดสอบ Model และ Controller

**ตัวอย่าง Unit Tests:**

1. **Student Model Tests**
   - ทดสอบการสร้างนักเรียนด้วยข้อมูลที่ถูกต้อง
   - ทดสอบการปฏิเสธข้อมูลที่ซ้ำ
   - ทดสอบการค้นหานักเรียน

2. **Grade Model Tests**
   - ทดสอบการบันทึกคะแนน
   - ทดสอบการคำนวณคะแนนรวม
   - ทดสอบการปฏิเสธคะแนนที่เกินคะแนนเต็ม

3. **Attendance Model Tests**
   - ทดสอบการบันทึกการเข้าเรียน
   - ทดสอบการคำนวณสถิติการเข้าเรียน

4. **XLSX Processing Tests**
   - ทดสอบการอ่านไฟล์ XLSX ที่ถูกต้อง
   - ทดสอบการจัดการไฟล์ที่มีข้อผิดพลาด

### Property-Based Testing

ใช้ **Eris** (Property-Based Testing library สำหรับ PHP) เพื่อทดสอบคุณสมบัติความถูกต้อง

**การตั้งค่า:**
- ติดตั้ง Eris ผ่าน Composer: `composer require --dev giorgiosironi/eris`
- แต่ละ property-based test จะรันอย่างน้อย 100 iterations
- แต่ละ test จะมี comment ที่อ้างอิงถึง property ในเอกสารนี้

**ตัวอย่าง Property-Based Tests:**

1. **Property 1: การอ่านไฟล์ XLSX ถูกต้อง**
   - สร้างข้อมูลนักเรียนแบบสุ่ม
   - สร้างไฟล์ XLSX จากข้อมูลนั้น
   - อ่านไฟล์และตรวจสอบว่าข้อมูลตรงกัน

2. **Property 3: การป้องกันข้อมูลซ้ำ**
   - สร้างนักเรียนแบบสุ่ม
   - พยายามเพิ่มนักเรียนที่มีรหัสซ้ำ
   - ตรวจสอบว่าระบบปฏิเสธ

3. **Property 16: การคำนวณคะแนนรวม**
   - สร้างรายวิชาและหมวดคะแนนแบบสุ่ม
   - บันทึกคะแนนแบบสุ่ม
   - ตรวจสอบว่าคะแนนรวมเท่ากับผลรวมที่คำนวณเอง

4. **Property 20: การคำนวณสถิติการเข้าเรียน**
   - สร้างการเข้าเรียนแบบสุ่ม
   - คำนวณสถิติเอง
   - ตรวจสอบว่าสถิติที่ระบบคำนวณตรงกับที่คำนวณเอง

**รูปแบบการ tag property-based tests:**
```php
/**
 * @test
 * Feature: student-grade-attendance-system, Property 16: การคำนวณคะแนนรวม
 * Validates: Requirements 4.5, 8.3
 */
public function testTotalGradeCalculation() {
    // test implementation
}
```

### Integration Testing

ทดสอบการทำงานร่วมกันของ components

1. **End-to-End Workflows**
   - ทดสอบการลงทะเบียนนักเรียน → ลงทะเบียนเรียน → บันทึกคะแนน → ดูสรุปผล
   - ทดสอบการ login นักเรียน → ดูคะแนน → ดูการเข้าเรียน

2. **Database Integration**
   - ทดสอบ foreign key constraints
   - ทดสอบ cascade delete

### Manual Testing

1. **UI/UX Testing**
   - ทดสอบการใช้งานจริงบนเบราว์เซอร์ต่างๆ
   - ทดสอบ responsive design

2. **File Upload Testing**
   - ทดสอบอัปโหลดไฟล์ XLSX ขนาดต่างๆ
   - ทดสอบไฟล์ที่มีข้อมูลผิดรูปแบบ

## การติดตั้งและ Deployment

### ความต้องการของ Hosting

- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือ MariaDB 10.3 หรือสูงกว่า
- PHP Extensions: mysqli, zip, xml (สำหรับ PhpSpreadsheet)
- อนุญาตให้อัปโหลดไฟล์ (upload_max_filesize อย่างน้อย 10MB)

### ขั้นตอนการติดตั้ง

1. อัปโหลดไฟล์ทั้งหมดไปยัง hosting
2. สร้างฐานข้อมูล MySQL
3. Import schema จากไฟล์ `database/schema.sql`
4. แก้ไขไฟล์ `config/database.php` ให้ตรงกับข้อมูลฐานข้อมูล
5. ตั้งค่า permissions สำหรับโฟลเดอร์ `uploads/` ให้เขียนได้
6. สร้างผู้ใช้ครูคนแรกผ่าน script `setup/create_admin.php`

### Security Considerations

1. **Password Hashing** - ใช้ `password_hash()` และ `password_verify()` ของ PHP
2. **SQL Injection Prevention** - ใช้ Prepared Statements
3. **XSS Prevention** - ใช้ `htmlspecialchars()` เมื่อแสดงข้อมูล
4. **CSRF Protection** - ใช้ CSRF tokens สำหรับ forms
5. **File Upload Security** - ตรวจสอบ file type และ file size
6. **Session Security** - ใช้ secure session settings

## สรุป

ระบบนี้ออกแบบให้ทำงานบน hosting ที่รองรับ PHP และ MySQL โดยไม่ต้องใช้ Node.js มีโครงสร้างที่ชัดเจนตามแบบ MVC และมีการทดสอบที่ครอบคลุมทั้ง unit tests และ property-based tests เพื่อให้มั่นใจในความถูกต้องของระบบ
