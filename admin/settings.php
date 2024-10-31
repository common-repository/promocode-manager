<?php
wp_enqueue_style('thickbox');
wp_enqueue_script('thickbox');
$autoincrement = get_option("dkdAutoIncrement");
?>
<div class="wrap">
    <form method="POST" enctype="multipart/form-data">
        <h2>Promo Code Manager Settings</h2>
        <?php include(PCM__PLUGIN_DIR."/admin/partials/message_block.php");?>
        <table class="form-table">
            <tr>
                <th>
                    <label for="Reset">Reset Tables</label>
                </th>
                <td>
                    <a href="#TB_inline?width=100&height=100&inlineId=examplePopup1" id="Reset" class="thickbox dkd-reset-db button" >Reset</a>
                    <p class="description">This will clear all database tables and is NOT reversible</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="AutoIncrement">Auto increment promo uses</label>
                </th>
                <td>
                    <input type="hidden" name="AutoIncrement" value="0"/>
                    <input type="checkbox" name="AutoIncrement" id="AutoIncrement" value="1" <?php if($autoincrement){ echo "checked='checked'";}?>/>
                    <p class="description">The use counter for a promotion goes up when a promo code is submitted by a user (if this is off, use the API).</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="Token">API Token</label>
                </th>
                <td>
                    <input type="text" name="Token" id="Token" value="<?php echo get_option('dkdToken');?>"/>
                    <p class="description">This is NOT a public token, use this to access private defined functions in the api.</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="Import">Import XML</label>
                </th>
                <td>
                    <input type="file" name="import" id="Import" value="Upload"/>
                    <p>Import a backup promocode manager XML file.</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="Export">Export XML</label>
                </th>
                <td>
                    <a href="?page=promocode_manager/settings&export=dkd&noheader=true" download="promocode_manager_export.xml">Export</a>
                    <p>Export a backup promocode manager XML file.</p>
                </td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
    <div id="examplePopup1" style="display:none">
        <form method="POST">
            <p>Are you absolutely sure?</p>
            <input type="hidden" name="action" value="resetdb"/>
            <input type="submit" class="button button-secondary button-thickbox-resetdata" value="Reset the data"/>
            <a href="#" class="button button-cancel button-thickbox-cancel">Cancel</a>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function(){
        jQuery('.button').click(function(){
            self.parent.tb_remove();
        });
    });
</script>