-- =============================================================================
-- Schritt 1: Bot-User in der users-Tabelle anlegen
-- Der Bot erscheint automatisch in der Userliste (profile/userlist),
-- sobald er in der users-Tabelle existiert. Von dort aus kann ein User
-- eine Konversation starten, und der Bot taucht dann auch im Messenger-Inbox
-- (message/index) als Gesprächspartner auf.
-- =============================================================================

INSERT INTO `huge`.`users` (
    `user_id`,
    `session_id`,
    `user_name`,
    `user_password_hash`,
    `user_email`,
    `user_active`,
    `user_deleted`,
    `user_account_type`,
    `user_has_avatar`,
    `user_remember_me_token`,
    `user_creation_timestamp`,
    `user_suspension_timestamp`,
    `user_last_login_timestamp`,
    `user_failed_logins`,
    `user_last_failed_login`,
    `user_activation_hash`,
    `user_password_reset_hash`,
    `user_password_reset_timestamp`,
    `user_provider_type`
) VALUES (
    3,                              -- user_id (angepasst an deine DB – ggf. auf nächste freie ID setzen)
    NULL,                           -- session_id (Bot loggt sich nie ein)
    'Huge_AI_Bot',                  -- user_name
    NULL,                           -- user_password_hash (Bot hat kein Passwort)
    'ai-bot@huge.local',           -- user_email (eindeutig)
    1,                              -- user_active = aktiv
    0,                              -- user_deleted = nein
    1,                              -- user_account_type = Standard
    0,                              -- user_has_avatar = nein
    NULL,                           -- user_remember_me_token
    UNIX_TIMESTAMP(),               -- user_creation_timestamp
    NULL,                           -- user_suspension_timestamp
    NULL,                           -- user_last_login_timestamp
    0,                              -- user_failed_logins
    NULL,                           -- user_last_failed_login
    NULL,                           -- user_activation_hash
    NULL,                           -- user_password_reset_hash
    NULL,                           -- user_password_reset_timestamp
    'DEFAULT'                       -- user_provider_type
);
