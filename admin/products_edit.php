<?php
//class.promocode_manager.php has the "controller" logic

?>
<div class="wrap">
    <form method="POST" id="ProductEdit">
        <p><a href="?page=<?php echo $obj->links['back'];?>">Back</a></p>
        <h2>Products</h2>
        <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
        <table class="form-table">
            <?php foreach($row as $col=>$field): ?>
                <tr>
                    <th>
                        <label for="<?php echo $col;?>"><?php echo $col;?></label>
                    </th>
                    <td>
                        <?php $obj->outputColFormItem($row,$col,$field);?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php

        ?>
        <?php if($id): ?>
            <input type="hidden">
        <table class="wp-list-table widefat">
            <thead>
            <tr>
                <th>
                    Attribute
                </th>
                <th>
                    Value
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($pa_rows as $col => $row):
                $val = '';
                foreach($p_x_pa_rows as $pxpa_row){
                    if(intval($pxpa_row['ProductAttributeID'])==intval($row->ProductAttributeID)){
                        $val = $pxpa_row['Value'];
                        if($p_x_pa->cols_to_show["Value"]['validate']){
                            $val = stripslashes(esc_html($val));
                        }
                    }
                }
                ?>
                <tr>
                    <td><?php echo $row->Attribute;?></td>
                    <td><input type="text" name="tie[<?php echo $row->ProductAttributeID;?>]" value="<?php echo $val;?>"/><?php ?></td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
        <?php endif;?>
        <input type="hidden" name="thisisnew" id="thisisnew" value="<?php echo $thisIsTrue ?>" />

        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>

