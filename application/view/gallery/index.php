<div class="container">
    <h1>My Gallery</h1>

    <div class="gallery-actions">
        <a href="<?php echo Config::get('URL'); ?>gallery/upload" class="gallery-btn">
            Upload New Image
        </a>
    </div>

    <?php $this->renderFeedbackMessages(); ?>

    <?php if (isset($this->files) && count($this->files) > 0): ?>
        <div class="gallery-grid">
            <?php foreach ($this->files as $file): ?>
                <div class="gallery-item">
                    <a href="<?php echo Config::get('URL'); ?>gallery/fullscreen/<?php echo $file->id; ?>">
                        <img src="<?php echo Config::get('URL'); ?>gallery/view/<?php echo $file->id; ?>"
                             alt="<?php echo htmlentities($file->name); ?>"
                             loading="lazy">
                    </a>
                    <div class="gallery-item-info">
                        <span class="gallery-item-name" title="<?php echo htmlentities($file->name); ?>">
                            <?php echo htmlentities($file->name); ?>
                        </span>
                        <span class="gallery-item-size">
                            <?php echo FileModel::formatFileSize($file->size); ?>
                        </span>
                        <?php if ($file->shared): ?>
                            <span class="gallery-item-shared-badge">Public</span>
                        <?php endif; ?>
                    </div>
                    <div class="gallery-item-actions">
                        <a href="<?php echo Config::get('URL'); ?>gallery/download/<?php echo $file->id; ?>"
                           class="gallery-action-link" title="Download">
                            Download
                        </a>
                        <?php if ($file->owner_id == Session::get('user_id')): ?>
                            <a href="<?php echo Config::get('URL'); ?>gallery/toggleShare/<?php echo $file->id; ?>"
                               class="gallery-action-link" title="Toggle Share">
                                <?php echo $file->shared ? 'Private' : 'Share'; ?>
                            </a>
                            <a href="<?php echo Config::get('URL'); ?>gallery/delete/<?php echo $file->id; ?>"
                               class="gallery-action-link gallery-action-delete"
                               onclick="return confirm('Are you sure you want to delete this image?');"
                               title="Delete">
                                Delete
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="gallery-item-owner">
                        by <?php echo htmlentities($file->owner_name); ?>
                        <?php if ($file->owner_id != Session::get('user_id')): ?>
                            (shared)
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="feedback info">
            No images yet. <a href="<?php echo Config::get('URL'); ?>gallery/upload">Upload your first image!</a>
        </div>
    <?php endif; ?>
</div>
