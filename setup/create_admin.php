<?php
/**
 * Create Admin User Script
 * 
 * Run this script from command line to create the first admin/teacher account.
 * Usage: php setup/create_admin.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

echo "=== สร้างบัญชีผู้ดูแลระบบ ===\n\n";

// Get input from command line
echo "ชื่อผู้ใช้: ";
$username = trim(fgets(STDIN));

echo "รหัสผ่าน: ";
$password = trim(fgets(STDIN));

echo "ชื่อ-นามสกุล: ";
$name = trim(fgets(STDIN));

echo "บทบาท (teacher/admin) [admin]: ";
$role = trim(fgets(STDIN));
if (empty($role)) {
    $role = 'admin';
}

// Validate
if (empty($username) || empty($password) || empty($name)) {
    die("❌ กรุณากรอกข้อมูลให้ครบถ้วน\n");
}

if (!in_array($role, ['teacher', 'admin'])) {
    die("❌ บทบาทต้องเป็น teacher หรือ admin\n");
}

try {
    $userModel = new User();
    
    $userId = $userModel->create([
        'username' => $username,
        'password' => $password,
        'name' => $name,
        'role' => $role
    ]);
    
    echo "\n✅ สร้างบัญชีสำเร็จ!\n";
    echo "ID: {$userId}\n";
    echo "ชื่อผู้ใช้: {$username}\n";
    echo "ชื่อ: {$name}\n";
    echo "บทบาท: {$role}\n";
    
} catch (Exception $e) {
    echo "\n❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
}
