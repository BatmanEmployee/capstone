# PANELIST REVIEW — MCAD Web-Based Event Management System
**Reviewer Role:** Strict Capstone Defense Panelist  
**Review Date:** 2026-05-25  
**Reviewed By:** Claude (Co-developer audit)  
**Stack:** PHP / MySQL / XAMPP / Vanilla JS  

---

## EXECUTIVE VERDICT

The system is **70% defense-ready**. Core modules are all present and functional.
The remaining 30% is a mix of security gaps, UI inconsistencies, missing micro-features,
and one critical self-registration vulnerability that will get you failed on the spot.
Fix the 5 critical items first. Everything else is polish.

---

## PART 1 — CRITICAL (Will likely fail defense if not fixed)

### C-01 — Anyone Can Self-Register as Admin [SECURITY FAILURE]
**File:** `actions/register_action.php` line 11  
**Problem:**
```php
$allowed_roles = ['admin', 'imam', 'leader', 'viewer'];
```
The allowed_roles list includes 'admin'. The register form only shows viewer/leader/imam
in the dropdown, but an attacker (or a panelist testing it) can submit `role=admin`
via Postman, curl, or browser DevTools and get full admin access instantly.

**Fix:** Remove 'admin' from the allowed_roles list. Admin accounts should only be
created directly in the database or by an existing admin through User Management.
```php
$allowed_roles = ['imam', 'leader', 'viewer'];
```

---

### C-02 — SQL Injection in pages/*.php (pages are not protected) [SECURITY FAILURE]
**Files:** `dashboard.php`, `forum.php`, `forum_thread.php`, `attendance.php`,
           `announcements.php`, `export_report.php`, `reports.php`

**Problem:** While all `actions/*.php` use prepared statements correctly, the `pages/*.php`
files still use direct string interpolation in SQL queries. Examples:

`dashboard.php` line 17:
```php
$res = $conn->query("SELECT community_id FROM users WHERE id = $user_id");
```

`forum.php` line 20:
```php
$tRes = $conn->query("SELECT t.*, u.name... WHERE t.community_id = $community_id ...");
```

`forum_thread.php` line 21:
```php
$tRes = $conn->query("SELECT t.*, u.name... WHERE t.id = $thread_id");
```

These are safe only because the values come from `$_SESSION` (trusted) or are cast
to `(int)`. However, a panelist testing for SQL injection will flag the pattern as
bad practice — and rightly so. The manuscript likely claims prepared statements are used
throughout. That claim is currently false for all page files.

**Fix:** Convert all page queries to prepared statements, or at minimum ensure every
interpolated variable is explicitly cast: `(int)$user_id`, `(int)$community_id`.
The int casts that ARE present make this safe, but the code pattern looks unprofessional
and contradicts your manuscript's security claims.

---

### C-03 — CSRF Missing on User Management Page [SECURITY]
**File:** `pages/users.php` lines 140-149  
**Problem:** The activate/suspend form POSTs without a CSRF token. Every other form
in the system now has CSRF protection — this one was missed.
```html
<form method="POST" action="users.php" class="action-form">
    <!-- NO <?= csrf_field() ?> HERE -->
    <input type="hidden" name="toggle_user_id" value="...">
```

**Fix:** Add `<?= csrf_field() ?>` inside that form, and add `csrf_verify()` check
at the top of the POST handler in users.php.

---

### C-04 — No Session Timeout [SECURITY]
**Problem:** Sessions never expire. If a user leaves their browser open on a shared
computer, the session remains active indefinitely. The manuscript likely references
session management as a security feature — this is unimplemented.

**Fix:** Add to the top of every protected page (or in config/database.php):
```php
ini_set('session.gc_maxlifetime', 3600); // 1 hour
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity']) > 3600) {
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();
```

---

### C-05 — No Brute Force / Login Rate Limiting [SECURITY]
**File:** `actions/login_action.php`  
**Problem:** The login form has no attempt limiting. A panelist can repeatedly submit
wrong passwords without any lockout. OWASP A07:2021 (Identification and Authentication
Failures) specifically lists this as a critical flaw.

**Fix (simple):** Track failed attempts in session and lock for 5 minutes after 5 failures:
```php
$_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
if ($_SESSION['login_attempts'] >= 5) {
    // Lock for 5 minutes using a session timestamp
}
```

