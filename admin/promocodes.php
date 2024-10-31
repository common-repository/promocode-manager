<?php
//class.promocode_manager.php has the "controller" logic
?>
<style>
    .column-Description{
        width:auto!important;
    }
</style>
<div class="wrap">
    <form method="POST">
        <h2>
            Promo Codes
            <a href="?page=promocode_manager/promocodes/edit" class="add-new-h2">Add New</a>
        </h2>
        <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
        <ul class="subsubsub">
            <li class="all"><a href="?page=promocode_manager" class="current">All <span class="count">(<?php echo $total_count;?>)</span></a> |</li>
            <li class="active"><a href="?page=promocode_manager&active=1">Active <span class="count">(<?php echo $active_count;?>)</span></a> |</li>
            <li class="inactive"><a href="?page=promocode_manager&active=0">Inactive <span class="count">(<?php echo $inactive_count;?>)</span></a></li>
        </ul>

        <?php
        $promo->prepare_items();
        $promo->display();
        ?>
    </form>
    <form method="POST">
        <h3>Messages</h3>
        <table class="form-table">
            <tr>
                <th>Success</th>
                <td>
                    <textarea name="messages[dkdGeneralSuccess]" class="large-text code"><?php echo  stripslashes(get_option("dkdGeneralSuccess"));?></textarea>
                </td>
            </tr>
            <tr>
                <th>Failure: Wrong Code</th>
                <td>
                    <textarea name="messages[dkdGeneralWrongCode]" class="large-text code"><?php echo  stripslashes(get_option("dkdGeneralWrongCode"));?></textarea>
                </td>
            </tr>
            <tr>
                <th>Failure: Promo hasn't started</th>
                <td>
                    <textarea name="messages[dkdGeneralTooEarly]" class="large-text code"><?php echo  stripslashes(get_option("dkdGeneralTooEarly"));?></textarea>
                </td>
            </tr>
            <tr>
                <th>Failure: Promo has ended</th>
                <td>
                    <textarea name="messages[dkdGeneralExpired]" class="large-text code"><?php echo  stripslashes(get_option("dkdGeneralExpired"));?></textarea>
                </td>
            </tr>
            <tr>
                <th>Failure: Promo limit reached (all codes used)</th>
                <td>
                    <textarea name="messages[dkdGeneralLimit]" class="large-text code"><?php echo  stripslashes(get_option("dkdGeneralLimit"));?></textarea>
                </td>
            </tr>
            <tr>
                <th>Failure: Promo Inactive</th>
                <td>
                    <textarea name="messages[dkdGeneralInactive]" class="large-text code"><?php echo  stripslashes(get_option("dkdGeneralInactive"));?></textarea>
                </td>
            </tr>
        </table>
        <input type="submit" class="button button-primary" value="Update Messages" />
    </form>
</div>