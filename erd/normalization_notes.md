# Normalization Analysis: Municipal KK Profiling System
## From 1NF to 5NF

---

## Original Database Issues

The original database schema contained several normalization violations:

### Violations Found

| Table | Issue | Normal Form Violated |
|-------|-------|---------------------|
| `osy_profiles` | `skills` stored as comma-separated TEXT | 1NF (atomic values) |
| `osy_profiles` | `interests` stored as comma-separated TEXT | 1NF (atomic values) |
| `osy_profiles` | `barangay` stored as raw text, not referencing a lookup table | 2NF (partial dependency) |
| `osy_profiles` | `education_level` stored as raw text | 2NF (partial dependency) |
| `osy_profiles` | `primary_skill` duplicated inside `skills` | 3NF (transitive dependency) |
| `osy_profiles` | `age` is derivable from `date_of_birth` | 3NF (transitive/derived dependency) |
| `osy_profiles` | `govt_id_type`, `govt_id_number`, `govt_id_image` — multi-valued, a person can have multiple IDs | 1NF/4NF |
| `opportunities` | `benefits` stored as comma-separated TEXT | 1NF (atomic values) |
| `opportunities` | `type` mixes job/training/scholarship — different attribute sets | 3NF (unused nullable columns per type) |
| `notifications` | `recipient_type` = 'Specific' with no link to actual recipients | Referential integrity |
| `notification_templates` | Template variables embedded as free-text `{{name}}` | Acceptable (template engine pattern) |

| `system_references` | Generic EAV pattern — acceptable for lookup data | Acceptable |

---

## Normalization Steps Applied

### 1NF — First Normal Form
> *All columns must contain atomic (indivisible) values; no repeating groups.*

**Changes:**
- **`osy_profiles.skills`** → Extracted to `profile_skills` junction table (profile_id → skill_id)
- **`osy_profiles.interests`** → Extracted to `profile_interests` junction table (profile_id → interest_id)
- **`osy_profiles.govt_id_*`** → Extracted to `profile_govt_ids` table (supports multiple IDs per person)
- **`opportunities.benefits`** → Extracted to `opportunity_benefits` junction table
- **Created `skills` lookup table** with unique skill names
- **Created `interests` lookup table** with unique interest names

### 2NF — Second Normal Form
> *Must be in 1NF + every non-key column must depend on the entire primary key.*

**Changes:**
- **`osy_profiles.barangay`** → Replaced with `barangay_id` FK referencing a `barangays` lookup table
- **`osy_profiles.education_level`** → Replaced with `education_level_id` FK referencing `education_levels` lookup table
- **`opportunities.location`** → Now just descriptive text (acceptable); location is fully dependent on opportunity PK
- All junction tables (`profile_skills`, `profile_interests`, `osy_matches`) use composite PKs where every non-key attribute depends on the full composite key

### 3NF — Third Normal Form
> *Must be in 2NF + no transitive dependencies (non-key → non-key).*

**Changes:**
- **Removed `osy_profiles.age`** — It is derived from `date_of_birth` (calculated field, not stored)
- **Removed `osy_profiles.primary_skill`** — Instead, added `is_primary` flag on `profile_skills` junction table
- **`opportunities` type-specific columns** (`employment_type`, `work_schedule`, `experience_req`, `training_provider`, `duration`, `modality`) → Extracted to subtype tables:
  - `opportunity_job_details` — for Job Openings
  - `opportunity_training_details` — for Vocational Training
  - `opportunity_scholarship_details` — for Scholarships

### BCNF — Boyce-Codd Normal Form
> *Must be in 3NF + every determinant must be a candidate key.*

**Changes:**
- All tables verified: every functional dependency has a superkey as its determinant.
- `users.username` and `users.email` are both candidate keys (UNIQUE constraints preserved).
- `system_settings.setting_key` is a candidate key (UNIQUE constraint).
- No violations found after 3NF fixes.

### 4NF — Fourth Normal Form
> *Must be in BCNF + no multi-valued dependencies.*

**Changes:**
- **Government IDs** extracted to `profile_govt_ids` — a person can have multiple IDs of different types (multi-valued dependency resolved).
- **Skills and Interests** are independent multi-valued facts about a profile → separated into independent junction tables (`profile_skills`, `profile_interests`).
- **Notification recipients** — when `recipient_type = 'Specific'`, now tracked via `notification_recipients` junction table instead of implicit logic.

### 5NF — Fifth Normal Form (Project-Join Normal Form)
> *Must be in 4NF + no join dependencies that are not implied by candidate keys.*

**Changes:**
- The `osy_matches` table represents a three-way relationship (profile, opportunity, match-metadata). This is NOT decomposable into smaller joins without losing information — it correctly stays as one table.
- `profile_skills` (profile_id, skill_id, is_primary) — The `is_primary` attribute depends on the full composite key (profile_id, skill_id), not on any subset. No spurious join dependency.
- All junction tables verified: no table can be losslessly decomposed into smaller tables.

---

## Final Normalized Table Summary (22 Tables)

| # | Table | Purpose | Keys |
|---|-------|---------|------|
| 1 | `users` | Admin/Staff accounts | PK: id, UK: username, UK: email |
| 2 | `barangays` | Barangay lookup | PK: id, UK: name |
| 3 | `education_levels` | Education level lookup | PK: id, UK: name |
| 4 | `skills` | Skills catalog | PK: id, UK: name |
| 5 | `interests` | Interests catalog | PK: id, UK: name |
| 6 | `govt_id_types` | Government ID type catalog | PK: id, UK: name |
| 7 | `youth_profiles` | Core youth profile data | PK: id, FK: barangay_id, education_level_id, created_by |
| 8 | `profile_skills` | Profile ↔ Skill junction | PK: (profile_id, skill_id), FK: both |
| 9 | `profile_interests` | Profile ↔ Interest junction | PK: (profile_id, interest_id), FK: both |
| 10 | `profile_govt_ids` | Profile's government IDs | PK: id, FK: profile_id, govt_id_type_id |
| 11 | `opportunities` | Core opportunity data | PK: id, FK: created_by |
| 12 | `opportunity_benefits` | Opportunity ↔ Benefit | PK: id, FK: opportunity_id |
| 13 | `opportunity_job_details` | Job-specific attributes | PK: opportunity_id (1:1), FK |
| 14 | `opportunity_training_details` | Training-specific attributes | PK: opportunity_id (1:1), FK |
| 15 | `opportunity_scholarship_details` | Scholarship-specific attributes | PK: opportunity_id (1:1), FK |
| 16 | `opportunity_required_skills` | Skills required by opportunity | PK: (opportunity_id, skill_id), FK: both |
| 17 | `osy_matches` | Profile ↔ Opportunity matching | PK: id, UK: (profile_id, opportunity_id), FK: both |
| 18 | `notifications` | System notifications | PK: id, FK: created_by |
| 19 | `notification_recipients` | Specific notification targets | PK: (notification_id, profile_id), FK: both |
| 20 | `notification_templates` | Message templates | PK: id, FK: created_by |
| 21 | `system_settings` | Key-value configuration | PK: id, UK: setting_key |
| 22 | `audit_log` | Change tracking | PK: id, FK: user_id |
