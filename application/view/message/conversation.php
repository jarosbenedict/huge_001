<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=PT+Sans">

<div class="container">
    <h1>Chat mit <?= htmlentities($this->partner->user_name); ?></h1>
    <a href="<?= Config::get('URL'); ?>message/index">&larr; Back to inbox</a>

    <div class="box">
        <?php $this->renderFeedbackMessages(); ?>

        <div class="messenger-scroll" id="messenger-scroll">

            <?php if (empty($this->messages)) { ?>
                <p style="text-align:center;color:#aaa;padding:2em;">No messages.</p>
            <?php } else { ?>

                <?php
                $msgs   = $this->messages;
                $me     = Session::get('user_id');
                $total  = count($msgs);
                ?>

                <section class="discussion">
                <?php for ($i = 0; $i < $total; $i++) {
                    $msg      = $msgs[$i];
                    $is_mine  = ($msg->sender_id == $me);
                    $side     = $is_mine ? 'recipient' : 'sender';

                    $same_prev = ($i > 0          && $msgs[$i-1]->sender_id == $msg->sender_id);
                    $same_next = ($i < $total - 1 && $msgs[$i+1]->sender_id == $msg->sender_id);

                    

                    if      (!$same_prev && !$same_next) { $pos = ''; }
                    elseif  (!$same_prev &&  $same_next) { $pos = 'first'; }
                    elseif  ( $same_prev && !$same_next) { $pos = 'last'; }
                    else                                 { $pos = 'middle'; }
                ?>
                    <div class="bubble <?= $side; ?> <?= $pos; ?>" title="<?= htmlentities($msg->sender_name); ?> &middot; <?= $msg->created_at; ?>">
                        <?= nl2br(htmlentities($msg->message_text)); ?>
                    </div>
                <?php } ?>
                </section>

            <?php } ?>
        </div>

        <form class="messenger-form" method="post"
              action="<?= Config::get('URL'); ?>message/send/<?= (int) $this->partner_id; ?>">
            <input type="text"
                   name="message_text"
                   placeholder="Write a message..."
                   autocomplete="off"
                   required />
            <input type="submit" value="Send" />
        </form>
    </div>
</div>

<script>
    // ^zu den neusten nachrichten scolln
    var box = document.getElementById('messenger-scroll');
    if (box) { box.scrollTop = box.scrollHeight; }
</script>
