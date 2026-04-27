<?php

/**
 * Matching Class
 * 
 * Handles skills matching algorithm and matching operations
 */

class Matching
{
    private $db;
    private $table = 'osy_matches';

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Create a match between OSY and opportunity
     */
    public function createMatch($osy_id, $opportunity_id, $match_score)
    {
        try {
            $query = "INSERT INTO {$this->table} 
                     (osy_id, opportunity_id, match_score, status, created_at) 
                     VALUES (?, ?, ?, 'Pending', NOW())";

            $this->db->execute($query, [$osy_id, $opportunity_id, $match_score], "iii");

            return [
                'success' => true,
                'message' => 'Match created successfully',
                'id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate match score between OSY and opportunity
     */
    public function calculateMatchScore($osy_id, $opportunity_id)
    {
        try {
            // Get OSY profile with all relevant fields
            $osy = $this->db->fetchOne(
                "SELECT primary_skill, skills, interests, education_level, age, purok FROM osy_profiles WHERE id = ? LIMIT 1",
                [$osy_id],
                "i"
            );

            if (!$osy) {
                throw new Exception("OSY profile not found");
            }

            // Get opportunity with all relevant fields
            $opportunity = $this->db->fetchOne(
                "SELECT title, description, location, certification FROM opportunities WHERE id = ? LIMIT 1",
                [$opportunity_id],
                "i"
            );

            if (!$opportunity) {
                throw new Exception("Opportunity not found");
            }

            $score = 0;
            $maxScore = 100;
            
            // Text to match against
            $oppText = strtolower($opportunity['title'] . ' ' . $opportunity['description']);
            $oppLoc = strtolower($opportunity['location']);

            // 1. Primary Skill Match (up to 30 points)
            $pSkill = strtolower($osy['primary_skill']);
            if (strpos($oppText, $pSkill) !== false) {
                $score += 30;
            } else {
                // Synonyms Check
                $synonyms = [
                    'cooking' => ['culinary', 'kitchen', 'food', 'chef', 'baking', 'cook'],
                    'driving' => ['driver', 'truck', 'delivery', 'transport', 'logistics', 'vehicle'],
                    'welding' => ['welder', 'metal', 'fabrication', 'construction'],
                    'it' => ['computer', 'tech', 'software', 'hardware', 'programming', 'developer', 'coding'],
                    'sales' => ['retail', 'cashier', 'marketing', 'customer service', 'store'],
                    'housekeeping' => ['cleaning', 'maid', 'maintenance', 'janitor']
                ];
                
                foreach ($synonyms as $key => $related) {
                    if (strpos($pSkill, $key) !== false || in_array($pSkill, $related)) {
                        foreach ($related as $syn) {
                            if (strpos($oppText, $syn) !== false) {
                                $score += 20; // Partial points for synonym match
                                break 2;
                            }
                        }
                    }
                }
            }

            // 2. Secondary Skills Match (up to 20 points)
            if (!empty($osy['skills'])) {
                $secondarySkills = array_map('trim', explode(',', strtolower($osy['skills'])));
                $skillPoints = 0;
                foreach ($secondarySkills as $ss) {
                    if (!empty($ss) && $ss !== $pSkill && strpos($oppText, $ss) !== false) {
                        $skillPoints += 10;
                    }
                }
                $score += min(20, $skillPoints);
            }

            // 3. Interests Alignment (up to 15 points)
            if (!empty($osy['interests'])) {
                $interests = array_map('trim', explode(',', strtolower($osy['interests'])));
                foreach ($interests as $interest) {
                    if (!empty($interest) && strpos($oppText, $interest) !== false) {
                        $score += 15;
                        break;
                    }
                }
            }

            // 4. Education Level Fit (up to 20 points)
            $eduLevel = $osy['education_level'];
            $reqText = strtolower($opportunity['certification'] . ' ' . $opportunity['description']);
            
            if (strpos($reqText, 'college') !== false || strpos($reqText, 'degree') !== false) {
                if ($eduLevel == 'College Graduate') $score += 20;
                elseif ($eduLevel == 'College Undergraduate') $score += 10;
            } elseif (strpos($reqText, 'high school') !== false || strpos($reqText, 'shs') !== false) {
                if (in_array($eduLevel, ['High School Graduate', 'Senior High School Graduate', 'College Undergraduate', 'College Graduate'])) {
                    $score += 20;
                }
            } else {
                $score += 15; // Generic point if no specific high-level education required
            }

            // 5. Location Proximity (up to 15 points)
            $purok = strtolower(str_replace('Purok ', 'p', $osy['purok'])); // Convert "Purok 1" to "p1"
            if (strpos($oppLoc, $purok) !== false || strpos($oppLoc, strtolower($osy['purok'])) !== false) {
                $score += 15;
            } elseif (strpos($oppLoc, 'any') !== false || strpos($oppLoc, 'remote') !== false || empty($opportunity['location'])) {
                $score += 10;
            }

            // Ensure minimum base score if they have any match
            if ($score > 0 && $score < 40) {
                $score += 20; // Boost baseline for partial fits
            }

            return min(100, max(0, $score));
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get matches for OSY
     */
    public function getMatchesForOSY($osy_id, $min_score = 0)
    {
        $query = "SELECT m.*, 
                 o.title, o.type, o.location, o.compensation,
                 p.first_name, p.last_name, p.primary_skill
                 FROM {$this->table} m
                 JOIN opportunities o ON m.opportunity_id = o.id
                 JOIN osy_profiles p ON m.osy_id = p.id
                 WHERE m.osy_id = ? AND m.match_score >= ?
                 ORDER BY m.match_score DESC";

        return $this->db->fetchAll($query, [$osy_id, $min_score], "ii");
    }

    /**
     * Get matches for opportunity
     */
    public function getMatchesForOpportunity($opportunity_id, $min_score = 0)
    {
        $query = "SELECT m.*, 
                 p.first_name, p.last_name, p.age, p.email, p.phone, p.primary_skill,
                 o.title, o.type
                 FROM {$this->table} m
                 JOIN osy_profiles p ON m.osy_id = p.id
                 JOIN opportunities o ON m.opportunity_id = o.id
                 WHERE m.opportunity_id = ? AND m.match_score >= ?
                 ORDER BY m.match_score DESC";

        return $this->db->fetchAll($query, [$opportunity_id, $min_score], "ii");
    }

    /**
     * Update match status
     */
    public function updateMatchStatus($match_id, $status)
    {
        try {
            $query = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, [$status, $match_id], "si");

            return [
                'success' => true,
                'message' => 'Match status updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get total matches
     */
    public function getTotalCount()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'Accepted'");
        return $result['total'];
    }

    /**
     * Find best matches for an opportunity
     */
    public function findBestMatches($opportunity_id, $limit = 10)
    {
        $query = "SELECT m.*, 
                 p.first_name, p.last_name, p.age, p.primary_skill, p.education_level,
                 o.title
                 FROM {$this->table} m
                 JOIN osy_profiles p ON m.osy_id = p.id
                 JOIN opportunities o ON m.opportunity_id = o.id
                 WHERE m.opportunity_id = ?
                 ORDER BY m.match_score DESC
                 LIMIT ?";

        return $this->db->fetchAll($query, [$opportunity_id, $limit], "ii");
    }

    /**
     * Generate matches for all OSY against an opportunity
     */
    public function generateMatches($opportunity_id)
    {
        try {
            // Get all OSY profiles
            $osy_list = $this->db->fetchAll("SELECT id FROM osy_profiles");

            $created = 0;

            foreach ($osy_list as $osy) {
                // Check if match already exists
                $existing = $this->db->fetchOne(
                    "SELECT id FROM {$this->table} WHERE osy_id = ? AND opportunity_id = ? LIMIT 1",
                    [$osy['id'], $opportunity_id],
                    "ii"
                );

                if (!$existing) {
                    $score = $this->calculateMatchScore($osy['id'], $opportunity_id);
                    $this->createMatch($osy['id'], $opportunity_id, $score);
                    $created++;
                }
            }

            return [
                'success' => true,
                'message' => "Matches generated successfully ($created created)",
                'created' => $created
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
