CREATE TABLE IF NOT EXISTS `huge`.`user_roles` (
 `role_id` tinyint(1) NOT NULL,
 `role_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
 PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user groups/roles';

INSERT INTO `huge`.`user_roles` (`role_id`, `role_name`) VALUES
  (1, 'Gast'),
  (2, 'Benutzer'),
  (7, 'Admin');
