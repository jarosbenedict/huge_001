<div class="container">
    <h1>Messages</h1>
    <div class="box">
        <?php $this->renderFeedbackMessages(); ?>

        <?php if (empty($this->partners)) { ?>
            <p>You have no conversations. Go to <a href="<?= Config::get('URL'); ?>profile/userlist">Users &amp; Groups</a> and click on a user to start a conversation.</p>
        <?php } else { ?>
            <table class="messenger-inbox-table">
                <thead>
                    <tr>
                        <td>user</td>
                        <td>last message</td>
                        <td>unread</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->partners as $partner) { ?>
                        <tr>
                            <td><?= htmlentities($partner->user_name); ?></td>
                            <td><?= htmlentities(substr($partner->last_message, 0, 60)) . (strlen($partner->last_message) > 60 ? '...' : ''); ?></td>
                            <td>
                                <?php if ($partner->unread_count > 0) { ?>
                                    <span class="msg-badge"><?= (int) $partner->unread_count; ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="<?= Config::get('URL'); ?>message/conversation/<?= (int) $partner->user_id; ?>">Open chat</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>
