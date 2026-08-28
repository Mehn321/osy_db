# Security Implementation Plan

## Objective

This plan defines a practical rollout to strengthen account and platform security for the Municipal KK / OSY system.

## Scope

- Password policy hardening for all users
- Two-Factor Authentication (2FA) through email
- Activity Alerts through user email
- Supporting controls (session hardening, rate limits, logging, recovery)

## Required Security Policies

### 1. Strong Password Required for Every User

All user accounts must use strong passwords and cannot use common or weak passwords.

Strong password rules:

- Minimum length: 12 characters
- Requires a mix of letters, numbers, and symbols
- Requires upper and lower case letters
- Blocks common or weak passwords
- Blocks passwords containing obvious user info (username, email local-part, first/last name)
- Password reuse prevention: cannot reuse last 5 passwords

Implementation notes:

- Enforce during signup, user creation, password reset, and password change
- Validate server-side in all flows (client-side checks are helper only)
- Store password hashes with password_hash (bcrypt/argon2 depending on environment)
- Keep clear user-facing error messages without exposing sensitive details

### 2. Two-Factor Authentication (2FA) Through Email

All users must complete email OTP verification after successful username/password login.

2FA rules:

- OTP length: 6 digits
- OTP expiration: 10 minutes
- OTP max attempts per code: 5
- OTP resend cooldown: 60 seconds
- Account/session lock after repeated abuse attempts

Implementation notes:

- Add login step sequence:
  1. Username/password check
  2. Generate OTP and email it to the user
  3. Verify OTP before granting full session access
- Use pending-auth session state until OTP is verified
- Invalidate OTP after successful use
- Store OTP as hashed value in DB (never plaintext)
- Add fallback flow: "resend code" with throttling

### 3. Activity Alerts Through Email

Send security activity alerts to users by email for important account events.

Alert events:

- New login from unknown browser/device
- Login from new IP/location pattern
- Password changed
- Email changed
- 2FA enabled/disabled
- Multiple failed login attempts / lockout

Alert content requirements:

- Event type and timestamp
- Approximate device/IP details
- If this was not you: include account recovery action link/instructions
- No sensitive data in email body

## Architecture Changes

### Database

Add tables/fields:

- user_security_settings
  - user_id (PK/FK)
  - two_factor_enabled (bool)
  - alert_email_enabled (bool, default true)
  - last_password_changed_at
- user_password_history
  - id
  - user_id
  - password_hash
  - created_at
- user_login_events
  - id
  - user_id
  - ip_address
  - user_agent
  - login_at
  - login_result (success/failed)
  - failure_reason
- user_2fa_codes
  - id
  - user_id
  - otp_hash
  - expires_at
  - attempts
  - consumed_at
  - created_at

### Backend

- Add PasswordPolicy service/class
- Add Auth2FA service/class for OTP creation, hash/verify, expiry, throttling
- Add SecurityAlert service/class for email notifications
- Add middleware/check for "2FA complete" state before accessing protected pages/APIs

### Frontend / UX

- Add 2FA verification page after login
- Add security settings section:
  - Toggle email 2FA
  - Toggle activity alerts
  - Show recent security activity
- Add clear password strength feedback on forms

## Implementation Phases

## Phase 1: Password Policy Enforcement (High Priority)

Target: 2 to 3 days

Tasks:

1. Create centralized password validation helper used by:
   - user registration
   - provider registration
   - SK/LYDO user creation
   - password reset
   - password change
2. Add weak/common password blocklist and pattern checks
3. Add password history table and reuse prevention
4. Update UI validation hints and error messages
5. Add audit log entries for password changes

Definition of done:

- Every password entry point enforces the same strong rules
- Weak/common passwords are rejected
- Reuse of last 5 passwords is rejected

## Phase 2: Email 2FA (High Priority)

Target: 3 to 5 days

Tasks:

1. Add DB migration for 2FA and login event storage
2. Implement OTP generation (secure random), hashing, expiry, and verification
3. Add login flow split (password success -> OTP required)
4. Add 2FA verify endpoint/page with resend and cooldown
5. Add abuse protections (attempt caps, lockouts, rate limits)
6. Add rollout controls:
   - start with optional 2FA
   - switch to required 2FA for all users after stabilization

Definition of done:

- Users cannot complete login without valid OTP
- OTP cannot be reused and expires correctly
- Throttling and lockout behaviors are active

## Phase 3: Email Activity Alerts (High Priority)

Target: 2 to 4 days

Tasks:

1. Implement security event detection points in auth/account flows
2. Create standardized security alert email templates
3. Add user preference toggle for alerts (default enabled)
4. Send alerts asynchronously (queue/background preferred)
5. Add event logging for traceability and support

Definition of done:

- All targeted security events trigger alerts
- Users receive clear and actionable alert emails
- Alert delivery failures are logged and visible to admins

## Phase 4: Supporting Hardening (Medium Priority)

Target: 2 to 4 days

Tasks:

1. Session hardening:
   - Secure, HttpOnly, SameSite cookies
   - Session rotation on login and privilege changes
2. Endpoint rate limiting for login/OTP/reset
3. Security headers (CSP, HSTS, X-Frame-Options, Referrer-Policy, X-Content-Type-Options)
4. Remove or protect debug scripts in production
5. Lock down upload directories and validate MIME server-side

Definition of done:

- Baseline browser and session protections are globally active
- Brute-force and OTP abuse are rate-limited

## Testing Plan

### Unit tests

- Password policy validator (valid/invalid matrix)
- OTP lifecycle (create, verify, expire, consume, attempt cap)
- Alert trigger conditions

### Integration tests

- End-to-end login with 2FA success/failure flows
- Password change + history check
- New device/IP login triggers alert email

### Security tests

- Brute-force simulation on login and OTP endpoints
- Replay attempt using old OTP
- Bypass attempt for pages/APIs without completed 2FA

## Rollout Strategy

1. Deploy Phase 1 first (strong passwords)
2. Deploy Phase 2 in optional mode for 1 week
3. Monitor delivery, failures, and support issues
4. Enforce mandatory 2FA for all users
5. Deploy Phase 3 activity alerts immediately after 2FA stabilization

## Monitoring and Metrics

Track:

- % of accounts with strong-password compliance
- 2FA success/failure rates
- OTP resend and lockout counts
- suspicious login alerts sent
- password reset frequency

## Ownership

- Backend: auth logic, policy enforcement, OTP, alerts
- Frontend: 2FA pages, settings UX, password guidance
- QA: automated and manual security test suites
- Operations: email reliability, logs, production config hardening

## Immediate Next Actions

1. Create DB migrations for password history, login events, and 2FA codes
2. Implement centralized password policy validator and connect to all auth flows
3. Implement OTP email service and 2FA verification flow
4. Implement security activity alert templates and trigger hooks
