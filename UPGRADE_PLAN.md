# Municipal KK Profiling System Upgrade Plan

## Project Overview
This document outlines the comprehensive architectural plan and step-by-step implementation guide for upgrading the Municipal KK Profiling System to include Role-Based Access Control (RBAC), enhanced registration workflows, opportunity management, skills matching, and system enhancements.

## Current System Analysis
Based on the existing codebase, the system currently has:
- Basic user management with classes (User.php, Database.php)
- Opportunity and matching functionality (Opportunity.php, Matching.php)
- Profile management (OSYProfile.php)
- Notification and messaging systems (Notification.php, Messages.php)
- Dashboard and reporting (Dashboard.php, Report.php)
- Email and SMS services (EmailService.php, SmsService.php)

## Architectural Plan

### 1. Database Architecture
#### New Tables Required:
- `user_roles` - Define roles (lydo, sk_chairman, youth, employer, training_provider)
- `user_role_assignments` - Link users to roles with additional metadata (e.g., barangay for SK Chairman)
- `registration_approvals` - Track approval workflow for all user types
- `audit_logs` - Comprehensive activity logging
- `notifications` - User notification system
- `skills_matching` - Store skills matching logic and results
- `opportunity_applications` - Track applications to opportunities

#### Modified Tables:
- `users` - Add status field (pending, verified, action_required, approved, declined)
- `opportunities` - Add provider_id, approval_status
- `profiles` - Add verification fields (id_upload_path, consent_given, remarks)

### 2. Backend Architecture
#### Authentication & Authorization:
- Implement RBAC middleware
- Session-based role checking
- Permission gates for different actions

#### Business Logic Layers:
- UserRegistrationService - Handle all registration workflows
- ApprovalWorkflowService - Manage approval processes
- SkillsMatchingService - Implement matching algorithms
- NotificationService - Enhanced notification system
- AuditService - Activity logging

#### API Structure:
- RESTful endpoints for CRUD operations
- Role-specific API access
- Secure file upload handling

### 3. Frontend Architecture
#### User Interface Updates:
- Role-specific dashboards
- Registration forms with file uploads
- Approval interfaces for LYDO and SK Chairmen
- Opportunity management interfaces
- Notification center

#### Security Enhancements:
- CSRF protection
- Input validation
- File upload security
- Session management

### 4. Security Considerations
- Password hashing and validation
- File upload restrictions (type, size, malware scanning)
- SQL injection prevention
- XSS protection
- Audit logging for sensitive operations
- Data privacy compliance (PDPA/PIPA)

### 5. Performance & Scalability
- Database indexing for large datasets
- Caching for frequently accessed data
- Optimized queries for reporting
- File storage management

## Detailed Step-by-Step To-Do List

### Phase 1: Database Schema Updates
1. **Create user_roles table**
   - Fields: id, role_name, description, created_at
   - Insert default roles: lydo, sk_chairman, youth, employer, training_provider

2. **Create user_role_assignments table**
   - Fields: id, user_id, role_id, barangay_id (nullable), assigned_by, assigned_at
   - Add foreign key constraints

3. **Create registration_approvals table**
   - Fields: id, user_id, reviewer_id, status, remarks, reviewed_at, created_at

4. **Create audit_logs table**
   - Fields: id, user_id, action, description, ip_address, user_agent, created_at

5. **Create notifications table**
   - Fields: id, user_id, type, title, message, is_read, created_at

6. **Create skills_matching table**
   - Fields: id, youth_id, opportunity_id, match_score, matched_skills, created_at

7. **Create opportunity_applications table**
   - Fields: id, youth_id, opportunity_id, application_status, applied_at

8. **Modify users table**
   - Add status enum: pending, verified, action_required, approved, declined
   - Add temporary_password field
   - Add password_reset_required boolean

9. **Modify opportunities table**
   - Add provider_id foreign key
   - Add approval_status enum: pending, approved, declined

10. **Modify profiles table**
    - Add id_upload_path varchar
    - Add consent_given boolean
    - Add verification_remarks text
    - Add verified_by foreign key to users
    - Add verified_at timestamp

### Phase 2: Backend Core Updates
11. **Implement RBAC System**
    - Create Role.php class
    - Create Permission.php class
    - Update User.php class with role checking methods
    - Create middleware for role-based access control

12. **Update Authentication System**
    - Modify login logic to handle different user types
    - Implement password change requirement for new SK Chairmen
    - Add role-based redirect after login

