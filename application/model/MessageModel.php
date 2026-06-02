<?php

class MessageModel
{
    public static function sendMessage($receiver_id, $message_text)
    {
        $receiver_id  = (int) $receiver_id;
        $message_text = trim($message_text);

        if (empty($message_text) || $receiver_id <= 0) {
            Session::add('feedback_negative', 'Message could not be sent.');
            return false;
        }

        // nicht selber nachrichten schicken
        if ($receiver_id == Session::get('user_id')) {
            Session::add('feedback_negative', 'You cannot send a message to yourself.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text)
                VALUES (:sender_id, :receiver_id, :message_text)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id'    => Session::get('user_id'),
            ':receiver_id'  => $receiver_id,
            ':message_text' => $message_text
        ));

        return ($query->rowCount() == 1);
    }


    public static function getMessages($partner_id)
    {
        $partner_id = (int) $partner_id;
        $me         = (int) Session::get('user_id');

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT m.message_id, m.sender_id, m.receiver_id, m.message_text,
                       m.is_read, m.created_at, u.user_name AS sender_name
                FROM messages m
                JOIN users u ON u.user_id = m.sender_id
                WHERE (m.sender_id = :me1 AND m.receiver_id = :partner1)
                   OR (m.sender_id = :partner2 AND m.receiver_id = :me2)
                ORDER BY m.created_at ASC";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':me1'      => $me,
            ':partner1' => $partner_id,
            ':partner2' => $partner_id,
            ':me2'      => $me
        ));

        return $query->fetchAll();
    }


    public static function markAsRead($partner_id)
    {
        $partner_id = (int) $partner_id;
        $me         = (int) Session::get('user_id');

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE messages SET is_read = 1
                WHERE sender_id = :partner_id AND receiver_id = :me AND is_read = 0";
        $query = $database->prepare($sql);
        $query->execute(array(':partner_id' => $partner_id, ':me' => $me));
    }


    public static function countUnread()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "SELECT COUNT(*) FROM messages WHERE receiver_id = :me AND is_read = 0";
        $query = $database->prepare($sql);
        $query->execute(array(':me' => Session::get('user_id')));

        return (int) $query->fetchColumn();
    }


    public static function getConversationPartners()
    {
        $me       = (int) Session::get('user_id');
        $database = DatabaseFactory::getFactory()->getConnection();

        // partner bekommen, herausfinden wer mit mir oder mit wem ich geschrieben habe
        $sql = "SELECT u.user_id, u.user_name,
                       (SELECT message_text FROM messages
                        WHERE (sender_id = u.user_id AND receiver_id = :me1)
                           OR (sender_id = :me2 AND receiver_id = u.user_id)
                        ORDER BY created_at DESC LIMIT 1) AS last_message,
                       (SELECT COUNT(*) FROM messages
                        WHERE sender_id = u.user_id AND receiver_id = :me3 AND is_read = 0) AS unread_count
                FROM users u
                WHERE u.user_id IN (
                    SELECT DISTINCT sender_id   FROM messages WHERE receiver_id = :me4
                    UNION
                    SELECT DISTINCT receiver_id FROM messages WHERE sender_id   = :me5
                )
                ORDER BY (SELECT MAX(created_at) FROM messages
                          WHERE (sender_id = u.user_id AND receiver_id = :me6)
                             OR (sender_id = :me7 AND receiver_id = u.user_id)) DESC";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':me1' => $me, ':me2' => $me, ':me3' => $me,
            ':me4' => $me, ':me5' => $me, ':me6' => $me, ':me7' => $me
        ));

        return $query->fetchAll();
    }


    public static function getUserById($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "SELECT user_id, user_name FROM users WHERE user_id = :user_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => (int) $user_id));

        return $query->fetch();
    }
}
