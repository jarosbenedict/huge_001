DROP PROCEDURE IF EXISTS sp_send_message;

DELIMITER //

CREATE PROCEDURE sp_send_message(
    IN p_sender_id   INT,
    IN p_receiver_id INT,
    IN p_message_text TEXT
)
BEGIN
    INSERT INTO messages (sender_id, receiver_id, message_text)
    VALUES (p_sender_id, p_receiver_id, p_message_text);
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_conversation;

DELIMITER //

CREATE PROCEDURE sp_get_conversation(
    IN p_user1 INT,
    IN p_user2 INT
)
BEGIN
    SELECT m.message_id, m.sender_id, m.receiver_id, m.message_text,
           m.is_read, m.created_at, u.user_name AS sender_name
    FROM messages m
    JOIN users u ON u.user_id = m.sender_id
    WHERE (m.sender_id = p_user1 AND m.receiver_id = p_user2)
       OR (m.sender_id = p_user2 AND m.receiver_id = p_user1)
    ORDER BY m.created_at ASC;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_mark_messages_read;

DELIMITER //

CREATE PROCEDURE sp_mark_messages_read(
    IN p_partner_id INT,
    IN p_me         INT
)
BEGIN
    UPDATE messages SET is_read = 1
    WHERE sender_id = p_partner_id AND receiver_id = p_me AND is_read = 0;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_count_unread_messages;

DELIMITER //

CREATE PROCEDURE sp_count_unread_messages(
    IN p_user_id INT
)
BEGIN
    SELECT COUNT(*) AS unread_count
    FROM messages
    WHERE receiver_id = p_user_id AND is_read = 0;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_conversation_partners;

DELIMITER //

CREATE PROCEDURE sp_get_conversation_partners(
    IN p_me INT
)
BEGIN
    SELECT u.user_id, u.user_name,
           (SELECT m.message_text FROM messages m
            WHERE (m.sender_id = u.user_id AND m.receiver_id = p_me)
               OR (m.sender_id = p_me AND m.receiver_id = u.user_id)
            ORDER BY m.created_at DESC LIMIT 1) AS last_message,
           (SELECT COUNT(*) FROM messages m
            WHERE m.sender_id = u.user_id AND m.receiver_id = p_me AND m.is_read = 0) AS unread_count
    FROM users u
    WHERE u.user_id IN (
        SELECT DISTINCT sender_id   FROM messages WHERE receiver_id = p_me
        UNION
        SELECT DISTINCT receiver_id FROM messages WHERE sender_id   = p_me
    )
    ORDER BY (SELECT MAX(m.created_at) FROM messages m
              WHERE (m.sender_id = u.user_id AND m.receiver_id = p_me)
                 OR (m.sender_id = p_me AND m.receiver_id = u.user_id)) DESC;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_user_by_id;

DELIMITER //

CREATE PROCEDURE sp_get_user_by_id(
    IN p_user_id INT
)
BEGIN
    SELECT user_id, user_name FROM users WHERE user_id = p_user_id LIMIT 1;
END //

DELIMITER ;

-- =============================================================================
-- Gruppen & Gruppen-Mitglieder
-- =============================================================================

DROP PROCEDURE IF EXISTS sp_create_group;

DELIMITER //

CREATE PROCEDURE sp_create_group(
    IN p_group_name VARCHAR(100),
    IN p_created_by INT
)
BEGIN
    INSERT INTO `groups` (group_name, created_by) VALUES (p_group_name, p_created_by);
    INSERT INTO group_members (group_id, user_id) VALUES (LAST_INSERT_ID(), p_created_by);
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_add_group_member;

DELIMITER //

CREATE PROCEDURE sp_add_group_member(
    IN p_group_id INT,
    IN p_user_id  INT
)
BEGIN
    INSERT INTO group_members (group_id, user_id) VALUES (p_group_id, p_user_id);
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_remove_group_member;

DELIMITER //

CREATE PROCEDURE sp_remove_group_member(
    IN p_group_id INT,
    IN p_user_id  INT
)
BEGIN
    DELETE FROM group_members WHERE group_id = p_group_id AND user_id = p_user_id;
END //

DELIMITER ;

-- =============================================================================
-- Kombinierte Prüfung: is_member + is_creator
-- =============================================================================

DROP PROCEDURE IF EXISTS sp_check_group_access;

