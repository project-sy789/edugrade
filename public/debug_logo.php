<?php
// Debug script to check logo setting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database config
$config = require __DIR__ . '/../config/database.php';

// Connect to database
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Debug Logo Settings</h2>";
    
    // Check what's in database
    $stmt = $pdo->query("SELECT * FROM settings WHERE setting_key LIKE '%logo%'");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Settings in Database:</h3>";
    echo "<pre>";
    print_r($settings);
    echo "</pre>";
    
    // Check uploads folder
    echo "<h3>Files in uploads/logos:</h3>";
    $uploadDir = __DIR__ . '/uploads/logos';
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        echo "<pre>";
        print_r($files);
        echo "</pre>";
    } else {
        echo "Directory does not exist: $uploadDir";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
