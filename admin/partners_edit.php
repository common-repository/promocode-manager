<?php
//class.promocode_manager.php has the "controller" logic
?>
<div class="wrap">
    <form method="POST">
        <p><a href="?page=<?php echo $obj->links['back'];?>">Back</a></p>
        <h2><?php echo $title;?></h2>
        <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
        <table class="form-table">
            <?php foreach($row as $col=>$field): ?>
                <tr>
                    <th>
                        <label for="<?php echo $col;?>"><?php echo $col;?></label>
                    </th>
                    <td>
                        <?php
                        if($col=="Active" && $row['PartnerID']==1){
                            echo "PartnerID 1 cannot be deactivated";
                        }
                        else{
                            $obj->outputColFormItem($row,$col,$field);
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
        //class.promocode_manager.php has the "controller" logic
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
                if($pa_rows):
                foreach($pa_rows as $col => $row):
                    $val = '';
                    foreach($p_x_pa_rows as $pxpa_row){
                        if(intval($pxpa_row['PartnerAttributeID'])==intval($row->PartnerAttributeID)){
                            $val = $pxpa_row['Value'];
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo $row->Attribute;?></td>
                        <td><input type="text" name="tie[<?php echo $row->PartnerAttributeID;?>]" value="<?php echo $val;?>"/><?php ?></td>
                    </tr>
                <?php endforeach;
                else:?>
                    <tr class="no-items"><td class="colspanchange" colspan="2">No Attributes to add</td></tr>
                <?php
                endif;
                ?>
                </tbody>
            </table>
        <?php endif;?>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>
