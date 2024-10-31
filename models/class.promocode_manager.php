<?php
//class for the plugin itself, not to be confused with the Promocode class
class PromocodeManager {
	protected $pcm_db_version = "1.0";
	public $cols_to_show;
    public static $db_tables;
	public function __construct(){
        //moved $db_tables to static variables
	}
    //tables names
    public static function init(){
        global $wpdb;
        self::$db_tables = array(
            "product" => $wpdb->prefix . "dkdProduct",
            "productattr" => $wpdb->prefix . "dkdProductAttribute",
            "productattrtype" => $wpdb->prefix . "dkdProductAttributeType",
            "product_x_productattr" => $wpdb->prefix . "dkdProduct_x_dkdProductAttribute",
            "partner" => $wpdb->prefix . "dkdPartner",
            "promocode" => $wpdb->prefix . "dkdPromoCode",
            "partnerattr" => $wpdb->prefix . "dkdPartnerAttribute",
            "partner_x_partnerattr" => $wpdb->prefix . "dkdPartner_x_dkdPartnerAttribute",
            "promoproduct" => $wpdb->prefix . "dkdPromoProduct",
            "promoproduct_x_productattr" => $wpdb->prefix . "dkdPromoProduct_x_dkdProductAttribute",
        );
    }
    //this function runs on plugin activation
	public static function plugin_activation() {
        //don't activate if not the right wp version
		if ( version_compare( $GLOBALS['wp_version'], PCM__MINIMUM_WP_VERSION, '<' ) ) {
            deactivate_plugins(basename(PCM__PLUGIN_DIR)); // Deactivate ourself
            wp_die("Sorry, but you can't run this plugin, it requires Wordpress ".PCM__MINIMUM_WP_VERSION." or higher.");
            return false;
		}
		global $wpdb;
		$product_tbl = self::$db_tables['product'];
		$productattr_tbl = self::$db_tables['productattr'];
        $productattrtype_tbl = self::$db_tables['productattrtype'];
		$product_x_attr_tbl = self::$db_tables['product_x_productattr'];
		$partner_tbl = self::$db_tables['partner'];
		$promocode_tbl = self::$db_tables['promocode'];
		$partnerattr_tbl = self::$db_tables['partnerattr'];
		$partner_x_partnerattr_tbl = self::$db_tables['partner_x_partnerattr'];
		$promoproduct_tbl = self::$db_tables['promoproduct'];
		$promoproduct_x_productattr_tbl = self::$db_tables['promoproduct_x_productattr'];
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        //create all the tables, dbdelta will modify tables if they already exist
		//dbdelta didn't like it when i created all the tables together, also note the tables in the foreign keys setup
		$sql = "
		CREATE TABLE $product_tbl (
		  ProductID INT NOT NULL AUTO_INCREMENT,
		  ProductCode VARCHAR(255) NOT NULL UNIQUE,
		  ShortCodeExample VARCHAR(255) NULL,
		  Price DECIMAL(19,2) NULL,
		  Active TINYINT NOT NULL DEFAULT '0',
		  MID INT NULL,
		  Name VARCHAR(512) NULL,
		  PRIMARY KEY (ProductID)) ENGINE = INNODB;
		";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );
        $sql ="
		CREATE TABLE $productattrtype_tbl (
		  ProductAttributeTypeID INT NOT NULL AUTO_INCREMENT,
		  Name VARCHAR(255) NOT NULL,
		  AttributeType VARCHAR(255) NOT NULL,
		  PRIMARY KEY (ProductAttributeTypeID)) ENGINE = INNODB;
		";
        dbDelta( $sql );
		$sql ="
		CREATE TABLE $productattr_tbl (
		  ProductAttributeID INT NOT NULL AUTO_INCREMENT,
		  Attribute VARCHAR(255) NULL,
		  ShortCode VARCHAR(255) NOT NULL UNIQUE,
		  ShortCodeExample VARCHAR(255) NULL,
		  Description VARCHAR(1024) NULL,
		  ProductAttributeTypeID INT NOT NULL,
		  PRIMARY KEY (ProductAttributeID),
		  INDEX fk_dkdPartnerAttr_dkdPartnerAttrType_idx (ProductAttributeTypeID ASC),
		  CONSTRAINT fk_dkdPartnerAttr_dkdPartnerAttrType
		    FOREIGN KEY (ProductAttributeTypeID)
		    REFERENCES $productattrtype_tbl (ProductAttributeTypeID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION) ENGINE = INNODB;
		";
		dbDelta( $sql );
		$sql = "
		CREATE TABLE $product_x_attr_tbl (
		  ProductID INT NOT NULL,
		  ProductAttributeID INT NOT NULL,
		  Value VARCHAR(2048) NULL,
		  PRIMARY KEY (ProductID, ProductAttributeID),
		  INDEX fk_dkdProduct_has_dkdProductAttribute_dkdProductAttribute1_idx (ProductAttributeID ASC),
		  INDEX fk_dkdProduct_has_dkdProductAttribute_dkdProduct_idx (ProductID ASC),
		  CONSTRAINT fk_dkdProduct_x_dkdProductAttribute_dkdProduct
		    FOREIGN KEY (ProductID)
		    REFERENCES $product_tbl (ProductID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION,
		  CONSTRAINT fk_dkdProduct_x_dkdProductAttribute_dkdProductAttribute1
		    FOREIGN KEY (ProductAttributeID)
		    REFERENCES $productattr_tbl (ProductAttributeID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION) ENGINE = INNODB;
		";
		dbDelta( $sql );
		$sql = "
		CREATE TABLE $partner_tbl (
		  PartnerID INT NOT NULL AUTO_INCREMENT,
		  ShortCodeExample VARCHAR(255) NULL,
		  Name VARCHAR(255) NOT NULL,
		  logoId INT NOT NULL,
		  Active TINYINT NOT NULL DEFAULT '0',
		  PRIMARY KEY (PartnerID)) ENGINE = INNODB;
		";
		dbDelta( $sql );
		$sql = "
		CREATE TABLE $promocode_tbl (
		  PromoCodeID INT NOT NULL AUTO_INCREMENT,
		  PromoCode VARCHAR(255) NOT NULL UNIQUE,
		  Active TINYINT NOT NULL DEFAULT '0',
		  MaxUses INT NULL,
		  NumberUsed INT NULL,
		  Description VARCHAR(2048) NULL,
		  StartDate DATETIME NOT NULL,
		  EndDate DATETIME NOT NULL,
		  NoEndDate TINYINT NULL,
		  DisplayText TEXT NULL,
		  PartnerID INT NOT NULL,
		  PRIMARY KEY (PromoCodeID),
		  INDEX fk_dkdPromoCode_dkdPartner1_idx (PartnerID ASC),
		  CONSTRAINT fk_dkdPromoCode_dkdPartner1
		    FOREIGN KEY (PartnerID)
		    REFERENCES $partner_tbl (PartnerID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION) ENGINE = INNODB;
		";
		dbDelta( $sql );
		$sql = "
		CREATE TABLE $partnerattr_tbl (
		  PartnerAttributeID INT NOT NULL AUTO_INCREMENT,
		  Attribute VARCHAR(255) NULL,
		  ShortCode VARCHAR(255) NOT NULL UNIQUE,
		  ShortCodeExample VARCHAR(255) NULL,
		  Description VARCHAR(1024) NULL,
		  PRIMARY KEY (PartnerAttributeID)) ENGINE = INNODB;
		";
		dbDelta( $sql );
		$sql = "
		CREATE TABLE $partner_x_partnerattr_tbl (
		  PartnerID INT NOT NULL,
		  PartnerAttributeID INT NOT NULL,
		  Value VARCHAR(2048) NULL,
		  PRIMARY KEY (PartnerID, PartnerAttributeID),
		  INDEX fk_dkdPartner_has_dkdPartnerAttribute_dkdPartnerAttribute1_idx (PartnerAttributeID ASC),
		  INDEX fk_dkdPartner_has_dkdPartnerAttribute_dkdPartner1_idx (PartnerID ASC),
		  CONSTRAINT fk_dkdPartner_has_dkdPartnerAttribute_dkdPartner1
		    FOREIGN KEY (PartnerID)
		    REFERENCES $partner_tbl (PartnerID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION,
		  CONSTRAINT fk_dkdPartner_has_dkdPartnerAttribute_dkdPartnerAttribute1
		    FOREIGN KEY (PartnerAttributeID)
		    REFERENCES $partnerattr_tbl (PartnerAttributeID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION) ENGINE = INNODB;
		";
		dbDelta( $sql );
		$sql = "
		CREATE TABLE $promoproduct_tbl (
		  PromoCodeID INT NOT NULL,
		  ProductID INT NOT NULL,
		  Price DECIMAL(19,2) NULL,
		  MID INT NULL,
		  Disable TINYINT NOT NULL DEFAULT '0',
		  Name VARCHAR(512) NULL,
		  PRIMARY KEY (PromoCodeID, ProductID),
		  INDEX fk_dkdPromoCode_has_dkdProduct_dkdProduct1_idx (ProductID ASC),
		  INDEX fk_dkdPromoCode_has_dkdProduct_dkdPromoCode1_idx (PromoCodeID ASC),
		  CONSTRAINT fk_dkdPromoCode_has_dkdProduct_dkdPromoCode1
		    FOREIGN KEY (PromoCodeID)
		    REFERENCES $promocode_tbl (PromoCodeID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION,
		  CONSTRAINT fk_dkdPromoCode_has_dkdProduct_dkdProduct1
		    FOREIGN KEY (ProductID)
		    REFERENCES $product_tbl (ProductID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION) ENGINE = INNODB;
		";
		dbDelta( $sql );
		$sql = "
		CREATE TABLE $promoproduct_x_productattr_tbl (
		  PromoCodeID INT NOT NULL,
		  ProductID INT NOT NULL,
		  ProductAttributeID INT NOT NULL,
		  Value VARCHAR(2048) NULL,
		  Active TINYINT NOT NULL DEFAULT '0',
		  PRIMARY KEY (PromoCodeID, ProductID, ProductAttributeID),
		  INDEX fk_dkdPromoProduct_has_dkdProductAttribute_dkdProductAttrib_idx (ProductAttributeID ASC),
		  INDEX fk_dkdPromoProduct_has_dkdProductAttribute_dkdPromoProduct1_idx (PromoCodeID ASC, ProductID ASC),
		  CONSTRAINT fk_dkdPromoProduct_has_dkdProductAttribute_dkdPromoProduct1
		    FOREIGN KEY (PromoCodeID , ProductID)
		    REFERENCES $promoproduct_tbl (PromoCodeID , ProductID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION,
		  CONSTRAINT fk_dkdPromoProduct_has_dkdProductAttribute_dkdProductAttribute1
		    FOREIGN KEY (ProductAttributeID)
		    REFERENCES $productattr_tbl (ProductAttributeID)
		    ON DELETE NO ACTION
		    ON UPDATE NO ACTION) ENGINE = INNODB;
		";
		dbDelta( $sql );
        //insert global partner and default records
        $sql = "INSERT INTO $partner_tbl (PartnerID, ShortCodeExample, Name, Active) VALUES
(1, '[partner partnerid=\"1\"]', 'Global', 1);";
        dbDelta( $sql );
        $sql = "INSERT INTO $partnerattr_tbl (PartnerAttributeID, Attribute, ShortCode,ShortCodeExample, Description) VALUES
(1, 'Logo File','logoFile', '[partner partnerid=\"PARTNERID\" attribute=\"logoFile\"]', 'Logo file location');";
        dbDelta( $sql );
        $sql = "INSERT INTO $productattrtype_tbl (ProductAttributeTypeID, Name, AttributeType) VALUES
(1, 'Default', 'value');";
        dbDelta( $sql );
        $sql = "INSERT INTO $productattrtype_tbl (ProductAttributeTypeID, Name, AttributeType) VALUES
(2, 'Link', 'href');";
        dbDelta( $sql );
        $sql = "INSERT INTO $productattr_tbl (ProductAttributeID, Attribute, ShortCode,ShortCodeExample, Description,ProductAttributeTypeID) VALUES
(1, 'Term', 'term','[product productcode=\"PRODUCTCODE\" attribute=\"term\"]', 'Product Terms and Conditions',1);";
        dbDelta( $sql );
        $sql = "INSERT INTO $productattr_tbl (ProductAttributeID, Attribute, ShortCode,ShortCodeExample, Description,ProductAttributeTypeID) VALUES
(2, 'Trial Period', 'trialPeriod','[product productcode=\"PRODUCTCODE\" attribute=\"trialPeriod\"]', 'Product Trial Period',1);";
        dbDelta( $sql );
        $sql = "INSERT INTO $productattr_tbl (ProductAttributeID, Attribute, ShortCode,ShortCodeExample, Description,ProductAttributeTypeID) VALUES
(3, 'Long Description', 'longDesc','[product productcode=\"PRODUCTCODE\" attribute=\"longDesc\"]', 'Product Long Description',1);";
        dbDelta( $sql );
        $sql = "INSERT INTO $productattr_tbl (ProductAttributeID, Attribute, ShortCode,ShortCodeExample, Description,ProductAttributeTypeID) VALUES
(4, 'Full Link', 'fullLink','[product productcode=\"PRODUCTCODE\" attribute=\"fullLink\"]', 'Product Full Link',2);";
        dbDelta( $sql );
	}

    //check if we need to upgrade the db
    public static function plugin_upgrade_check(){
        if(PCM_DB_VERSION!=get_site_option("PCM_DB_VERSION")){
            global $wpdb;

            self::plugin_activation();
            update_option("PCM_DB_VERSION","1.0.1");

            //set_site_option("PCM_DB_VERSION",PCM_DB_VERSION);
        }
    }

    public static function resetdb(){
        global $wpdb;
        $success = true;
        //order matters due to foreign keys
        $wpdb->query('START TRANSACTION');
//        $wpdb->query('SET SQL_SAFE_UPDATES=0;');
        self::removedb();

        self::plugin_activation();
        if($success){
            $wpdb->query('COMMIT');
            $res = true;
            $message = "Database has been reset";
        }
        else{
            $wpdb->query('ROLLBACK');
            $res = false;
            $message = "Database could not be reset - ".mysql_error();
        }
        $out = array(
            "success"=>$res,
            "message"=>$message
        );
        return $out;
    }
    public static function removedb(){
        global $wpdb;
        $success = true;
        //order matters due to foreign keys
        $wpdb->query('START TRANSACTION');
        $wpdb->query('SET SQL_SAFE_UPDATES=0;');
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['promoproduct_x_productattr'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['partner_x_partnerattr'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['product_x_productattr'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['promoproduct'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['promocode'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['product'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['partnerattr'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['productattr'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['productattrtype'].";");
        $res[] = $wpdb->query("DROP TABLE ".self::$db_tables['partner'].";");
        while($result= array_shift($res)&&$success){//this needs to be updated, wpdb->query doesn't return anything for drop statments
            if(!$result){
                $success=false;
            }
        }
        if($success){
            $wpdb->query('COMMIT');
        }
        else{
            $wpdb->query('ROLLBACK');
        }
    }

    //add some code here to run on plugin deactivation
	public static function plugin_deactivation( ) {

	}
    public static function exportDataXML(){
        global $wpdb;
        $xml = new SimpleXMLElement('<PromoCodeData/>');
        $tables = PromocodeManager::$db_tables;
        foreach($tables as $key=>$table){
            $table_node = $xml->addChild($key."s");
            $query = "SELECT * FROM $table";
            $res = $wpdb->get_results($query,ARRAY_A);
            //print_r($res);
            foreach($res as $idx=>$row){
                $row_node = $table_node->addChild($key);
                foreach($row as $col=>$val){
                    $row_node->addChild($col,$val);
                }
            }
        }
        return $xml;
    }
    //add code here to run on plugin uninstall
    public static function plugin_uninstall(){
        //if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
        //    exit();
        self::removedb();//remove promocode tables
        delete_option('dkdGeneralSuccess');
        delete_option('dkdGeneralWrongCode');
        delete_option('dkdGeneralTooEarly');
        delete_option('dkdGeneralExpired');
        delete_option('dkdGeneralLimit');
        delete_option('dkdGeneralInactive');
        delete_option('dkdAutoIncrement');
        delete_option('dkdToken');
        delete_site_option("PCM_DB_VERSION");
        remove_shortcode("partner");
        remove_shortcode("product");
        remove_shortcode("promo");
    }

    //setup the admin pages
    function register_promocode_manager_page(){
        //promocode main page, the other admin pages follow a similar format
        $promocodePage = add_menu_page(
            'Promo Code Manager',
            'Items & Promos',
            'manage_options',
            'promocode_manager',
            function(){
                wp_enqueue_style( 'style-name',PCM__PLUGIN_URL .'/css/dkd_admin.css' );
                if($_REQUEST['message']){
                    $message= sanitize_text_field($_REQUEST['message']);
                }
                if($_POST['messages']){
                    foreach($_POST['messages'] as $key=>$val){
                        update_option($key, sanitize_text_field($val));
                    }
                    $message="Messages Updated";
                }
                $promo = new Promocode();
                $inactive_count = $promo->getAllInactiveCount();
                $active_count = $promo->getAllActiveCount();
                $total_count = $promo->getAllCount();
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/promocodes.php");
            },
            PCM__PLUGIN_URL."images/icon.png",
            30
        );
        //help for promocode page
        add_action("load-$promocodePage",function(){
            $screen = get_current_screen();
            $screen->add_help_tab( array(
                'id' => 'promo_simple_shortcode',            //unique id for the tab
                'title' => 'Simple Shortcode',      //unique visible title for the tab
                'content' => '<br/>An example shortcode for a promo: [promo]. This will output a general promo code submission link for a user to activate a promotion.',
            ));
            $screen->add_help_tab( array(
                'id' => 'promo_specific_shortcode',            //unique id for the tab
                'title' => 'Specific Promo Shortcode',      //unique visible title for the tab
                'content' => '<br/>An example shortcode for a promo: [promo promocode="PROMOCODE"]. This will output the promo code submission link with the promocode\'s display text.',
            ));
        });

        $productPage = add_submenu_page(
            'promocode_manager',
            'Promo Code Manager | Products',
            'Products',
            'manage_options',
            'promocode_manager/products',
            function(){
                wp_enqueue_style( 'style-name',PCM__PLUGIN_URL .'/css/dkd_admin.css' );
                if($_REQUEST['message']){
                    $message= sanitize_text_field($_REQUEST['message']);
                }
                if($_REQUEST['message_level']){
                    $message_level = sanitize_text_field($_REQUEST['message_level']);
                }
                $product = new Product();
                $inactive_count = $product->getAllInactiveCount();
                $active_count = $product->getAllActiveCount();
                $total_count = $product->getAllCount();
                $p_rows = $product->getRows();
                $product_attr = new ProductAttribute();
                $pa_rows = $product_attr->getRows();
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/products.php");
            }
        );
        add_action("load-$productPage",function(){
            $screen = get_current_screen();
            $screen->add_help_tab( array(
                'id' => 'product_simple_shortcode',            //unique id for the tab
                'title' => 'Product Simple Shortcode',      //unique visible title for the tab
                'content' => '<br/>An example shortcode for a product: [product productcode="PRODUCTCODE"]. This will output the product\'s price. When a successful promo code is processed, this can be overridden to display the promotion price.',
            ));
            $screen->add_help_tab( array(
                'id' => 'product_specific_shortcode',            //unique id for the tab
                'title' => 'Product Field Shortcode',      //unique visible title for the tab
                'content' => '<br/>An example shortcode for a product: [promo productcode="PRODUCTCODE" get="Name"]. This will output the product\'s default field. When a successful promo code is processed, this can be overridden to display the promotion field. The available Product fields are:
                    <ul>
                        <li>Name</li>
                        <li>MID</li>
                        <li>Price</li>
                    </ul>
                ',
            ));
            $screen->add_help_tab( array(
                'id' => 'product_attr_shortcode',            //unique id for the tab
                'title' => 'Product Attribute Shortcode',      //unique visible title for the tab
                'content' => '<br/>An example shortcode for a promo: [promo productcode="PROMOCODE" attribute="ATTRIBUTESHORTCODE"]. This will output the product\'s specfied attribute. When a successful promo code is processed, this can be overridden to display the promoproduct attribute',
            ));
        });

        $partnerPage = add_submenu_page(
            'promocode_manager',
            'Promo Code Manager | Partners',
            'Partners',
            'manage_options',
            'promocode_manager/partners',
            function(){
                wp_enqueue_style( 'style-name',PCM__PLUGIN_URL .'/css/dkd_admin.css' );
                if($_REQUEST['message']){
                    $message= sanitize_text_field($_REQUEST['message']);
                }
                if($_REQUEST['message_level']){
                    $message_level= sanitize_text_field($_REQUEST['message_level']);
                }
                $pmc = new Partner();
                $inactive_count = $pmc->getAllInactiveCount();
                $active_count = $pmc->getAllActiveCount();
                $total_count = $pmc->getAllCount();
                $rows = $pmc->getRows();
                $partner_attr = new PartnerAttribute();
                $pa_rows = $partner_attr->getRows();
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/partners.php");
            }
        );
        add_action("load-$partnerPage",function(){
            $screen = get_current_screen();
            $screen->add_help_tab( array(
                'id' => 'partner_shortcode',            //unique id for the tab
                'title' => 'Simple Shortcode',      //unique visible title for the tab
                'content' => '<br/>An example shortcode for a partner: [partner partnerid="PARTNERID"]. This will output the partner\'s name.',
            ));
            $screen->add_help_tab( array(
                'id' => 'partner_attr_shortcode',            //unique id for the tab
                'title' => 'Specific Promo Shortcode',      //unique visible title for the tab
                'content' => '<br/>An example shortcode for a partner attribute: [partner partnerid="PARTNERID" attribute="ATTRIBUTESHORTCODE"]. This will output the partner\'s specified attribute.',
            ));
        });

        add_submenu_page(
            'promocode_manager',
            'Promo Code Manager | Settings',
            'Settings',
            'manage_options',
            'promocode_manager/settings',
            function(){
                if($_REQUEST['export']){
                    $xml = PromocodeManager::exportDataXML();
                    Header('Content-type: text/xml');
                    echo $xml->asXML();
                    exit();
                }
                if($_REQUEST['message']){
                    $message= sanitize_text_field($_REQUEST['message']);
                }
                if($_POST['action']){
                    if(sanitize_text_field($_POST['action'])=='resetdb'){
                        $res = PromocodeManager::resetdb();
                        $message="Database tables have been reset";
                    }
                }
                elseif($_POST){
                    if(isset($_POST['AutoIncrement'])){
                        $val = $_POST['AutoIncrement']?1:0;
                        update_option("dkdAutoIncrement",$val);
                    }
                    if($_FILES['import']){
                        global $wpdb;
                        try{
                            $xml=simplexml_load_file($_FILES['import']['tmp_name']);
                            if($xml){
                                $tables = PromocodeManager::$db_tables;

                                PromocodeManager::resetdb();
                                foreach($xml->children() as $table)
                                {
                                    $counter=0;
                                    foreach($table->children() as $record){
                                        $ins_array = array();
                                        foreach($record->children() as $el){
                                            $val = (string)$el;
                                            if($val){
                                                $ins_array[$el->getName()] = $val;
                                            }
                                            $input[$record->getName()][$counter][$el->getName()] = (string)$el;
                                            $db_table = $tables[$record->getName()];
                                        }
                                        $wpdb->insert($db_table,$ins_array);
                                        $counter++;
                                    }
                                }
                            }
                            else{
                                $post_error = true;
                                $message = "Import data couldn't be read properly";
                                $message_level="error";
                            }
                        }
                        catch(Exception $e){
                            $post_error = true;
                            $message = "Error importing data";
                            $message_level="error";
                        }
                    }
                    if(!$post_error){
                        $message="Settings Updated";
                    }
                }
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/settings.php");
            }
        );
        add_submenu_page(
            null,//we don't want to show this in the nav
            'Promo Code Manager | Products | Edit',
            null,
            'manage_options',
            'promocode_manager/products/edit',
            function(){
                $title= "Product";
                $obj = new Product();
                //delete
                if($_REQUEST['delete']){
                    $id= intval($_REQUEST['delete']);
                    $res = $obj->delete($id);
                    $message_level = $res['success']?"updated":"error";
                    header("Location: ?page=".$obj->links['back']."&message=".$res['message']."&message_level=".$message_level);//redirect out of edit
                    exit;
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
                    if($res['success']){
                        global $wpdb;
                        $id = $wpdb->insert_id;
                    }
                    $message=$res['message'];
                    $message_level = $res['success']?"updated":"error";
                    header("Location: ?page=".$obj->links['back']."&message=Product Created");//redirect out of edit
                }
                //handle updates
                else if($_POST['data'] && $_POST[$obj->primary_key]){
                    $res = $obj->update($_POST[$obj->primary_key],$_POST['data']);
                    $message="Product Updated";
                }

                if($_POST['tie'] && $id){
                    $pkeys = array(
                        "ProductID"=>$_POST['ProductID'],
                    );
                    $p_x_pa = new Product_x_ProductAttr();
                    foreach($_POST['tie'] as $pattr_id => $val){
                        $pkeys["ProductAttributeID"] = $pattr_id;
                        $p_x_pa->update($pkeys,array('Value'=>$val));
                    }
                    $message="Product Updated";
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
                //get product x product attributes
                $product_attr = new ProductAttribute();
                $pa_rows = $product_attr->getRows();
                $p_x_pa = new Product_x_ProductAttr();
                $p_x_pa_rows = $p_x_pa->getByIDs(array("ProductID"=>$id));
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/products_edit.php");
            }
        );
        add_submenu_page(
            null,//we don't want to show this in the nav
            'Promo Code Manager | Products Attributes | Edit',
            null,
            'manage_options',
            'promocode_manager/product-attributes/edit',
            function(){
                if($_REQUEST['message']){
                    $message= $_REQUEST['message'];
                }
                $title= "Product Attribute";
                $class = "ProductAttribute";//set a class for the generic edit page
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/edit.php");
            }
        );
        add_submenu_page(
            null,//we don't want to show this in the nav
            'Promo Code Manager | Partners | Edit',
            null,
            'manage_options',
            'promocode_manager/partners/edit',
            function(){
                if($_REQUEST['message']){
                    $message= $_REQUEST['message'];
                }
                $obj = new Partner();

                //delete
                if($_REQUEST['delete']){
                    $id= intval($_REQUEST['delete']);
                    $obj->delete($id);
                    header("Location: ?page=".$obj->links['back']."&message=Partner Deleted");//redirect out of edit
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
                    $obj->insert($_POST['data'])!==false;
                    global $wpdb;
                    $id = $wpdb->insert_id;
                    $data =$_POST['data'];
                    $data[$obj->primary_key]=$id;
                    $updated_data = $obj->updateParams($data);//shortcode generation, uses pkey
                    unset($data[$obj->primary_key]);
                    $obj->update($id,$updated_data);
                    $message="Partner Added";
                    header("Location: ?page=".$obj->links['back']."&message=Partner Created");//redirect out of edit
                }
                //handle updates
                else if($_POST['data'] && $_POST[$obj->primary_key]){
                    //print_r($_POST['data']);
                    $obj->update($_POST[$obj->primary_key],$_POST['data']);
                    $message="Partner Updated";
                }

                if($_POST['tie'] && $id){
                    $pkeys = array(
                        "PartnerID"=>$_POST['PartnerID'],
                    );
                    $p_x_pa = new Partner_x_PartnerAttr();
                    foreach($_POST['tie'] as $pattr_id => $val){
                        $pkeys["PartnerAttributeID"] = $pattr_id;
                        $p_x_pa->update($pkeys,array('Value'=>$val));
                    }
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
                //get product x partner attribute data
                $partner_attr = new PartnerAttribute();
                $pa_rows = $partner_attr->getRows();
                $p_x_pa = new Partner_x_PartnerAttr();
                $p_x_pa_rows = $p_x_pa->getByIDs(array("PartnerID"=>$id));
                $title= "Partner";
                $class = "Partner";//set a class for the generic edit page
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/partners_edit.php");
            }
        );
        add_submenu_page(
            null,//we don't want to show this in the nav
            'Promo Code Manager | Products Attributes | Edit',
            null,
            'manage_options',
            'promocode_manager/partner-attributes/edit',
            function(){
                if($_REQUEST['message']){
                    $message= $_REQUEST['message'];
                }
                $title= "Partner Attribute";
                $class = "PartnerAttribute";//set a class for the generic edit page
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/edit.php");
            }
        );
        add_submenu_page(
            null,//we don't want to show this in the nav
            'Promo Code Manager | Promo Codes | Edit',
            null,
            'manage_options',
            'promocode_manager/promocodes/edit',
            function(){
                wp_enqueue_style( 'style-name',PCM__PLUGIN_URL .'/css/dkd_admin.css' );
                if($_REQUEST['message']){
                    $message= $_REQUEST['message'];
                }
                $obj = new Promocode();
                if($_REQUEST['delete']){
                    $id= intval($_REQUEST['delete']);
                    $obj->delete($id);
                    header("Location: ?page=".$obj->links['back']."&message=Record Deleted");
                }
                if($_REQUEST['edit']){
                    $id= intval($_REQUEST['edit']);
                }
                if($_POST['data'] && !$_POST[$obj->primary_key]){
                    //print_r($_POST['data']);
                    $intermediate=$_POST['data'];
                    $intermediate['PromoCode'] =  preg_replace("|[^0-9A-Za-z]|", "", $intermediate['PromoCode']);
                    if(strlen($intermediate['PromoCode'])>16){
                        $intermediate['PromoCode'] = substr($intermediate['PromoCode'],0,16);
                    }
                    $obj->insert($intermediate);
                    global $wpdb;
                    $id = $wpdb->insert_id;
                    $message="Promo Code Added";
                }
                else if($_POST['data'] && $_POST[$obj->primary_key]){
                    $intermediate=$_POST['data'];
                    $intermediate['PromoCode'] =  preg_replace("|[^0-9A-Za-z]|", "", $intermediate['PromoCode']);
                    if(strlen($intermediate['PromoCode'])>16){
                        $intermediate['PromoCode'] = substr($intermediate['PromoCode'],0,16);
                    }
                    $obj->update($_POST[$obj->primary_key],$intermediate);
                    $message="Promo Code Updated";
                }
                if($id){
                    $row = $obj->getByID($id);
                }
                else{
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
                $partners = new Partner();
                $partners_rows = $partners->getRows();
                $class = "Promocode";//set a class for the generic edit page
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/promocodes_edit.php");
            }
        );
        add_submenu_page(
            null,//we don't want to show this in the nav
            'Promo Code Manager | Promo Products | Edit',
            null,
            'manage_options',
            'promocode_manager/promoproducts/edit',
            function(){
                if($_REQUEST['message']){
                    $message= $_REQUEST['message'];
                }
                $obj = new PromoProduct();
                $keys = $obj->primary_keys;

                //get id
                if($_POST["PromoCodeID"] && $_POST["ProductID"]){
                    $ids = array(
                        "PromoCodeID"=>intval($_REQUEST["PromoCodeID"]),
                        "ProductID"=>intval($_REQUEST["ProductID"]),
                    );
                }
                else if($_REQUEST["PromoCodeID"] || $_REQUEST["ProductID"]){
                    $ids = array(
                        "PromoCodeID"=>intval($_REQUEST["PromoCodeID"]),
                        "ProductID"=>intval($_REQUEST["ProductID"]),
                    );
                }
                //delete
                if($_REQUEST['delete'] && $ids){
                    $obj->delete($ids);
                    header("Location: ?page=".$obj->links['back']."&edit=".$ids['PromoCodeID']."&message=Promo Product Deleted");//redirect out of edit
                }
                //print_r($_POST);
                //handle inserts/updates
                if($_POST['data']&& $_POST["PromoCodeID"] && $_POST["ProductID"]){
                    $pkeys = array(
                        "PromoCodeID"=>$_POST['PromoCodeID'],
                        "ProductID"=>$_POST['ProductID']
                    );
                    $obj->update($pkeys,$_POST['data']);
                    header("Location: ?page=".$obj->links['back']."&edit=".$ids['PromoCodeID']);//redirect out of edit

                    $message="Promo Product Updated";
                }
                if($_POST['tie']){
                    $pkeys = array(
                        "PromoCodeID"=>$_POST['PromoCodeID'],
                        "ProductID"=>$_POST['ProductID']
                    );
                    $p_x_pa = new PromoProductAttribute();
                    foreach($_POST['tie'] as $pattr_id => $vals){
                        $pkeys["ProductAttributeID"] = $pattr_id;
                        $p_x_pa->update($pkeys,$vals);
                    }
                    $message="Promo Product Updated";
                }
                //get data/columns for edit/add
                if($ids){//edit
                    $row = $obj->getByIDs($ids);
                    $row = $row[0];
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
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/promoproducts_edit.php");
            }
        );
        add_submenu_page(
            'promocode_manager',
            'Promo Code Manager | Help',
            'Help',
            'manage_options',
            'promocode_manager/help',
            function(){
                wp_enqueue_style( 'style-name',PCM__PLUGIN_URL .'/css/dkd_admin.css' );
                //get view, which has access to all our above variables
                include(PCM__PLUGIN_DIR."/admin/help.php");
            }
        );
    }
}
PromocodeManager::init();//do NOT delete, this sets the static variables