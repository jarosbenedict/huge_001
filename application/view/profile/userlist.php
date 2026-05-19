<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" />

<div class="container">
    <h1>Users &amp; rollen</h1>

    <div class="box">

        <?php $this->renderFeedbackMessages(); ?>

        <div>
            <table id="userlist-table" class="overview-table">
                <thead>
                <tr>
                    <td>Id</td>
                    <td>Avatar</td>
                    <td>Usernam</td>
                    <td>Group</td>
                </tr>
                </thead>
                <?php foreach ($this->users as $user) { ?>
                    <tr>
                        <td><?= $user->user_id; ?></td>
                        <td class="avatar">
                            <?php if (isset($user->user_avatar_link)) { ?>
                                <img src="<?= $user->user_avatar_link; ?>" />
                            <?php } ?>
                        </td>
                        <td><?= $user->user_name; ?></td>
                        <td><?= isset($user->role_name) ? $user->role_name : $user->user_account_type; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#userlist-table').DataTable();
    });
</script>
