<?php

/**
 * Class UserRoleModel
 *
 * This class contains everything that is related to up- and downgrading accounts.
 */
class UserRoleModel
{
    /**
     * Upgrades / downgrades the user's account. Currently it's just the field user_account_type in the database that
     * can be 1 or 2 (maybe "basic" or "premium"). Put some more complex stuff in here, maybe a pay-process or whatever
     * you like.
     *
     * @param $type
     *
     * @return bool
     */
    public static function changeUserRole($type)
    {
        if (!$type) {
            return false;
        }

        // save new role to database
        if (self::saveRoleToDatabase($type)) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_TYPE_CHANGE_SUCCESSFUL'));
            return true;
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_TYPE_CHANGE_FAILED'));
            return false;
        }
    }

    public static function getAllRoles()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT role_id, role_name FROM user_roles ORDER BY role_id ASC");
        $query->execute();
        return $query->fetchAll();
    }

    public static function saveRoleToDatabase($type)
    {
        if (!in_array($type, [1, 2])) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET user_account_type = :new_type WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(
            ':new_type' => $type,
            ':user_id' => Session::get('user_id')
        ));

        if ($query->rowCount() == 1) {
            // set account type in session
            Session::set('user_account_type', $type);
            return true;
        }

        return false;
    }
}
