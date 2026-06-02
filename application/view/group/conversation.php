<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=PT+Sans">

<div class="container">
    <h1>Group: <?= htmlentities($this->group->group_name); ?></h1>
    <a href="<?= Config::get('URL'); ?>group/index">&larr; Back to groups</a>

    <div class="box">
        <?php $this->renderFeedbackMessages(); ?>

        <!-- ── Chat window ──────────────────────────────────────── -->
        <div class="messenger-scroll" id="messenger-scroll">

            <?php if (empty($this->messages)) { ?>
                <p style="text-align:center;color:#aaa;padding:2em;">No messages yet.</p>
            <?php } else { ?>

                <?php
                $msgs  = $this->messages;
                $me    = (int) Session::get('user_id');
                $total = count($msgs);
                ?>

                <section class="discussion">
                <?php for ($i = 0; $i < $total; $i++) {
                    $msg     = $msgs[$i];
                    $is_mine = ($msg->sender_id == $me);
                    $side    = $is_mine ? 'recipient' : 'sender';

                    $same_prev = ($i > 0          && $msgs[$i-1]->sender_id == $msg->sender_id);
                    $same_next = ($i < $total - 1 && $msgs[$i+1]->sender_id == $msg->sender_id);

                    if      (!$same_prev && !$same_next) { $pos = ''; }
                    elseif  (!$same_prev &&  $same_next) { $pos = 'first'; }
                    elseif  ( $same_prev && !$same_next) { $pos = 'last'; }
                    else                                 { $pos = 'middle'; }

                    // Show sender name label above the first bubble of each foreign sender
                    $show_name = (!$is_mine && !$same_prev);
                ?>
                    <?php if ($show_name) { ?>
                        <div class="group-sender-name"><?= htmlentities($msg->sender_name); ?></div>
                    <?php } ?>
                    <div class="bubble <?= $side; ?> <?= $pos; ?>"
                         title="<?= htmlentities($msg->sender_name); ?> &middot; <?= $msg->created_at; ?>">
                        <?= nl2br(htmlentities($msg->message_text)); ?>
                    </div>
                <?php } ?>
                </section>

            <?php } ?>
        </div>

        <!-- ── Send form ────────────────────────────────────────── -->
        <form class="messenger-form" method="post"
              action="<?= Config::get('URL'); ?>group/send/<?= (int) $this->group_id; ?>">
            <input type="text"
                   name="message_text"
                   placeholder="Write a message..."
                   autocomplete="off"
                   required />
            <input type="submit" value="Send" />
        </form>
    </div>

    <!-- ── Member management ───────────────────────────────────── -->
    <div class="box" style="margin-top:1em;">
        <h3>Members</h3>
        <ul style="margin:0 0 1em 1.5em;">
            <?php foreach ($this->members as $member) { ?>
                <li>
                    <?= htmlentities($member->user_name); ?>
                    <?php if ($member->user_id == $this->group->created_by) { ?>
                        <em style="color:#888;">(creator)</em>
                    <?php } ?>
                    <?php
                    $me_id = (int) Session::get('user_id');
                    // Creator can remove anyone except themselves; members can leave themselves
                    $can_remove = ($this->is_creator && $member->user_id != $me_id)
                                  || ($member->user_id == $me_id && !$this->is_creator);
                    ?>
                    <?php if ($can_remove) { ?>
                        <a href="<?= Config::get('URL'); ?>group/removeMember/<?= (int) $this->group_id; ?>/<?= (int) $member->user_id; ?>"
                           style="color:red;margin-left:.5em;font-size:.85em;"
                           onclick="return confirm('Remove <?= htmlentities($member->user_name); ?> from this group?');">
                            remove
                        </a>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>

        <?php if ($this->is_creator && !empty($this->non_members)) { ?>
            <h4>Add member</h4>
            <form method="post" action="<?= Config::get('URL'); ?>group/addMember/<?= (int) $this->group_id; ?>"
                  style="display:flex;gap:.5em;align-items:center;">
                <select name="user_id">
                    <?php foreach ($this->non_members as $u) { ?>
                        <option value="<?= (int) $u->user_id; ?>"><?= htmlentities($u->user_name); ?></option>
                    <?php } ?>
                </select>
                <input type="submit" value="Add" />
            </form>
        <?php } ?>

        <?php
        // Allow any member (non-creator) to leave the group
        $me_id = (int) Session::get('user_id');
        if (!$this->is_creator) { ?>
            <a href="<?= Config::get('URL'); ?>group/removeMember/<?= (int) $this->group_id; ?>/<?= $me_id; ?>"
               style="color:red;font-size:.9em;"
               onclick="return confirm('Leave this group?');">
                Leave group
            </a>
        <?php } ?>
    </div>
</div>

<script>
    var box = document.getElementById('messenger-scroll');
    if (box) { box.scrollTop = box.scrollHeight; }
</script>