13. **Create UserRegistrationService**
    - Handle youth registration with file upload
    - Handle employer/training provider registration
    - Implement data privacy consent tracking

14. **Create ApprovalWorkflowService**
    - LYDO approval for employers/training providers
    - SK Chairman approval for youth registrations
    - Status update logic with remarks

15. **Implement SK Chairman Management**
    - LYDO interface to create SK Chairman accounts
    - Auto-generate temporary passwords
    - Barangay assignment logic

16. **Update Opportunity Management**
    - Add provider ownership validation
    - Implement CRUD operations with role checks
    - Add approval workflow for new opportunities

17. **Implement Skills Matching Logic**
    - Create SkillsMatchingService
    - Develop matching algorithm based on skills overlap
    - Generate match scores and recommendations

18. **Create NotificationService Enhancement**
    - Email notifications for approvals/declines
    - In-system notifications for new registrations
    - SMS alerts for critical updates

19. **Implement Audit Logging**
    - Create AuditService
    - Log all critical actions (approvals, registrations, opportunity posts)
    - Include user context and timestamps

### Phase 3: Frontend Updates
20. **Update Registration Forms**
    - Public youth registration form with file upload
    - Employer/Training Provider registration with proof upload
    - Data privacy consent checkbox

21. **Create Role-Specific Dashboards**
    - LYDO dashboard: System overview, user management, reports
    - SK Chairman dashboard: Barangay youth management, approvals
    - Youth dashboard: Profile management, opportunity browsing
    - Employer dashboard: Job posting management, applicant viewing
    - Training Provider dashboard: Training management, applicant viewing

22. **Implement Approval Interfaces**
    - LYDO approval queue for providers
    - SK Chairman approval queue for youth
    - Bulk approval/decline functionality
    - Remarks input for declines

23. **Update Opportunity Interfaces**
    - Provider-specific CRUD forms
    - Skills matching display for providers
    - Application submission for youth
    - Application management for providers

24. **Create Notification Center**
    - In-app notification display
    - Mark as read functionality
    - Notification history

25. **Implement File Upload Security**
    - File type validation
    - Size limits
    - Secure storage paths
    - File deletion on decline

### Phase 4: Security & Testing
26. **Implement Security Measures**
    - CSRF tokens on all forms
    - Input sanitization and validation
    - SQL injection prevention
    - XSS protection

27. **Create Unit Tests**
    - Test RBAC functionality
    - Test registration workflows
    - Test approval processes
    - Test skills matching logic

28. **Integration Testing**
    - End-to-end user workflows
    - Cross-role interaction testing
    - File upload and processing
    - Notification delivery

29. **Performance Testing**
    - Database query optimization
    - Large dataset handling
    - Concurrent user load testing

### Phase 5: Deployment & Documentation
30. **Database Migration Scripts**
    - Create migration files for all schema changes
    - Data migration for existing users
    - Backward compatibility checks

31. **Update Documentation**
    - User manuals for each role
    - API documentation
    - System administration guide
    - Developer documentation

32. **Deployment Preparation**
    - Environment configuration
    - File system permissions
    - Database backup procedures
    - Rollback plans

33. **Final System Testing**
    - Complete workflow testing
    - Security audit
    - Performance validation
    - User acceptance testing

### Phase 6: Post-Deployment
34. **Monitoring Setup**
    - Error logging and monitoring
    - Performance monitoring
    - User activity tracking

35. **User Training**
    - Training sessions for LYDO and SK Chairmen
    - User guides and tutorials
    - Support documentation

36. **System Maintenance**
    - Regular security updates
    - Database maintenance procedures
    - Backup and recovery procedures

## Implementation Guidelines
- Follow existing code patterns and naming conventions
- Use prepared statements for all database queries
- Implement proper error handling and user feedback
- Ensure responsive design for all new interfaces
- Maintain data integrity across all workflows
- Test thoroughly at each phase before proceeding

## Risk Mitigation
- Regular backups before schema changes
- Feature flags for gradual rollout
- Comprehensive testing environment
- Rollback procedures for each phase
- Stakeholder communication throughout development

## Success Criteria
- All user roles can perform their designated functions
- Approval workflows work correctly for all user types
- Skills matching provides accurate recommendations
- Audit trail captures all critical activities
- Notification system delivers timely alerts
- System maintains data privacy and security standards