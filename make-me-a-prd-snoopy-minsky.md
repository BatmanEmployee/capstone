# PRD + Strict Panelist Review
# Web-Based Event Management System with Community Forum
# City Mayor's Office — Muslim Concerns and Affairs Division (MCAD)

---

## PART 1: PRODUCT REQUIREMENTS DOCUMENT (PRD)

### Context
This system was proposed to address the operational challenges faced by Muslim communities in General Santos City during the Ramadan season. Barangay officials, Imams, Community Leaders, and residents currently rely on paper-based records, messaging apps (Messenger/Viber), and informal social media postings (Facebook) to coordinate events, track attendance, and document donations. This creates schedule conflicts, incomplete records, and limited transparency. The proposed solution is a centralized, role-based, web-based platform that formalizes and digitalizes these workflows.

---

### 1. Product Overview

| Field | Detail |
|---|---|
| Product Name | Web-Based Event Management System with Community Forum for MCAD |
| Version | 1.0 (Capstone Proposal) |
| Tech Stack | HTML, CSS, JavaScript (Frontend) · PHP (Backend) · MySQL (Database) · VS Code (IDE) |
| Deployment | Cloud-based or local server (XAMPP during development) |
| Access | Browser-only, mobile-responsive, no dedicated mobile app |

---

### 2. User Roles & Permissions

| Role | Core Permissions |
|---|---|
| **LGU/Barangay Admin** | Full system access: manage users (create/activate/suspend), approve events, post announcements, generate all reports |
| **Imam/Mosque Administrator** | Input prayer schedules, manage mosque events, post Islamic announcements, validate religious content |
| **Community Leader/Organizer** | Submit community activities, record attendance, manage donations/distributions, monitor event status |
| **Community Viewer (Resident)** | Read-only: view published events, announcements, schedules — cannot modify any data |

---

### 3. Functional Requirements (7 Modules)

#### MODULE 1 — User Management
- FR-01: Admin can create, edit, activate, and **suspend** user accounts
- FR-02: Role-based access control enforced at every page (4 roles)
- FR-03: Secure login with hashed passwords (bcrypt minimum)
- FR-04: Community assignment during registration
- FR-05: Session-based authentication with proper logout/session invalidation

#### MODULE 2 — Ramadan Program & Activity Scheduling
- FR-06: Authorized users can create, edit, and delete events
- FR-07: Event fields: title, description, date, time, venue, organizer, event type, visibility
- FR-08: Event status tracking: **Upcoming, Ongoing, Completed** (three states)
- FR-09: **Calendar view AND list view** for event schedules
- FR-10: Events scoped by community (community-aware filtering)
- FR-11: System-generated events for daily Iftar, Taraweeh, Suhoor

#### MODULE 3 — Announcement & Notification
- FR-12: Admin/Imam can post announcements
- FR-13: Announcement **categories**: Prayer Schedules, Event Reminders, Charity Drive Updates, Community Advisories
- FR-14: In-system notification alerts for registered users
- FR-15: Optional email notification via PHPMailer
- FR-16: Announcements scoped by community

#### MODULE 4 — Activity Monitoring & Attendance Tracking
- FR-17: **Attendance list input per event** (this is MISSING in current code)
- FR-18: Event monitoring dashboard showing event status and participation statistics
- FR-19: Generate simple event monitoring/attendance reports
- FR-20: Leader/Admin can update event status during/after an event

#### MODULE 5 — Resource & Donation Management
- FR-21: Record donations: donor name (optional), type (cash/food/supplies), amount or quantity, date
- FR-22: Record distributions: beneficiary, item name, quantity, distribution date
- FR-23: Donation and distribution summary dashboard with totals
- FR-24: Community-scoped donation tracking

#### MODULE 6 — Report Generation
- FR-25: Generate printable Ramadan event schedule reports
- FR-26: Generate printable attendance summary reports (per event)
- FR-27: Generate printable donation & distribution records
- FR-28: Export via **PDF** or browser print function
- FR-29: Reports are community-scoped and role-accessible

