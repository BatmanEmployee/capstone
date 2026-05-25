# RMinder — Web-Based Event Management System with Community Forum

**Muslim Concerns and Affairs Division (MCAD), City Mayor's Office, General Santos City**

RMinder is a professional capstone web application built to streamline event scheduling, community announcements, donation tracking, attendance logging, and public service appointments for the MCAD community in General Santos City.

---

## 🌟 Key Features

* **User Management & RBAC:** Multi-role support for Admin, Imam, Community Leader, and Viewer with active suspension guards.
* **Event Scheduling:** Visual interactive calendar and upcoming list view, with daily automatic Islamic prayer schedule seeder (Taraweeh, Suhoor, Iftar).
* **Community Forum:** Scoped community discussions with pinning and administrative moderating functions.
* **Donations & Distributions:** Tracking system for cash, food, and supplies, linked directly to distribution history loggers.
* **Appointments Booking:** Public portal enabling citizens to book Islamic Marriage certification, Halal assistance, Burial assistance, and Scholarship aid appointments.
* **CSRF & prepared statements:** Standard security practices deployed globally across all page elements.
* **Printable Reports:** Beautiful, dynamic, print-ready reports for events, attendance lists, and resource history.

---

## 🚀 Tech Stack

* **Frontend:** Vanilla HTML5, CSS3 (Modern Dark Grid System), JavaScript
* **Backend:** PHP 8.2+
* **Database:** MariaDB / MySQL (using prepared SQL statements)
* **Environment:** XAMPP (Apache + MySQL)

---

## 🔧 Installation & Local Setup

1. Clone this repository or copy the folder into your XAMPP installation's `htdocs/` folder:
   ```bash
   C:\xampp\htdocs\mcad\WEBBASEMANAGEMENTSYSTEM\
   ```
2. Start **Apache** and **MySQL** services in your XAMPP Control Panel.
3. Import the database:
   * Open [phpMyAdmin](http://localhost/phpmyadmin/).
   * Create a new database named `rminderdb`.
   * Import the base file `rminder_db.sql`.
   * Import `migrate_additions.sql` to add the latest attendance, forum, and appointment modules.
4. Open the website in your browser:
   * Public Portal: `http://localhost/mcad/WEBBASEMANAGEMENTSYSTEM/`
   * Secure Sign In: `http://localhost/mcad/WEBBASEMANAGEMENTSYSTEM/pages/login.php`

---

## 🔐 Credentials (Demo)

* **Administrator:** `admin@email.com` / `admin`
* **Imam:** `alBallsani@email.com` / `123`
