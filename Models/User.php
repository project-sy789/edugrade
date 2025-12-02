<?php

namespace App\Models;

/**
 * User Model
 * 
 * Handles teacher/admin user management and authentication.
 */
class User
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int User ID
     * @throws \Exception if validation fails or username exists
     */
    public function create($data)
    {
        $this->validate($data);
        
        // Check if username already exists
        if ($this->usernameExists($data['username'])) {
            throw new \Exception('ชื่อผู้ใช้นี้มีอยู่แล้ว');
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->db->insert('users', [
            'username' => $data['username'],
            'password' => $hashedPassword,
            'name' => $data['name'],
            'role' => $data['role'] ?? 'teacher'
        ]);
    }
    
    /**
     * Update user information
     * 
     * @param int $id User ID
     * @param array $data User data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        $updateData = [];
        
        if (isset($data['username'])) {
            if ($this->usernameExists($data['username'], $id)) {
                throw new \Exception('ชื่อผู้ใช้นี้มีอยู่แล้ว');
            }
            $updateData['username'] = $data['username'];
        }
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        if (isset($data['role'])) {
            $updateData['role'] = $data['role'];
        }
        
        // Update password only if provided
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($updateData)) {
            return 0;
        }
        
        return $this->db->update('users', $updateData, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        return $this->db->delete('users', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            'SELECT id, username, name, role, created_at FROM users WHERE id = :id',
            [':id' => $id]
        );
    }
    
    /**
     * Find user by username
     * 
     * @param string $username Username
     * @return array|false User data or false if not found
     */
    public function findByUsername($username)
    {
        return $this->db->fetchOne(
            'SELECT id, username, password, name, role, created_at FROM users WHERE username = :username',
            [':username' => $username]
        );
    }
    
    /**
     * Get all users
     * 
     * @return array Array of users
     */
    public function getAll()
    {
        return $this->db->fetchAll(
            'SELECT id, username, name, role, created_at FROM users ORDER BY created_at DESC'
        );
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username Username
     * @param string $password Password
     * @return array|false User data if authenticated, false otherwise
     */
    public function authenticate($username, $password)
    {
        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE username = :username',
            [':username' => $username]
        );
        
        if (!$user) {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // Remove password from returned data
        unset($user['password']);
        
        return $user;
    }
    
    /**
     * Change user password
     * 
     * @param int $id User ID
     * @param string $oldPassword Old password
     * @param string $newPassword New password
     * @return bool Success status
     * @throws \Exception if old password is incorrect
     */
    public function changePassword($id, $oldPassword, $newPassword)
    {
        // Get user with password
        $user = $this->db->fetchOne(
            'SELECT password FROM users WHERE id = :id',
            [':id' => $id]
        );
        
        if (!$user) {
            throw new \Exception('ไม่พบผู้ใช้');
        }
        
        // Verify old password
        if (!password_verify($oldPassword, $user['password'])) {
            throw new \Exception('รหัสผ่านเดิมไม่ถูกต้อง');
        }
        
        // Update to new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update(
            'users',
            ['password' => $hashedPassword],
            'id = :id',
            [':id' => $id]
        );
        
        return true;
    }
    
    /**
     * Validate user data
     * 
     * @param array $data User data
     * @throws \Exception if validation fails
     */
    private function validate($data)
    {
        if (empty($data['username'])) {
            throw new \Exception('ชื่อผู้ใช้จำเป็นต้องกรอก');
        }
        
        if (empty($data['password'])) {
            throw new \Exception('รหัสผ่านจำเป็นต้องกรอก');
        }
        
        if (empty($data['name'])) {
            throw new \Exception('ชื่อ-นามสกุลจำเป็นต้องกรอก');
        }
        
        // Validate role
        if (isset($data['role']) && !in_array($data['role'], ['teacher', 'admin'])) {
            throw new \Exception('บทบาทต้องเป็น teacher หรือ admin');
        }
        
        // Validate password length
        if (strlen($data['password']) < 6) {
            throw new \Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        }
    }
    
    /**
     * Check if username already exists
     * 
     * @param string $username Username to check
     * @param int|null $excludeId User ID to exclude from check
     * @return bool True if username exists
     */
    private function usernameExists($username, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*) as count FROM users WHERE username = :username';
        $params = [':username' => $username];
        
        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Count users by role
     * 
     * @param string $role Role to count
     * @return int Count of users
     */
    public function countByRole($role)
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM users WHERE role = :role',
            [':role' => $role]
        );
        return $result['count'] ?? 0;
    }
    
    /**
     * Search users by username or name
     * 
     * @param string $query Search query
     * @return array Array of users
     */
    public function search($query)
    {
        return $this->db->fetchAll(
            'SELECT id, username, name, role, created_at FROM users 
             WHERE username LIKE :query OR name LIKE :query 
             ORDER BY created_at DESC',
            [':query' => '%' . $query . '%']
        );
    }
}
