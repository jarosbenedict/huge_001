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

        if ($receiver_id == Session::get('user_id')) {
            Session::add('feedback_negative', 'You cannot send a message to yourself.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "CALL sp_send_message(:sender_id, :receiver_id, :message_text)";
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

        $sql   = "CALL sp_get_conversation(:user1, :user2)";
        $query = $database->prepare($sql);
        $query->execute(array(':user1' => $me, ':user2' => $partner_id));

        return $query->fetchAll();
    }


    public static function markAsRead($partner_id)
    {
        $partner_id = (int) $partner_id;
        $me         = (int) Session::get('user_id');

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "CALL sp_mark_messages_read(:partner_id, :me)";
        $query = $database->prepare($sql);
        $query->execute(array(':partner_id' => $partner_id, ':me' => $me));
    }


    public static function countUnread()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "CALL sp_count_unread_messages(:me)";
        $query = $database->prepare($sql);
        $query->execute(array(':me' => Session::get('user_id')));

        return (int) $query->fetchColumn();
    }


    public static function getConversationPartners()
    {
        $me       = (int) Session::get('user_id');
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "CALL sp_get_conversation_partners(:me)";
        $query = $database->prepare($sql);
        $query->execute(array(':me' => $me));

        return $query->fetchAll();
    }


    public static function getUserById($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "CALL sp_get_user_by_id(:user_id)";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => (int) $user_id));

        return $query->fetch();
    }
}
