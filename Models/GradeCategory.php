<?php

namespace App\Models;

/**
 * GradeCategory Model
 * 
 * Handles grade category management for courses.
 */
class GradeCategory
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new grade category
     * 
     * @param int $courseId Course ID
     * @param array $data Category data
     * @return int Category ID
     */
    public function create($courseId, $data)
    {
        $this->validate($data);
        
        // Get next display order
        $order = $this->getNextOrder($courseId);
        
        return $this->db->insert('grade_categories', [
            'course_id' => $courseId,
            'category_name' => $data['category_name'],
            'max_score' => $data['max_score'],
            'weight' => $data['weight'] ?? 0,
            'display_order' => $order
        ]);
    }
    
    /**
     * Update grade category
     * 
     * @param int $id Category ID
     * @param array $data Category data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        $this->validate($data);
        
        $updateData = [
            'category_name' => $data['category_name'],
            'max_score' => $data['max_score'],
            'weight' => $data['weight'] ?? 0
        ];
        
        if (isset($data['display_order'])) {
            $updateData['display_order'] = $data['display_order'];
        }
        
        return $this->db->update(
            'grade_categories',
            $updateData,
            'id = :id',
            [':id' => $id]
        );
    }
    
    /**
     * Delete grade category
     * 
     * @param int $id Category ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        return $this->db->delete('grade_categories', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Find category by ID
     * 
     * @param int $id Category ID
     * @return array|false Category data or false if not found
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            'SELECT * FROM grade_categories WHERE id = :id',
            [':id' => $id]
        );
    }
    
    /**
     * Get all categories for a course
     * 
     * @param int $courseId Course ID
     * @return array Array of categories
     */
    public function findByCourse($courseId)
    {
        return $this->db->fetchAll(
            'SELECT * FROM grade_categories 
             WHERE course_id = :course_id 
             ORDER BY display_order, id',
            [':course_id' => $courseId]
        );
    }
    
    /**
     * Reorder categories
     * 
     * @param array $orders Array of ['id' => order] pairs
     * @return bool Success status
     */
    public function reorder($orders)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($orders as $id => $order) {
                $this->db->update(
                    'grade_categories',
                    ['display_order' => $order],
                    'id = :id',
                    [':id' => $id]
                );
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get next display order for a course
     * 
     * @param int $courseId Course ID
     * @return int Next order number
     */
    private function getNextOrder($courseId)
    {
        $result = $this->db->fetchOne(
            'SELECT MAX(display_order) as max_order FROM grade_categories WHERE course_id = :course_id',
            [':course_id' => $courseId]
        );
        
        return ($result['max_order'] ?? -1) + 1;
    }
    
    /**
     * Validate category data
     * 
     * @param array $data Category data
     * @throws \Exception if validation fails
     */
    private function validate($data)
    {
        if (empty($data['category_name'])) {
            throw new \Exception('ชื่อหมวดคะแนนจำเป็นต้องกรอก');
        }
        
        if (!isset($data['max_score']) || $data['max_score'] <= 0) {
            throw new \Exception('คะแนนเต็มต้องมากกว่า 0');
        }
    }
}
