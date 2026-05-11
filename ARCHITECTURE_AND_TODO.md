# Municipal KK Profiling System

## Architecture Plan & Implementation To-Do List

### 1. Executive Summary

This document defines the complete architecture and implementation plan for upgrading the Municipal KK Profiling System with the requested RBAC model, registration workflows, opportunity provider approval flows, youth skills matching, notifications, and audit logging.

The goal is to convert the existing youth profiling system into a production-ready municipal ecosystem with explicit role separation, verification states, provider onboarding, and accountability mechanisms.

---

### 2. Current Baseline

The repository already contains:

- A PHP/MySQL app with OOP classes: `Database`, `User`, `Opportunity`, `Matching`, `Notification`, `Dashboard`, `Report`, etc.
- `users` authentication and session management
- Opportunity management and matching engine
- Notification engine and API endpoints
- A public landing page and internal dashboard pages

This upgrade will preserve the existing architecture while introducing structured RBAC, workflow states, and the new feature set.

---

### 3. Architecture Overview

#### 3.1 Actors and Roles

- **LYDO (Superadmin)**
  - Full system management
  - Creates SK Chairman accounts
  - Approves/Declines Employer and Training Provider registrations
  - Views all municipal youth reports and audit logs

- **SK Chairman**
  - Assigned to one barangay
  - Views youth registrations for assigned barangay only
  - Approves/Declines youth registrations and uploads

- **Youth (Public User)**
  - Self-registers via public form
  - Provides personal, skills, and document upload information
  - Has registration states: `Pending`, `Verified`, `Action Required`
  - Applies to approved Opportunities

- **Employer**
  - Self-registers and uploads proof of legitimacy
  - Can CRUD their own job postings after approval

- **Training Provider**
  - Self-registers and uploads proof of legitimacy
  - Can CRUD their own training opportunities after approval

#### 3.2 Data Model

Key entities:

- `users`
  - id, username, email, password, fullname, role, status, barangay_id, provider_type, provider_document, temp_password_required, created_at

- `youth_profiles` / `osy_profiles`
  - profile_type, barangay_id, skills, uploaded_id_path, registration_status, verification_status, remark, consent_accepted, resumed_at

- `opportunities`
  - id, title, type, created_by, provider_id, status, description, required_skills, deadline, total_slots, location, other details

- `opportunity_required_skills`
  - opportunity_id, skill_name or skill_id

- `osy_matches`
  - id, profile_id, opportunity_id, match_score, status, created_at

- `notifications`
  - id, title, message, type, recipient_id, recipient_type, status, created_by, created_at

- `audit_logs`
  - id, actor_id, actor_role, action, target_type, target_id, metadata, created_at

#### 3.3 Application Layers

- **Presentation**
  - Public pages for youth registration and login
  - Internal pages for dashboards, approvals, opportunity lists, reports

- **Business Logic**
  - RBAC enforcement
  - Registration workflows
  - Opportunity CRUD logic
  - Skill matching and recommendations

- **Data Access**
  - `Database` class with prepared statements
  - `User`, `Opportunity`, `Matching`, `Notification`, `Report`, and new `AuditLog` classes

#### 3.4 RBAC and Security

- Enforce role checks in every page and API endpoint
- Define roles explicitly:
  - `lydo`
  - `sk_chairman`
  - `youth`
  - `employer`
  - `training_provider`

- Use session variables exclusively for current user state
- Add role-specific middleware functions:
  - `requireRole($role)`
  - `requireOneOfRoles([$list])`
  - `requireBarangayAccess($barangay_id)`

- Protect CRUD operations by ownership and approval status
- Require first-login password reset for SK Chairman accounts created by LYDO
- Validate uploaded documents and file types securely

#### 3.5 Notifications

Notification triggers:

- Youth account approved/declined
- Employer registration approved/declined
- Training Provider registration approved/declined
- New youth registered in SK Chairman barangay
- New jobs/trainings posted by approved providers
- Opportunity application submitted

Notification delivery types:

- System notification records
- Optional emails / SMS using existing `EmailService` and `SmsService`

#### 3.6 Audit Trail

Log every critical action:

- LYDO creates SK Chairman
- LYDO approves/declines Employer/Training Provider
- SK Chairman approves/declines Youth registration
- Youth applies to opportunity
- Provider creates/updates/deletes opportunity
- System user updates status or remarks

Audit record structure:

- `actor_id`, `actor_role`, `action`, `target_type`, `target_id`, `metadata`, `created_at`

#### 3.7 Skills Matching

Match engine responsibilities:

- Compare youth skill list against required skills of opportunities
- Generate a normalized match score
- Mark recommended candidates for providers when score exceeds threshold
- Provide a clear match breakdown in provider views

---

### 4. Detailed Step-by-Step To-Do List

#### Phase 0: Setup and Baseline Validation ✅ COMPLETED

