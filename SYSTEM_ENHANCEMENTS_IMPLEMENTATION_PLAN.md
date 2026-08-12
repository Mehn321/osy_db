# System Enhancements Implementation Plan

## Overview
This document captures the implementation plan for the requested system enhancements covering access control, matching logic, AI scoring accuracy, notification upgrades, portability, and security hardening.

## 1. Access Control & RBAC Gateway Fixes

### Opportunities Page
- Update the page-level gateway guard in [pages/opportunities.php](pages/opportunities.php) to:
  - allow the `youth` role
  - deny the `sk_chairman` role
- Adjust the page queries and views for youth users:
  - Trainings / Scholarships: show all open training/scholarship opportunities
  - Job Openings: only show jobs when a match record exists with `status = 'Accepted'`
- Restrict youth inline action buttons so they can only apply to available training opportunities.

### Messages Page
- Correct the access check in [pages/messages.php](pages/messages.php) from `requireRole(['lydo', 'staff'])` to `requireRole(['lydo', 'sk_chairman'])`.

## 2. Advanced Matching Algorithm & UI Form Fields

### Database Changes
- Run a migration to add `age_min` and `age_max` columns to the `opportunities` table.

### Forms
- Add "Minimum Age" and "Maximum Age" fields to the posting forms in [pages/job-openings.php](pages/job-openings.php) and [pages/opportunities.php](pages/opportunities.php).
- Bind and save these fields during create/update flows.

### Matching Logic
- Update [Classes/Matching.php](Classes/Matching.php) so `calculateMatchScore`:
  - reads `age_min` and `age_max` from the opportunity
  - compares the youth's age against the range
  - awards up to 15 points when the youth falls within the preferred age range
  - verifies overlap against `opportunity_required_skills` entries instead of relying on simple description matching alone

## 3. Improving Gemini AI Matching Accuracy

### Gemini Service
- Refactor [Classes/GeminiService.php](Classes/GeminiService.php) so the prompt used in `calculateScoreOnly` and `formatMatchPrompt` asks for a JSON object:
  - `score` as a number from 0 to 100
  - `reasoning` as a brief explanation
- Parse the response programmatically and extract the score.
- This should encourage more consistent and reliable scoring by making the model structure its reasoning into a machine-readable format.

## 4. SMS & Email Notification Upgrades

### SMS
- Update [Classes/SmsService.php](Classes/SmsService.php) to support the Semaphore SMS API.
- If `semaphore_api_key` is configured in settings/environment, prioritize Semaphore.
- Otherwise, fall back to Traccar.
- Improve timeout handling and connection cleanup.

### Email Templates
- Create a modern responsive HTML email template with:
  - indigo/blue theme
  - clean typography using Inter/Helvetica
  - structured tables
  - clear call-to-action buttons
- Replace simple plain-text email blocks with this template in transactional emails and their callers.

## 5. Async Portability & Parameterized SQL Security

### Background Processing
- Update [Classes/OSYProfile.php](Classes/OSYProfile.php) to detect the operating system and use standard Linux backgrounding when running on non-Windows servers.

### Query Security
- Convert concatenated filters in [Classes/Opportunity.php](Classes/Opportunity.php) for `getAll` and `getForYouth` to fully parameterized prepared statements.

## Verification Plan

### Functional Checks
- Verify that a `youth` user only sees open training opportunities and accepted/chosen jobs.
- Test job posting with preferred age constraints and confirm the match score changes for different youth profiles.
- Validate that Gemini-based scores align closely with candidate profiles and opportunity requirements.
- Send test SMS messages using both Traccar and Semaphore setups.
- Review the redesigned email notifications in an inbox client or log output.