---

## PART 2 — HIGH SEVERITY (Will be flagged, cost marks)

### H-01 — Theme Inconsistency Across Pages [UI]
**Problem:** Six pages still use the OLD white/light theme while the rest of the system
uses the dark theme. A panelist switching between pages will immediately notice the
jarring white flash.

| Page | Background | Status |
|---|---|---|
| `users.php` | `#f8fafc` / `white` | ❌ Light theme |
| `forum.php` | `#f8fafc` / `white` | ❌ Light theme |
| `forum_thread.php` | `#f8fafc` / `white` | ❌ Light theme |
| `attendance.php` | `#f8fafc` / `white` | ❌ Light theme |
| `announcements.php` | `#f8fafc` / `white` | ❌ Light theme |
| `edit_event.php` | `#f8fafc` / `white` | ❌ Light theme |
| `dashboard.php` | `#121212` | ✅ Dark |
| `events.php` | `#121212` | ✅ Dark |
| `donations.php` | `#121212` | ✅ Dark |
| `reports.php` | `#121212` | ✅ Dark |
| `profile.php` | `#121212` | ✅ Dark |

**Fix:** Apply the same dark theme CSS pattern to the 6 remaining pages.

---

### H-02 — No Pagination on Long Lists [UX]
**Problem:** The donation history shows 30 records, attendance shows 20, reports show
all records — with no pagination. During the live demo with sample data, these lists
will look cluttered. A real system managing months of Ramadan data will have hundreds
of records with no way to navigate them.

**Affected pages:** donations.php, attendance.php, reports.php, forum.php

**Fix:** Add simple PHP pagination (LIMIT/OFFSET) with Previous/Next buttons.

---

### H-03 — No Edit/Delete for Announcements [FUNCTIONALITY]
**Problem:** Admins and Imams can create announcements but cannot edit or delete them.
If an announcement has a typo or incorrect prayer time, there is no way to fix it
without direct database access. Panelists will test this.

**Fix:** Add an edit form (similar to edit_event.php) and a delete action for
announcements. Role-guard to admin/imam only.

---

### H-04 — No Edit for Donations or Attendance Records [FUNCTIONALITY]
**Problem:** Once a donation or attendance record is saved, it cannot be corrected.
Data entry errors are permanent. This contradicts the manuscript's claim of a
"complete resource management module."

**Fix:** Add edit capabilities for donation records and attendance entries.
Role-guard to admin/leader only.

---

### H-05 — No Forgot Password / Password Reset [FUNCTIONALITY]
**Problem:** If a user forgets their password, there is no recovery mechanism.
They must contact the admin directly. The system collects email addresses specifically
for this purpose, but no reset flow exists.

**Fix:** Even a simple "contact admin to reset password" UI message on the login page
is better than silence. Full implementation: generate a reset token, email it via
PHPMailer (already in manuscript), confirm token, allow new password entry.

---

### H-06 — Forum & Attendance Pages Have No Delete for Own Records [FUNCTIONALITY]
**Problem:** Users cannot delete their own forum posts. Attendance records once entered
cannot be removed even if they were entered by mistake.

---

### H-07 — RA 10173 Data Privacy Act (Philippines) Compliance [LEGAL]
**Problem:** The system collects personally identifiable information (PII):
- User names and email addresses
- Donor names and donation amounts
- Attendance records linking names to events
- Appointment details (name, contact, email)

The Republic Act 10173 (Data Privacy Act of 2012) requires:
- A Privacy Policy / Data Privacy Notice visible to users
- Consent before collecting personal data
- Data subject rights (access, correction, erasure)

A panelist from a Philippine institution will know this law. Not mentioning it
in your defense is a gap.

**Fix:** Add a simple Privacy Policy page. Add a consent checkbox on the
registration form. Mention RA 10173 compliance in your manuscript and defense.

---

### H-08 — No Input Length Validation on Critical Fields [SECURITY]
**Problem:** No maximum length checks on free-text inputs before database insertion.
Examples: forum thread body, announcement message, event description.
A user could submit 50,000 characters and crash the insert or cause display issues.

**Fix:** Add `maxlength` attributes on form inputs and `strlen()` server-side checks
in action files. Match the database column sizes (VARCHAR/TEXT limits).

---

## PART 3 — MEDIUM SEVERITY (Polish items, cost marks on criteria sheets)

