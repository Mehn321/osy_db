<?php

/**
 * Report Class
 * 
 * Handles report generation
 */

class Report
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Fetch youth profiles for the official Panaon KK profiling spreadsheet.
     */
    public function getYouthProfilingProfiles($filters = [])
    {
        $columnRows = $this->db->fetchAll("SHOW COLUMNS FROM osy_profiles");
        $columnNames = array_column($columnRows, 'Field');
        $addressSelect = in_array('address', $columnNames, true) ? 'address' : "'' AS address";
        $purokSelect = in_array('purok', $columnNames, true) ? 'purok' : "{$addressSelect} AS purok";
        $suffixSelect = in_array('suffix', $columnNames, true) ? 'suffix' : "'' AS suffix";
        $occupationSelect = in_array('occupation', $columnNames, true) ? 'occupation' : "engagement_status AS occupation";
        $provinceSelect = in_array('province', $columnNames, true) ? 'province' : "'Misamis Occidental' AS province";
        $municipalitySelect = in_array('municipality', $columnNames, true) ? 'municipality' : "'Panaon' AS municipality";

        $query = "SELECT last_name, first_name, middle_name, {$suffixSelect}, gender, civil_status,
                         date_of_birth, age, {$addressSelect}, {$purokSelect}, barangay,
                         {$provinceSelect}, {$municipalitySelect}, education_level,
                         engagement_status, {$occupationSelect}, primary_skill, profile_type
                  FROM osy_profiles
                  WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($filters['start_date'])) {
            $query .= " AND created_at >= ?";
            $params[] = $filters['start_date'] . ' 00:00:00';
            $types .= 's';
        }
        if (!empty($filters['end_date'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['end_date'] . ' 23:59:59';
            $types .= 's';
        }
        if (!empty($filters['barangay']) && !in_array($filters['barangay'], ['All Barangays', 'All'], true)) {
            $query .= " AND barangay = ?";
            $params[] = $filters['barangay'];
            $types .= 's';
        }
        if (!empty($filters['gender']) && !in_array($filters['gender'], ['All Genders', 'All'], true)) {
            $query .= " AND gender = ?";
            $params[] = $filters['gender'];
            $types .= 's';
        }
        if (!empty($filters['profile_type']) && !in_array($filters['profile_type'], ['All Types', 'All'], true)) {
            $query .= " AND profile_type = ?";
            $params[] = $filters['profile_type'];
            $types .= 's';
        }
        if (!empty($filters['education']) && !in_array($filters['education'], ['Any Level', 'All'], true)) {
            $query .= " AND education_level = ?";
            $params[] = $filters['education'];
            $types .= 's';
        }
        if (!empty($filters['status']) && !in_array($filters['status'], ['All Status', 'All'], true)) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['verification_status']) && !in_array($filters['verification_status'], ['All Verification', 'All'], true)) {
            $query .= " AND verification_status = ?";
            $params[] = $filters['verification_status'];
            $types .= 's';
        }

        $query .= " ORDER BY barangay ASC, last_name ASC, first_name ASC";

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Export filtered youth profiles using the official Panaon Excel template.
     */
    public function exportPanaonYouthProfiling($filters = [])
    {
        require_once __DIR__ . '/PanaonYouthProfilingExport.php';
        $profiles = $this->getYouthProfilingProfiles($filters);
        $exporter = new PanaonYouthProfilingExport();
        $result = $exporter->export($profiles);
        $result['count'] = count($profiles);
        return $result;
    }

    /**
     * Generate OSY profile report
     */
    public function generateOSYReport($filters = [])
    {
        $query = "SELECT p.*, 
                 COUNT(DISTINCT m.id) as match_count,
                 AVG(m.match_score) as avg_match_score
                 FROM osy_profiles p
                 LEFT JOIN osy_matches m ON p.id = m.osy_id
                 WHERE 1=1";

        if (isset($filters['status'])) {
            $query .= " AND p.status = '{$this->db->escape($filters['status'])}'";
        }

        if (isset($filters['skill'])) {
            $query .= " AND p.primary_skill = '{$this->db->escape($filters['skill'])}'";
        }

        if (isset($filters['barangay'])) {
            $query .= " AND p.barangay = '{$this->db->escape($filters['barangay'])}'";
        }

        $query .= " GROUP BY p.id ORDER BY p.created_at DESC";

        return $this->db->fetchAll($query);
    }

    /**
     * Generate opportunity report
     */
    public function generateOpportunityReport($filters = [])
    {
        $query = "SELECT o.*, 
                 COUNT(DISTINCT m.id) as matched_count,
                 (o.total_slots - COUNT(DISTINCT m.id)) as slots_available
                 FROM opportunities o
                 LEFT JOIN osy_matches m ON o.id = m.opportunity_id
                 WHERE 1=1";

        if (isset($filters['type'])) {
            $query .= " AND o.type = '{$this->db->escape($filters['type'])}'";
        }

        if (isset($filters['status'])) {
            $query .= " AND o.status = '{$this->db->escape($filters['status'])}'";
        }

        $query .= " GROUP BY o.id ORDER BY o.created_at DESC";

        return $this->db->fetchAll($query);
    }

    /**
     * Generate matching statistics report
     */
    public function generateMatchingStats($filters = [])
    {
        $profileWhere = $this->buildProfileFilterSql($filters, 'p');

        return [
            'total_matches_made' => $this->getQueryResult(
                "SELECT COUNT(*) as count FROM osy_matches m JOIN osy_profiles p ON p.id = m.osy_id " . $profileWhere . " AND m.status = 'Accepted'"
            ),
            'pending_matches' => $this->getQueryResult(
                "SELECT COUNT(*) as count FROM osy_matches m JOIN osy_profiles p ON p.id = m.osy_id " . $profileWhere . " AND m.status = 'Pending'"
            ),
            'average_match_score' => $this->getQueryResult(
                "SELECT AVG(m.match_score) as avg FROM osy_matches m JOIN osy_profiles p ON p.id = m.osy_id " . $profileWhere
            ),
            'highest_match_score' => $this->getQueryResult(
                "SELECT MAX(m.match_score) as max FROM osy_matches m JOIN osy_profiles p ON p.id = m.osy_id " . $profileWhere
            ),
            'employment_success_rate' => $this->calculateSuccessRate($filters)
        ];
    }

    /**
     * Calculate employment success rate
     */
    private function calculateSuccessRate($filters = [])
    {
        $where = $this->buildProfileFilterSql($filters, 'p');

        $employed = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM osy_profiles p" . $where . " AND p.status = 'Employed'"
        );
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM osy_profiles p" . $where
        );

        $total_count = $total['count'] > 0 ? $total['count'] : 1;
        return round(($employed['count'] / $total_count) * 100, 2);
    }

    private function buildProfileFilterSql($filters, $alias = '')
    {
        $prefix = $alias !== '' ? $alias . '.' : '';
        $where = ' WHERE 1=1';
        $conditions = [
            'barangay' => 'barangay',
            'gender' => 'gender',
            'profile_type' => 'profile_type',
            'education' => 'education_level',
            'status' => 'status',
            'verification_status' => 'verification_status',
        ];

        if (!empty($filters['start_date'])) {
            $where .= " AND {$prefix}created_at >= '" . $this->db->escape($filters['start_date']) . " 00:00:00'";
        }
        if (!empty($filters['end_date'])) {
            $where .= " AND {$prefix}created_at <= '" . $this->db->escape($filters['end_date']) . " 23:59:59'";
        }

        foreach ($conditions as $filter => $column) {
            $value = $filters[$filter] ?? '';
            $ignored = [
                'barangay' => ['', 'All', 'All Barangays'],
                'gender' => ['', 'All', 'All Genders'],
                'profile_type' => ['', 'All', 'All Types'],
                'education' => ['', 'All', 'Any Level'],
                'status' => ['', 'All', 'All Status'],
                'verification_status' => ['', 'All', 'All Verification'],
            ][$filter];
            if (!in_array($value, $ignored, true)) {
                $where .= " AND {$prefix}{$column} = '" . $this->db->escape($value) . "'";
            }
        }

        return $where;
    }

    /**
     * Helper method to get query result
     */
    private function getQueryResult($query)
    {
        $result = $this->db->fetchOne($query);
        return array_values($result)[0] ?? 0;
    }

    /**
     * Generate monthly activity report
     */
    public function generateMonthlyActivityReport()
    {
        $query = "SELECT 
                 DATE_TRUNC(created_at, MONTH) as month,
                 COUNT(DISTINCT CASE WHEN TABLE_NAME = 'osy_profiles' THEN id END) as new_profiles,
                 COUNT(DISTINCT CASE WHEN TABLE_NAME = 'opportunities' THEN id END) as new_opportunities,
                 COUNT(DISTINCT CASE WHEN TABLE_NAME = 'osy_matches' THEN id END) as new_matches
                 FROM activity_log
                 GROUP BY month
                 ORDER BY month DESC
                 LIMIT 12";

        // Since we don't have activity_log yet, return sample structure
        return [
            "success" => true,
            "message" => "Monthly activity report generated",
            "data" => []
        ];
    }

    /**
     * Export report to CSV
     */
    public function exportToCSV($reportType, $data)
    {
        $filename = $reportType . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = __DIR__ . '/../exports/' . $filename;

        // Create exports directory if not exists
        if (!is_dir(__DIR__ . '/../exports')) {
            mkdir(__DIR__ . '/../exports', 0755, true);
        }

        // Convert data to CSV
        $fp = fopen($filepath, 'w');

        if (is_array($data) && count($data) > 0) {
            // Write headers
            fputcsv($fp, array_keys($data[0]));

            // Write data
            foreach ($data as $row) {
                fputcsv($fp, $row);
            }
        }

        fclose($fp);

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }
}