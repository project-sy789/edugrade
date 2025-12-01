-- Migration: Add Club System Tables
-- Date: 2025-11-29

-- Create clubs table
CREATE TABLE IF NOT EXISTS clubs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    club_name TEXT NOT NULL,
    description TEXT,
    teacher_id INTEGER NOT NULL,
    academic_year TEXT NOT NULL,
    semester INTEGER NOT NULL CHECK(semester IN (1, 2)),
    class_levels TEXT NOT NULL, -- JSON array: ["1","2","3"]
    max_students INTEGER DEFAULT 30,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create club_enrollments table
CREATE TABLE IF NOT EXISTS club_enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    club_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    grade REAL,
    status TEXT DEFAULT 'active' CHECK(status IN ('active', 'dropped')),
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE(student_id, club_id)
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_clubs_academic ON clubs(academic_year, semester);
CREATE INDEX IF NOT EXISTS idx_clubs_teacher ON clubs(teacher_id);
CREATE INDEX IF NOT EXISTS idx_club_enrollments_club ON club_enrollments(club_id);
CREATE INDEX IF NOT EXISTS idx_club_enrollments_student ON club_enrollments(student_id);
