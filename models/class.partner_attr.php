<?php
class PartnerAttribute extends BasePromoObject{
	public $cols_to_show;
	public $primary_key = "PartnerAttributeID";
    public static $table_name;
    //set database table
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables["partnerattr"];
    }
	public function __construct(){
		global $wpdb;
        //links for the edit/delete/back pages
        $links = array(
            "edit"=>"promocode_manager/partner-attributes/edit",
            "delete"=>"promocode_manager/partner-attributes/edit",
            "back"=>"promocode_manager/partners"
        );
        parent::__construct(self::$table_name,$this->primary_key,$links);
        //config to power the wptables/promocode functionalities
        $this->cols_to_show = array(
            "Attribute"=>array(
                "type"=>"text",//general type
                "text"=>"Attribute",//admin panel display text
                "format"=>"%s",//db insert/update format
                "edit_col"=>true//admin panel - (WPTable) have the edit/delete options show up in the column of the table
            ),
            "ShortCode"=>array(
                "type"=>"text",//general type
                "text"=>"Short Code",//admin panel display text
                "format"=>"%s",//db insert/update format
                "generatedFromFields"=>array("Attribute")
            ),
            "ShortCodeExample"=>array(
                "type"=>"text",//general type
                "text"=>"Shortcode",//admin panel display text
                "format"=>"%s",//db insert/update format
                "generated"=>true,
                "generated-fields"=>array(
                    "text"=>"product",
                    "placeholder"=>"PARTNERID",
                    "auto"=>"Attribute",
                ),
                "generated-base"=>"partner",//auto generate example shortcode, partner is the base
                "generated-cols"=>array(//sets the shortcode attribute
                    "PartnerID",//ie. first part: [partner partnerid="PARTNERID"]
                    "ShortCode"//get shortcode value, ie. [partner partnerid="PARTNERID" attribute="LogoFile"]
                ),
                "generated-cols-defaults"=>array(
                    "PartnerID"=>"PARTNERID"//pass 'PARTNERID' string to generate [partner partnerid="PARTNERID"]
                ),
                "validate"=>array("unique")//shortcodes should be unique
            ),
            "Description"=>array(
                "type"=>"text",//general type
                "text"=>"Description",//admin panel display text
                "format"=>"%s",//db insert/update format
            )
        );
    }
    //override basepromo delete function
    public function delete($id){
        global $wpdb;
        $where = array($this->primary_key=>$id);
        $where_params = array("%d");//ids always numeric
        $wpdb->delete(PromocodeManager::$db_tables['partner_x_partnerattr'],$where,$where_params);
        return $wpdb->delete($this->table,$where,$where_params);
    }
}
PartnerAttribute::init();//do NOT delete