<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=PT+Sans">

<div class="container">
    <h1>
        Chat mit <?= htmlentities($this->partner->user_name); ?>
        <?php if ((int) $this->partner->user_id === (int) Config::get('BOT_USER_ID')): ?>
            <span class="bot-badge">🤖 AI</span>
        <?php endif; ?>
    </h1>
    <a href="<?= Config::get('URL'); ?>message/index">&larr; Back to inbox</a>

    <div class="box">
        <?php $this->renderFeedbackMessages(); ?>

        <div class="messenger-scroll" id="messenger-scroll">

            <?php if (empty($this->messages)) { ?>
                <p style="text-align:center;color:#aaa;padding:2em;">No messages yet. Say hello to the AI! 👋</p>
            <?php } else { ?>

                <?php
                $msgs    = $this->messages;
                $me      = Session::get('user_id');
                $total   = count($msgs);
                $botId   = (int) Config::get('BOT_USER_ID');
                ?>

                <section class="discussion">
                <?php for ($i = 0; $i < $total; $i++) {
                    $msg      = $msgs[$i];
                    $is_mine  = ($msg->sender_id == $me);
                    $is_bot   = ($msg->sender_id == $botId);

                    // bubble-seite: meine nachrichten rechts, andere links
                    $side     = $is_mine ? 'mine' : 'theirs';

                    // gruppierung: zusammenhängende nachrichten vom gleichen sender
                    $same_prev = ($i > 0          && $msgs[$i-1]->sender_id == $msg->sender_id);
                    $same_next = ($i < $total - 1 && $msgs[$i+1]->sender_id == $msg->sender_id);

                    if      (!$same_prev && !$same_next) { $pos = ''; }
                    elseif  (!$same_prev &&  $same_next) { $pos = 'first'; }
                    elseif  ( $same_prev && !$same_next) { $pos = 'last'; }
                    else                                 { $pos = 'middle'; }

                    // sender-label (nur bei ersten nachrichten einer gruppe oder anderen sendern)
                    $show_sender = !$is_mine && !$same_prev;
                ?>
                    <div class="bubble <?= $side; ?> <?= $pos; ?><?= $is_bot ? ' bot' : ''; ?>"
                         title="<?= htmlentities($msg->sender_name); ?> · <?= $msg->created_at; ?>">
                        <?php if ($show_sender): ?>
                            <span class="bubble-sender-name"><?= htmlentities($msg->sender_name); ?></span>
                        <?php endif; ?>
                        <span class="bubble-text"><?= nl2br(htmlentities($msg->message_text)); ?></span>
                    </div>
                <?php } ?>
                </section>

            <?php } ?>
        </div>

        <form class="messenger-form" method="post"
              action="<?= Config::get('URL'); ?>message/send/<?= (int) $this->partner_id; ?>">
            <input type="text"
                   name="message_text"
                   placeholder="<?= ((int) $this->partner_id === (int) Config::get('BOT_USER_ID')) ? 'Ask the AI something...' : 'Write a message...'; ?>"
                   autocomplete="off"
                   required />
            <input type="submit" value="Send" />
        </form>
    </div>
</div>

<script>
    // Automatisch zu den neuesten Nachrichten scrollen
    var box = document.getElementById('messenger-scroll');
    if (box) { box.scrollTop = box.scrollHeight; }
</script>
