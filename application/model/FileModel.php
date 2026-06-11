<?php

class FileModel
{
    public static function getAllFiles()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT f.id, f.name, f.stored_name, f.size, f.downloads, f.owner_id, f.shared, f.created_at,
                       u.user_name AS owner_name
                FROM files f
                JOIN users u ON f.owner_id = u.user_id
                WHERE f.owner_id = :user_id OR f.shared = 1
                ORDER BY f.created_at DESC";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => Session::get('user_id')));

        return $query->fetchAll();
    }

    public static function getUserFiles()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT f.id, f.name, f.stored_name, f.size, f.downloads, f.owner_id, f.shared, f.created_at,
                       u.user_name AS owner_name
                FROM files f
                JOIN users u ON f.owner_id = u.user_id
                WHERE f.owner_id = :user_id
                ORDER BY f.created_at DESC";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => Session::get('user_id')));

        return $query->fetchAll();
    }

    public static function getPublicFiles()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT f.id, f.name, f.stored_name, f.size, f.downloads, f.owner_id, f.shared, f.created_at,
                       u.user_name AS owner_name
                FROM files f
                JOIN users u ON f.owner_id = u.user_id
                WHERE f.shared = 1
                ORDER BY f.created_at DESC";
        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public static function getFile($file_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT f.id, f.name, f.stored_name, f.size, f.downloads, f.owner_id, f.shared, f.created_at,
                       u.user_name AS owner_name
                FROM files f
                JOIN users u ON f.owner_id = u.user_id
                WHERE f.id = :file_id
                LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':file_id' => $file_id));

        return $query->fetch();
    }

    public static function createFile($name, $stored_name, $size)
    {
        if (!$name || strlen($name) == 0) {
            Session::add('feedback_negative', Text::get('FEEDBACK_FILE_CREATION_FAILED'));
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO files (name, stored_name, size, owner_id, shared)
                VALUES (:name, :stored_name, :size, :owner_id, 0)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':name' => $name,
            ':stored_name' => $stored_name,
            ':size' => $size,
            ':owner_id' => Session::get('user_id')
        ));

        if ($query->rowCount() == 1) {
            return $database->lastInsertId();
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_FILE_CREATION_FAILED'));
        return false;
    }

    public static function deleteFile($file_id)
    {
        $file = self::getFile($file_id);

        if (!$file || $file->owner_id != Session::get('user_id')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_FILE_DELETE_FAILED'));
            return false;
        }

        $userPicturePath = Config::get('PATH_USERPICTURES') . $file->owner_id . '/' . $file->stored_name;

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM files WHERE id = :file_id AND owner_id = :user_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':file_id' => $file_id, ':user_id' => Session::get('user_id')));

        if ($query->rowCount() == 1) {
            if (file_exists($userPicturePath)) {
                unlink($userPicturePath);
            }
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_FILE_DELETE_FAILED'));
        return false;
    }

    public static function toggleShare($file_id)
    {
        $file = self::getFile($file_id);

        if (!$file || $file->owner_id != Session::get('user_id')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_FILE_SHARE_FAILED'));
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE files SET shared = :shared WHERE id = :file_id AND owner_id = :user_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':shared' => $file->shared ? 0 : 1,
            ':file_id' => $file_id,
            ':user_id' => Session::get('user_id')
        ));

        if ($query->rowCount() == 1) {
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_FILE_SHARE_FAILED'));
        return false;
    }

    public static function incrementDownload($file_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE files SET downloads = downloads + 1 WHERE id = :file_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':file_id' => $file_id));
    }

    public static function getUserPicturePath($user_id)
    {
        $path = Config::get('PATH_USERPICTURES') . $user_id . '/';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    public static function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
