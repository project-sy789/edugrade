<?php
/**
 * Database Configuration
 * 
 * SQLite configuration for easy testing without MySQL server.
 * For production with MySQL, change driver to 'mysql' and configure host/username/password.
 */

return [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/../database/student_grade_system.db',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

/* MySQL Configuration (for production)
return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'student_grade_system',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
*/
