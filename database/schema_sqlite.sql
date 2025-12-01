-- Student Grade and Attendance System Database Schema
-- SQLite Version

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
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_code TEXT UNIQUE NOT NULL,
    id_card TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    class_level TEXT NOT NULL,
    classroom TEXT NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_student_code ON students(student_code);
CREATE INDEX idx_id_card ON students(id_card);
CREATE INDEX idx_class ON students(class_level, classroom);

-- Courses table
CREATE TABLE courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_code TEXT NOT NULL,
    course_name TEXT NOT NULL,
    academic_year TEXT NOT NULL,
    semester INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(course_code, academic_year, semester)
);

CREATE INDEX idx_academic_year ON courses(academic_year, semester);

-- Course enrollments table
CREATE TABLE course_enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE(student_id, course_id)
);

CREATE INDEX idx_student ON course_enrollments(student_id);
CREATE INDEX idx_course ON course_enrollments(course_id);

-- Grade categories table
CREATE TABLE grade_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id INTEGER NOT NULL,
    category_name TEXT NOT NULL,
    max_score REAL NOT NULL,
    weight REAL DEFAULT 0,
    display_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE INDEX idx_gc_course ON grade_categories(course_id);
CREATE INDEX idx_gc_order ON grade_categories(course_id, display_order);

-- Grades table
CREATE TABLE grades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    score REAL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES grade_categories(id) ON DELETE CASCADE,
    UNIQUE(student_id, category_id)
);

CREATE INDEX idx_student_course ON grades(student_id, course_id);
CREATE INDEX idx_grade_course ON grades(course_id);

-- Attendance table
CREATE TABLE attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    date DATE NOT NULL,
    status TEXT NOT NULL CHECK(status IN ('present', 'absent', 'sick', 'leave', 'late')),
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE(student_id, course_id, date)
);

CREATE INDEX idx_att_student_course ON attendance(student_id, course_id);
CREATE INDEX idx_att_course_date ON attendance(course_id, date);

-- Users table (for teachers/admins)
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    name TEXT NOT NULL,
    role TEXT DEFAULT 'teacher' CHECK(role IN ('teacher', 'admin')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_username ON users(username);

-- Insert default admin user (password: admin123)
-- Note: Change this password immediately after installation!
INSERT INTO users (username, password, name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin');

-- Sample data for testing
INSERT INTO students (student_code, id_card, name, class_level, classroom) VALUES
('S001', '1234567890123', 'สมชาย ใจดี', 'ม.1', '1'),
('S002', '1234567890124', 'สมหญิง รักเรียน', 'ม.1', '1'),
('S003', '1234567890125', 'สมศักดิ์ ขยัน', 'ม.1', '2');
