<div class="container">
    <p>
        <a href="<?php echo Config::get('URL'); ?>gallery/index">Back to Gallery</a>
    </p>

    <div class="fullscreen-view">
        <div class="fullscreen-image-container">
            <img src="<?php echo Config::get('URL'); ?>gallery/view/<?php echo $this->file->id; ?>"
                 alt="<?php echo htmlentities($this->file->name); ?>"
                 class="fullscreen-image">
        </div>

        <div class="fullscreen-info">
            <h2><?php echo htmlentities($this->file->name); ?></h2>
            <ul>
                <li>Size: <?php echo FileModel::formatFileSize($this->file->size); ?></li>
                <li>Downloads: <?php echo $this->file->downloads; ?></li>
                <li>Uploaded by: <?php echo htmlentities($this->file->owner_name); ?></li>
                <li>Status: <?php echo $this->file->shared ? 'Public' : 'Private'; ?></li>
                <li>Uploaded: <?php echo $this->file->created_at; ?></li>
            </ul>

            <div class="fullscreen-actions">
                <a href="<?php echo Config::get('URL'); ?>gallery/download/<?php echo $this->file->id; ?>"
                   class="gallery-btn">
                    Download
                </a>

                <?php if ($this->file->owner_id == Session::get('user_id')): ?>
                    <a href="<?php echo Config::get('URL'); ?>gallery/toggleShare/<?php echo $this->file->id; ?>"
                       class="gallery-btn">
                        <?php echo $this->file->shared ? 'Make Private' : 'Make Public'; ?>
                    </a>
                    <a href="<?php echo Config::get('URL'); ?>gallery/delete/<?php echo $this->file->id; ?>"
                       class="gallery-btn gallery-action-delete"
                       onclick="return confirm('Are you sure you want to delete this image?');">
                        Delete
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
