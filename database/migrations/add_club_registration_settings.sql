-- Migration: Add Club Registration Period Settings
-- Date: 2025-12-11

-- Add settings for club registration period control
INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES
('club_registration_mode', 'manual', NOW(), NOW()),
('club_registration_manual_status', '0', NOW(), NOW()),
('club_registration_start', NULL, NOW(), NOW()),
('club_registration_end', NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Verify settings were added
SELECT * FROM settings WHERE setting_key LIKE 'club_registration%';
