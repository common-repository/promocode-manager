<?php
//class.promocode_manager.php has the "controller" logic
?>
<div class="wrap">
    <form method="POST">
        <h2>
            Partners
            <a href="?page=promocode_manager/partners/edit" class="add-new-h2">Add New</a>
        </h2>
        <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
        <ul class="subsubsub">
            <li class="all"><a href="?page=promocode_manager/partners" class="current">All <span class="count">(<?php echo $total_count;?>)</span></a> |</li>
            <li class="active"><a href="?page=promocode_manager/partners&active=1">Active <span class="count">(<?php echo $active_count;?>)</span></a> |</li>
            <li class="inactive"><a href="?page=promocode_manager/partners&active=0">Inactive <span class="count">(<?php echo $inactive_count;?>)</span></a></li>
        </ul>
        <?php
        $pmc->prepare_items();
        $pmc->display();
        ?>
    </form>
    <form method="POST">
        <h2>
            Partner Attributes
            <a href="?page=promocode_manager/partner-attributes/edit" class="add-new-h2">Add New</a>
        </h2>
        <?php
        $partner_attr->prepare_items();
        $partner_attr->display();
        ?>
    </form>
</div>