<?php

class GalleryController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::checkAuthentication();
    }

    public function index()
    {
        $this->View->render('gallery/index', array(
            'files' => FileModel::getAllFiles()
        ));
    }

    public function upload()
    {
        if (isset($_POST['submit_upload'])) {
            $this->handleUpload();
        } else {
            $this->View->render('gallery/upload');
        }
    }

    private function handleUpload()
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Session::add('feedback_negative', Text::get('FEEDBACK_FILE_UPLOAD_FAILED'));
            Redirect::to('gallery/upload');
            return;
        }

        if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
            Session::add('feedback_negative', Text::get('FEEDBACK_FILE_TOO_LARGE'));
            Redirect::to('gallery/upload');
            return;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['file']['tmp_name']);
        $erlaubt = array('image/jpeg', 'image/png', 'image/gif');

        if (!in_array($mime, $erlaubt)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_FILE_TYPE_NOT_ALLOWED'));
            Redirect::to('gallery/upload');
            return;
        }

        $originalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file']['name']));
        $storedName = time() . '_' . bin2hex(random_bytes(8)) . '_' . $originalName;
        $userId = Session::get('user_id');

        $userPicturePath = FileModel::getUserPicturePath($userId);
        $targetPath = $userPicturePath . $storedName;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_FILE_UPLOAD_FAILED'));
            Redirect::to('gallery/upload');
            return;
        }

        $fileId = FileModel::createFile($originalName, $storedName, $_FILES['file']['size']);

        if ($fileId) {
            Session::add('feedback_positive', Text::get('FEEDBACK_FILE_UPLOAD_SUCCESSFUL'));
        }

        Redirect::to('gallery/index');
    }

    public function download($file_id)
    {
        $file = FileModel::getFile($file_id);

        if (!$file) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        if ($file->owner_id != Session::get('user_id') && !$file->shared) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }

        $filePath = Config::get('PATH_USERPICTURES') . $file->owner_id . '/' . $file->stored_name;

        if (!file_exists($filePath)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        FileModel::incrementDownload($file_id);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($filePath);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $file->name . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($filePath);
        exit;
    }

    public function view($file_id)
    {
        $file = FileModel::getFile($file_id);

        if (!$file) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        if ($file->owner_id != Session::get('user_id') && !$file->shared) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }

        $filePath = Config::get('PATH_USERPICTURES') . $file->owner_id . '/' . $file->stored_name;

        if (!file_exists($filePath)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($filePath);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $file->name . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($filePath);
        exit;
    }

    public function fullscreen($file_id)
    {
        $file = FileModel::getFile($file_id);

        if (!$file) {
            $this->View->render('error/404');
            return;
        }

        if ($file->owner_id != Session::get('user_id') && !$file->shared) {
            $this->View->render('error/404');
            return;
        }

        $this->View->render('gallery/fullscreen', array(
            'file' => $file
        ));
    }

    public function delete($file_id)
    {
        FileModel::deleteFile($file_id);
        Redirect::to('gallery/index');
    }

    public function toggleShare($file_id)
    {
        FileModel::toggleShare($file_id);
        Redirect::to('gallery/index');
    }
}
