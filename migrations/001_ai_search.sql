CREATE TABLE IF NOT EXISTS `item_index_jobs` (
    `job_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `item_id` INT NOT NULL,
    `operation` ENUM('upsert', 'delete') NOT NULL DEFAULT 'upsert',
    `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `attempts` INT NOT NULL DEFAULT 0,
    `last_error` TEXT,
    `available_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_index_jobs_work` (`status`, `available_at`),
    INDEX `idx_index_jobs_item` (`item_id`)
);

CREATE TABLE IF NOT EXISTS `item_index_state` (
    `item_id` INT PRIMARY KEY,
    `content_checksum` CHAR(64) NOT NULL,
    `embedding_model` VARCHAR(100) NOT NULL,
    `indexed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `item_match_scores` (
    `source_item_id` INT NOT NULL,
    `candidate_item_id` INT NOT NULL,
    `semantic_score` DECIMAL(7,6) NOT NULL,
    `combined_score` DECIMAL(7,6) NOT NULL,
    `explanation` VARCHAR(255) NOT NULL,
    `notified_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`source_item_id`, `candidate_item_id`),
    FOREIGN KEY (`source_item_id`) REFERENCES `items`(`item_id`) ON DELETE CASCADE,
    FOREIGN KEY (`candidate_item_id`) REFERENCES `items`(`item_id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `assistant_rate_limits` (
    `user_id` INT NOT NULL,
    `window_started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `request_count` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);

ALTER TABLE `items`
    ADD INDEX `idx_items_matching` (`status`, `type`, `category_id`, `location_id`, `item_date`);
