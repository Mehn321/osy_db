# System Use Case Documentation

This document outlines the primary use cases and responsibilities for each user role in the **Integrated Web-Based Information System for Youth Profiling and Skills Matching**.

---

## 1. LYDO (Local Youth Development Officer / Admin)
*The primary administrator of the system.*

**Primary Use Cases:**
- **Dashboard Overview**: Monitor system-wide statistics, youth distribution, and matching success rates.
- **Youth Profile Management**: View, search, and manage all registered youth profiles across all barangays.
- **Opportunity Management**: Create and manage both Job Openings and Vocational Training programs.
- **Skills Matching**: Utilize the AI-driven matching engine to connect youth members with suitable opportunities.
- **Stakeholder Management**: 
    - Create and manage SK Chairman accounts for each barangay.
    - Approve or decline registrations from Employers and Training Providers.
- **Broadcast Notifications**: Send system-wide announcements or specific alerts to groups of users.
- **Reporting & Analytics**: Generate comprehensive reports on skills distribution, employment status, and program impact.
- **System Auditing**: Monitor system activity via Audit Logs for security and accountability.

---

## 2. SK Chairman
*Barangay-level administrator responsible for local youth data.*

**Primary Use Cases:**
- **Barangay Dashboard**: View real-time statistics of youth residents within their specific barangay.
- **Youth Verification**: Review and verify the registration profiles of youth residents to ensure data accuracy.
- **Barangay Registry**: Access and update the list of youth members residing in their barangay.
- **Profile Auditing**: Monitor the status (Active, In Training, Employed) of youth residents.
- **Receive Alerts**: Get notified about system updates or specific instructions from the LYDO.

---

## 3. Youth Member
*The primary beneficiaries of the system.*

**Primary Use Cases:**
- **Self-Profiling (Registration)**: Register into the system by providing personal details, educational background, and skills.
- **Skills Portfolio**: Update their primary and secondary skills to improve their matching score.
- **Opportunity Search**: Browse available job openings, scholarships, and training programs.
- **Apply for Opportunities**: Submit applications for specific opportunities matched by the system.
- **Personal Dashboard**: View the status of their applications and recent notifications.
- **Matching Insights**: Receive AI-generated suggestions on which skills to improve for better matching.

---

## 4. Employer
*External partners providing employment opportunities.*

**Primary Use Cases:**
- **Job Posting**: Create and publish job openings with specific requirements (experience, skills, education).
- **Applicant Review**: View profiles of youth members who have been matched or applied to their postings.
- **Manage Openings**: Update the status (Open/Closed) of their job postings.
- **Notifications**: Receive alerts when new candidates match their job requirements.

---

## 5. Training Provider
*Partners providing skills development and vocational training.*

**Primary Use Cases:**
- **Program Posting**: Publish vocational training courses, scholarships, or workshops.
- **Trainee Management**: Review the list of interested youth members and manage enrollment.
- **Course Updates**: Update details, deadlines, and requirements for training programs.
- **Outcome Tracking**: Monitor the number of youth who have successfully completed their programs.

---

## System Workflow Summary
1. **Registration**: Youth registers; SK Chairman verifies the residency.
2. **Profiling**: Youth updates skills; AI engine prepares matching scores.
3. **Posting**: Employers/Providers post opportunities (subject to LYDO oversight).
4. **Matching**: LYDO or AI matches Youth with Opportunities.
5. **Success**: Youth gets hired or trained; System updates status for reporting.
