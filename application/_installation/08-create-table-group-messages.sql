CREATE TABLE IF NOT EXISTS `group_messages` (
    `message_id`   INT(11)    NOT NULL AUTO_INCREMENT,
    `group_id`     INT(11)    NOT NULL,
    `sender_id`    INT(11)    NOT NULL,
    `message_text` TEXT       NOT NULL,
    `is_read`      TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`message_id`),
    KEY `idx_group_id`  (`group_id`),
    KEY `idx_sender_id` (`sender_id`)
);
