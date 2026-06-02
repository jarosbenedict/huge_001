<div class="container">
    <h1>Create new group</h1>
    <a href="<?= Config::get('URL'); ?>group/index">&larr; Back to groups</a>

    <div class="box">
        <?php $this->renderFeedbackMessages(); ?>

        <form method="post" action="<?= Config::get('URL'); ?>group/create">
            <label for="group_name">Group name</label><br>
            <input type="text"
                   id="group_name"
                   name="group_name"
                   placeholder="Enter group name..."
                   required
                   style="width:100%;max-width:400px;margin-bottom:1em;" /><br>

            <?php if (!empty($this->all_users)) { ?>
                <label>Add members (optional)</label><br>
                <select name="member_ids[]" multiple size="8"
                        style="width:100%;max-width:400px;margin-bottom:1em;">
                    <?php foreach ($this->all_users as $user) { ?>
                        <option value="<?= (int) $user->user_id; ?>">
                            <?= htmlentities($user->user_name); ?>
                        </option>
                    <?php } ?>
                </select><br>
                <small>Hold Ctrl / Cmd to select multiple users.</small><br><br>
            <?php } ?>

            <input type="submit" name="submit_create_group" value="Create group" />
        </form>
    </div>
</div>