DELIMITER //

CREATE PROCEDURE sp_check_group_access(
    IN p_group_id INT,
    IN p_user_id  INT
)
BEGIN
    SELECT
        EXISTS(SELECT 1 FROM group_members WHERE group_id = p_group_id AND user_id = p_user_id) AS is_member,
        EXISTS(SELECT 1 FROM `groups`       WHERE group_id = p_group_id AND created_by = p_user_id) AS is_creator;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_user_groups;

DELIMITER //

CREATE PROCEDURE sp_get_user_groups(
    IN p_me INT
)
BEGIN
    SELECT g.group_id, g.group_name, g.created_by,
           (SELECT gm.message_text FROM group_messages gm
            WHERE gm.group_id = g.group_id
            ORDER BY gm.created_at DESC LIMIT 1) AS last_message,
           (SELECT COUNT(*) FROM group_messages gm
            WHERE gm.group_id = g.group_id
              AND gm.sender_id != p_me
              AND gm.is_read   = 0) AS unread_count
    FROM `groups` g
    JOIN group_members gmbr ON gmbr.group_id = g.group_id AND gmbr.user_id = p_me
    ORDER BY (SELECT MAX(gm2.created_at) FROM group_messages gm2
              WHERE gm2.group_id = g.group_id) DESC;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_group_by_id;

DELIMITER //

CREATE PROCEDURE sp_get_group_by_id(
    IN p_group_id INT
)
BEGIN
    SELECT group_id, group_name, created_by FROM `groups` WHERE group_id = p_group_id LIMIT 1;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_group_members;

DELIMITER //

CREATE PROCEDURE sp_get_group_members(
    IN p_group_id INT
)
BEGIN
    SELECT u.user_id, u.user_name
    FROM group_members gm
    JOIN users u ON u.user_id = gm.user_id
    WHERE gm.group_id = p_group_id
    ORDER BY u.user_name ASC;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_non_group_members;

DELIMITER //

CREATE PROCEDURE sp_get_non_group_members(
    IN p_group_id INT
)
BEGIN
    SELECT user_id, user_name FROM users
    WHERE user_id NOT IN (
        SELECT user_id FROM group_members WHERE group_id = p_group_id
    )
    ORDER BY user_name ASC;
END //

DELIMITER ;

-- =============================================================================
-- Gruppennachrichten
-- =============================================================================

DROP PROCEDURE IF EXISTS sp_send_group_message;

DELIMITER //

CREATE PROCEDURE sp_send_group_message(
    IN p_group_id     INT,
    IN p_sender_id    INT,
    IN p_message_text TEXT
)
BEGIN
    INSERT INTO group_messages (group_id, sender_id, message_text)
    VALUES (p_group_id, p_sender_id, p_message_text);
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_group_messages;

DELIMITER //

CREATE PROCEDURE sp_get_group_messages(
    IN p_group_id INT
)
BEGIN
    SELECT gm.message_id, gm.group_id, gm.sender_id, gm.message_text,
           gm.is_read, gm.created_at, u.user_name AS sender_name
    FROM group_messages gm
    JOIN users u ON u.user_id = gm.sender_id
    WHERE gm.group_id = p_group_id
    ORDER BY gm.created_at ASC;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_mark_group_messages_read;

DELIMITER //

CREATE PROCEDURE sp_mark_group_messages_read(
    IN p_group_id INT,
    IN p_me       INT
)
BEGIN
    UPDATE group_messages SET is_read = 1
    WHERE group_id = p_group_id AND sender_id != p_me AND is_read = 0;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_count_unread_group_messages;

DELIMITER //

CREATE PROCEDURE sp_count_unread_group_messages(
    IN p_me INT
)
BEGIN
    SELECT COUNT(*) AS unread_count
    FROM group_messages gm
    JOIN group_members gmbr ON gmbr.group_id = gm.group_id AND gmbr.user_id = p_me
    WHERE gm.sender_id != p_me AND gm.is_read = 0;
END //

DELIMITER ;

-- =============================================================================

DROP PROCEDURE IF EXISTS sp_get_all_users_except;

DELIMITER //

CREATE PROCEDURE sp_get_all_users_except(
    IN p_me INT
)
BEGIN
    SELECT user_id, user_name FROM users WHERE user_id != p_me ORDER BY user_name ASC;
END //

DELIMITER ;
