-- =====================================================
-- Rusan: Create Activities Table
-- Run this in phpMyAdmin / MySQL console on the live DB
-- =====================================================

CREATE TABLE IF NOT EXISTS `activities` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`       BIGINT UNSIGNED NULL,
    `cid`           VARCHAR(255) NULL,
    `type`          VARCHAR(80) NOT NULL,
    `module`        VARCHAR(50) NULL,
    `subject_id`    BIGINT UNSIGNED NULL,
    `subject_label` VARCHAR(255) NULL,
    `description`   TEXT NULL,
    `value`         VARCHAR(255) NULL,
    `ip_address`    VARCHAR(45) NULL,
    `user_agent`    VARCHAR(255) NULL,
    `created_at`    TIMESTAMP NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP NULL DEFAULT NULL,
    INDEX `activities_user_id_index` (`user_id`),
    INDEX `activities_cid_index` (`cid`),
    CONSTRAINT `activities_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Also insert a row in the migrations table so
-- php artisan migrate:status knows it's been run.
-- =====================================================
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_04_10_090000_create_activities_table', 99);
