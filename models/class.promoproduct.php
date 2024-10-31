<?php
class PromoProduct extends BasePromoCrossObject{
    public $cols_to_show;
    public $primary_keys = array("PromoCodeID","ProductID");
    public static $table_name = "dkdPromoProduct";
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['promoproduct'];
    }
    public function __construct(){
        global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/promoproducts/edit",
            "delete"=>"promocode_manager/promoproducts/edit",
            "back"=>"promocode_manager/promocodes/edit"
        );
        parent::__construct(self::$table_name,$this->primary_keys,$links);
        $this->cols_to_show = array(
            "PromoCodeID"=>array(
                "type"=>"text",//general type
                "text"=>"PromoCodeID",//admin panel display text
                "format"=>"%d",//db insert/update format
                "hideFromTable"=>true//hide this column form WPListTables
            ),
            "ProductID"=>array(
                "type"=>"text",//general type
                "text"=>"Product ID",//admin panel display text
                "format"=>"%d",//db insert/update format
                "hideFromTable"=>true
            ),
            "ProductCode"=>array(
                "type"=>"text",//general type
                "text"=>"Product Code",//admin panel display text
                "format"=>"%s",//db insert/update format
                "active_dependent"=>array(
                    "table"=>"dkdProduct",
                    "col"=>"ProductID",
                ),
                "edit_col"=>true
            ),
            "Name"=>array(
                "type"=>"text",//general type
                "text"=>"Name",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "Price"=>array(
                "type"=>"text",//general type
                "text"=>"Price",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "MID"=>array(
                "type"=>"text",//general type
                "text"=>"MID",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "Attributes"=>array(
                "type"=>"text",//general type
                "source"=>"getPromoProductAttributes",//function to call to get attributes
                "source_col"=>"Attribute",//we want attribute names
                "text"=>"Other Attributes Changed",
                "format"=>"%s"//db insert/update format
            ),
            "Disable"=>array(
                "type"=>"tinyint",//general type
                "text"=>"Disabled",//admin panel display text
                "format"=>"%d",//db insert/update format
            )
        );
    }
    public function getPromoProductCount($id,$partnerid){
        global $wpdb;
        $promocode_table = PromocodeManager::$db_tables['promocode'];
        $params = array($id,$partnerid);
        $query ="SELECT COUNT(*) FROM ".$this->table." a LEFT JOIN ".$promocode_table." b ON a.PromoCodeID = b.PromoCodeID
        WHERE a.PromoCodeID = %d AND a.Disable = 0 AND (b.PartnerID=1 OR b.PartnerID=%d)";
        $count = $wpdb->get_var( $wpdb->prepare($query,$params) );
        return $count;
    }
    //override baseclass delet
    public function delete($ids){
        global $wpdb;
        $where = $ids;
        foreach($ids as $id){
            $where_params[]="%d";//ids always numeric
        }
        $wpdb->delete(PromocodeManager::$db_tables['promoproduct_x_productattr'],$where,$where_params);
        return $wpdb->delete($this->table,$where,$where_params);
    }
    public function getPromoProductAttributes($pc_id,$prod_id){
        global $wpdb;
        $promoproduct_x_productattr = PromocodeManager::$db_tables['promoproduct_x_productattr'];
        $productattr = PromocodeManager::$db_tables['productattr'];
        $params = array($pc_id,$prod_id);
        $query = "SELECT pp_pa.*,pa.Attribute FROM ".$promoproduct_x_productattr." pp_pa,".$productattr." pa
        WHERE pp_pa.ProductAttributeID = pa.ProductAttributeID AND pp_pa.Active=1 AND
        PromoCodeID = %d AND ProductID = %d";
        return $wpdb->get_results($wpdb->prepare($query,$params));
    }
    public function getRows($where=""){
        global $wpdb;
        $promocode = PromocodeManager::$db_tables['promocode'];
        $product = PromocodeManager::$db_tables['product'];
        $query ="SELECT pp.*,pc.PromoCode,p.ProductCode,p.Name FROM ".self::$table_name." pp,".$promocode." pc, ".$product." p WHERE pp.PromoCodeID =pc.PromoCodeID AND p.ProductID = pp.ProductID ".$where;
        //echo $query;
        return $wpdb->get_results( $query);
    }
    public function prepare_items($where=""){
        $this->process_bulk_action();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data =$this->getRows($where);
        $per_page = 100;
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $this->found_data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page                     //WE have to determine how many items to show on a page
        ) );
        $this->items = $this->found_data;
    }
    //base class override
    function column_default( $item, $column_name ) {
        $cols = array_keys($this->cols_to_show);
        if(in_array($column_name,$cols)){
            if($this->cols_to_show[$column_name]['edit_col']){

                $pid = $item->ProductID;
                $pcid = $item->PromoCodeID;
                $actions = array(
                    'edit'      => sprintf('<a href="?page=%s&PromoCodeID=%s&ProductID=%s">Edit</a>',$this->links['edit'],$pcid,$pid),
                    'delete'    => sprintf('<a href="?noheader=true&page=%s&PromoCodeID=%s&ProductID=%s&delete=1">Delete</a>',$this->links['delete'],$pcid,$pid),
                );
                return sprintf('%1$s %2$s', $item->$column_name, $this->row_actions($actions) );
            }
            else if($this->cols_to_show[$column_name]['hideFromTable']){
                return NULL;
            }
            //populate the overrided attributes column
            else if($this->cols_to_show[$column_name]['source']&&$this->cols_to_show[$column_name]['source_col']){
                $call = $this->cols_to_show[$column_name]['source'];
                $source_col = $this->cols_to_show[$column_name]['source_col'];
                $res = $this->$call($item->PromoCodeID,$item->ProductID);
                //print_r($res[0]);
                if($res){
                    foreach($res as $idx=>$source_row){
                        $source_out[] =  $source_row->$source_col;
                    }
                    return implode(",",$source_out);
                }
                else{
                    return "None";
                }
            }
            else if($this->cols_to_show[$column_name]['active_dependent']){
                global $wpdb;
                $table = $this->cols_to_show[$column_name]['active_dependent']['table'];
                $col = $this->cols_to_show[$column_name]['active_dependent']['col'];
                $query = "SELECT * FROM ".self::$table_name." WHERE ".$col."=".$this->cols_to_show[$col]['format'];
                $params = array($item->$col);
                $row = $wpdb->get_row($wpdb->prepare($query,$params));
                if(intval($row->Active)===0){
                    return $item->$column_name."<p class='error'>(Inactive)</p>";
                }
            }
            else if($this->cols_to_show[$column_name]['type']=="tinyint"){
                return $item->$column_name?"Y":"N";
            }
            return $item->$column_name;
        }
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
}
PromoProduct::init();//do NOT delete