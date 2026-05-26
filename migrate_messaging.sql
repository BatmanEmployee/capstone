-- ═══════════════════════════════════════════════════════════════
-- MESSAGING SYSTEM - DM + Group Chat with Role-Based Access
-- Run this AFTER migrate_additions.sql
-- ═══════════════════════════════════════════════════════════════

-- 1. Direct messages (1-on-1 between users)
CREATE TABLE IF NOT EXISTS `messages` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `sender_id`   INT(11) NOT NULL,
  `receiver_id` INT(11) NOT NULL,
  `message`     TEXT NOT NULL,
  `is_read`     TINYINT(1) DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_receiver` (`receiver_id`),
  KEY `idx_conversation` (`sender_id`, `receiver_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Chat groups (community-scoped or open)
CREATE TABLE IF NOT EXISTS `chat_groups` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `community_id` INT(11) DEFAULT NULL,
  `created_by`   INT(11) NOT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_community` (`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Group members (who is part of which group)
CREATE TABLE IF NOT EXISTS `chat_group_members` (
  `id`        INT(11) NOT NULL AUTO_INCREMENT,
  `group_id`  INT(11) NOT NULL,
  `user_id`   INT(11) NOT NULL,
  `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_member` (`group_id`, `user_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Group messages
CREATE TABLE IF NOT EXISTS `chat_group_messages` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `group_id`   INT(11) NOT NULL,
  `sender_id`  INT(11) NOT NULL,
  `message`    TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_group_time` (`group_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─────────────────────────────────────────────────
-- Seed: Create a default group for each community
-- ─────────────────────────────────────────────────
INSERT INTO `chat_groups` (`name`, `description`, `community_id`, `created_by`) VALUES
('Lagao Community Chat', 'General discussion for Lagao community members', 1, 1),
('Bula Community Chat',  'General discussion for Bula community members',  2, 1),
('Uhaw Community Chat',  'General discussion for Uhaw community members',  3, 1),
('MCAD Staff',           'Chat group for MCAD administrators and imams',   NULL, 1);

-- Auto-enroll all existing users into their community group
INSERT INTO `chat_group_members` (`group_id`, `user_id`)
SELECT cg.id, u.id
FROM `users` u
JOIN `chat_groups` cg ON cg.community_id = u.community_id
WHERE u.community_id IS NOT NULL;

-- Enroll all admins and imams into the MCAD Staff group
INSERT INTO `chat_group_members` (`group_id`, `user_id`)
SELECT 4, id FROM `users` WHERE role IN ('admin', 'imam');
