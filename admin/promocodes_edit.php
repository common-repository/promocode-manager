<?php
//needed for jquery datepicker
wp_enqueue_style('plugin_name-admin-ui-css',
    PCM__PLUGIN_URL.'/css/jquery-ui.css',
    false,
    PCM_VERSION,
    false);
wp_enqueue_script( "pcodeValidation", PCM__PLUGIN_URL."/admin/promocode-validate.js", "jquery", null, true );
//class.promocode_manager.php has the "controller" logic
    ?>
<div class="wrap">
    <form method="POST" id="PromoCodeEdit">
        <?php if($id): ?>
            <input type="hidden" name="PromoCodeID" value="<?php echo $id;?>">
        <?php endif;?>
        <p><a href="?page=<?php echo $obj->links['back'];?>">Back</a></p>
        <h2>Manage Code</h2>
        <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
        <div id="col-container">
            <div id="col-right">
                <div class="col-wrap">
                    <div class="form-wrap">
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="Description"><?php echo $obj->cols_to_show["Description"]['text'];?></label>
                                </th>
                                <td>
                                    <?php $obj->outputColFormItem($row,"Description",$row["Description"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="StartDate"><?php echo $obj->cols_to_show["StartDate"]['text'];?></label>
                                </th>
                                <td>
                                    <?php $obj->outputColFormItem($row,"StartDate",$row["StartDate"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="EndDate"><?php echo $obj->cols_to_show["EndDate"]['text'];?></label>
                                </th>
                                <td>
                                    <?php $obj->outputColFormItem($row,"EndDate",$row["EndDate"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="NoEndDate"><?php echo $obj->cols_to_show["NoEndDate"]['text'];?></label>
                                </th>
                                <td>
                                    <?php $obj->outputColFormItem($row,"NoEndDate",$row["NoEndDate"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="DisplayText"><?php echo $obj->cols_to_show["DisplayText"]['text'];?></label>
                                </th>
                                <td>
                                    <?php
                                    if(!$row["DisplayText"]){
                                        $row["DisplayText"] = "Have a Promo Code?";
                                    }
                                    $obj->outputColFormItem($row,"DisplayText",$row["DisplayText"]);?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div id="col-left">
                <div class="col-wrap">
                    <div class="form-wrap">
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="PromoCode"><?php echo $obj->cols_to_show["PromoCode"]['text'];?></label>
                                </th>
                                <td class="zookeeper">
                                    <?php $obj->outputColFormItem($row,"PromoCode",$row["PromoCode"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="Active"><?php echo $obj->cols_to_show["Active"]['text'];?></label>
                                </th>
                                <td>
                                    <?php $obj->outputColFormItem($row,"Active",$row["Active"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="MaxUses"><?php echo $obj->cols_to_show["MaxUses"]['text'];?></label>
                                </th>
                                <td>
                                    <?php $obj->outputColFormItem($row,"MaxUses",$row["MaxUses"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="NumberUsed"><?php echo $obj->cols_to_show["NumberUsed"]['text'];?></label>
                                </th>
                                <td>
                                    <?php $obj->outputColFormItem($row,"NumberUsed",$row["NumberUsed"]);?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="PartnerID">Partner ID<?php echo $obj->cols_to_show["Partner"]['text'];?></label>
                                </th>
                                <td>
                                    <select name="data[PartnerID]">
                                        <?php foreach($partners_rows as $partner_row):
                                            $sel = '';
                                            if($partner_row->PartnerID == $row['PartnerID']){
                                                $sel = ' selected="selected" ';
                                            }
                                            ?>
                                            <option value="<?php echo $partner_row->PartnerID;?>"<?php echo $sel;?>><?php echo $partner_row->Name;?></option>
                                        <?php endforeach;?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
    <?php
        $products = new Product();
        $rows = $products->getRows();
    if($id && $rows): ?>
    <form method="POST">
        <h2>
            Promo Products
            <a href="?page=promocode_manager/promoproducts/edit&PromoCodeID=<?php echo $id;?>" class="add-new-h2">Add New</a>
        </h2>
        <?php
        if($rows){
            $promoproduct = new PromoProduct();
            $promoproduct->prepare_items(" AND pp.PromoCodeID=$id");
            $promoproduct->display();
        }
        ?>
    </form>
    <?php elseif($id && !$rows): ?>
    <h2>Promo Products</h2>
    <p>No products in the system to add.</p>
    <?php endif; ?>
</div>
<?php
wp_enqueue_script('jquery-ui-datepicker');

?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.timepicker').datepicker({
            dateFormat : 'yy-mm-dd 00:00:00'
        });
        jQuery(document).on("submit","#PromoCodeEdit",function(e){
            <?php if($obj->cols_to_show['PromoCode']['minlength']):
            $minlength = $obj->cols_to_show['PromoCode']['minlength'];
            ?>
            if(jQuery('#PromoCode').val().length<<?php echo $minlength;?>){
                e.preventDefault();
                jQuery(document).scrollTop( jQuery("#message").offset().top-35 );//admin bar is about 32px
                jQuery("#message").html("<p>Promo Code must be at least <?php echo $minlength;?> characters</p>");
                jQuery("#message").addClass("error");
            }
            <?php endif; ?>
        });
    });
</script>
