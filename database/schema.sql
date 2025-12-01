-- Student Grade and Attendance System Database Schema
-- MySQL 5.7+ / MariaDB 10.3+

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS student_grade_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE student_grade_system;

-- Drop tables if exists (for clean installation)
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS grade_categories;
DROP TABLE IF EXISTS course_enrollments;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_code VARCHAR(20) UNIQUE NOT NULL COMMENT 'รหัสนักเรียน',
    id_card VARCHAR(13) UNIQUE NOT NULL COMMENT 'เลขบัตรประชาชน 13 หลัก',
    name VARCHAR(255) NOT NULL COMMENT 'ชื่อ-นามสกุล',
    class_level VARCHAR(10) NOT NULL COMMENT 'ระดับชั้น เช่น ม.1, ม.2',
    classroom VARCHAR(10) NOT NULL COMMENT 'ห้องเรียน เช่น 1, 2, 3',
    notes TEXT COMMENT 'หมายเหตุ',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_student_code (student_code),
    INDEX idx_id_card (id_card),
    INDEX idx_class (class_level, classroom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ข้อมูลนักเรียน';

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) NOT NULL COMMENT 'รหัสวิชา',
    course_name VARCHAR(255) NOT NULL COMMENT 'ชื่อวิชา',
    academic_year VARCHAR(10) NOT NULL COMMENT 'ปีการศึกษา เช่น 2567',
    semester INT NOT NULL COMMENT 'ภาคเรียน 1 หรือ 2',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_course (course_code, academic_year, semester),
    INDEX idx_academic_year (academic_year, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='รายวิชา';

-- Course enrollments table
CREATE TABLE course_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    INDEX idx_student (student_id),
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='การลงทะเบียนเรียน';

-- Grade categories table
CREATE TABLE grade_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    category_name VARCHAR(100) NOT NULL COMMENT 'ชื่อหมวดคะแนน เช่น เก็บ1, กลางภาค, ปลายภาค',
    max_score DECIMAL(5,2) NOT NULL COMMENT 'คะแนนเต็ม',
    weight DECIMAL(5,2) DEFAULT 0 COMMENT 'น้ำหนักคะแนน (ถ้าใช้)',
    display_order INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_order (course_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='หมวดคะแนน';

-- Grades table
CREATE TABLE grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    category_id INT NOT NULL,
    score DECIMAL(5,2) COMMENT 'คะแนนที่ได้ (NULL = ยังไม่ได้บันทึก)',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES grade_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade (student_id, category_id),
    INDEX idx_student_course (student_id, course_id),
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='คะแนนนักเรียน';

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    date DATE NOT NULL COMMENT 'วันที่เข้าเรียน',
    status ENUM('present', 'absent', 'sick', 'leave', 'late') NOT NULL COMMENT 'มา, ขาด, ป่วย, ลา, สาย',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, course_id, date),
    INDEX idx_student_course (student_id, course_id),
    INDEX idx_course_date (course_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='การเข้าเรียน';

-- Users table (for teachers/admins)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL COMMENT 'ชื่อผู้ใช้',
    password VARCHAR(255) NOT NULL COMMENT 'รหัสผ่าน (hashed)',
    name VARCHAR(255) NOT NULL COMMENT 'ชื่อ-นามสกุล',
    role ENUM('teacher', 'admin') DEFAULT 'teacher' COMMENT 'บทบาท',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ผู้ใช้งาน (ครู/ผู้ดูแลระบบ)';

-- Insert default admin user (password: admin123)
-- Note: Change this password immediately after installation!
INSERT INTO users (username, password, name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin');

-- Sample data for testing (optional - comment out for production)
-- INSERT INTO students (student_code, id_card, name, class_level, classroom) VALUES
-- ('S001', '1234567890123', 'สมชาย ใจดี', 'ม.1', '1'),
-- ('S002', '1234567890124', 'สมหญิง รักเรียน', 'ม.1', '1'),
-- ('S003', '1234567890125', 'สมศักดิ์ ขยัน', 'ม.1', '2');
