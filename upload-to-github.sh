#!/bin/bash

# คำสั่งอัปโหลดโปรเจค edugrade ไป GitHub
# รันสคริปต์นี้เพื่ออัปโหลดโปรเจค

echo "🚀 กำลังอัปโหลดโปรเจค edugrade ไป GitHub..."
echo ""

# ไปที่โฟลเดอร์โปรเจค
cd "/Users/jamies/Library/CloudStorage/OneDrive-ส่วนบุคคล/Kiro/score-v2"

# ลบไฟล์ test
echo "🗑️  ลบไฟล์ test..."
rm -f test_save_grade.php

# ลบ database (จะสร้างใหม่ตอนติดตั้ง)
echo "🗑️  ลบ database..."
rm -f database/score.db

# Initialize git (ถ้ายังไม่ได้ทำ)
if [ ! -d ".git" ]; then
    echo "📦 Initialize git..."
    git init
fi

# Add ทุกไฟล์
echo "➕ Add ไฟล์ทั้งหมด..."
git add .

# Commit
echo "💾 Commit..."
git commit -m "Initial commit: EduGrade - Student Grade & Attendance System"

# เชื่อมต่อกับ GitHub
echo "🔗 เชื่อมต่อกับ GitHub..."
git remote add origin https://github.com/project-sy789/edugrade.git 2>/dev/null || git remote set-url origin https://github.com/project-sy789/edugrade.git

# Push ขึ้น GitHub
echo "⬆️  Push ขึ้น GitHub..."
git branch -M main
git push -u origin main

echo ""
echo "✅ อัปโหลดเสร็จแล้ว!"
echo ""
echo "🌐 URL: https://github.com/project-sy789/edugrade"
echo ""
