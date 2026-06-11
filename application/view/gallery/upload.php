<div class="container">
    <h1>Upload New Image</h1>

    <p>
        <a href="<?php echo Config::get('URL'); ?>gallery/index">Back to Gallery</a>
    </p>

    <?php $this->renderFeedbackMessages(); ?>

    <form method="post" action="<?php echo Config::get('URL'); ?>gallery/upload" enctype="multipart/form-data">
        <div class="upload-form-group">
            <label for="file-input">Choose an image (JPG, PNG, GIF, max. 5 MB):</label>
            <input type="file" id="file-input" name="file" accept=".jpg,.jpeg,.png,.gif" required>
        </div>

        <div class="upload-form-group">
            <input type="submit" name="submit_upload" value="Upload">
        </div>
    </form>

    <div class="box">
        <h3>Upload Information</h3>
        <ul>
            <li>Allowed file types: JPG, PNG, GIF</li>
            <li>Maximum file size: 5 MB</li>
            <li>Images are stored securely outside the public web root</li>
            <li>Images are private by default - use the Share button to make them public</li>
        </ul>
    </div>
</div>
