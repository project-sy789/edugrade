-- Migration: Add Club Registration Period Settings
-- Date: 2025-12-11

-- Select database
USE subyaisite_edugrade;

-- Add settings for club registration period control
INSERT INTO settings (setting_key, setting_value) VALUES
('club_registration_mode', 'manual'),
('club_registration_manual_status', '0'),
('club_registration_start', NULL),
('club_registration_end', NULL)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Verify settings were added
SELECT * FROM settings WHERE setting_key LIKE 'club_registration%';