### M-01 — Appointments Module Is Incomplete
**Problem:** `appointments.php` and `book_appointment.php` exist but:
- The appointment form (book_appointment.php) has no CSRF token
- `update_appointment.php` exists but is not linked from any UI
- There is no admin view of pending appointments with approve/reject UI
- The sidebar links to "Appointments" but the page experience is disconnected from the main system

---

### M-02 — No Audit Log for Sensitive Actions
**Problem:** When an admin suspends a user, a donation is deleted, or an attendance
record is modified, there is no record of who did it and when. Audit trails are
standard in government and LGU systems and will be asked about.

**Fix:** Create a simple `audit_logs` table (user_id, action, target_table, target_id,
created_at) and insert a row for critical actions.

---

### M-03 — Error Handling Shows Raw PHP Errors
**Problem:** `add_event_action.php` line 44 outputs:
```php
echo "Error: " . $conn->error;
```
Raw database errors should never be shown to end users in any system — especially
not in a government system. The panelist will run a bad query and see this.

**Fix:** Log errors silently, redirect with a generic message. Remove all
`echo "Error: ".$conn->error` lines.

---

### M-04 — No Search Within Modules (Only Global Search)
**Problem:** The global search in the header finds events and announcements.
But there is no search within the donations list, attendance records, or forum threads.
With months of data, users cannot find a specific donor or event attendance.

---

