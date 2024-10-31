<?php
//generic edit page, because many classes COULD call this we're keeping the controller logic in the view (rather than copy+paste this into every controller call that calls this page)
if(class_exists($class)):
    $obj = new $class();
    $thisIsTrue = 0;
    //delete

    if($_POST['data']['thisisnew']==1 && $res['success']){
        $message = "Record Created";
        header("Location: ?page=".$obj->links['back']."&message=".$message);//redirect out of edit
    }

    if($_REQUEST['delete']){
        $id= intval($_REQUEST['delete']);
        $res = $obj->delete($id);
        $message_level = $res["success"]?"updated":"error";
        $message = urlencode($res['message']);
        header("Location: ?page=".$obj->links['back']."&message=".$message."&message_level=".$message_level);//redirect out of edit
    }

    //get id
    if($_REQUEST['edit']){
        $id= intval($_REQUEST['edit']);
    }
    elseif($_POST[$obj->primary_key]){
        $id=$_POST[$obj->primary_key];
    }

    //handle insert
    if($_POST['data'] && !$_POST[$obj->primary_key]){
        $res = $obj->insert($_POST['data']);
        global $wpdb;
        $id = $wpdb->insert_id;
        $message = $res['message'];
        $message_level = $res['success']?"updated":"error";
        $thisIsTrue = 1;
    }
    //handle updates
    else if($_POST['data'] && $_POST[$obj->primary_key]){
        $obj->update($_POST[$obj->primary_key],$_POST['data']);
        $message = "Record Updated";
    }

    //get data/columns for edit/add
    if($id){//edit
        $row = $obj->getByID($id);
    }
    else{//add
        $cols = array_keys($obj->cols_to_show);
        foreach($cols as $col){
            if($_POST['data']){
                $row[$col]=$_POST['data'][$col];
            }
            else{
                $row[$col]="";
            }
        }
    }
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
                        <?php $obj->outputColFormItem($row,$col,$field);?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <input type="hidden" name="thisisnew" id="thisisnew" value="<?php echo $thisIsTrue ?>" />
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>

<?php endif;?>