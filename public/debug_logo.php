<?php
// Debug script to check logo setting
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

echo "<h2>Debug Logo Settings</h2>";

// Check what's in database
$db = App\Models\Database::getInstance();
$settings = $db->fetchAll("SELECT * FROM settings WHERE setting_key LIKE '%logo%'");

echo "<h3>Settings in Database:</h3>";
echo "<pre>";
print_r($settings);
echo "</pre>";

echo "<h3>Logo Path Function Result:</h3>";
echo "logoPath() = " . logoPath();

echo "<h3>Direct Setting Calls:</h3>";
echo "setting('logo_path') = " . setting('logo_path') . "<br>";
echo "setting('site_logo') = " . setting('site_logo') . "<br>";

echo "<h3>File Check:</h3>";
$logoPath = setting('logo_path');
if ($logoPath) {
    $fullPath = __DIR__ . '/public' . $logoPath;
    echo "Full path: $fullPath<br>";
    echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "<br>";
}
?>
