<?php
wp_enqueue_style('thickbox');
wp_enqueue_script('thickbox');
?>
<div class="wrap">
    <h1>Help</h1>
    <h2>Quick Links</h2>
    <ul>
        <li><a href="#About">About</a></li>
        <li><a href="#Promos">Promos</a></li>
        <li><a href="#Partners">Partners</a></li>
        <li><a href="#Products">Products</a></li>
        <li><a href="#Promos">API</a></li>
        <li><a href="#Extra">Extra</a></li>
    </ul>

    <h2 id="About">About</h2>
    <ul>
        <li>Code Version: <?php echo PCM_VERSION;?></li>
        <li>DB Version: <?php echo PCM_DB_VERSION;?></li>
    </ul>

    <h2 id="Promos">Promos</h2>
    <p>The promo submission box is generated with the following shortcode:</p>
    <table class="widefat wp-list-table">
        <thead>
        <tr>
            <th class="help-1">ShortCode</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>[promo]</td>
            <td>returns the promo submission box</td>
        </tr>
        </tbody>
    </table>
    <p>A successful submission will replace any prices/attributes with the promotion defined equivalents. The promo use counter will also normally go up by one unless turned off in the settings (in which case the API can be used to trigger this action).</p>
    <p>The promo submission can also be activated on page load with the following querystring:</p>
    <table class="wp-list-table widefat">
        <thead>
        <tr>
            <th class="help-1">QueryString</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>?dkdpromocode=PROMOCODE</td>
            <td>Activates the first promo submission box on page load.</td>
        </tr>
        </tbody>
    </table>

    <h2 id="Partners">Partners</h2>
    <p>Promotions can be set to display for only specific partners. Unless otherwise defined, the active partner is global (PartnerID 1). The <b>active partner</b> is set with a query string:</p>
    <table class="wp-list-table widefat">
        <thead>
        <tr>
            <th class="help-1">QueryString</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td>?partnerid=PARTNERID</td>
                <td>Sets the active partner, which can enable/disable partner specific promotions. Once set, this is stored in session.</td>
            </tr>
        </tbody>
    </table>
    <p>Shortcodes for partners:</p>
    <table class="widefat wp-list-table">
        <thead>
        <tr>
            <th class="help-1">ShortCode</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>[partner partnerid="<i>PARTNERID</i>"]</td>
            <td>returns the partner name</td>
        </tr>
        </tbody>
    </table>
    <p>Partner Attributes can also be defined and display with shortcodes:</p>
    <table class="widefat wp-list-table">
        <thead>
        <tr>
            <th class="help-2">ShortCode</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td>[partner partnerid="<i>PARTNERID</i>" attribute="<i>ATTRIBUTESHORTCODE</i>"]</td>
                <td>returns the attribute for the defined partner, regardless of the active partner</td>
            </tr>
            <tr>
                <td>[partner attribute="<i>ATTRIBUTESHORTCODE</i>"]</td>
                <td>returns the attribute for the active partner</td>
            </tr>
        </tbody>
    </table>
    <h3>Some System Behaviours</h3>
    <ul>
        <li>If a partner is not active, any associated promotions and related products are also disabled.</li>
        <li>When a partner is deleted, any associated promotions are defaulted to Global.</li>
    </ul>

    <h2 id="Products">Products</h2>
    <p>Products are the key pieces for the plugin. Once products are defined, their data can be called using shortcodes:</p>
    <table class="widefat wp-list-table">
        <thead>
        <tr>
            <th class="help-2">ShortCode</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>[product productcode="<i>PRODUCTCODE</i>"]</td>
            <td>returns the product's price</td>
        </tr>
        <tr>
            <td>[product productcode="<i>PRODUCTCODE</i>" get="<i>FIELD</i>"]</td>
            <td>returns the requested Product field, these are:
                <ul>
                    <li>Name</li>
                    <li>MID</li>
                    <li>Price</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td>[product productcode="<i>PRODUCTCODE</i>" attribute="<i>ATTRIBUTESHORTCODE</i>"]</td>
            <td>returns the attribute for the requested product</td>
        </tr>
        </tbody>
    </table>
    <p>When defined in the promo record, any overrides will display on successful promo submission.</p>
    
    <h2 id="API">API</h2>
    <p>The promocode manager has a backend api (returns in JSON format) with the following operations:</p>
    <table class="wp-list-table widefat">
        <thead>
            <tr>
                <th>Operation</th>
                <th>Type</th>
                <th>Description</th>
                <th>Sample</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Promocode Submission</td>
                <td>Public</td>
                <td>Validates a promocode and returns related promotional data on success (used by the plugin)</td>
                <td><a href="#TB_inline?width=100&height=100&inlineId=examplePopup1" class="thickbox">Sample</a></td>
            </tr>
            <tr>
                <td>Increment Number Used</td>
                <td>Private</td>
                <td>Increments the 'Used' field for a promo, useful when the autoincrement is disabled and you want to only increment on a successful purchase.</td>
                <td><a href="#TB_inline?width=100&height=100&inlineId=examplePopup2" class="thickbox">Sample</a></td>
            </tr>
            <tr>
                <td>Get Data</td>
                <td>Private</td>
                <td>Takes the input list of tables and outputs all the data, useful for exporting. Tables are currently:
                    <ul>
                        <?php foreach(PromocodeManager::$db_tables as $table): ?>
                            <li><?php echo $table;?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
                <td><a href="#TB_inline?width=800&height=100&inlineId=examplePopup3" class="thickbox">Sample</a></td>
            </tr>
        </tbody>
    </table>
    <p>A private operation should never be exposed publicly and can be accessed by adding the dkdToken=TOKENVALUE parameter defined in the plugin settings.</p>

    <h2 id="Extra">Extra</h2>
    <h3>Shortcodes in PHP</h3>
    <p>Refer to this article: <a target="_blank" href="http://codex.wordpress.org/Function_Reference/do_shortcode">link</a></p>
    <p>Any of the above shortcodes can be called directly in php using the <i>do_shorcode()</i> function. Here's an example:</p>
    <p><?php echo htmlspecialchars("do_shortcode('[partner attribute=\"logoFile\"]');");?></p>
</div>
<div id="examplePopup1" style="display:none">
    <p><i>SITEURL</i>/wp-content/plugins/promocode_manager/promocode_ajax.php?action=api&api_action=promocode_submission&data[0][name]=promocode&data[0][value]=PROMOCODE&data[1][name]=PartnerID&data[1][value]=1</p>
</div>
<div id="examplePopup2" style="display:none">
    <p><i>SITEURL</i>/wp-content/plugins/promocode_manager/promocode_ajax.php?dkdToken=TOKENVALUE&action=api&api_action=incrementNumberUsed&data[PromoCode]=PROMOCODE</p>
</div>
<div id="examplePopup3" style="display:none">
    <p><i>SITEURL</i>/wp-content/plugins/promocode_manager/promocode_ajax.php?dkdToken=TOKENVALUE&action=api&api_action=getData&data[tables]=wp_dkdProduct,wp_dkdProductAttribute</p>
</div>
<script>
    jQuery(document).ready(function(){
        jQuery('.button').click(function(){
            self.parent.tb_remove();
        });
        jQuery('.button-thickbox-resetdata').click(function(){

        });
    });
</script>