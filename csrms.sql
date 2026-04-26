-- ============================================================
-- Complaint & Service Request Management System (CSRMS)
-- Database: csrms
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `csrms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `csrms`;

-- --------------------------------------------------------
-- Table: users  (roles: admin | user | technician)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL,
  `password`   VARCHAR(100) NOT NULL,
  `role`       ENUM('admin','user','technician') NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id`   INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: complaints
-- --------------------------------------------------------
CREATE TABLE `complaints` (
  `id`            INT(11) NOT NULL AUTO_INCREMENT,
  `user_email`    VARCHAR(100) NOT NULL,
  `category_id`   INT(11) NOT NULL,
  `title`         VARCHAR(200) NOT NULL,
  `description`   TEXT NOT NULL,
  `image_path`    VARCHAR(255) DEFAULT NULL,
  `priority`      ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status`        ENUM('Open','In Progress','On Hold','Resolved','Closed','Escalated','Reopened') DEFAULT 'Open',
  `assigned_to`   VARCHAR(100) DEFAULT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at`    TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at`   TIMESTAMP NULL DEFAULT NULL,
  `is_escalated`  TINYINT(1) DEFAULT 0,
  `escalated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: service_notes  (technician updates)
-- --------------------------------------------------------
CREATE TABLE `service_notes` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` INT(11) NOT NULL,
  `tech_email`   VARCHAR(100) NOT NULL,
  `note`         TEXT NOT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `complaint_id` (`complaint_id`),
  CONSTRAINT `fk_note_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default users
INSERT INTO `users` (`name`, `email`, `password`, `role`, `department`, `phone`) VALUES
('Admin',          'admin@csrms.com',      'admin123',  'admin',      'Management',  '0300-0000001'),
('Ali Hassan',     'ali@csrms.com',        'user123',   'user',       'Engineering', '0300-1111111'),
('Sara Khan',      'sara@csrms.com',       'user123',   'user',       'HR',          '0300-2222222'),
('Usman Tech',     'usman@csrms.com',      'tech123',   'technician', 'Maintenance', '0300-3333333'),
('Bilal Tech',     'bilal@csrms.com',      'tech123',   'technician', 'IT Support',  '0300-4444444');

-- Default categories
INSERT INTO `categories` (`name`) VALUES
('Electrical'),
('Plumbing'),
('IT / Network'),
('HVAC / AC'),
('Furniture / Fixtures'),
('Housekeeping'),
('Security'),
('Other');

-- Sample complaints
INSERT INTO `complaints`
  (`user_email`, `category_id`, `title`, `description`, `priority`, `status`, `assigned_to`, `resolved_at`, `is_escalated`)
VALUES
('ali@csrms.com',  3, 'Internet not working in Room 204', 'Wi-Fi signal is very weak and drops every few minutes.',  'High',     'In Progress', 'usman@csrms.com', NULL,  0),
('sara@csrms.com', 1, 'Flickering lights in corridor',    'The corridor lights on Floor 2 flicker at night.',         'Medium',   'Open',        NULL,              NULL,  0),
('ali@csrms.com',  2, 'Leaking tap in washroom',          'Cold water tap in washroom B is dripping continuously.',   'Low',      'Resolved',    'bilal@csrms.com', NOW(), 0),
('sara@csrms.com', 4, 'AC not cooling in office',         'AC unit in the HR office stopped cooling after noon.',     'Critical', 'Escalated',   'usman@csrms.com', NULL,  1);

-- Sample service notes
INSERT INTO `service_notes` (`complaint_id`, `tech_email`, `note`) VALUES
(1, 'usman@csrms.com', 'Checked router on Floor 2 — firmware update required. Will complete by EOD.'),
(3, 'bilal@csrms.com', 'Replaced the washroom tap washer. Issue resolved.');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
