# 🎯 QUICK REFERENCE GUIDE

## Municipal KK Profiling System v1.0

### For Different User Roles

---

## 👨‍💼 LYDO (Municipal Administrator)

### Main Tasks

| Task                       | How To                                            | Location                        |
| -------------------------- | ------------------------------------------------- | ------------------------------- |
| Create SK Chairman         | Dashboard → Manage SK Chairmen → Create New       | `/pages/manage-sk-chairmen.php` |
| Approve Employers          | Dashboard → Provider Approvals → Review & Approve | `/pages/provider-approvals.php` |
| Approve Training Providers | Dashboard → Provider Approvals → Review & Approve | `/pages/provider-approvals.php` |
| View All Youth             | Dashboard → Profiles → Browse                     | `/pages/profiles.php`           |
| View All Opportunities     | Dashboard → Opportunities → All                   | `/pages/opportunities.php`      |
| View Audit Logs            | Dashboard → Audit Logs → Filter by action         | `/pages/audit-logs.php`         |

### Quick Links

- Dashboard: `index.php`
- Create SK Chairman: `manage-sk-chairmen.php`
- Approve Providers: `provider-approvals.php`
- View Audit Trail: `audit-logs.php`
- Reports: `reports.php`

---

## 👥 SK Chairman (Barangay Officer)

### Main Tasks

| Task                       | How To                                    | Location                       |
| -------------------------- | ----------------------------------------- | ------------------------------ |
| Review Youth Registrations | Dashboard → Verify Youth → Review Pending | `/pages/verify-youth.php`      |
| Approve Youth Profile      | Click "Approve" on pending youth          | `/pages/verify-youth.php`      |
| Decline Youth Profile      | Click "Decline" + add remarks             | `/pages/verify-youth.php`      |
| View My Barangay Youth     | Dashboard → My Barangay → Browse          | `/pages/sk-barangay-youth.php` |
| See Youth Details          | Click on youth name → View profile        | `/pages/profile-detail.php`    |

### Important Notes

- ⚠️ You can ONLY see and approve youth from your assigned barangay
- 💡 When declining, provide clear remarks so youth know why
- 📧 Youth receive notifications when you approve/decline
- 📋 Youth in "Action Required" status can resubmit after fixing issues

### Quick Links

- Dashboard: `index.php`
- Verify Youth: `verify-youth.php`
- My Barangay: `sk-barangay-youth.php`
- Profile Detail: `profile-detail.php?id=[ID]`

---

## 👨‍🎓 Youth (Out-of-School Youth)

### Main Tasks

| Task                              | How To                                               | Location                        |
| --------------------------------- | ---------------------------------------------------- | ------------------------------- |
| **NEW:** View My Profile          | Click "My Profile" in navigation                     | `/pages/my-profile.php`         |
| Check Registration Status         | Go to My Profile → See status badge                  | `/pages/my-profile.php`         |
| **NEW:** See Why I Was Declined   | Go to My Profile → Read remarks section              | `/pages/my-profile.php`         |
| Edit My Profile                   | My Profile → Click "Edit Profile" → Save             | `/pages/my-profile.php`         |
| Resubmit After Decline            | My Profile → Edit & Save (status returns to Pending) | `/pages/my-profile.php`         |
| Browse Opportunities              | Dashboard → Opportunities                            | `/pages/opportunities.php`      |
| **NEW:** Filter by Required Skill | Opportunities → Enter skill name → Apply Filter      | `/pages/opportunities.php`      |
| View Opportunity Details          | Click "View Details" on opportunity card             | Popup modal                     |
| Apply to Opportunity              | Click "Apply" button on opportunity                  | `/api/apply_to_opportunity.php` |
| **NEW:** Track My Applications    | My Profile → Scroll to "My Applications"             | `/pages/my-profile.php`         |
| View Notifications                | Click bell icon → Notification center                | `/pages/notifications.php`      |

### Status Meanings

| Status             | Meaning                          | What You Can Do                 |
| ------------------ | -------------------------------- | ------------------------------- |
| 🟡 Pending         | Waiting for SK Chairman approval | Wait or view remarks            |
| 🟢 Verified        | Approved! Profile is active      | Browse & apply to opportunities |
| 🔴 Declined        | Needs corrections                | Edit & resubmit                 |
| 🟠 Action Required | Same as Declined                 | Review remarks & fix issues     |