#### MODULE 7 — Dashboard & Analytics
- FR-30: Summary dashboard with total events, upcoming events, attendance totals, donation totals
- FR-31: Role-specific dashboard views (each role sees relevant stats)
- FR-32: Recent activity feed (last 7 days of events + announcements)

#### MODULE 8 — Community Forum *(Title Module — Currently Absent)*
- FR-33: Registered users can post topics/threads in a community discussion board
- FR-34: Users can reply to forum threads
- FR-35: Admin/Imam can moderate (pin, delete) forum posts
- FR-36: Forum scoped by community

---

### 4. Non-Functional Requirements

| Category | Requirement |
|---|---|
| **Security** | bcrypt password hashing, prepared statements (PDO/MySQLi), CSRF tokens, session timeout |
| **Usability** | Mobile-responsive design, intuitive UI for elderly/non-tech users |
| **Availability** | 24/7 system access for Community Viewers |
| **Performance** | Page load < 3 seconds on standard connection |
| **Compatibility** | Google Chrome, Microsoft Edge (modern versions) |
| **Accessibility** | System accessible on smartphone browsers |
| **Offline** | No offline mode required |
| **SMS** | Not required unless free API available |
| **QR/Biometric Attendance** | Optional/out of scope for base version |
| **Online Payments** | Not supported — donations manually entered |
| **Islamic Calendar** | No auto-computation — dates entered manually |

---

### 5. System Boundaries / Out of Scope
- Native Android/iOS mobile application
- Real-time SMS gateway
- Biometric attendance tracking
- QR code scanning
- Online payment processing
- Automated Islamic calendar computation
- Multi-organization (cross-LGU) access

---

### 6. Hardware & Software Requirements

**Software:** Windows OS · Google Chrome / MS Edge · HTML/CSS/JS · PHP · MySQL · VS Code · PHPMailer

**Hardware (minimum):** Intel Core i3 · 4 GB RAM · 256 GB HDD/SSD · 1366×768 display · Stable internet · Keyboard and Mouse

---

---

## PART 2: STRICT PANELIST CODE REVIEW

> Role: Acting as a strict capstone defense panelist evaluating the implemented system against the manuscript's stated objectives, scope, and requirements.

---

### VERDICT SUMMARY

| Module | Manuscript Requirement | Implementation Status | Severity |
|---|---|---|---|
| Community Forum | Required (in project title) | **MISSING** | CRITICAL |
| Attendance Tracking | Required (Module 4) | **MISSING** | CRITICAL |
| Calendar View | Required (Module 2) | **MISSING** | HIGH |
| Event Status "Ongoing" | Required | **MISSING** (only upcoming/completed) | HIGH |
| Announcement Categories | Required (Module 3) | **MISSING** | HIGH |
| User Account Suspend/Activate | Required (Admin function) | **MISSING** | HIGH |
| Password Hashing (bcrypt) | Non-functional requirement | **VIOLATED** (uses MD5) | HIGH |
| SQL Injection Protection | Security requirement | **PARTIALLY VIOLATED** | HIGH |
| Attendance Reports | Required (Module 4 + 6) | **MISSING** | HIGH |
| PDF Export | Required (Module 6) | **MISSING** (print only) | MEDIUM |
| Email Notifications | Optional but stated | **MISSING** (PHPMailer not wired) | MEDIUM |
| Donor Name Optional | Stated in manuscript | **VIOLATED** (form requires it) | LOW |
| Notification categories | Required | **MISSING** | MEDIUM |

---

### CRITICAL DEFICIENCIES (Will likely fail defense)

#### DEFICIENCY 1 — Community Forum Is Completely Absent
**Manuscript states:** The system title itself includes "Community Forum." Objective #1 states role-based access for all modules including the forum. Mohammed et al. (2023) is cited specifically for "discussion forums."
**Found in code:** No `forum`, `threads`, `replies`, or `posts` table. No forum page. No forum sidebar link for any role. Zero implementation.
**Required:** A forum module with post/reply/moderation capability, scoped per community.

