<?php

/**
 * Reference Class
 * 
 * Handles fetching static reference data (Purok, Skills, Education levels, etc.) used in dropdowns.
 */

class Reference
{
    private $db;
    private $table = 'system_references';

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Get active references by category
     * 
     * @param string $category The category to fetch (e.g. 'purok', 'primary_skill')
     * @return array
     */
    public function getByCategory($category)
    {
        $query = "SELECT value FROM {$this->table} WHERE category = ? AND is_active = 1 ORDER BY id ASC";
        $results = $this->db->fetchAll($query, [$category], "s");
        
        $flattened = [];
        foreach ($results as $row) {
            $flattened[] = $row['value'];
        }
        
        return $flattened;
    }
}
