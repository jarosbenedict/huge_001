CREATE TABLE IF NOT EXISTS `groups` (
    `group_id`   INT(11)      NOT NULL AUTO_INCREMENT,
    `group_name` VARCHAR(100) NOT NULL,
    `created_by` INT(11)      NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`group_id`),
    KEY `idx_created_by` (`created_by`)
);