### How to Resubmit After Decline

1. Go to **My Profile**
2. Read the decline remarks in the red box
3. Click **"Edit and Resubmit"**
4. Update your information based on remarks
5. Click **"Save Changes"**
6. Status returns to **Pending** for re-review
7. SK Chairman will review again

### Quick Links

- My Profile: `pages/my-profile.php` ⭐ NEW
- Opportunities: `pages/opportunities.php`
- My Applications: In My Profile (scroll down)
- Notifications: `pages/notifications.php`

---

## 🏢 Employer (Job Provider)

### Main Tasks

| Task                               | How To                                                  | Location                           |
| ---------------------------------- | ------------------------------------------------------- | ---------------------------------- |
| Register as Employer               | Public page → Provider Registration → Select "Employer" | `/pages/provider-registration.php` |
| Wait for LYDO Approval             | Dashboard → Show "Pending" status                       | —                                  |
| After Approval: Create Job Opening | Dashboard → Opportunities → "Encode New Job"            | `/pages/opportunities.php`         |
| **NEW:** Add Required Skills       | When creating job → Scroll to "Required Skills" section | Modal form                         |
| **NEW:** Specify Skill Importance  | Mark skill as Required/Preferred/Nice to have           | Modal form                         |
| Edit Job Opportunity               | Dashboard → My Opportunities → Click edit icon          | `/pages/opportunities.php`         |
| View Applications                  | Dashboard → Opportunities → Click "View Applications"   | Applications list                  |
| See Match Score                    | View Applications → See % score for each applicant      | `/pages/matching.php`              |
| Accept Applicant                   | Applications → Click "Accept"                           | `/api/update_match_status.php`     |
| Reject Applicant                   | Applications → Click "Reject"                           | `/api/update_match_status.php`     |
| Close Job Opening                  | Edit job → Change status to "Closed"                    | Job edit form                      |

### About Required Skills (NEW!)

- When you create a job, add the skills you need
- Youth can filter opportunities by these skills
- Skills used in matching algorithm
- More matching skills = better recommendations

### Quick Links

- Register: `pages/provider-registration.php`
- Dashboard: `index.php`
- My Opportunities: `pages/my-job-openings.php`
- View Applications: In Opportunities list

---

## 🎓 Training Provider

### Main Tasks

| Task                            | How To                                                           | Location                           |
| ------------------------------- | ---------------------------------------------------------------- | ---------------------------------- |
| Register as Provider            | Public page → Provider Registration → Select "Training Provider" | `/pages/provider-registration.php` |
| Wait for LYDO Approval          | Dashboard → Show "Pending" status                                | —                                  |
| After Approval: Create Training | Dashboard → Opportunities → "Encode New Training"                | `/pages/opportunities.php`         |
| **NEW:** Add Required Skills    | When creating training → Scroll to "Required Skills" section     | Modal form                         |
| Add Training Details            | Training form → Duration, Modality, Certification, Provider      | Form fields                        |
| Edit Training Program           | Dashboard → My Training → Click edit icon                        | `/pages/opportunities.php`         |
| View Applications               | Dashboard → Opportunities → Click "View Applications"            | Applications list                  |
| Accept Trainee                  | Applications → Click "Accept"                                    | `/api/update_match_status.php`     |
| Reject Trainee                  | Applications → Click "Reject"                                    | `/api/update_match_status.php`     |

### Quick Links

- Register: `pages/provider-registration.php`
- Dashboard: `index.php`
- My Training: `pages/my-training-programs.php`

---

## 🆕 NEW FEATURES EXPLAINED

### 1. My Profile Page (Youth)

**What's New:** Complete personal dashboard for youth

- See registration status (Pending/Verified/Declined)
- View decline remarks if applicable
- Edit personal information
- See all your applications in one place

**Access:** Click "My Profile" in top navigation menu

### 2. Skill-Based Filtering (Youth)

**What's New:** Filter opportunities by required skills

- New filter input on Opportunities page
- Shows only opportunities needing specific skills
- Combines with existing filters (type, location)
- Helps youth find best-fitting opportunities

**How to Use:**

1. Go to Opportunities
2. Enter skill name (e.g., "Welding", "Cooking")
3. Click "Apply Filters"
4. See only opportunities requiring that skill

### 3. Opportunity Skills Management (Providers)