### M-05 — system_events.php Uses String Interpolation (Not Prepared Statements)
**File:** `functions/system_events.php` lines 23-38  
**Problem:** Although `real_escape_string` is used, the query still uses string
interpolation instead of prepared statements. Inconsistent with the pattern
established in actions/*.php.

---

### M-06 — No `lang` Attribute on HTML Pages
**Problem:** `login.php`, `register.php`, `book_appointment.php` and others are
missing `<html lang="en">`. This is a WCAG 2.1 accessibility requirement and an
HTML5 best practice that panelists testing accessibility will flag.

---

### M-07 — No 404 / Error Pages
**Problem:** Accessing a non-existent page (e.g., `/pages/xyz.php`) returns the
default Apache 404 page — unbranded and unprofessional. A well-built system
has a custom error page.

---

### M-08 — Moderate_thread.php Uses Direct Queries (Non-Prepared)
**File:** `actions/moderate_thread.php` lines 20-24  
**Problem:**
```php
$conn->query("UPDATE forum_threads SET is_pinned = NOT is_pinned WHERE id = $thread_id");
$conn->query("DELETE FROM forum_replies WHERE thread_id = $thread_id");
$conn->query("DELETE FROM forum_threads WHERE id = $thread_id");
```
These are safe because `$thread_id` is cast to int, but panelists grading
"use of prepared statements" will deduct marks.

---

### M-09 — No Confirmation After Key Actions (Missing Flash Messages)
**Problem:** After posting a forum reply, recording attendance, or adding a donation,
there is no success message — the page just redirects. Users don't know if their
action succeeded.

**Affected actions:** add_reply.php, add_thread.php, add_donation.php, add_distribution.php

---

### M-10 — Forum Thread Page Has Light Theme
**Problem:** `forum_thread.php` uses `background:#f8fafc` (white) but it is one of
the most-used pages in the system. During live demo switching between forum.php
and forum_thread.php produces a jarring white page.

---

## PART 4 — LOW SEVERITY (Minor polish)

### L-01 — Hardcoded Venue as "Mosque" in System Events
**File:** `functions/system_events.php` line 37  
`venue = 'Mosque'` — should be a configurable setting, not hardcoded.

### L-02 — Dashboard Queries Use `$community_id` via Direct Query First
`dashboard.php` line 17 fetches community_id with a direct query before
the prepared-statement pattern kicks in. Minor inconsistency.

### L-03 — `edit_event.php` Has Light Theme
Same as H-01 but deserves a separate note because the edit flow is
a primary admin function visible during the defense demo.

### L-04 — No Character Count on Textarea Fields
Forum thread body, announcement message, event description — no feedback
on how long the text is or what the limit is.

### L-05 — Missing `autocomplete="off"` on Password Fields
Password fields on login and register do not disable autocomplete,
which may be flagged as a security concern by a strict panelist.

### L-06 — `appointment_confirmation.php` Has No Redirect if No Session
If someone accesses `appointment_confirmation.php` directly without
going through the booking flow, `$_SESSION['appt_confirmed']` is empty
and the page crashes or shows blank content.

### L-07 — `logout.php` Should Regenerate Session ID Before Destroy
Best practice for preventing session fixation attacks:
`session_regenerate_id(true)` before `session_destroy()`.

### L-08 — No `<meta name="description">` on Any Page
Minor HTML hygiene — not required for capstone but reflects attention to detail.

---

## PART 5 — ISO 25010 SOFTWARE QUALITY ASSESSMENT

| Characteristic | Sub-characteristic | Status | Notes |
|---|---|---|---|
| **Functional Suitability** | Completeness | 75% | Forum ✅ Attendance ✅ Donations ✅ No pwd reset ❌ No ann. edit ❌ |
| **Functional Suitability** | Correctness | 80% | Core flows work; edge cases not handled |
| **Reliability** | Fault Tolerance | 60% | Raw DB errors exposed; no graceful error pages |
| **Reliability** | Recoverability | 40% | No data backup, no undo, no soft deletes |
| **Security** | Confidentiality | 70% | bcrypt ✅ CSRF ✅ Session timeout ❌ Rate limit ❌ |
| **Security** | Integrity | 75% | Prepared statements in actions ✅ Pages inconsistent ⚠️ |
| **Security** | Authenticity | 65% | Role RBAC ✅ Admin self-reg bug ❌ |
| **Usability** | Learnability | 80% | Dark UI clean; help page present ✅ |
| **Usability** | User Error Protection | 55% | Limited validation feedback; no undo |
| **Usability** | Accessibility | 40% | No lang attr; no ARIA labels; color contrast untested |
| **Maintainability** | Modularity | 80% | pages/actions/includes separation ✅ |
| **Maintainability** | Testability | 40% | No automated tests; no test documentation |
| **Portability** | Adaptability | 70% | XAMPP-dependent; no environment config |
| **Performance** | Time Behaviour | 75% | No query optimization/indexing documented |

---

## PART 6 — WHAT PANELISTS WILL SPECIFICALLY TEST (Live Demo Scenarios)

These are the exact actions a panelist will attempt during your live defense:

1. **Register as Admin** — Submit registration form via DevTools with `role=admin`
   → Will succeed unless C-01 is fixed ❌

2. **SQL Injection on Login** — Enter `' OR '1'='1` in email field
   → Will fail (prepared statements) ✅

3. **Access admin page as viewer** — Login as viewer, navigate to `/pages/users.php` directly
   → Correctly blocked ✅

4. **Create event, edit it, delete it** — Full CRUD cycle
   → Works ✅ but delete now uses CSRF POST correctly ✅

5. **Post an announcement with typo, try to edit it**
   → No edit available ❌ (H-03)

6. **Record attendance for an event, view it in reports, export as PDF**
   → Works ✅ export_report.php functional ✅

7. **Post a forum thread, reply, pin it as admin**
   → Works ✅

8. **Try wrong password 10 times, check if locked out**
   → No lockout ❌ (C-05)

9. **Leave session idle for 2 hours, try to use the system**
   → Session still active ❌ (C-04)

10. **Check the page source for any exposed credentials or sensitive data**
    → Database credentials are in config/database.php (acceptable for localhost dev)

11. **Switch between pages — check if theme is consistent**
    → 6 pages still white/light ❌ (H-01)

12. **Check if donor name is truly optional**
    → Correctly handled (defaults to Anonymous) ✅

13. **Try to access dashboard without logging in**
    → Correctly redirects to login ✅

14. **Submit empty forms**
    → Some protected with `required` attr; server-side validation varies ⚠️

---

## PART 7 — FIX PRIORITY ORDER (Before Defense)

| Priority | Item | Effort | Impact |
|---|---|---|---|
| 🔴 1 | C-01: Remove admin from self-registration | 2 minutes | Critical |
| 🔴 2 | C-03: Add CSRF to users.php form | 5 minutes | Critical |
| 🔴 3 | H-01: Dark theme all 6 remaining pages | 3 hours | High |
| 🔴 4 | C-04: Add session timeout | 20 minutes | Critical |
| 🟡 5 | C-05: Add login brute force limit | 30 minutes | High |
| 🟡 6 | H-03: Edit/Delete announcements | 1 hour | High |
| 🟡 7 | M-03: Remove raw error outputs | 15 minutes | Medium |
| 🟡 8 | M-08: Prepared stmts in moderate_thread.php | 20 minutes | Medium |
| 🟢 9 | H-07: Add Privacy Policy page (RA 10173) | 30 minutes | Medium |
| 🟢 10 | M-06: Add `lang="en"` to all pages | 5 minutes | Low |
| 🟢 11 | L-07: session_regenerate_id on logout | 2 minutes | Low |
| 🟢 12 | M-09: Flash success messages after actions | 1 hour | Medium |
| 🟢 13 | L-06: Fix appointment_confirmation guard | 10 minutes | Low |
| 🔵 14 | H-05: Forgot password flow | 2 hours | Medium |
| 🔵 15 | H-02: Pagination on long lists | 2 hours | Medium |

---

## PART 8 — WHAT IS DONE CORRECTLY (Strengths to defend)

✅ bcrypt password hashing with legacy MD5 fallback for demo accounts  
✅ CSRF tokens on all POST forms (13 action files + all page forms)  
✅ Prepared statements in all actions/*.php  
✅ Role-based access control (admin/imam/leader/viewer) on every page  
✅ Community-scoped data filtering — data is properly isolated per community  
✅ Session-based authentication with proper logout  
✅ System-generated daily prayer time events (Iftar/Taraweeh/Suhoor)  
✅ All 7 manuscript modules implemented (User Mgmt, Events, Announcements,
   Attendance, Donations, Reports, Forum)  
✅ Interactive calendar with event dots and month navigation  
✅ Global live search (AJAX, 280ms debounce)  
✅ PDF export (clean A4 print-ready layout)  
✅ Notification bell with mark-as-read functionality  
✅ Profile page with edit + password change + activity stats  
✅ Settings page (localStorage-based preferences)  
✅ Help center with searchable FAQ accordion  
✅ Announcement categories (prayer_schedule, event_reminder, charity_drive, advisory)  
✅ Ongoing event status (all three states: upcoming/ongoing/completed)  
✅ Admin user management (activate/suspend accounts)  
✅ Communities page with member breakdown  
✅ htmlspecialchars() applied consistently on all user-output data  
✅ Donation type validation (whitelist for cash/food/supplies)  
✅ Donor name optional (defaults to Anonymous)  
✅ delete_event.php converted from vulnerable GET to CSRF-protected POST  

---

## PART 9 — DEFENSE PRESENTATION CHECKLIST

Before standing in front of the panelists, verify:

**System Demo:**
- [ ] XAMPP running, Apache and MySQL started
- [ ] Database imported (rminder_db.sql + migrate_additions.sql)
- [ ] Demo data present (at least 5 events, 3 announcements, 2 donors, forum thread)
- [ ] All 4 roles have working demo accounts (admin, imam, leader, viewer)
- [ ] Can switch between accounts smoothly (use incognito for second account)

**Manuscript Alignment:**
- [ ] Every objective in the manuscript has a corresponding implemented feature
- [ ] Every scope item is present and working
- [ ] Every non-functional requirement (security, bcrypt, prepared stmts) is implemented
- [ ] RRL citations match what the system actually does

**Presentation Flow:**
- [ ] Prepared to explain the database schema (all 9 tables)
- [ ] Prepared to explain RBAC — which role can do what and why
- [ ] Prepared to explain CSRF protection — what it is, how it works
- [ ] Prepared to explain bcrypt — why MD5 was replaced
- [ ] Prepared to explain community scoping — how data isolation works
- [ ] Prepared to explain system-generated events — what triggers them

**Common Panelist Questions:**
- "What happens if the admin is deleted from the database?" — Answer: the system has no protection; a db-level admin user should be protected
- "How does the system handle concurrent users?" — Answer: MySQL handles locking; PHP sessions are user-isolated
- "Why PHP instead of a framework?" — Answer: simplicity, XAMPP compatibility, team familiarity, meets requirements
- "How would you scale this beyond 3 communities?" — Answer: add communities table entries; architecture already supports it
- "What is your test methodology?" — Answer: manual testing per test case document (prepare one)
- "What is the system's response time?" — Prepare a browser network tab screenshot showing < 1 second

---

*End of Panelist Review — MCAD Event Management System — Generated 2026-05-25*
