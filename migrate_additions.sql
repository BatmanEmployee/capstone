-- ============================================================
-- MCAD System — Migration: Add Missing Tables & Columns
-- Run this ONCE against rminderdb after the base rminder_db.sql
-- ============================================================

-- 1. Add status column to users (for activate/suspend)
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `status` ENUM('active','suspended','pending') NOT NULL DEFAULT 'active';

-- 2. Add category column to announcements
ALTER TABLE `announcements`
  ADD COLUMN IF NOT EXISTS `category` ENUM('prayer_schedule','event_reminder','charity_drive','community_advisory') NOT NULL DEFAULT 'event_reminder';

-- 3. Add 'ongoing' to events status enum
ALTER TABLE `events`
  MODIFY `status` ENUM('upcoming','ongoing','completed') NOT NULL DEFAULT 'upcoming';

-- 4. Attendance tracking table
CREATE TABLE IF NOT EXISTS `attendance` (
  `id`         INT(11)       NOT NULL AUTO_INCREMENT,
  `event_id`   INT(11)       NOT NULL,
  `name`       VARCHAR(255)  NOT NULL,
  `status`     ENUM('present','absent') NOT NULL DEFAULT 'present',
  `recorded_by` INT(11)      DEFAULT NULL,
  `community_id` INT(11)     DEFAULT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Community Forum — threads table
CREATE TABLE IF NOT EXISTS `forum_threads` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(255)  NOT NULL,
  `body`         TEXT          NOT NULL,
  `user_id`      INT(11)       NOT NULL,
  `community_id` INT(11)       DEFAULT NULL,
  `is_pinned`    TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Community Forum — replies table
CREATE TABLE IF NOT EXISTS `forum_replies` (
  `id`        INT(11)  NOT NULL AUTO_INCREMENT,
  `thread_id` INT(11)  NOT NULL,
  `user_id`   INT(11)  NOT NULL,
  `body`      TEXT     NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Clean the 143 empty test announcements, keep only real ones
DELETE FROM `announcements` WHERE `title` = '' AND `message` = '';

-- 8. Insert realistic sample announcements
INSERT INTO `announcements` (`title`, `message`, `user_id`, `community_id`, `category`, `created_at`) VALUES
('Ramadan Mubarak!', 'Assalamu Alaikum. The Ramadan season has officially begun. May Allah accept our fasts and prayers. Please check the event schedule for Iftar and Taraweeh timings.', 1, 1, 'community_advisory', NOW()),
('Iftar Program — Lagao Community', 'The Iftar gathering for Lagao (1st & 3rd) community will be held at the Barangay Hall every Friday during Ramadan. All community members are welcome.', 1, 1, 'event_reminder', NOW()),
('Zakat al-Fitr Reminder', 'Please submit your Zakat al-Fitr donations to the Community Leader or Imam before Eid prayer. Accepted: cash (P150/person), rice, or dry goods.', 7, 2, 'charity_drive', NOW()),
('Taraweeh Prayer Schedule', 'Taraweeh prayers will be held at the mosque every night at 7:30 PM during Ramadan. Congregation is encouraged. Please arrive on time.', 7, 2, 'prayer_schedule', NOW()),
('Community Advisory: Bula Barangay Hall', 'The Bula Barangay Hall will serve as the distribution center for food packs this Ramadan. Distribution starts at 4:00 PM on Saturdays.', 1, 2, 'community_advisory', NOW());

-- 9. Sample forum threads
INSERT INTO `forum_threads` (`title`, `body`, `user_id`, `community_id`, `is_pinned`, `created_at`) VALUES
('Welcome to the Community Forum!', 'This is the official community discussion board for MCAD. Use this space to ask questions, share updates, and coordinate Ramadan activities.', 1, NULL, 1, NOW()),
('Suggestions for Iftar venue — Lagao', 'I would like to suggest we move the Friday Iftar gathering to the covered court for better space. What do you think?', 5, 1, 0, NOW()),
('Volunteers needed for Saturday distribution', 'We need 10 volunteers for the food pack distribution this Saturday at Bula Barangay Hall. Please reply if you are available.', 7, 2, 0, NOW());

INSERT INTO `forum_replies` (`thread_id`, `user_id`, `body`, `created_at`) VALUES
(1, 7, 'Jazakallahu khairan for setting this up. This will be very helpful for coordination.', NOW()),
(3, 5, 'I can volunteer this Saturday. What time should we report?', NOW());

-- 10. Appointments table (public booking, no login required)
CREATE TABLE IF NOT EXISTS `appointments` (
  `id`            INT(11)       NOT NULL AUTO_INCREMENT,
  `reference_no`  VARCHAR(30)   NOT NULL,
  `full_name`     VARCHAR(255)  NOT NULL,
  `contact`       VARCHAR(20)   NOT NULL,
  `email`         VARCHAR(255)  DEFAULT NULL,
  `service_type`  ENUM(
                    'islamic_marriage',
                    'halal_certification',
                    'burial_assistance',
                    'scholarship'
                  ) NOT NULL,
  `preferred_date` DATE         NOT NULL,
  `preferred_time` TIME         NOT NULL,
  `purpose`        TEXT         DEFAULT NULL,
  `status`         ENUM('pending','approved','rejected','completed')
                               NOT NULL DEFAULT 'pending',
  `admin_remarks`  TEXT         DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_no` (`reference_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 11. Sample appointments for demo
INSERT INTO `appointments`
  (`reference_no`, `full_name`, `contact`, `email`, `service_type`, `preferred_date`, `preferred_time`, `purpose`, `status`)
VALUES
  ('MCAD-20260601-001', 'Aminah Sali Pendatun', '09171234567', 'aminah@email.com',
   'islamic_marriage', '2026-06-10', '09:00:00',
   'We would like to process our Islamic marriage certificate.', 'pending'),
  ('MCAD-20260601-002', 'Hadji Norodin Macapado', '09281234567', NULL,
   'burial_assistance', '2026-06-05', '10:00:00',
   'Requesting assistance for the burial of my father who passed away yesterday.', 'approved'),
  ('MCAD-20260601-003', 'Fatima Bai Abubakar', '09351234567', 'fatima@email.com',
   'scholarship', '2026-06-15', '13:00:00',
   'Applying for the Muslim youth scholarship program for college.', 'pending');
