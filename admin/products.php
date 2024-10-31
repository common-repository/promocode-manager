<?php
//class.promocode_manager.php has the "controller" logic
?>
<div class="wrap">
<form method="POST">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'];?>"/>
	<h2>
        Products
        <a href="?page=promocode_manager/products/edit" class="add-new-h2">Add New</a>
    </h2>
    <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
	<ul class="subsubsub">
		<li class="all"><a href="?page=promocode_manager/products" class="current">All <span class="count">(<?php echo $total_count;?>)</span></a> |</li>
		<li class="active"><a href="?page=promocode_manager/products&active=1">Active <span class="count">(<?php echo $active_count;?>)</span></a> |</li>
		<li class="inactive"><a href="?page=promocode_manager/products&active=0">Inactive <span class="count">(<?php echo $inactive_count;?>)</span></a></li>
	</ul>
	<?php
    $product->prepare_items();
    $product->display();
    ?>
</form>
<form method="POST">
	<h2>
        Product Attributes
        <a href="?page=promocode_manager/product-attributes/edit" class="add-new-h2">Add New</a>
    </h2>
    <?php
    $product_attr->prepare_items();
    $product_attr->display();
    ?>
</form>
</div>