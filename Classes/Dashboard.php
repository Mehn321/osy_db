<?php

/**
 * Dashboard Class
 * 
 * Provides dashboard statistics and analytics
 */

class Dashboard
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        return [
            'total_kk' => $this->getTotalKK(),
            'total_osy' => $this->getTotalOSY(),
            'active_osy' => $this->getActiveOSY(),
            'employed_osy' => $this->getEmployedOSY(),
            'total_opportunities' => $this->getTotalOpportunities(),
            'total_matches' => $this->getTotalMatches(),
            'accepted_matches' => $this->getAcceptedMatches(),
            'pending_matches' => $this->getPendingMatches(),
            'total_notifications_sent' => $this->getTotalNotificationsSent()
        ];
    }

    /**
     * Get statistics for SK Chairman (Barangay-scoped)
     */
    public function getSKStats($barangay)
    {
        $stats = [];
        
        $res = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM osy_profiles WHERE barangay = ?", [$barangay]);
        $stats['total_kk'] = $res['cnt'] ?? 0;
        
        $res = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM osy_profiles WHERE barangay = ? AND verification_status IN ('Pending', 'Drafting', 'Action Required')", [$barangay]);
        $stats['pending_verification'] = $res['cnt'] ?? 0;
        
        $res = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM osy_profiles WHERE barangay = ? AND verification_status = 'Verified'", [$barangay]);
        $stats['verified_youth'] = $res['cnt'] ?? 0;
        
        return $stats;
    }

    /**
     * Get statistics for Provider (Employer/Training Provider)
     */
    public function getProviderStats($userId)
    {
        $stats = [];
        
        $res = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM opportunities WHERE created_by = ?", [$userId]);
        $stats['total_posted'] = $res['cnt'] ?? 0;
        
        // Count matches/applications for their opportunities
        $res = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM osy_matches m 
                                   JOIN opportunities o ON m.opportunity_id = o.id 
                                   WHERE o.created_by = ?", [$userId]);
        $stats['total_applications'] = $res['cnt'] ?? 0;
        
        return $stats;
    }

    /**
     * Get total KK (all registered youth)
     */
    public function getTotalKK()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM osy_profiles");
        return $result['total'] ?? 0;
    }

    /**
     * Get total OSY count
     */
    public function getTotalOSY()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM osy_profiles WHERE profile_type = 'OSY'");
        return $result['total'] ?? 0;
    }

    /**
     * Get active OSY count
     */
    public function getActiveOSY()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM osy_profiles WHERE status = 'Active'");
        return $result['total'] ?? 0;
    }

    /**
     * Get employed OSY count
     */
    public function getEmployedOSY()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM osy_profiles WHERE status = 'Employed'");
        return $result['total'] ?? 0;
    }

    /**
     * Get total opportunities
     */
    public function getTotalOpportunities()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM opportunities WHERE status = 'Open'");
        return $result['total'] ?? 0;
    }

    /**
     * Get total matches
     */
    public function getTotalMatches()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM osy_matches");
        return $result['total'] ?? 0;
    }

    /**
     * Get accepted matches
     */
    public function getAcceptedMatches()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM osy_matches WHERE status = 'Accepted'");
        return $result['total'] ?? 0;
    }

    /**
     * Get pending matches
     */
    public function getPendingMatches()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM osy_matches WHERE status = 'Pending'");
        return $result['total'] ?? 0;
    }

    /**
     * Get total notifications sent
     */
    public function getTotalNotificationsSent()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM notifications");
        return $result['total'] ?? 0;
    }

    /**
     * Get skill distribution
     */
    public function getSkillDistribution()
    {
        $query = "SELECT primary_skill, COUNT(*) as count FROM osy_profiles 
                 WHERE primary_skill IS NOT NULL 
                 AND primary_skill != '' 
                 AND primary_skill != 'Not Specified'
                 GROUP BY primary_skill 
                 ORDER BY count DESC 
                 LIMIT 10";
        return $this->db->fetchAll($query);
    }

    /**
     * Get status distribution
     */
    public function getStatusDistribution()
    {
        $query = "SELECT status, COUNT(*) as count FROM osy_profiles GROUP BY status";
        return $this->db->fetchAll($query);
    }

    /**
     * Get recent registrations
     */
    public function getRecentRegistrations($limit = 5)
    {
        $query = "SELECT id, first_name, last_name, email, age, primary_skill, status, created_at 
                 FROM osy_profiles ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($query, [$limit], "i");
    }

    /**
     * Get recent registrations by barangay
     */
    public function getRecentRegistrationsByBarangay($barangay, $limit = 5)
    {
        $query = "SELECT id, first_name, last_name, email, age, primary_skill, status, created_at 
                 FROM osy_profiles WHERE barangay = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($query, [$barangay, $limit], "si");
    }

    /**
     * Get recent opportunities
     */
    public function getRecentOpportunities($limit = 5)
    {
        $query = "SELECT id, title, type, location, deadline, status, created_at 
                 FROM opportunities ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($query, [$limit], "i");
    }

    /**
     * Get recent opportunities by provider
     */
    public function getRecentOpportunitiesByProvider($userId, $limit = 5)
    {
        $query = "SELECT id, title, type, location, deadline, status, created_at 
                 FROM opportunities WHERE created_by = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($query, [$userId, $limit], "ii");
    }

    /**
     * Get top matched OSY (employment ready)
     */
    public function getTopMatchedOSY($limit = 5)
    {
        $query = "SELECT p.id, p.first_name, p.last_name, p.primary_skill, COUNT(m.id) as match_count, AVG(m.match_score) as avg_score
                 FROM osy_profiles p
                 LEFT JOIN osy_matches m ON p.id = m.osy_id
                 GROUP BY p.id
                 ORDER BY match_count DESC, avg_score DESC
                 LIMIT ?";
        return $this->db->fetchAll($query, [$limit], "i");
    }

    /**
     * Get opportunities needing matches
     */
    public function getOpportunitiesNeedingMatches($limit = 5)
    {
        $query = "SELECT o.id, o.title, o.type, o.total_slots, 
                 COUNT(m.id) as matched_count, (o.total_slots - COUNT(m.id)) as slots_needed
                 FROM opportunities o
                 LEFT JOIN osy_matches m ON o.id = m.opportunity_id
                 WHERE o.status = 'Open'
                 GROUP BY o.id
                 HAVING slots_needed > 0
                 ORDER BY slots_needed DESC
                 LIMIT ?";
        return $this->db->fetchAll($query, [$limit], "i");
    }
}
