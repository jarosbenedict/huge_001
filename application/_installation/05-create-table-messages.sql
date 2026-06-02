CREATE TABLE IF NOT EXISTS `messages` (
    `message_id`   INT(11)      NOT NULL AUTO_INCREMENT,
    `sender_id`    INT(11)      NOT NULL,
    `receiver_id`  INT(11)      NOT NULL,
    `message_text` TEXT         NOT NULL,
    `is_read`      TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`message_id`), KEY `idx_receiver` (`receiver_id`), KEY `idx_sender`   (`sender_id`)
);
