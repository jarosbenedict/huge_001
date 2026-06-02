<?php

class GroupModel
{
    // -------------------------------------------------------------------------
    // Group management
    // -------------------------------------------------------------------------

    /**
     * Create a new group and add the creator as the first member.
     * Returns the new group_id on success, false on failure.
     */
    public static function createGroup($group_name)
    {
        $group_name = trim($group_name);

        if (empty($group_name)) {
            Session::add('feedback_negative', 'Group name cannot be empty.');
            return false;
        }

        $me       = (int) Session::get('user_id');
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql   = "INSERT INTO `groups` (group_name, created_by) VALUES (:group_name, :created_by)";
        $query = $database->prepare($sql);
        $query->execute(array(':group_name' => $group_name, ':created_by' => $me));

        if ($query->rowCount() != 1) {
            Session::add('feedback_negative', 'Group could not be created.');
            return false;
        }

        $group_id = (int) $database->lastInsertId();

        // add creator as member
        self::addMember($group_id, $me);

        return $group_id;
    }

    /**
     * Add a user to a group. Only the group creator may do this.
     */
    public static function addMember($group_id, $user_id)
    {
        $group_id = (int) $group_id;
        $user_id  = (int) $user_id;
        $me       = (int) Session::get('user_id');

        // allow when the caller is the creator OR when adding self (initial join on create)
        if ($user_id !== $me && !self::isCreator($group_id, $me)) {
            Session::add('feedback_negative', 'Only the group creator can add members.');
            return false;
        }

        if (self::isMember($group_id, $user_id)) {
            Session::add('feedback_negative', 'User is already a member of this group.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => $group_id, ':user_id' => $user_id));

        return ($query->rowCount() == 1);
    }

    /**
     * Remove a member from a group. Only the creator may remove others;
     * any member may leave themselves.
     */
    public static function removeMember($group_id, $user_id)
    {
        $group_id = (int) $group_id;
        $user_id  = (int) $user_id;
        $me       = (int) Session::get('user_id');

        if ($user_id !== $me && !self::isCreator($group_id, $me)) {
            Session::add('feedback_negative', 'Only the group creator can remove other members.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "DELETE FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => $group_id, ':user_id' => $user_id));

        return ($query->rowCount() == 1);
    }

    /**
     * Returns true if $user_id is a member of $group_id.
     */
    public static function isMember($group_id, $user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "SELECT 1 FROM group_members WHERE group_id = :group_id AND user_id = :user_id LIMIT 1";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => (int) $group_id, ':user_id' => (int) $user_id));
        return ($query->fetchColumn() !== false);
    }

    /**
     * Returns true if $user_id is the creator of $group_id.
     */
    public static function isCreator($group_id, $user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "SELECT 1 FROM `groups` WHERE group_id = :group_id AND created_by = :user_id LIMIT 1";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => (int) $group_id, ':user_id' => (int) $user_id));
        return ($query->fetchColumn() !== false);
    }

    /**
     * Fetch all groups the current user belongs to, with last message preview
     * and count of unread messages (= messages not sent by me that arrived
     * after my last visit — tracked via is_read flag).
     */
    public static function getGroupsForUser()
    {
        $me       = (int) Session::get('user_id');
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT g.group_id, g.group_name, g.created_by,
                       (SELECT gm.message_text FROM group_messages gm
                        WHERE gm.group_id = g.group_id
                        ORDER BY gm.created_at DESC LIMIT 1) AS last_message,
                       (SELECT COUNT(*) FROM group_messages gm
                        WHERE gm.group_id = g.group_id
                          AND gm.sender_id != :me1
                          AND gm.is_read   = 0) AS unread_count
                FROM `groups` g
                JOIN group_members gmbr ON gmbr.group_id = g.group_id AND gmbr.user_id = :me2
                ORDER BY (SELECT MAX(gm2.created_at) FROM group_messages gm2
                          WHERE gm2.group_id = g.group_id) DESC";

        $query = $database->prepare($sql);
        $query->execute(array(':me1' => $me, ':me2' => $me));

        return $query->fetchAll();
    }

    /**
     * Fetch group meta by id. Returns false if group does not exist.
     */
    public static function getGroupById($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "SELECT group_id, group_name, created_by FROM `groups` WHERE group_id = :group_id LIMIT 1";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => (int) $group_id));
        return $query->fetch();
    }

    /**
     * Return all members of a group with their user_name.
     */
    public static function getMembers($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "SELECT u.user_id, u.user_name
                     FROM group_members gm
                     JOIN users u ON u.user_id = gm.user_id
                     WHERE gm.group_id = :group_id
                     ORDER BY u.user_name ASC";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => (int) $group_id));
        return $query->fetchAll();
    }

    /**
     * Return all users NOT yet in a given group (for the add-member dropdown).
     */
    public static function getNonMembers($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "SELECT user_id, user_name FROM users
                     WHERE user_id NOT IN (
                         SELECT user_id FROM group_members WHERE group_id = :group_id
                     )
                     ORDER BY user_name ASC";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => (int) $group_id));
        return $query->fetchAll();
    }

    // -------------------------------------------------------------------------
    // Messaging
    // -------------------------------------------------------------------------

    /**
     * Send a message to a group. The sender must be a member.
     */
    public static function sendGroupMessage($group_id, $message_text)
    {
        $group_id     = (int) $group_id;
        $message_text = trim($message_text);
        $me           = (int) Session::get('user_id');

        if (empty($message_text)) {
            Session::add('feedback_negative', 'Message cannot be empty.');
            return false;
        }

        if (!self::isMember($group_id, $me)) {
            Session::add('feedback_negative', 'You are not a member of this group.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "INSERT INTO group_messages (group_id, sender_id, message_text)
                     VALUES (:group_id, :sender_id, :message_text)";
        $query    = $database->prepare($sql);
        $query->execute(array(
            ':group_id'     => $group_id,
            ':sender_id'    => $me,
            ':message_text' => $message_text
        ));

        return ($query->rowCount() == 1);
    }

    /**
     * Fetch all messages for a group, with sender name.
     */
    public static function getGroupMessages($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "SELECT gm.message_id, gm.group_id, gm.sender_id, gm.message_text,
                            gm.is_read, gm.created_at, u.user_name AS sender_name
                     FROM group_messages gm
                     JOIN users u ON u.user_id = gm.sender_id
                     WHERE gm.group_id = :group_id
                     ORDER BY gm.created_at ASC";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => (int) $group_id));
        return $query->fetchAll();
    }

    /**
     * Mark all unread messages in a group (not sent by me) as read.
     */
    public static function markGroupRead($group_id)
    {
        $me       = (int) Session::get('user_id');
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "UPDATE group_messages SET is_read = 1
                     WHERE group_id = :group_id AND sender_id != :me AND is_read = 0";
        $query    = $database->prepare($sql);
        $query->execute(array(':group_id' => (int) $group_id, ':me' => $me));
    }

    /**
     * Count total unread group messages across all groups the current user belongs to.
     */
    public static function countUnreadGroup()
    {
        $me       = (int) Session::get('user_id');
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT COUNT(*) FROM group_messages gm
                JOIN group_members gmbr ON gmbr.group_id = gm.group_id AND gmbr.user_id = :me
                WHERE gm.sender_id != :me2 AND gm.is_read = 0";
        $query = $database->prepare($sql);
        $query->execute(array(':me' => $me, ':me2' => $me));

        return (int) $query->fetchColumn();
    }
}
