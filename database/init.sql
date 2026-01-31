-- Waste2Worth Database Initialization Script

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL DEFAULT (UUID()),
  `role` ENUM('collector', 'client', 'admin') NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  INDEX `role_status` (`role`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for collector_profiles
-- ----------------------------
CREATE TABLE IF NOT EXISTS `collector_profiles` (
  `collector_id` INT NOT NULL,
  `display_name` VARCHAR(120) DEFAULT NULL,
  `bio` TEXT,
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `service_radius_km` DECIMAL(5,2) DEFAULT '5.00',
  `waste_types` JSON DEFAULT NULL,
  `availability_status` ENUM('available', 'busy', 'offline') DEFAULT 'offline',
  `verification_level` ENUM('unverified', 'pending', 'verified') DEFAULT 'unverified',
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `id_document_path` VARCHAR(255) DEFAULT NULL,
  `rating_avg` DECIMAL(3,2) DEFAULT '0.00',
  `rating_count` INT DEFAULT '0',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`collector_id`),
  CONSTRAINT `fk_collector_user` FOREIGN KEY (`collector_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  INDEX `availability_status` (`availability_status`),
  INDEX `location` (`latitude`, `longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for client_profiles
-- ----------------------------
CREATE TABLE IF NOT EXISTS `client_profiles` (
  `client_id` INT NOT NULL,
  `organization_name` VARCHAR(150) DEFAULT NULL,
  `address_line` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(120) DEFAULT NULL,
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `preferred_waste_types` JSON DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_id`),
  CONSTRAINT `fk_client_user` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  INDEX `location` (`latitude`, `longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for service_requests
-- ----------------------------
CREATE TABLE IF NOT EXISTS `service_requests` (
  `request_id` BIGINT NOT NULL AUTO_INCREMENT,
  `client_id` INT NOT NULL,
  `collector_id` INT DEFAULT NULL,
  `requested_waste_types` JSON DEFAULT NULL,
  `description` TEXT,
  `pickup_address` VARCHAR(255) DEFAULT NULL,
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `desired_pickup_time` DATETIME DEFAULT NULL,
  `status` ENUM('pending', 'accepted', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
  `client_confirmed_at` DATETIME DEFAULT NULL,
  `collector_confirmed_at` DATETIME DEFAULT NULL,
  `payment_status` ENUM('unpaid', 'pending', 'paid', 'refunded') DEFAULT 'unpaid',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  CONSTRAINT `fk_request_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_request_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`user_id`),
  INDEX `client_status` (`client_id`, `status`),
  INDEX `collector_status` (`collector_id`, `status`),
  INDEX `location` (`latitude`, `longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for completed_pickups
-- ----------------------------
CREATE TABLE IF NOT EXISTS `completed_pickups` (
  `pickup_id` BIGINT NOT NULL AUTO_INCREMENT,
  `request_id` BIGINT NOT NULL,
  `collector_id` INT NOT NULL,
  `client_id` INT NOT NULL,
  `verified_weight_kg` DECIMAL(8,2) DEFAULT NULL,
  `waste_category_breakdown` JSON DEFAULT NULL,
  `collector_verification_photo` VARCHAR(255) DEFAULT NULL,
  `client_verification_photo` VARCHAR(255) DEFAULT NULL,
  `collector_confirmed_at` DATETIME DEFAULT NULL,
  `client_confirmed_at` DATETIME DEFAULT NULL,
  `verification_status` ENUM('pending', 'verified', 'disputed') DEFAULT 'pending',
  `co2_offset_kg` DECIMAL(10,2) DEFAULT NULL,
  `landfill_diverted_kg` DECIMAL(10,2) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pickup_id`),
  UNIQUE KEY `request_id` (`request_id`),
  CONSTRAINT `fk_pickup_request` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pickup_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_pickup_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  INDEX `collector_id` (`collector_id`),
  INDEX `client_id` (`client_id`),
  INDEX `verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for payments
-- ----------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` BIGINT NOT NULL AUTO_INCREMENT,
  `request_id` BIGINT NOT NULL,
  `payer_id` INT NOT NULL,
  `collector_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` CHAR(3) DEFAULT 'USD',
  `payment_method` ENUM('cash', 'card', 'wallet', 'mobile_money') DEFAULT NULL,
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('initiated', 'successful', 'failed', 'refunded') DEFAULT 'initiated',
  `processed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `transaction_ref` (`transaction_ref`),
  CONSTRAINT `fk_payment_request` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`),
  CONSTRAINT `fk_payment_payer` FOREIGN KEY (`payer_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_payment_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`user_id`),
  INDEX `request_id` (`request_id`),
  INDEX `payer_id` (`payer_id`),
  INDEX `status_processed` (`status`, `processed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `type` ENUM('request', 'confirmation', 'payment', 'reward', 'system') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT '0',
  `metadata` JSON DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  INDEX `user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sms_logs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `sms_logs` (
  `sms_id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('queued', 'sent', 'failed') DEFAULT 'queued',
  `provider_response` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sms_id`),
  CONSTRAINT `fk_sms_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  INDEX `user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for reviews
-- ----------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` BIGINT NOT NULL AUTO_INCREMENT,
  `request_id` BIGINT NOT NULL,
  `client_id` INT NOT NULL,
  `collector_id` INT NOT NULL,
  `rating` TINYINT DEFAULT NULL,
  `comment` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  CONSTRAINT `fk_review_request` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`),
  CONSTRAINT `fk_review_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_review_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`user_id`),
  INDEX `collector_rating` (`collector_id`, `rating`),
  INDEX `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for reward_ledgers
-- ----------------------------
CREATE TABLE IF NOT EXISTS `reward_ledgers` (
  `reward_id` BIGINT NOT NULL AUTO_INCREMENT,
  `collector_id` INT NOT NULL,
  `period_month` CHAR(7) NOT NULL,
  `verified_pickups` INT DEFAULT '0',
  `bonus_amount` DECIMAL(10,2) DEFAULT '0.00',
  `status` ENUM('pending', 'approved', 'paid') DEFAULT 'pending',
  `calculated_at` DATETIME DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`reward_id`),
  UNIQUE KEY `collector_period` (`collector_id`, `period_month`),
  CONSTRAINT `fk_reward_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  INDEX `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for analytics_metrics
-- ----------------------------
CREATE TABLE IF NOT EXISTS `analytics_metrics` (
  `metric_id` BIGINT NOT NULL AUTO_INCREMENT,
  `metric_date` DATE NOT NULL,
  `collector_id` INT DEFAULT NULL,
  `city` VARCHAR(120) DEFAULT NULL,
  `metric_type` ENUM('waste_volume', 'co2_offset', 'requests', 'completed', 'revenue') NOT NULL,
  `value` DECIMAL(18,4) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`metric_id`),
  CONSTRAINT `fk_analytics_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  INDEX `date_type` (`metric_date`, `metric_type`),
  INDEX `collector_date` (`collector_id`, `metric_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for audit_logs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `audit_id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(120) NOT NULL,
  `entity` VARCHAR(120) DEFAULT NULL,
  `entity_id` VARCHAR(50) DEFAULT NULL,
  `request_ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  INDEX `user_action` (`user_id`, `action`),
  INDEX `entity_search` (`entity`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for session_tokens
-- ----------------------------
CREATE TABLE IF NOT EXISTS `session_tokens` (
  `session_id` CHAR(64) NOT NULL,
  `user_id` INT NOT NULL,
  `csrf_token` CHAR(64) DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  INDEX `user_expires` (`user_id`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for file_uploads
-- ----------------------------
CREATE TABLE IF NOT EXISTS `file_uploads` (
  `file_id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `entity` VARCHAR(100) DEFAULT NULL,
  `entity_id` BIGINT DEFAULT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(120) DEFAULT NULL,
  `size_kb` INT DEFAULT NULL,
  `status` ENUM('active', 'archived', 'deleted') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_id`),
  CONSTRAINT `fk_file_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  INDEX `user_entity` (`user_id`, `entity`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
