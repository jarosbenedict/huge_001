CREATE TABLE IF NOT EXISTS `group_members` (
    `group_id`  INT(11)  NOT NULL,
    `user_id`   INT(11)  NOT NULL,
    `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`group_id`, `user_id`),
    KEY `idx_user_id` (`user_id`)
);