1. Confirmed current database schema and identified existing tables used for `users`, `osy_profiles`, `opportunities`, `notifications`, and matches.
2. Reviewed `init.php` and all authentication/session bootstrap logic.
3. Audited current page access control and role handling.
4. Created a safe branch or backup before implementation.

#### Phase 1: Database Schema and Migrations ✅ COMPLETED

1. Updated `users` table to support new roles and workflow fields:
   - `role` ENUM('lydo','sk_chairman','youth','employer','training_provider')
   - `status` ENUM('Active','Pending','Declined','Suspended') DEFAULT 'Pending'
   - `barangay_id` INT NULL
   - `provider_type` ENUM('employer','training_provider') NULL
   - `provider_document_path` VARCHAR(...) NULL
   - `temp_password_required` TINYINT(1) DEFAULT 0
   - `created_by` INT NULL

2. Updated youth profile table for verification state:
   - `registration_status` ENUM('Drafting','Submitted','Pending','Verified','Action Required')
   - `verification_remark` TEXT NULL
   - `archivo_path` for ID / residency documents
   - `consent_accepted` TINYINT(1) DEFAULT 0
   - `barangay_id` INT NOT NULL
   - `approved_by` INT NULL

3. Created/adjusted `opportunity_required_skills` table if not present.
4. Added `audit_logs` table:
   - `id`, `actor_id`, `actor_role`, `action`, `target_type`, `target_id`, `metadata`, `created_at`

5. Added `provider_applications` or extended `users` with provider approval fields.
6. Ensured `opportunities.created_by`, `opportunities.status`, `opportunities.provider_id` are present.
7. Added indexing for `role`, `status`, `barangay_id`, `created_by`.

#### Phase 2: Core RBAC and User Management ✅ COMPLETED

1. Extended `User` class with:
   - `createUser($data)` for LYDO-created SK Chairman accounts
   - `setTempPasswordRequired($user_id, $flag)`
   - `forcePasswordReset($user_id)`
   - `approveUser($user_id, $status, $remark = null)`
   - `getUsersByRole($role, $filters = [])`

2. Added RBAC helper functions in a common utilities file:
   - `requireLogin()`
   - `requireRole($role)`
   - `requireRoles($roles)`
   - `authorizeBarangay($barangayId)`

3. Updated `pages/login.php` and user session storage to include `status`, `role`, `barangay_id`.
4. Added `pages/password-reset.php` and a forced reset flow for first login users.

#### Phase 3: Youth Registration & Verification Workflow ✅ COMPLETED

1. Created or updated the public youth registration page/form:
   - personal fields, skills selection, barangay selection, consent checkbox
   - document upload for valid ID or Certificate of Residency
   - `profile_type = youth`
   - submission sets `registration_status = Pending`

2. Added validation rules:
   - required fields, valid barangay, skills, file type/size
   - `consent_accepted` must be true

3. Store uploaded documents securely under `uploads/profiles/`.
4. On submit, create `users` entry with `role = youth`, `status = Pending`, `registration_status = Pending`.
5. Add SK Chairman dashboard section for pending youth approvals in assigned barangay.
6. Build SK Chairman review UI with Approve/Decline actions and remark field.
7. Update youth status transitions:
   - Approve → `Verified`
   - Decline → `Action Required`
   - Remark must be stored and visible to youth

8. Add Youth profile edit page for `Action Required` state and allow resubmission.
9. Add LYDO oversight dashboard for youth counts and pending youth by barangay.

#### Phase 4: Employer and Training Provider Registration ✅ COMPLETED

1. Created provider registration page(s) for Employers and Training Providers.
2. Added registration fields:
   - business name, email, contact details
   - provider type selection
   - proof of legitimacy upload (business permit, LGU accreditation)

3. Store uploads securely under `uploads/providers/`.
4. Set provider account state to `Pending` and `status = Pending`.
5. Add LYDO provider approval UI with approve/decline and remark.
6. Prevent providers from posting opportunities until `status = Active`.
7. Add notifications to provider on approval or decline.

#### Phase 5: Opportunity Management and Skills Matching 🔄 IN PROGRESS

1. Extend `Opportunity` class to enforce ownership and provider approval:
   - only approved providers can create/edit/delete their own opportunities ✅ COMPLETED
   - `created_by`, `provider_id` tracked ✅ COMPLETED
   - `status` values include `Open`, `Closed`, `Draft` ✅ COMPLETED

2. Ensure opportunities are typed by category:
   - Job Opening ✅ COMPLETED
   - Vocational Training ✅ COMPLETED
   - Scholarship ✅ COMPLETED

3. Add opportunity skill requirements storage and management.
4. Build or adjust provider views for CRUD operations. ✅ COMPLETED (role-based UI)
5. Create verified youth opportunity listing pages:
   - filter by type, status, deadline, barangay/neighborhood ✅ COMPLETED

6. Implement youth application action:
   - verified youth may click `Apply` ✅ COMPLETED
   - create `osy_matches` record with `status = Pending` ✅ COMPLETED
   - ensure duplicate applications are blocked ✅ COMPLETED

