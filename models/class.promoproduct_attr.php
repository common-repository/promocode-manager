<?php
class PromoProductAttribute extends BasePromoCrossObject{
    public $cols_to_show;
    public $primary_keys = array("PromoCodeID","ProductID","AttributeID");
    public static $table_name;
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['promoproduct_x_productattr'];
    }
    public function __construct(){
        global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/promoproducts/edit",
        );
        parent::__construct(self::$table_name,$this->primary_keys,$links);
        $this->cols_to_show = array(
            "PromoCodeID"=>array(
                "type"=>"int",//general type
                "text"=>"Promo Code",//admin panel display text
                "format"=>"%d",//db insert/update format
                "edit_col"=>true
            ),
            "ProductID"=>array(
                "type"=>"int",//general type
                "text"=>"Product ID",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "AttributeID"=>array(
                "type"=>"int",//general type
                "text"=>"Attribute",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "Value"=>array(
                "type"=>"text",//general type
                "text"=>"Override Value",//admin panel display text
                "format"=>"%s",//db insert/update format
                "validate"=>array("esc_html")
            ),
            "Active"=>array(
                "type"=>"tinyint",//general type
                "text"=>"Active",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
        );
    }
    function column_default( $item, $column_name ) {
        $cols = array_keys($this->cols_to_show);
        if(in_array($column_name,$cols)){
            if($this->cols_to_show[$column_name]['edit_col']){

                $pid = $item->ProductID;
                $pcid = $item->PromoCodeID;
                $actions = array(
                    'edit'      => sprintf('<a href="?page=%s&PromoCodeID=%s&ProductID=%s">Edit</a>',$this->links['edit'],$pcid,$pid),
                    'delete'    => sprintf('<a href="?page=%s&delete=%s%s">Delete</a>',$this->links['delete'],$pcid,$pid),
                );
                return sprintf('%1$s %2$s', $item->$column_name, $this->row_actions($actions) );
            }
            return $item->$column_name;
        }
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
}
PromoProductAttribute::init();//do NOT delete