**What's New:** Providers specify required skills for opportunities

- When creating opportunity, add required skills
- Mark importance (Required/Preferred/Nice to have)
- System uses skills in matching algorithm
- Youth can filter by these skills

**How to Use:**

1. Create new Job/Training
2. Scroll to "Required Skills" section
3. Enter skill name
4. Select importance level
5. Click "Add Skill"
6. Repeat for more skills
7. Submit opportunity

### 4. Verification Remarks Visibility (Youth)

**What's New:** Youth can see why their profile was declined

- When profile declined, remarks displayed on My Profile
- Clear explanation of required corrections
- "Edit and Resubmit" button for easy action

**How to Use:**

1. Go to My Profile
2. If Declined: Red box shows remarks
3. Click "Edit and Resubmit"
4. Fix issues mentioned
5. Save and resubmit

---

## ⚡ Quick Actions

### For Users Who Are...

**Just Registered & Pending Approval**

1. Go to My Profile
2. Review your information
3. Wait for SK Chairman approval
4. Check notifications

**Declined & Need to Resubmit**

1. Go to My Profile
2. Read remarks in red box
3. Click "Edit and Resubmit"
4. Make corrections
5. Save changes
6. Wait for re-review

**Verified & Ready to Apply**

1. Go to Opportunities
2. Use filters to find good matches
3. NEW: Filter by Required Skill
4. Click "Apply" on interesting opportunities
5. Track your applications in My Profile

**Trying to Create Opportunities (Providers)**

1. Go to Opportunities
2. Click "Encode New Job/Training"
3. Fill all required fields
4. NEW: Add Required Skills section
5. Submit
6. Youth will see it on their opportunities list

---

## 🔑 Key Workflows

### Youth Registration & Approval Workflow

```
1. Youth Self-Registers
   ↓
2. Status = Pending
   ↓
3. SK Chairman Reviews
   ├→ Approve → Status = Verified ✅
   └→ Decline + Remarks → Status = Declined ❌
      ↓
      Youth Edits & Resubmits
      ↓
      Back to Step 3
```

### Opportunity Creation & Application Workflow

```
1. Provider Creates Opportunity
   ├→ Adds Required Skills (NEW)
   └→ Status = Open
      ↓
2. Youth Sees Opportunity
   ├→ Filters by Required Skill (NEW)
   ├→ Sees Match Score
   └→ Clicks "Apply"
      ↓
3. Application Created
   ├→ Provider Reviews
   ├→ Sees Youth Match Score
   ├→ Accepts or Rejects
   └→ Youth Notified
```

---

## 🆘 Common Questions

**Q: I'm a youth and my profile is Pending. What now?**
A: Wait for your SK Chairman to review it. Check back in My Profile for status updates.

**Q: Why was my profile declined?**
A: Go to My Profile. You'll see SK Chairman's remarks in red box explaining why.

**Q: How do I resubmit after being declined?**
A: Click "Edit and Resubmit" in the remarks box, fix the issues, save. Your status goes back to Pending.

**Q: I'm a provider and can't create opportunities.**
A: You must be approved by LYDO first. Wait for approval notification.

**Q: How do skills help matching?**
A: When you have skills and opportunities need those skills, the system recognizes this as a good match. Higher match score = better recommendation.

**Q: Where do I see my applications?**
A: Go to My Profile (youth only). Scroll down to "My Applications" section.

**Q: Can I change my barangay?**
A: No, barangay is assigned at registration and cannot be changed.

---

## 📞 Getting Help

1. **Questions About Features?** → Check this guide
2. **Technical Problems?** → Contact IT Support
3. **Registration Issues?** → Contact SK Chairman
4. **Opportunity Issues?** → Contact Provider
5. **System Issues?** → Contact Municipal Administrator (LYDO)

---

## 📋 Important Reminders

- ✅ Always provide complete information during registration
- ✅ Upload clear, valid documents
- ✅ Check My Profile regularly for status updates
- ✅ Act quickly when opportunities are posted (spots are limited)
- ✅ Respond to notifications promptly
- ⚠️ Do not share login credentials
- ⚠️ System logs all activities for accountability
- ⚠️ Barangay access is strictly enforced (for SK Chairmen)

---

**Last Updated:** May 17, 2026  
**Version:** 1.0  
**For:** All Users of Municipal KK Profiling System
