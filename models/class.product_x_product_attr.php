<?php
class Product_x_ProductAttr extends BasePromoCrossObject{
    public $cols_to_show;
    public $primary_keys = array("Product","ProductAttributeID");
    public static $table_name;
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['product_x_productattr'];
    }
    public function __construct(){
        global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/promoproducts/edit"
        );
        parent::__construct(self::$table_name,$this->primary_keys,$links);
        $this->cols_to_show = array(
            "ProductID"=>array(
                "type"=>"int",//general type
                "text"=>"Product ID",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "ProductAttributeID"=>array(
                "type"=>"text",//general type
                "text"=>"Product Attribute ID",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "Value"=>array(
                "type"=>"text",//general type
                "text"=>"Value",//admin panel display text
                "format"=>"%s",//db insert/update format
                "validate"=>array("esc_html")
            ),
        );
    }
}
Product_x_ProductAttr::init();//do NOT delete