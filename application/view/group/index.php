<div class="container">
    <h1>Groups</h1>
    <div class="box">
        <?php $this->renderFeedbackMessages(); ?>

        <p><a href="<?= Config::get('URL'); ?>group/create">+ Create new group</a></p>

        <?php if (empty($this->groups)) { ?>
            <p>You are not a member of any group yet.</p>
        <?php } else { ?>
            <table class="messenger-inbox-table">
                <thead>
                    <tr>
                        <td>Group</td>
                        <td>Last message</td>
                        <td>Unread</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->groups as $group) { ?>
                        <tr>
                            <td><?= htmlentities($group->group_name); ?></td>
                            <td><?= htmlentities(substr($group->last_message ?? '', 0, 60)) . (strlen($group->last_message ?? '') > 60 ? '...' : ''); ?></td>
                            <td>
                                <?php if ($group->unread_count > 0) { ?>
                                    <span class="msg-badge"><?= (int) $group->unread_count; ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="<?= Config::get('URL'); ?>group/conversation/<?= (int) $group->group_id; ?>">Open chat</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>
