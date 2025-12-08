# Database Migrations

This directory contains SQL migration scripts for the EduGrade system.

## Active Migration Files

### For New Installations
- **`complete_setup.sql`** - Complete database setup for fresh installations
  - Run this if setting up the system for the first time
  - Creates all tables with proper schema

### For Existing Installations
- **`apply_all_migrations.sql`** - All-in-one migration script
  - Run this to apply all pending migrations at once
  - Safe to run multiple times (uses IF NOT EXISTS)
  - Includes: settings table, attendance period, max_score, club columns

### Data Fixes
- **`fix_null_teacher_id.sql`** - Fix courses with NULL teacher_id
  - Run this if old courses don't show teacher names
  - Updates existing courses to assign a teacher

## Archived Migrations
The `archive/` folder contains individual migration files that have been consolidated into `apply_all_migrations.sql`. These are kept for reference but you don't need to run them individually.

## How to Use

### New Installation
1. Run `complete_setup.sql` in phpMyAdmin

### Existing Installation (Updating)
1. Run `apply_all_migrations.sql` in phpMyAdmin
2. If needed, run `fix_null_teacher_id.sql` to fix old data

## Notes
- Always backup your database before running migrations
- Migrations use `IF NOT EXISTS` to prevent errors when run multiple times
- Check the SQL comments in each file for specific instructions
