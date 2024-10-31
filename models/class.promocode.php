<?php
class Promocode extends BasePromoObject{
	public $cols_to_show;
	public $primary_key = "PromoCodeID";
    public static $table_name;
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['promocode'];
    }
	public function __construct(){
		global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/promocodes/edit",
            "delete"=>"promocode_manager/promocodes/edit",
            "back"=>"promocode_manager"
        );
        parent::__construct(self::$table_name,$this->primary_key,$links);
        //config to power the wptables/promocode functionalities
        $this->cols_to_show = array(
            "PromoCode"=>array(
                "type"=>"text",//general type
                "text"=>"Promo Code",//admin panel display text
                "format"=>"%s",//db insert/update format
                "edit_col"=>true,//admin panel - (WPTable) have the edit/delete options show up in the column of the table
                "maxlength"=>16,//field max length
                "minlength"=>4//field min length
            ),
            "Description"=>array(
                "type"=>"text",//general type
                "text"=>"Description",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "Name"=>array(//partnername, joined from partner table
                "type"=>"text",//general type
                "text"=>"Partner",//admin panel display text
                "format"=>"%s",//db insert/update format
                "active_dependent"=>array(//promocodes can't work unless the partner is active
                    "table"=>PromocodeManager::$db_tables['partner'],//dkdpartner
                    "col"=>"PartnerID",
                )
            ),
            "StartDate"=>array(
                "type"=>"timestamp",//general type
                "text"=>"Start Date",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "EndDate"=>array(
                "type"=>"timestamp",//general type
                "text"=>"End Date",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "NoEndDate"=>array(
                "type"=>"tinyint",//general type
                "text"=>"No End Date",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "MaxUses"=>array(
                "type"=>"int",//general type
                "text"=>"Limit",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "NumberUsed"=>array(
                "type"=>"int",//general type
                "text"=>"Uses",//admin panel display text
                "format"=>"%d",//db insert/update format
            ),
            "DisplayText"=>array(
                "type"=>"text",//general type
                "text"=>"Display Text",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "Active"=>array(
                "type"=>"tinyint",//general type
                "text"=>"Active",//admin panel display text
                "format"=>"%d",//db insert/update format
            )
        );
    }
    //override baseclass delete
    public function delete($id){
        global $wpdb;
        $where = array($this->primary_key=>$id);
        $where_params = array("%d");//ids always numeric
        $wpdb->delete(PromocodeManager::$db_tables['promoproduct_x_productattr'],$where,$where_params);
        $wpdb->delete(PromocodeManager::$db_tables['promoproduct'],$where,$where_params);
        return $wpdb->delete($this->table,$where,$where_params);
    }
	//overrides base class due to join
	public function getRows(){
		global $wpdb;
        $query =  "SELECT c.*,p.Name FROM ".PromocodeManager::$db_tables['promocode']." c,".PromocodeManager::$db_tables['partner']." p WHERE p.PartnerID=c.PartnerID";
		return$wpdb->get_results($query);
	}
    public function getByPromoCode($promocode){
        global $wpdb;
        $query = "SELECT * FROM ".self::$table_name." WHERE PromoCode = '%s'";
        $params = array($promocode);
        return $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
    }
    public function incrementNumberUsed($promocodeid){
        global $wpdb;
        $res = $wpdb->query($wpdb->prepare("UPDATE ".self::$table_name." SET NumberUsed = NumberUsed+1 WHERE PromoCodeID=%d",$promocodeid));
        return $res;
    }
    public static function getIDFromCode($code){
        global $wpdb;
        $params = array($code);
        $query = "SELECT PromoCodeID FROM ".self::$table_name." WHERE PromoCode = '%s'";
        $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
        return $res['PromoCodeID'];
    }
    public static function shortcode($attr=array()){
        global $wpdb;
        extract(shortcode_atts(array(
        ),$attr));
        //try to set session partner if partner not specified
        if($attr&&!$attr['partnerid'] && $_SESSION['dkdpartnerid']){
            $attr['partnerid']=$_SESSION['dkdpartnerid'];
        }
        if($attr&&!$attr['partnerid']){
            $attr['partnerid']=1;
        }

        if($attr&&$attr['promocode'] && $attr['productid'] && $attr['attribute']){
            $table = PromocodeManager::$db_tables['promoproduct'];
            $promo = self::$table_name;
            $promocodeid = self::getIDFromCode($attr['promocode']);
            $attr_table = PromocodeManager::$db_tables['productattr'];
            $x_table = PromocodeManager::$db_tables['promoproduct_x_productattr'];//dkdpromoproduct_x_dkdproductattribute
            $params = array($promocodeid,$attr['productid'],$attr['attribute'],$attr['partnerid']);
            $query = "SELECT x.Value FROM ".$table." a,".$promo." p,".$attr_table." b,".$x_table." x
            WHERE a.PromoCodeID = p.PromoCodeID AND a.PromoCodeID = x.PromoCodeID AND a.ProductID = x.ProductID AND b.ProductAttributeID = x.ProductAttributeID
            AND a.PromoCodeID = '%d' AND a.ProductID = '%d' AND b.ShortCode = '%s' AND (p.PartnerID=1 OR p.PartnerID=%d)";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
            return $res["Value"];
        }
        else if($attr&&$attr['promocode'] && $attr['productid'] ){
            $table = PromocodeManager::$db_tables['promoproduct'];
            $promo = self::$table_name;
            $promocodeid = self::getIDFromCode($attr['promocode']);
            $params = array($promocodeid,$attr['productid'],$attr['partnerid']);
            $query = "SELECT * FROM ".$table." a,".$promo." b WHERE a.PromoCodeID=b.PromoCodeID AND a.PromoCodeID = %d AND a.ProductID = %d AND (b.PartnerID=1 OR b.PartnerID=%d)";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
            return $res['Price'];
        }
        else if($attr&&$attr['promocode']){
            $partner = PromocodeManager::$db_tables['partner'];
            //only markup if results are returned (also checking if partner is active)
            $params = array($attr['promocode']);
            $query = "SELECT * FROM ".self::$table_name." c JOIN ".$partner." p ON c.PartnerID = p.PartnerID WHERE c.PromoCode = '%s' AND p.Active = 1";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
            if($res){
                if(!$res['DisplayText']){
                    $res['DisplayText']="Enter Promo Code";
                }
                $out = '<div class="promocontainer"><p class="dkdmsg">&nbsp;</p><form data-attr="" class="promosubmitform"><input placeholder="'.htmlspecialchars(stripslashes($res["DisplayText"],ENT_QUOTES)).'" class="promocodetext" type="text" name="promocode"> <input class="dkdSubmitBtn" type="submit" value="Apply"/></form><p class="dkdundo">Remove Promo Code</p></div>';
                return $out;
            }
            else{
                echo "<!-- no results found for this promo -->";
            }
        }
        else{
            $out = '<div class="promocontainer"><span class="dkdspinner"><img src="'.PCM__PLUGIN_URL.'/images/ajax-loader.gif"></span><p class="dkdmsg">&nbsp;</p><form data-attr="" class="promosubmitform"><input placeholder="Enter Promo Code" class="promocodetext" type="text" name="promocode"><input class="dkdSubmitBtn" type="submit" value="Apply"/></form><p class="dkdundo">Remove Promo Code</p></div>';
            return $out;
        }
    }
}
Promocode::init();//do NOT delete