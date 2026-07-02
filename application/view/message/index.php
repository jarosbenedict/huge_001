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
                            <td><?php
                                $last = $partner->last_message ?? '';
                                echo htmlentities(substr($last, 0, 60)) . (strlen($last) > 60 ? '...' : '');
                            ?></td>
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

        <hr style="margin:2em 0;">

        <h2>Groups <a href="<?= Config::get('URL'); ?>group/create" style="font-size:.7em;font-weight:normal;margin-left:.5em;">+ New group</a></h2>
        <?php $groups = GroupModel::getGroupsForUser(); ?>
        <?php if (empty($groups)) { ?>
            <p>You are not a member of any group. <a href="<?= Config::get('URL'); ?>group/create">Create one</a>.</p>
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
                    <?php foreach ($groups as $group) { ?>
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
