CREATE TABLE IF NOT EXISTS `huge`.`files` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing file id',
  `name` varchar(255) NOT NULL COMMENT 'original filename',
  `stored_name` varchar(255) NOT NULL COMMENT 'filename on disk',
  `size` int(11) NOT NULL COMMENT 'file size in bytes',
  `downloads` int(11) NOT NULL DEFAULT '0' COMMENT 'download counter',
  `owner_id` int(11) NOT NULL COMMENT 'FK to users table',
  `shared` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=private, 1=public',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'upload timestamp',
  PRIMARY KEY (`id`),
  KEY `idx_owner_id` (`owner_id`),
  KEY `idx_shared` (`shared`),
  CONSTRAINT `fk_files_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='uploaded files for gallery';
