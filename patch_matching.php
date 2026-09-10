<?php
$file = __DIR__ . '/Classes/Matching.php';
$src = file_get_contents($file);

$bulk = <<<'PHPCODE'

    /**
     * Bulk update match statuses
     */
    public function bulkUpdateStatus(array $matchIds, string $status)
    {
        if (empty($matchIds)) {
            return ['success' => false, 'message' => 'No matches selected'];
        }
        $status = ucfirst(strtolower($status));
        if (!in_array($status, ['Accepted', 'Rejected'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        $placeholders = implode(',', array_fill(0, count($matchIds), '?'));
        $query  = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)";
        $params = array_merge([$status], array_map('intval', $matchIds));
        $types  = 's' . str_repeat('i', count($matchIds));
        try {
            $this->db->execute($query, $params, $types);
            return ['success' => true, 'message' => count($matchIds) . ' record(s) updated to ' . $status];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
PHPCODE;

$pos = strpos($src, "     * Get total matches");
if ($pos === false) {
    die("Could not find insertion point\n");
}
// walk backwards to the `    /**`
$pos = strrpos(substr($src, 0, $pos), "    /**");

$new = substr($src, 0, $pos) . $bulk . "\r\n" . substr($src, $pos);
file_put_contents($file, $new);
echo "Done - bulkUpdateStatus inserted\n";
