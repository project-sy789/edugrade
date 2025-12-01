<?php
/**
 * Installation Script for Student Grade & Attendance System
 * 
 * This script will:
 * 1. Create necessary directories
 * 2. Set up the database
 * 3. Create admin user
 * 4. Start the development server
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   Student Grade & Attendance System - Installation      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    die("❌ Error: PHP 8.0 or higher is required. Current version: " . PHP_VERSION . "\n");
}

echo "✅ PHP Version: " . PHP_VERSION . "\n";

// Check SQLite extension
if (!extension_loaded('sqlite3')) {
    die("❌ Error: SQLite3 extension is not installed.\n");
}

echo "✅ SQLite3 extension is installed\n\n";

// Step 1: Create directories
echo "📁 Creating directories...\n";

$directories = [
    'database',
    'uploads',
    'uploads/logos',
    'sessions',
    'logs'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "   ✓ Created: $dir\n";
    } else {
        echo "   ⊙ Exists: $dir\n";
    }
}

// Create .gitkeep files
$gitkeepDirs = ['uploads', 'uploads/logos', 'sessions', 'logs'];
foreach ($gitkeepDirs as $dir) {
    $gitkeepFile = $dir . '/.gitkeep';
    if (!file_exists($gitkeepFile)) {
        touch($gitkeepFile);
    }
}

echo "\n";

// Step 2: Check if database exists
$dbFile = 'database/score.db';
$dbExists = file_exists($dbFile);

if ($dbExists) {
    echo "⚠️  Database already exists at: $dbFile\n";
    echo "   Do you want to recreate it? (This will delete all data) [y/N]: ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'y') {
        echo "   Skipping database creation.\n\n";
        goto skip_db;
    }
    
    unlink($dbFile);
    echo "   ✓ Deleted old database\n";
}

echo "🗄️  Creating database...\n";

// Create database from schema
$schemaFile = 'database/schema_sqlite.sql';
if (!file_exists($schemaFile)) {
    die("❌ Error: Schema file not found: $schemaFile\n");
}

$schema = file_get_contents($schemaFile);
$db = new SQLite3($dbFile);

// Execute schema
$db->exec($schema);

echo "   ✓ Database created successfully\n\n";

skip_db:

// Step 3: Create admin user
echo "👤 Creating admin user...\n";

// Check if admin exists
$db = new SQLite3($dbFile);
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
$row = $result->fetchArray(SQLITE3_ASSOC);

if ($row['count'] > 0) {
    echo "   ⊙ Admin user already exists\n\n";
} else {
    $password = password_hash('password', PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (username, password, name, role) VALUES (:username, :password, :name, :role)");
    $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':name', 'ผู้ดูแลระบบ', SQLITE3_TEXT);
    $stmt->bindValue(':role', 'admin', SQLITE3_TEXT);
    $stmt->execute();
    
    echo "   ✓ Admin user created\n";
    echo "   📝 Username: admin\n";
    echo "   📝 Password: password\n";
    echo "   ⚠️  Please change the password after first login!\n\n";
}

$db->close();

// Step 4: Installation complete
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              🎉 Installation Complete! 🎉               ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "📌 Next steps:\n";
echo "\n";
echo "1. Start the development server:\n";
echo "   php -S localhost:8000 -t public\n";
echo "\n";
echo "2. Open your browser:\n";
echo "   http://localhost:8000\n";
echo "\n";
echo "3. Login with:\n";
echo "   Username: admin\n";
echo "   Password: password\n";
echo "\n";
echo "4. ⚠️  Change admin password immediately!\n";
echo "\n";
echo "📚 For more information, see README.md\n";
echo "\n";

// Ask if user wants to start the server
echo "Do you want to start the development server now? [Y/n]: ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'n') {
    echo "\n🚀 Starting development server on http://localhost:8000\n";
    echo "   Press Ctrl+C to stop\n\n";
    passthru('php -S localhost:8000 -t public');
}

echo "\n✨ Thank you for using Student Grade & Attendance System!\n\n";
