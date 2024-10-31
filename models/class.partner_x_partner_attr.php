<?php
class Partner_x_PartnerAttr extends BasePromoCrossObject{
    public $cols_to_show;
    public $primary_keys = array("PartnerID","PartnerAttributeID");
    public static $table_name;
    //set database table
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['partner_x_partnerattr'];
    }
    public function __construct(){
        global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/partners/edit"
        );
        parent::__construct(self::$table_name,$this->primary_keys,$links);
        //config to power the wptables/promocode functionalities
        $this->cols_to_show = array(
            "PartnerID"=>array(
                "type"=>"int",//general type
                "text"=>"Product ID",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "PartnerAttributeID"=>array(
                "type"=>"text",//general type
                "text"=>"Product Attribute ID",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "Value"=>array(
                "type"=>"text",//general type
                "text"=>"Value",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
        );
    }
}
Partner_x_PartnerAttr::init();//do NOT delete