<?php
//class.promocode_manager.php has the "controller" logic
$isNew = "";

if(isset($_REQUEST['ProductID'])){
    $isNew = false;
}else{
    $isNew = true;
}


global $wpdb;
$promoproduct = PromocodeManager::$db_tables['promoproduct'];

$query2 ="SELECT pp.*
            FROM ".$promoproduct." pp
            WHERE pp.PromoCodeID = ".intval($ids['PromoCodeID']);
$moo = $wpdb->get_results($query2, ARRAY_A );

foreach($moo as $calf) {
    // each column in your row will be accessible like this

    $configuredProducts[] = intval($calf['ProductID']);
}




$product = new Product();
$p_rows = $product->getRows();


foreach($p_rows as $p_row){
    $availableProducts[] = $p_row->ProductID;
}

if(count($availableProducts) <= count($configuredProducts) && $isNew){
    $message = "There are no additional products to add to this promo code.";
}

?>
<div class="wrap">
    <form method="POST">
        <input type="hidden" name="PromoCodeID" value="<?php echo $ids['PromoCodeID'];?>" />
        <p><a href="?page=<?php echo $obj->links['back'];?>&edit=<?php echo $ids['PromoCodeID'];?>">Back</a></p>
        <h2>Edit Promo Product</h2>
        <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
        <?php if(count($availableProducts) > count($configuredProducts && $isNew)){ ?>

            <table class="form-table">
                <tr>
                    <th>
                        <label for="ProductID">ProductID</label>
                    </th>
                    <td>

                        <select name="ProductID" >
                            <?php

                            foreach($p_rows as $p_row):
                                $sel = "";
                                if($p_row->ProductID == $row['ProductID']){
                                    $sel = ' selected="selected" ';
                                }
                                if(($isNew  && !in_array($p_row->ProductID,$configuredProducts) || $sel != "") ){
                                    ?>
                                    <option value="<?php echo $p_row->ProductID;?>"<?php echo $sel;?>><?php echo $p_row->Name;?></option>
                                <?php } ?>
                            <?php endforeach;?>
                        </select>
                        <?php //$obj->outputColFormItem($row,"ProductID",$row["ProductID"]);?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="Name">Name</label>
                    </th>
                    <td>
                        <?php $obj->outputColFormItem($row,"Name",$row["Name"]);?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="Price">Price</label>
                    </th>
                    <td>
                        <?php $obj->outputColFormItem($row,"Price",$row["Price"]);?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="MID">MID</label>
                    </th>
                    <td>
                        <?php $obj->outputColFormItem($row,"MID",$row["MID"]);?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="Disable">Disable</label>
                    </th>
                    <td>
                        <?php $obj->outputColFormItem($row,"Disable",$row["Disable"]);?>
                    </td>
                </tr>
            </table>
            <table class="wp-list-table widefat">
                <thead>
                <tr>
                    <th>
                        Attribute
                    </th>
                    <th>
                        Use Custom
                    </th>
                    <th>
                        Value
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                $product_attr = new ProductAttribute();
                $pa_rows = $product_attr->getRows();
                $p_attr = new PromoProductAttribute();
                $p_attr_res = $p_attr->getByIDs(array("ProductID"=>$ids['ProductID'],"PromoCodeID"=>$ids['PromoCodeID']));
                foreach($p_attr_res as $p_attr_row){
                    $p_attr_rows[$p_attr_row['ProductAttributeID']]=$p_attr_row;
                }
                foreach($pa_rows as $col => $row):
                    $val = "";
                    if( $p_attr_rows[$row->ProductAttributeID]['Value']){
                        $val = $p_attr_rows[$row->ProductAttributeID]['Value'];
                    }
                    if($val && $p_attr->cols_to_show["Value"]['validate']){
                        $val = stripslashes(esc_html($val));
                    }
                    ?>
                    <tr>
                        <td><?php echo $row->Attribute;?></td>
                        <td><input name="tie[<?php echo $row->ProductAttributeID; ?>][Active]" type="hidden" value="0" />
                            <input name="tie[<?php echo $row->ProductAttributeID; ?>][Active]" type="checkbox" value="1" <?php if($p_attr_rows[$row->ProductAttributeID]['Active']){ echo 'checked="checked"';}?> /></td>
                        <td>
                            <input name="tie[<?php echo $row->ProductAttributeID; ?>][Value]" type="text" value="<?php echo $val;?>"/>
                            <?php //echo $pa_rows->;?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
        <?php } ?>
    </form>
</div>
