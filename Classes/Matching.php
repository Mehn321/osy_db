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
    private $matchingConfig = []; // holds weights and filter flags

    public function __construct($database)
    {
        $this->db = $database;
        // Load matching configuration (weights, filters)
        $configPath = __DIR__ . '/../config/matching.php';
        if (file_exists($configPath)) {
            $this->matchingConfig = require $configPath;
        } else {
            // Default fallback configuration
                $this->matchingConfig = [
                    'demographic_weight' => 0.4,
                    'semantic_weight'    => 0.6,
                    'filter_age'        => true,
                    'filter_location'   => true,
                    'filter_education'  => true,
                    'filter_gender'     => false,
                ];
        }

    }

    /**
     * Filter demographic criteria based on config flags.
     * Returns true if OSY passes all enabled demographic checks.
     */
    private function passesDemographicFilters(array $osy, array $opp): bool
    {
        // Age range
        if (!empty($this->matchingConfig['filter_age'])) {
            $userAge = intval($osy['age'] ?? 0);
            $minAge = intval($opp['age_min'] ?? 0);
            $maxAge = intval($opp['age_max'] ?? 0);
            if ($userAge > 0) {
                if ($minAge > 0 && $userAge < $minAge) return false;
                if ($maxAge > 0 && $userAge > $maxAge) return false;
            }
        }

        // Education level
        if (!empty($this->matchingConfig['filter_education'])) {
            $eduLevel = $osy['education_level'] ?? '';
            $reqText = strtolower(($opp['certification'] ?? '') . ' ' . ($opp['description'] ?? ''));
            $hasCollege = strpos($reqText, 'college') !== false || strpos($reqText, 'degree') !== false;
            $hasHigh = strpos($reqText, 'high school') !== false || strpos($reqText, 'shs') !== false;
            if ($hasCollege && !in_array($eduLevel, ['College Graduate', 'College Undergraduate'])) {
                return false;
            }
            if ($hasHigh && !in_array($eduLevel, ['High School Graduate', 'Senior High School Graduate', 'College Undergraduate', 'College Graduate'])) {
                return false;
            }
        }

        // Location proximity / remote
        if (!empty($this->matchingConfig['filter_location'])) {
            $barangay = strtolower($osy['barangay'] ?? '');
            $oppLoc = strtolower($opp['location'] ?? '');
            if (strpos($oppLoc, $barangay) === false &&
                strpos($oppLoc, 'any') === false &&
                strpos($oppLoc, 'remote') === false &&
                !empty($opp['location'])) {
                return false;
            }
        }

        // Gender (optional)
        if (!empty($this->matchingConfig['filter_gender'])) {
            if (isset($osy['gender']) && isset($opp['gender'])) {
                if (strtolower($osy['gender']) !== strtolower($opp['gender'])) return false;
            }
        }

        return true;
    }

    /**
     * Obtain semantic skill alignment score via Gemini.
     * Returns false on API failure.
     */
    private function calculateSemanticScore(array $osy, array $opp)
    {
        require_once __DIR__ . '/GeminiService.php';
        $gemini = new GeminiService($this->db);
        $score = $gemini->calculateScoreOnly($osy, $opp);
        return $score; // may be false
    }

    /**
     * Hybrid score combining demographic filter score and Gemini semantic score.
     */
    private function calculateHybridScore($osy_id, $opportunity_id)
    {
        // Load data
        $osy = $this->db->fetchOne(
            "SELECT primary_skill, skills, interests, education_level, age, barangay, gender FROM osy_profiles WHERE id = ? LIMIT 1",
            [$osy_id],
            "i"
        );
        $opp = $this->db->fetchOne(
            "SELECT title, description, location, certification, age_min, age_max, gender FROM opportunities WHERE id = ? LIMIT 1",
            [$opportunity_id],
            "i"
        );
        if (!$osy || !$opp) return 0;

        // Demographic score components (max 40)
        $demoScore = 0;
        // Age (up to 15)
        if (!empty($this->matchingConfig['filter_age'])) {
            $userAge = intval($osy['age'] ?? 0);
            $minAge = intval($opp['age_min'] ?? 0);
            $maxAge = intval($opp['age_max'] ?? 0);
            if ($userAge > 0) {
                if (($minAge == 0 || $userAge >= $minAge) && ($maxAge == 0 || $userAge <= $maxAge)) {
                    $demoScore += 15;
                }
            }
        }
        // Education (up to 15)
        if (!empty($this->matchingConfig['filter_education'])) {
            $eduLevel = $osy['education_level'];
            $reqText = strtolower(($opp['certification'] ?? '') . ' ' . ($opp['description'] ?? ''));
            if (strpos($reqText, 'college') !== false || strpos($reqText, 'degree') !== false) {
                if ($eduLevel == 'College Graduate') $demoScore += 15;
                elseif ($eduLevel == 'College Undergraduate') $demoScore += 8;
            } elseif (strpos($reqText, 'high school') !== false || strpos($reqText, 'shs') !== false) {
                if (in_array($eduLevel, ['High School Graduate', 'Senior High School Graduate', 'College Undergraduate', 'College Graduate'])) {
                    $demoScore += 15;
                }
            } else {
                $demoScore += 5; // generic small credit
            }
        }
        // Location (up to 10)
        if (!empty($this->matchingConfig['filter_location'])) {
            $barangay = strtolower($osy['barangay'] ?? '');
            $oppLoc = strtolower($opp['location'] ?? '');
            if (strpos($oppLoc, $barangay) !== false) {
                $demoScore += 10;
            } elseif (strpos($oppLoc, 'any') !== false || strpos($oppLoc, 'remote') !== false || empty($opp['location'])) {
                $demoScore += 5;
            }
        }

        // Semantic score via Gemini
        $semanticScore = $this->calculateSemanticScore($osy, $opp);
        if ($semanticScore === false) {
            // Fallback to existing deterministic full match score
            return $this->calculateMatchScore($osy_id, $opportunity_id);
        }

        // Weighting
        $demoWeight = $this->matchingConfig['demographic_weight'] ?? 0.4;
        $semanticWeight = $this->matchingConfig['semantic_weight'] ?? 0.6;
        $combined = ($demoScore * $demoWeight) + ($semanticScore * $semanticWeight);
        return min(100, max(0, $combined));
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
     * Create a match using array parameters (for applications)
     */
    public function createMatchFromArray($data)
    {
        try {
            $osy_id = $data['osy_id'] ?? $data['profile_id'] ?? null;
            if (!$osy_id) {
                throw new Exception('OSY profile ID is required to create a match');
            }

            $query = "INSERT INTO {$this->table} 
                     (osy_id, opportunity_id, status, created_at) 
                     VALUES (?, ?, ?, NOW())";

            $this->db->execute($query, [
                $osy_id,
                $data['opportunity_id'],
                $data['status'] ?? 'Pending'
            ], "iis");

            return [
                'success' => true,
                'message' => 'Application submitted successfully',
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
     * Check if youth already applied to opportunity
     */
    public function getMatchByYouthAndOpportunity($profile_id, $opportunity_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE osy_id = ? AND opportunity_id = ? LIMIT 1";
        return $this->db->fetchOne($query, [$profile_id, $opportunity_id], "ii");
    }

    /**
     * Calculate match score between OSY and opportunity
     */
    public function calculateMatchScore($osy_id, $opportunity_id)
    {
        try {
            // Get OSY profile with all relevant fields
            $osy = $this->db->fetchOne(
                "SELECT primary_skill, skills, interests, education_level, age, barangay FROM osy_profiles WHERE id = ? LIMIT 1",
                [$osy_id],
                "i"
            );

            if (!$osy) {
                throw new Exception("OSY profile not found");
            }

            // Get opportunity with all relevant fields
            $opportunity = $this->db->fetchOne(
                "SELECT title, description, location, certification, age_min, age_max FROM opportunities WHERE id = ? LIMIT 1",
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

            // Age range matching (up to 15 points)
            $userAge = intval($osy['age'] ?? 0);
            $minAge = intval($opportunity['age_min'] ?? 0);
            $maxAge = intval($opportunity['age_max'] ?? 0);
            if ($userAge > 0) {
                if ($minAge > 0 && $userAge < $minAge) {
                    $score += 0;
                } elseif ($maxAge > 0 && $userAge > $maxAge) {
                    $score += 0;
                } else {
                    $score += 15;
                }
            }

            // Skill table overlap (up to 35 points)
            $requiredSkills = $this->db->fetchAll(
                "SELECT skill FROM opportunity_required_skills WHERE opportunity_id = ?",
                [$opportunity_id],
                "i"
            );
            $skillMatches = 0;
            $normalizedUserSkills = [];
            foreach (array_filter(array_map('trim', preg_split('/[,;]/', strtolower($osy['primary_skill'] . ',' . ($osy['skills'] ?? ''))))) as $skill) {
                if ($skill !== '') {
                    $normalizedUserSkills[] = strtolower(trim($skill));
                }
            }

            foreach ($requiredSkills as $requiredSkill) {
                $required = strtolower(trim($requiredSkill['skill']));
                if ($required === '') {
                    continue;
                }
                if (in_array($required, $normalizedUserSkills, true)) {
                    $skillMatches++;
                } elseif (in_array($required, ['computer', 'it', 'technology', 'tech'], true) && !empty(array_intersect($normalizedUserSkills, ['computer', 'it', 'technology', 'tech', 'programming', 'coding', 'software', 'hardware']))) {
                    $skillMatches++;
                }
            }

            if (!empty($requiredSkills)) {
                $score += min(35, intval(round(($skillMatches / count($requiredSkills)) * 35)));
            }

            // 1. Primary Skill Match (up to 20 points)
            $pSkill = strtolower($osy['primary_skill']);
            if (strpos($oppText, $pSkill) !== false) {
                $score += 20;
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
                                $score += 10; // Partial points for synonym match
                                break 2;
                            }
                        }
                    }
                }
            }

            // 2. Secondary Skills Match (up to 15 points)
            if (!empty($osy['skills'])) {
                $secondarySkills = array_map('trim', preg_split('/[,;]/', strtolower($osy['skills'])));
                $skillPoints = 0;
                foreach ($secondarySkills as $ss) {
                    if (!empty($ss) && $ss !== $pSkill && strpos($oppText, $ss) !== false) {
                        $skillPoints += 5;
                    }
                }
                $score += min(15, $skillPoints);
            }

            // 3. Interests Alignment (up to 10 points)
            if (!empty($osy['interests'])) {
                $interests = array_map('trim', preg_split('/[,;]/', strtolower($osy['interests'])));
                foreach ($interests as $interest) {
                    if (!empty($interest) && strpos($oppText, $interest) !== false) {
                        $score += 10;
                        break;
                    }
                }
            }

            // 4. Education Level Fit (up to 15 points)
            $eduLevel = $osy['education_level'];
            $reqText = strtolower($opportunity['certification'] . ' ' . $opportunity['description']);

            if (strpos($reqText, 'college') !== false || strpos($reqText, 'degree') !== false) {
                if ($eduLevel == 'College Graduate') $score += 15;
                elseif ($eduLevel == 'College Undergraduate') $score += 8;
            } elseif (strpos($reqText, 'high school') !== false || strpos($reqText, 'shs') !== false) {
                if (in_array($eduLevel, ['High School Graduate', 'Senior High School Graduate', 'College Undergraduate', 'College Graduate'])) {
                    $score += 15;
                }
            } else {
                $score += 10; // Generic point if no specific high-level education required
            }

            // 5. Location Proximity (up to 10 points)
            $barangay = strtolower($osy['barangay']);
            if (strpos($oppLoc, $barangay) !== false) {
                $score += 10;
            } elseif (strpos($oppLoc, 'any') !== false || strpos($oppLoc, 'remote') !== false || empty($opportunity['location'])) {
                $score += 5;
            }

            // Ensure minimum base score if they have any match
            if ($score > 0 && $score < 40) {
                $score += 15; // Boost baseline for partial fits
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
                 p.gender, p.education_level, p.skills, p.interests, p.barangay,
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
            // Get all verified OSY profiles
            $osy_list = $this->db->fetchAll("SELECT id FROM osy_profiles WHERE verification_status = 'Verified'");

            $created = 0;

            foreach ($osy_list as $osy) {
                // Check if match already exists
                $existing = $this->db->fetchOne(
                    "SELECT id FROM {$this->table} WHERE osy_id = ? AND opportunity_id = ? LIMIT 1",
                    [$osy['id'], $opportunity_id],
                    "ii"
                );

                if (!$existing) {
                    $score = $this->calculateHybridScore($osy['id'], $opportunity_id);
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
    /**
     * Automatically update AI match scores for all active opportunities for a specific OSY
     * This is triggered on profile create/update to save credits on rationales
     */
    public function updateAllScoresForOSY($osy_id)
    {
        try {
            require_once __DIR__ . '/GeminiService.php';
            $gemini = new GeminiService($this->db);

            // 1. Get OSY data
            $osy = $this->db->fetchOne(
                "SELECT id, primary_skill, skills, interests, education_level FROM osy_profiles WHERE id = ?",
                [$osy_id],
                "i"
            );
            if (!$osy) return false;

            // 2. Get all Open opportunities
            $opportunities = $this->db->fetchAll("SELECT id, title, description, certification FROM opportunities WHERE status = 'Open'");

            foreach ($opportunities as $opp) {
                // Check if match already exists
                $existing = $this->db->fetchOne(
                    "SELECT id FROM {$this->table} WHERE osy_id = ? AND opportunity_id = ? LIMIT 1",
                    [$osy_id, $opp['id']],
                    "ii"
                );

                // Get AI Score (Lightweight call)
                $score = $gemini->calculateScoreOnly($osy, $opp);

                // Fallback to local matching algorithm if AI fails (e.g. rate limit exceeded)
                if ($score === false) {
                    $score = $this->calculateMatchScore($osy_id, $opp['id']);
                }

                if ($existing) {
                    $this->db->execute(
                        "UPDATE {$this->table} SET match_score = ?, updated_at = NOW() WHERE id = ?",
                        [$score, $existing['id']],
                        "ii"
                    );
                } else {
                    $this->createMatch($osy_id, $opp['id'], $score);
                }

                // 1 second sleep to stay within free tier rate limits
                usleep(1000000);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error in updateAllScoresForOSY: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get statistics on how many profiles are missing AI scores for active jobs
     */
    public function getGlobalSyncStats()
    {
        // Total possible matches (Profiles * Open Opportunities)
        $profilesCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM osy_profiles")['count'];
        $oppsCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM opportunities WHERE status = 'Open'")['count'];
        $totalPossible = $profilesCount * $oppsCount;

        // Existing matches
        $existingCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM {$this->table}")['count'];

        return [
            'total_possible' => $totalPossible,
            'existing_matches' => $existingCount,
            'missing_matches' => max(0, $totalPossible - $existingCount),
            'profiles_count' => $profilesCount,
            'opportunities_count' => $oppsCount
        ];
    }
}