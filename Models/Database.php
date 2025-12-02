<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Database Base Class
 * 
 * Provides PDO connection and common database operations.
 */
class Database
{
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';
        
        try {
            // Check if using SQLite or MySQL
            if (isset($config['driver']) && $config['driver'] === 'sqlite') {
                // SQLite connection
                $dsn = 'sqlite:' . $config['database'];
                
                $this->connection = new PDO(
                    $dsn,
                    null,
                    null,
                    $config['options']
                );
                
                // Enable foreign keys for SQLite
                $this->connection->exec('PRAGMA foreign_keys = ON');
            } else {
                // MySQL connection
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    $config['host'],
                    $config['database'],
                    $config['charset']
                );
                
                $this->connection = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            }
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new \Exception('ไม่สามารถเชื่อมต่อฐานข้อมูลได้');
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Execute a query and return the statement
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return \PDOStatement
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql . ' | Params: ' . json_encode($params));
            // Show actual error in development
            throw new \Exception('Database Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Fetch all rows from a query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single row from a query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|false
     */
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Insert a record and return the last insert ID
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     */
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        $this->query($sql, $params);
        return (int) $this->connection->lastInsertId();
    }
    
    /**
     * Update records
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause (e.g., "id = :id")
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $setParts = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :set_$key";
            $params[":set_$key"] = $value;
        }
        
        $params = array_merge($params, $whereParams);
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            $where
        );
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete records
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = [])
    {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->connection->rollBack();
    }
    
    /**
     * Check if currently in a transaction
     * 
     * @return bool
     */
    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }
}