#### DEFICIENCY 2 — Attendance Tracking Module Is Completely Absent
**Manuscript states (Scope item 4):** "Attendance list input per event," "Event monitoring dashboard showing event status and participation statistics," "Generate simple event monitoring reports."
**Objective 3 explicitly states:** "Develop an activity monitoring, attendance tracking, and resource management module that records event participation."
**Found in code:** No `attendance` table in `rminder_db.sql`. No attendance form. No attendance page. No attendance report. The dashboard shows "0" for participation statistics as there is no data model to support it.
**Required:** An `attendance` table linking users to events, an input form per event, a participation summary per event, and an attendance report.

---

### HIGH SEVERITY DEFICIENCIES

#### DEFICIENCY 3 — No Calendar View
**Manuscript states:** "Calendar view and list view for event schedules."
**Found in code:** `events.php` shows a list view only. No calendar widget, no FullCalendar.js or similar library included.
**Required:** A monthly/weekly calendar interface that displays scheduled events visually.

#### DEFICIENCY 4 — Event Status "Ongoing" Missing
**Manuscript states:** "Event status tracking categorized as **upcoming, ongoing, or completed**."
**Found in code:** `events` table `status` column only has values `upcoming` and `completed` in both the schema and the edit form dropdown. "Ongoing" is never an option.
**Required:** Add "ongoing" as a valid status value and surface it in UI and reports.

#### DEFICIENCY 5 — Announcement Categories Missing
**Manuscript states:** "Post official announcements categorized as **prayer schedules, event reminders, charity drive updates, or community advisories**."
**Found in code:** `announcements` table has no `category` column. The add announcement form has no category dropdown. All announcements are uncategorized.
**Required:** Add `category` column to DB and a dropdown to the announcement form.

#### DEFICIENCY 6 — Admin Cannot Activate or Suspend Users
**Manuscript states:** "LGU/Barangay Admin — manages the entire system, **approves accounts**, and generates reports." FR-01: "Admin can create, edit, activate, and suspend user accounts."
**Found in code:** No user management page. No `status` or `is_active` column in the `users` table. Admin has no interface to view registered users or change their status. Any user who self-registers immediately has full access.
**Required:** Add `status` column (active/suspended/pending), a User Management page for Admin with activate/suspend actions.

#### DEFICIENCY 7 — MD5 Password Hashing (Security Violation)
**Manuscript states (Non-functional):** Security requirements. References Kumar & Priya (2025) and Manoj et al. (2025) on securing sensitive records.
**Found in code (`login_action.php`):** `$password = md5($_POST['password']);` — MD5 is cryptographically broken. It is trivially reversible via rainbow tables and is not an acceptable hashing algorithm for any system handling community personal data.
**Required:** Replace MD5 with PHP's `password_hash($password, PASSWORD_BCRYPT)` and `password_verify()`.

#### DEFICIENCY 8 — SQL Injection Vulnerabilities
**Manuscript states:** Security, RBAC, data integrity.
**Found in code:** Multiple files use direct string interpolation in SQL queries:
- `add_event_action.php`: `"... VALUES ('{$title}', '{$description}', ..."` — vulnerable
- `add_donation.php`: Direct string interpolation — vulnerable
- `add_distribution.php`: Direct string interpolation — vulnerable
- Only `add_announcement_action.php` uses `real_escape_string` — inconsistent
**Required:** All queries must use prepared statements with bound parameters (MySQLi or PDO).

---

### MEDIUM SEVERITY DEFICIENCIES

#### DEFICIENCY 9 — No PDF Export
**Manuscript states:** "Export options via PDF or browser print function."
**Found in code:** `reports.php` only has a `window.print()` button. No PDF library (e.g., TCPDF, FPDF, mPDF, or DomPDF) is included.
**Required:** Integrate a PHP PDF generation library to export reports as downloadable PDFs.

#### DEFICIENCY 10 — PHPMailer Not Integrated
**Manuscript states (Scope item 3):** "Optional email notification feature for stakeholder dissemination." PHPMailer is listed in Software Requirements table.
**Found in code:** PHPMailer is listed in requirements but not present in the file system. No `composer.json`, no `/vendor` folder, no mail-sending code anywhere.
**Required:** Either integrate PHPMailer with a configured SMTP provider, or explicitly document it as "deferred optional feature" in the defense.

