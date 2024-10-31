<?php
class ProductAttribute extends BasePromoObject{
	public $cols_to_show;
	public $primary_key = "ProductAttributeID";
    public static $table_name;
    //set database table
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['productattr'];
    }
	public function __construct(){
		global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/product-attributes/edit",
            "delete"=>"promocode_manager/product-attributes/edit",
            "back"=>"promocode_manager/products"
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
                "generatedFromFields"=>array("Attribute"),
                "description"=>"No spaces and changing this value will won't update shortcodes in posts/pages"//admin panel text help
            ),
            "ShortCodeExample"=>array(
                "type"=>"text",//general type
                "text"=>"Shortcode Example",//admin panel display text
                "format"=>"%s",//db insert/update format
                "generated"=>true,
                "generated-fields"=>array(
                    "text"=>"product",
                    "placeholder"=>"PRODUCTCODE",
                    "auto"=>"Attribute",
                ),
                "generated-base"=>"product",
                "generated-cols"=>array(//generates shortcode exmaple, pulling records product and shortcode
                    "ProductCode","ShortCode"
                ),
                "generated-cols-defaults"=>array(
                    "ProductCode"=>"PRODUCTCODE"
                )
            ),
            "Description"=>array(
                "type"=>"text",//general type
                "text"=>"Description",//admin panel display text
                "format"=>"%s",//db insert/update format
            ),
            "ProductAttributeTypeID"=>array(
                "type"=>"AttributeType",//general type
                "text"=>"Type",//admin panel display text
                "format"=>"%s",//db insert/update format
            )
        );
    }
    //override basepromoobj - delete records from other tables
    public function delete($id){
        global $wpdb;
        $where = array($this->primary_key=>$id);
        $where_params = array("%d");//ids always numeric
        //we have to delete from these tables due to foreign keys
        $wpdb->delete(PromocodeManager::$db_tables['promoproduct_x_productattr'],$where,$where_params);
        $wpdb->delete(PromocodeManager::$db_tables['product_x_productattr'],$where,$where_params);
        return $wpdb->delete($this->table,$where,$where_params);
    }
    //override basepromoobj - wplisttable cell values (partner attribute types text output)
    function column_default( $item, $column_name ) {
        $cols = array_keys($this->cols_to_show);
        if(in_array($column_name,$cols)){
            if($this->cols_to_show[$column_name]['edit_col']){
                $pkey = $this->primary_key;
                $actions = array(
                    'edit'      => sprintf('<a href="?page=%s&edit=%s">Edit</a>',$this->links['edit'],$item->$pkey),
                    'delete'    => sprintf('<a href="?noheader=true&page=%s&delete=%s">Delete</a>',$this->links['delete'],$item->$pkey),
                );
                return sprintf('%1$s %2$s', $item->$column_name, $this->row_actions($actions) );
            }
            else if($this->cols_to_show[$column_name]['active_dependent']){
                global $wpdb;
                $table = $this->cols_to_show[$column_name]['active_dependent']['table'];
                $col = $this->cols_to_show[$column_name]['active_dependent']['col'];
                $format = $this->cols_to_show[$col]['format']?$this->cols_to_show[$col]['format']:"%s";
                $query = "SELECT * FROM ".$table." WHERE ".$col."='".$format."'";
                $param = array($item->$col);
                $row = $wpdb->get_row($wpdb->prepare($query,$param));
                if(intval($row->Active)===0){
                    return $item->$column_name."<p class='error'>(Inactive)</p>";
                }
            }
            else if($this->cols_to_show[$column_name]['generated-base']){
                return stripslashes($item->$column_name);
            }
            else if($this->cols_to_show[$column_name]['type']=="tinyint"){
                return $item->$column_name?"Y":"N";
            }
            else if($column_name=="ProductAttributeTypeID"){
                if(!$this->attr_types){
                    $this->getAttributeTypes();
                }
                foreach($this->attr_types as $type){
                    if($type['ProductAttributeTypeID']==$item->$column_name){
                        return $type['Name'];
                    }
                }
            }
            return $item->$column_name;
        }
        //return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
    function getAttributeTypes(){
        global $wpdb;
        $query = "SELECT * FROM ".PromocodeManager::$db_tables['productattrtype'];
        $res= $wpdb->get_results($query,ARRAY_A);
        $this->attr_types=$res;
    }
    public function outputColFormItem($row,$col,$val){
        if($this->primary_key == $col){
            echo "$val";
            echo "<input name='".$col."' type='hidden' id='$col' value='$val' >";
        }
        //generated fields like shortcodes
        else if($this->cols_to_show[$col]['generated-base']){
            $base = $this->cols_to_show[$col]['generated-base'];
            foreach($this->cols_to_show[$col]["generated-cols"] as $gen_col){
                $gen_col_val = $row[$gen_col];
                if(!$row[$gen_col] && $this->cols_to_show[$col]["generated-cols-defaults"][$gen_col]){
                    $gen_col_val = $this->cols_to_show[$col]["generated-cols-defaults"][$gen_col];
                }
                $gen_val[] = strtolower($gen_col).'="'.$gen_col_val.'"';
            }
            $gen_val = "[".$base." ".implode(" ",$gen_val)."]";
            echo "<input name='data[$col]' type='hidden'     value='$gen_val' >
            <input  type='text' class='regular-text code generated-field' data-gen='".$col."'  readonly id='$col' value='$gen_val' >";
        }
        //deprecated shortcode
        else if($this->cols_to_show[$col]["generated"]){
            foreach($this->cols_to_show[$col]["generated-fields"] as $type=>$val){
                if($type=="auto"){
                    $gen_val[] = strtolower($row[$val]);
                }
                else{
                    $gen_val[] = $val;
                }
            }
            $gen_val = "[".implode("-",$gen_val)."]";
            echo "<input name='data[$col]' type='hidden'     value='$gen_val' >
            <input  type='text' class='regular-text code generated-field' data-gen='".$col."'  readonly id='$col' value='$gen_val' >";
        }
        //checkboxes
        else if($this->cols_to_show[$col]["type"]=='timestamp'){
            echo "<input class='timepicker' name='data[$col]' type='text' id='$col' value='$val'";
        }
        else if($this->cols_to_show[$col]["type"]=='tinyint'){
            if($val ==1){
                $checked = 'checked="checked"';
            }
            echo "<input name='data[$col]' type='hidden' id='$col' value='0'><input name='data[$col]' type='checkbox' id='$col' value='1' ".$checked.">";
        }
        else if($col=='ProductAttributeTypeID'){
            echo "<select name='data[".$col."]'>";
            if(!$this->attr_types){
                $this->getAttributeTypes();
            }
            foreach($this->attr_types as $type){
                $sel = "";
                if($type['ProductAttributeTypeID']==$val){
                    $sel = " selected='selected' ";
                }
                echo "<option value='".$type['ProductAttributeTypeID']."'".$sel.">".$type['Name']."</option>";
            }
            echo "</select>";
        }
        else{
            $maxlength = "";
            if($this->cols_to_show[$col]['maxlength']){
                $maxlength = ' maxlength="'.$this->cols_to_show[$col]['maxlength'].'" ';
            }
            echo "<input name='data[$col]' type='text' id='$col' value='$val' class='regular-text code'".$maxlength.">";
        }
        if($this->cols_to_show[$col]['description']){
            echo "<p class='description'>".$this->cols_to_show[$col]['description']."</p>";
        }
    }
}
ProductAttribute::init();//do NOT delete