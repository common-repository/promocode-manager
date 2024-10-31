<?php
class Product extends BasePromoObject{
	public $cols_to_show;
	public $primary_key = "ProductID";
    public static $table_name;// = "dkdProduct";
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['product'];
    }
	public function __construct(){
		global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/products/edit",
            "delete"=>"promocode_manager/products/edit",
            "back"=>"promocode_manager/products"
        );
        parent::__construct(self::$table_name,$this->primary_key,$links);
        //config to power the wptables/promocode functionalities
        $this->cols_to_show = array(
            "ProductCode"=>array(
                "type"=>"text",//general type
                "text"=>"Product Code",//admin panel display text
                "format"=>"%s",//db insert/update format
                "stripslashes"=>true,//run stripslashes function
                "edit_col"=>true,//admin panel - (WPTable) have the edit/delete options show up in the column of the table
            ),
            "ShortCodeExample"=>array(
                "type"=>"text",//general type
                "text"=>"Shortcode Example",//admin panel display text
                "format"=>"%s",//db insert/update format
                "generated"=>true,
                "generated-fields"=>array(
                    "text"=>"product",
                    "auto"=>"ProductCode",
                ),
                "generated-base"=>"product",//auto generate example shortcode, product is the base
                "generated-cols"=>array(//sets the shortcode attributes
                    "ProductCode"//ie. [product productcode="ZZZZZ"]
                )
            ),
            "Name"=>array(
                "type"=>"text",//general type
                "text"=>"Product Name",//admin panel display text
                "format"=>"%s"//db insert/update format
            ),
            "Price"=>array(
                "type"=>"decimal",//general type
                "text"=>"Price",//admin panel display text
                "format"=>"%s"//db insert/update format
            ),
            "MID"=>array(
                "type"=>"int",//general type
                "text"=>"MID",//admin panel display text
                "format"=>"%d"//db insert/update format
            ),
            "Active"=>array(
                "type"=>"tinyint",//general type
                "text"=>"Active",//admin panel display text
                "format"=>"%d"//db insert/update format
            )
        );
    }
    //basepromo override - multiple tables on one page caused conflicts with querystrings
    function get_pagenum() {
        $pagenum = isset( $_REQUEST['products_paged'] ) ? absint( $_REQUEST['products_paged'] ) : 0;

        if( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
            $pagenum = $this->_pagination_args['total_pages'];

        return max( 1, $pagenum );
    }
    //basepromo override - multiple tables on one page caused conflicts with querystrings
    function pagination( $which ) {
        if ( empty( $this->_pagination_args ) )
            return;

        extract( $this->_pagination_args, EXTR_SKIP );

        $output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

        $current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

        $page_links = array();

        $disable_first = $disable_last = '';
        if ( $current == 1 )
            $disable_first = ' disabled';
        if ( $current == $total_pages )
            $disable_last = ' disabled';

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
            'first-page' . $disable_first,
            esc_attr__( 'Go to the first page' ),
            esc_url( remove_query_arg( 'products_paged', $current_url ) ),
            '&laquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
            'prev-page' . $disable_first,
            esc_attr__( 'Go to the previous page' ),
            esc_url( add_query_arg( 'products_paged', max( 1, $current-1 ), $current_url ) ),
            '&lsaquo;'
        );

        if ( 'bottom' == $which )
            $html_current_page = $current;
        else
            $html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='products_paged' value='%s' size='%d' />",
                esc_attr__( 'Current page' ),
                $current,
                strlen( $total_pages )
            );

        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
            'next-page' . $disable_last,
            esc_attr__( 'Go to the next page' ),
            esc_url( add_query_arg( 'products_paged', min( $total_pages, $current+1 ), $current_url ) ),
            '&rsaquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
            'last-page' . $disable_last,
            esc_attr__( 'Go to the last page' ),
            esc_url( add_query_arg( 'products_paged', $total_pages, $current_url ) ),
            '&raquo;'
        );

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) )
            $pagination_links_class = ' hide-if-js';
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages )
            $page_class = $total_pages < 2 ? ' one-page' : '';
        else
            $page_class = ' no-pages';

        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }
    //override basepromoobj - delete records from other tables
    public function delete($id){
        global $wpdb;
        $where = array($this->primary_key=>intval($id));
        $where_params = array("%d");//ids always numeric
        $wpdb->delete(PromocodeManager::$db_tables['promoproduct_x_productattr'],$where,$where_params);
        $wpdb->delete(PromocodeManager::$db_tables['promoproduct'],$where,$where_params);
        $wpdb->delete(PromocodeManager::$db_tables['product_x_productattr'],$where,$where_params);
        $res = $wpdb->delete(self::$table_name,$where,$where_params);
        if($res){
            $message="Record Deleted Successfully";
        }
        else{
            $message= "Record could not be deleted - ".mysql_error();
        }
        $out = array(
            "success"=>$res?true:false,
            "message"=>$message
        );
        return $out;
    }

    public static function shortcodelink($attr,$content=null){
        global $wpdb;
        extract(shortcode_atts(array(),$attr));
        $attr['attribute'] = "fullLink";
        if($attr && $attr['productcode']){
            $table = self::$table_name;
            $attr_table = PromocodeManager::$db_tables['productattr'];//dkdproductattribute
            $attrtype_table = PromocodeManager::$db_tables['productattrtype'];//dkdProductAttributeType
            $x_table = PromocodeManager::$db_tables['product_x_productattr'];//dkdproduct_x_productattribute
            $params = array($attr['productcode'],$attr['attribute']);


            //to get attribute, product must be active, value must match an attribute's shortcode, and can output differently based on attributetype
            $query = "SELECT a.ProductID,b.ProductAttributeID,x.Value,b_type.AttributeType FROM ".$table." a,".$attr_table." b,".$x_table." x,".$attrtype_table." b_type
            WHERE a.ProductID = x.ProductID AND b.ProductAttributeID = x.ProductAttributeID AND b.ProductAttributeTypeID = b_type.ProductAttributeTypeID
            AND a.ProductCode = '%s' AND b.ShortCode = '%s' AND a.Active=1";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
            if(function_exists("AQSM_ReplaceQSInLinks") && strpos($res["Value"],"http")===0){
                //header("AQSM: ".json_encode($_SESSION['AQSM_TrackingQSVars']['allowedVariables']));
                $res["Value"] = AQSM_ReplaceQSInLinks($res['Value'],$_SESSION['AQSM_TrackingQSVars']['allowedVariables']);// AQSM_LinkTrackingQSFilter($res["Value"]);
            }

            return "<a class='dkdproductattr ".$attr['class']."' data-attributetype='".$res['AttributeType']."' data-attribute='".$res['ProductAttributeID']."' data-productid='".$res['ProductID']."' href='".($res["Value"])."'>".do_shortcode($content)."</a>";


        }

    }// end shortcodelink

    //shortcode functions
    public static function shortcode($attr){
        global $wpdb; global $q;
        extract(shortcode_atts(array(),$attr));

        //echo product attribute
        if($attr && $attr['productcode'] && $attr['attribute']){
            $table = self::$table_name;
            $attr_table = PromocodeManager::$db_tables['productattr'];//dkdproductattribute
            $attrtype_table = PromocodeManager::$db_tables['productattrtype'];//dkdProductAttributeType
            $x_table = PromocodeManager::$db_tables['product_x_productattr'];//dkdproduct_x_productattribute
            $params = array($attr['productcode'],$attr['attribute']);
            //to get attribute, product must be active, value must match an attribute's shortcode, and can output differently based on attributetype
            $query = "SELECT a.ProductID,b.ProductAttributeID,x.Value,b_type.AttributeType FROM ".$table." a,".$attr_table." b,".$x_table." x,".$attrtype_table." b_type
            WHERE a.ProductID = x.ProductID AND b.ProductAttributeID = x.ProductAttributeID AND b.ProductAttributeTypeID = b_type.ProductAttributeTypeID
            AND a.ProductCode = '%s' AND b.ShortCode = '%s' AND a.Active=1";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);




            //if valueonly is specified, return just the value with extra markup
            if($attr["return"]=='valueonly'){

                if($res['AttributeType']=="href"){
                    return $res["Value"];
                }else{
                    return stripslashes($res["Value"]);
                }

            }
            else if($res['AttributeType']=="href" && $attr["return"]!='valueonly'){
                $text = "Link";//text can be overriden with if specified in shortcode (text)
                if($attr['text']){
                    $text = $attr['text'];
                }
                if(function_exists("AQSM_ReplaceQSInLinks") && strpos($res["Value"],"http")===0){
                    $res["Value"] = AQSM_ReplaceQSInLinks($res['Value'],$_SESSION['AQSM_TrackingQSVars']['allowedVariables']);// AQSM_LinkTrackingQSFilter($res["Value"]);
                }
                return "<a class='dkdproductattr ".$attr['class']."' data-attributetype='".$res['AttributeType']."' data-attribute='".$res['ProductAttributeID']."' data-productid='".$res['ProductID']."' href='".($res["Value"])."'>".$text."</a>";
            }
            else if($res){
                return "<span class='dkdproductattr ".$attr['class']."' data-attributetype='".$res['AttributeType']."' data-attribute='".$res['ProductAttributeID']."' data-productid='".$res['ProductID']."'>".stripslashes($res["Value"])."</span>";
            }
        }
        //echo product column value (price,name,mid)
        else if($attr && $attr['productcode'] && $attr['get']){
            $table = self::$table_name;
            //we can grab any product field as long as that product is active
            $params = array($attr['productcode']);
            $query = "SELECT * FROM ".$table." WHERE ProductCode = '%s' AND Active=1";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
            return $res[$attr['get']];
        }
        //echo product price
        else if($attr&&$attr['productcode']){
            $table = self::$table_name;
            //we can return the price as long as the product is active
            $params = array($attr['productcode']);
            $query = "SELECT * FROM ".$table." WHERE ProductCode = '%s' AND Active=1";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
            if($res['Price']){
                $output = "$".number_format($res['Price'],2);
                return "<span class='dkdproductprice ".$attr['class']."' data-priceprefix='".$attr['priceprefix']."' data-pricesuffix='".$attr['pricesuffix']."'  data-productid='".$res['ProductID']."'><span class='productprice'>".$output."<span class='dkdstrike'></span></span></span><span class='promoprice'></span>";

            }
        }
    }
}
Product::init();//do NOT delete