#### DEFICIENCY 11 — No CSRF Protection
**Found in code:** All POST forms lack CSRF tokens. An attacker could forge requests on behalf of authenticated users.
**Required:** Generate and validate CSRF tokens in all forms.

---

### LOW SEVERITY / MINOR ISSUES

#### ISSUE 1 — Donor Name Required in Form (Should Be Optional)
**Manuscript states:** "donor name (optional)"
**Found in code:** `donations.php` form has `donor_name` with no indication it is optional and no fallback for anonymous donors.
**Fix:** Mark field as "(optional)" and handle empty donor_name gracefully (store as "Anonymous").

#### ISSUE 2 — 143 Empty/Test Announcement Records in Database
**Found in code:** `rminder_db.sql` seeds 143 announcement records, most with empty or garbage content. This will make the live demo look broken.
**Fix:** Clean the seed data. Keep only 5–10 realistic sample announcements.

#### ISSUE 3 — Hard-Coded Database Credentials
**Found in code (`config/database.php`):** Root credentials with no password hard-coded.
**Fix:** Move credentials to a `.env` file or `config.ini`, exclude from version control.

#### ISSUE 4 — `Xannouncements.php` Dead File
**Found in code:** A leftover file `Xannouncements.php` exists — appears to be a disabled/backup version.
**Fix:** Delete it. Dead code in a capstone system looks unprofessional in a defense.

#### ISSUE 5 — No Input Validation on Registration
**Found in code (`register_action.php`):** No email format check, no password length minimum, no duplicate email check before insert.
**Fix:** Validate email format, enforce minimum password length (8+ characters), and check for existing email before inserting.

---

### WHAT IS IMPLEMENTED CORRECTLY (Acknowledge strengths)

- Role-based sidebar navigation correctly adapts per role
- Community-scoped filtering on events, announcements, and donations is architecturally correct
- System-generated daily prayer time events (Iftar/Taraweeh/Suhoor) is a nice feature
- Dashboard with role-specific statistics is a good UX decision
- Donation + Distribution dual-tracking model is correctly designed
- `real_escape_string` used in at least one action file (shows awareness, needs consistency)
- Session-based login flow is structurally correct

---

### IMPLEMENTATION PRIORITY ORDER (What to fix first)

| Priority | Item | Estimated Effort |
|---|---|---|
| 1 | Attendance tracking module (table + form + report) | 2–3 days |
| 2 | Community Forum module (threads + replies + moderation) | 3–4 days |
| 3 | Calendar view for events (FullCalendar.js) | 1 day |
| 4 | Fix MD5 → bcrypt password hashing | 2–3 hours |
| 5 | Fix SQL injection (all action files → prepared statements) | 1 day |
| 6 | Add "Ongoing" event status | 1 hour |
| 7 | Add announcement categories | 2–3 hours |
| 8 | Add User Management page (Admin: activate/suspend) | 1 day |
| 9 | Integrate FPDF/mPDF for PDF export | 4–6 hours |
| 10 | Clean seed data, delete dead files | 1 hour |
| 11 | Add CSRF protection | 4–6 hours |
| 12 | Donor name optional + validation | 1 hour |

---

### Verification / Testing Plan

After fixes are implemented:

1. **Attendance Test:** Create an event → Record attendance for 3 users → View participation stats on dashboard → Generate attendance report → Print/export PDF
2. **Forum Test:** Login as viewer → Post a topic → Login as imam → Reply and pin thread → Login as admin → Delete inappropriate post
3. **Calendar Test:** Create events on different dates → Switch to calendar view → Confirm events appear on correct dates
4. **Security Test:** Attempt login with MD5-equivalent password after bcrypt migration → Confirm it fails → Confirm new bcrypt login works
5. **Role Test:** Login as Viewer → Attempt to access `/pages/add_event.php` directly → Confirm redirect/denial
6. **Announcement Category Test:** Post announcement with "Charity Drive Updates" category → Confirm it appears with correct label
7. **User Management Test:** Admin suspends a user → That user attempts login → Confirm access is denied