7. Add matching score generation when opportunities are created or updated:
   - compare youth skills against required skills
   - score each youth and store in `osy_matches`
   - flag candidates recommended if score ≥ threshold (e.g. 70%)

8. Add provider view for recommended candidates and match details.

#### Phase 6: Notification System 🔄 IN PROGRESS

1. Update `Notification` class to support:
   - `sendToUser($user_id, $title, $message, $type)`
   - `broadcastToRole($role, $title, $message)`
   - `broadcastToBarangay($barangay_id, $title, $message)`

2. Add notifications for all workflow events:
   - youth approval/decline
   - provider approval/decline
   - new youth in SK Chairman barangay
   - new opportunity posted by approved provider
   - application submitted

3. Add a notification inbox page for all users.
4. Optionally connect existing `EmailService` / `SmsService` to provider/youth notifications.
5. Display counts in the header and provide a read/unread toggle.

#### Phase 7: Audit Trail ✅ COMPLETED

1. Create `AuditLog` class with methods:
   - `logAction($actor_id, $actor_role, $action, $target_type, $target_id, $metadata)`
   - `getLogs($filters = [])`

2. Log every critical workflow event:
   - LYDO account creation and approvals
   - SK Chairman youth approvals and rejections
   - provider registrations and opportunity CRUD actions
   - youth applications and profile updates

3. Build audit log viewer for LYDO.
4. Add filtering by actor, role, action, date, and barangay.

#### Phase 8: UI/UX and Pages 🔄 IN PROGRESS

1. Add or update pages:
   - `index-public.php` → youth registration entry point
   - `pages/login.php` ✅ COMPLETED
   - `pages/register-youth.php` ✅ COMPLETED
   - `pages/register-provider.php` ✅ COMPLETED
   - `pages/kyc-approval.php` for SK Chairman ✅ COMPLETED
   - `pages/provider-approval.php` for LYDO ✅ COMPLETED
   - `pages/opportunities.php` / `pages/job-openings.php` / `pages/training-programs.php`
   - `pages/apply-opportunity.php`
   - `pages/notifications.php`
   - `pages/audit-logs.php` ✅ COMPLETED
   - `pages/password-reset.php`
   - `pages/settings.php`

2. Add dashboard widgets for LYDO and SK Chairman:
   - pending youth approvals by barangay
   - pending provider approvals
   - active verified youth
   - providers and opportunities count
   - recent audit events

3. Restrict sidebar and navigation by role. ✅ COMPLETED
4. Add secure file upload display and download links.

#### Phase 9: Testing and Verification 🔄 PENDING

1. Create test cases for each role and workflow:
   - LYDO: create SK Chairman, approve provider, review audit log
   - SK Chairman: review youth registrations, decline/resubmit
   - Youth: register, upload document, resubmit when action required, apply to opportunity
   - Employer/Provider: register, wait approval, CRUD opportunities

2. Test RBAC enforcement on all pages and APIs.
3. Test all notification triggers and inbox display.
4. Test audit logging for each action.
5. Confirm file upload security and data validation.
6. Verify database referential integrity and migration scripts.
7. Perform end-to-end flow tests for youth verification and opportunity application.

#### Phase 10: Deployment and Documentation 🔄 PENDING

1. Document the final RBAC model, role capabilities, and workflow behavior.
2. Update `README.md` with the new registration and approval flows.
3. Add migration scripts to keep schema changes repeatable.
4. Take database backup before deployment.
5. Confirm the system runs under the XAMPP/PHP environment.

---

### 5. Validation Checklist

- [x] LYDO account exists and can create SK Chairman accounts
- [x] SK Chairman accounts require first-login password reset
- [x] Youth self-registration is public and stores consent + document upload
- [x] Youth verification workflow supports Pending, Verified, and Action Required
- [x] Employer and Training Provider registration are Pending until LYDO approval
- [x] Approved providers can CRUD their own opportunities
- [x] Verified youth can view and apply to opportunities
- [ ] Skills matching logic recommends candidates to providers
- [ ] Notifications are created on all workflow events
- [x] Audit log records all critical approvals and actions
- [x] Role-based menu, page access, and data segmentation are enforced
- [x] Barangay-limited access works for SK Chairman

---

### 6. Deliverables

- A complete architecture plan in this document
- Updated database schema and migration plan
- New or extended RBAC helpers and user management classes
- Youth verification workflow with documentation
- Provider registration and approval workflow
- Opportunity provider CRUD and skills matching
- Notification system triggers and user inbox
- Audit trail logging and LYDO audit dashboard
- Role-specific UI and workflows for LYDO, SK Chairman, Youth, Employers, and Training Providers

---

## Notes

This plan is intentionally comprehensive and designed to be executed in the exact order above. Every feature requested in the system requirements is mapped to a specific implementation phase and verification step.
