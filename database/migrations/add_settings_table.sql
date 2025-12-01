-- Website Settings Table
-- Stores customizable website configuration

CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES
    ('site_name', 'ระบบจัดการโรงเรียน'),
    ('school_name', 'โรงเรียนตัวอย่าง'),
    ('logo_path', '/images/logo.png'),
    ('favicon_path', '/images/favicon.ico');

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_setting_key ON settings(setting_key);
