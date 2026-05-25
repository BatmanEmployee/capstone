# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**RMinder** — Web-Based Event Management System with Community Forum for the City Mayor's Office Muslim Concerns and Affairs Division (MCAD), General Santos City. A capstone project built by Ashley Jan M. Aso et al. at STI College General Santos.

## Local Development Setup

This project runs on **XAMPP** (Apache + MySQL). No build step, no package manager, no test suite.

1. Place the project folder inside `htdocs/` in your XAMPP installation
2. Start Apache and MySQL in the XAMPP Control Panel
3. Import the database: open phpMyAdmin → create database `rminderdb` → import `rminder_db.sql`
4. Then import `migrate_additions.sql` on top of it (adds attendance, forum, and category columns)
5. Access the app at `http://localhost/WEBBASEMANAGEMENTSYSTEM/`

**Default demo credentials (MD5-hashed in DB, bcrypt accepted for new registrations):**
- Admin: `admin@email.com` / `admin`
- Imam: `alBallsani@email.com` / `123`

## Architecture

### Request Flow
Every page follows the same pattern — no router, no MVC:

```
Browser → pages/*.php (view + data fetch) → form POST → actions/*.php (write + redirect)
```

`pages/*.php` files are the entry points. They include `config/database.php` for `$conn`, start sessions, do auth checks, run queries, then render HTML inline. All writes go through `actions/*.php` which redirect back after completion.

### Key Conventions

**Auth guard** — every protected page starts with:
```php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
```
Actions use `../` relative paths; pages use `../` to reach actions and config.

**Community scoping** — almost every query filters by `community_id`. A user's community_id comes from `$_SESSION['community_id']` (set at login). System events have `community_id = NULL` and are visible to all.

**Role-based access** — four roles: `admin`, `imam`, `leader`, `viewer`. Role is stored in `$_SESSION['role']`. UI elements and data are conditionally shown based on role. `admin` sees all communities; others see only their own.

**Prepared statements** — all `actions/*.php` files use `$conn->prepare()` / `bind_param()`. Do not use string interpolation in SQL. The `pages/*.php` files still use direct queries in some places (known tech debt).

**Password hashing** — `register_action.php` uses `password_hash($raw, PASSWORD_BCRYPT)`. `login_action.php` accepts both bcrypt (new accounts) and MD5 (legacy demo accounts) via a dual-check fallback.

### Shared Templates

Every authenticated page includes these three in order:
```php
include "../includes/header.php";   // <head>, fixed top bar, opens .layout div
include "../includes/sidebar.php";  // fixed 260px left nav (role-aware menu)
// ... page HTML ...
include "../includes/footer.php";   // closes .layout div
```
Pages must use `margin-left: 260px; padding: 90px 30px 30px;` on their main container to clear the sidebar and fixed header.

### Database Schema (rminderdb)

| Table | Purpose |
|---|---|
| `users` | id, name, email, password, role, community_id, status |
| `communities` | id, name — three records: Lagao, Bula, Uhaw |
| `events` | id, title, description, event_date, event_time, venue, status (upcoming/ongoing/completed), event_type (personal/system), visibility (personal/community), community_id, user_id |
| `announcements` | id, title, message, category (prayer_schedule/event_reminder/charity_drive/community_advisory), user_id, community_id |
| `donations` | id, donor_name, donation_type (cash/food/supplies), amount, quantity, remarks, community_id, user_id |
| `distributions` | id, donation_id, beneficiary, item_name, quantity, distributed_at, community_id, user_id |
| `attendance` | id, event_id, name, status (present/absent), recorded_by, community_id |
| `forum_threads` | id, title, body, user_id, community_id, is_pinned |
| `forum_replies` | id, thread_id, user_id, body |

### Auto-generated System Events

`functions/system_events.php` → `generateSystemEvents($conn)` is called on `dashboard.php` and `events.php` load. It inserts three daily prayer-time events (Iftar 18:00, Taraweeh 19:30, Suhoor 04:30) if they don't already exist for today. These have `event_type = 'system'` and `community_id = NULL`.

## File Map

```
index.php                    Landing page (login / register links)
pages/
  login.php / register.php   Auth pages — post to actions/
  dashboard.php              Role-specific stats + activity feed
  events.php                 Event list + mini calendar widget + quick-create form
  edit_event.php             Edit form for a single event
  announcements.php          Category-tagged announcements + create form (admin/imam)
  donations.php              Donation + distribution forms and summary
  attendance.php             Per-event attendance recording and report
  forum.php                  Thread list + new thread form
  forum_thread.php           Single thread view + reply form
  users.php                  Admin-only: activate/suspend user accounts
  reports.php                Printable: events + attendance + donation tables
actions/
  login_action.php           Session creation, bcrypt+MD5 verify, status check
  register_action.php        bcrypt hash, email validation, duplicate check
  add_event_action.php       Prepared statement insert into events
  update_event.php           Prepared statement update events
  delete_event.php           DELETE by id
  add_announcement_action.php  Role-guarded (admin/imam), inserts with category
  add_donation.php           Prepared insert, donor_name defaults to 'Anonymous'
  add_distribution.php       Prepared insert into distributions
  add_attendance.php         Prepared insert into attendance
  add_thread.php             Prepared insert into forum_threads
  add_reply.php              Prepared insert into forum_replies
  moderate_thread.php        Admin/imam only: pin toggle or delete thread+replies
  logout.php                 session_destroy + redirect
config/
  database.php               MySQLi connection → $conn (host: localhost, db: rminderdb)
functions/
  system_events.php          generateSystemEvents($conn) — daily prayer event seeder
includes/
  header.php                 HTML head, fixed top bar, notification popup, JS toggles
  sidebar.php                Role-based nav menu (isActive helper function)
  footer.php                 Closes .layout div
assets/
  ramadan.png                Hero image used on landing and login pages
```
