<?php
//similar to basepromo but tweaked in places to support multiple keys
Abstract class BasePromoCrossObject extends WP_List_Table{
    public $table;//database table
    public $cols_to_show = array();//columns to display on table, will probably get changed
    public $primary_keys;//database table pkey
    public $links;//admin links
    public function __construct($table,$pkeys,$links=array()){
        $this->setTable($table);
        $this->setPrimaryKeys($pkeys);
        $this->links = $links;
        parent::__construct( array(
            'singular'=> 'test', //Singular label
            'plural' => 'test', //plural label, also this well be one of the table css class
            'ajax'	=> false //We won't support Ajax for this table
        ) );
    }
    public function setTable($tablename){
        $this->table = $tablename;
    }
    public function setPrimaryKeys($pkeys){
        foreach($pkeys as $pkey){
            $this->primary_keys[] = $pkey;
        }
    }
    //edit form input fields
    public function outputColFormItem($row,$col,$val){
        if(in_array($col,$this->primary_keys)){
            echo "<input name='".$col."' type='text' id='$col' value='$val' >";
        }
        //generated fields like shortcodes
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
        else if($this->cols_to_show[$col]["type"]=='tinyint'){
            if($val ==1){
                $checked = 'checked="checked"';
            }
            echo "<input name='data[$col]' type='hidden' id='$col' value='0'><input name='data[$col]' type='checkbox' id='$col' value='1' ".$checked.">";
        }
        else{
            echo "<input name='data[$col]' type='text' id='$col' value='$val' class='regular-text code'>";
        }
    }
    //table cell output - deprecated for WPListTable
    public function outputRowItem($row,$col){
        if($this->isDateTimeCol($col)){
            $date = new DateTime($row->$col);
            echo $date->format('m/d/Y');
        }
        else if($this->cols_to_show[$col]["type"]=="tinyint"){
            echo "<input type='checkbox'/>";
        }
        else if($this->cols_to_show[$col]["foreign_key"]){

        }
        else{
            echo $row->$col;
        }
    }
    //gets single record
    public function getByIDs($id_arr){
        global $wpdb;
        foreach($id_arr as $col => $val){
            $where[] = $col." = %d";//ids are always int
            $param[] = $val;
        }
        $where = implode(" AND ",$where);
        $query = "SELECT * FROM ".$this->table." WHERE ".$where;
        return $wpdb->get_results($wpdb->prepare($query,$param),ARRAY_A);
    }
    //gets multiple records
    public function getRows(){
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM ".$this->table);
    }
    //timestamp column
    public function isDateTimeCol($col){
        return in_array($col, array("StartDate","EndDate"));
    }
    public function getAllInactiveCount(){
        global $wpdb;
        $inactive_count_result =  $wpdb->get_results( "SELECT COUNT(*) as count FROM ".$this->table." WHERE Active = 0");
        return $inactive_count_result[0]->count;
    }
    public function getAllActiveCount(){
        global $wpdb;
        $active_count_result =  $wpdb->get_results( "SELECT COUNT(*) as count FROM ".$this->table." WHERE Active = 1");
        return $active_count_result[0]->count;
    }
    public function getAllCount(){
        global $wpdb;
        $query = "SELECT COUNT(*) as count FROM ".$this->table;
        $count_result = $wpdb->get_results($query);
        return $count_result[0]->count;
    }
    //table header/footers
    public function table_controls(){
        echo '<tr>
				<th class="manage-column column-cb check-column">
					<input id="cb-select-all-1" type="checkbox">
				</th>';
        foreach($this->cols_to_show as $db_col_key => $db_col){
            echo '<th>'.$db_col["text"].'</th>';
        }
        echo '</tr>';
    }
    //table bulk delete
    public function deleteByIDs($ids){
        global $wpdb;
        if(!is_array($ids)){
            $ids = array($ids);
        }
        foreach($ids as $id){
            $wpdb->delete($this->table,array($this->primary_key=>$id));
        }
    }

    //WPListTable Overrides
    function no_items() {
        _e( 'No Records found.' );
    }
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
        );
        foreach($this->cols_to_show as $key => $arr){
            if($arr['hideFromTable']){

            }
            else{
                $columns[$key]=$arr['text'];
            }
        }
        return $columns;
    }
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
    function process_bulk_action(){
        if("delete"===$this->current_action() && $_REQUEST[$this->table]){
            $ids_list = ($_REQUEST[$this->table]);
            foreach($ids_list as $ids){
                $pkeys = unserialize(base64_decode($ids));
                $this->delete($pkeys);
            }
        }
    }
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
                $query = "SELECT * FROM ".$table." WHERE ".$col."=".$item->$col;
                $row = $wpdb->get_row($query);
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
    function column_cb($item) {
        $cols = $this->primary_keys;
        $pkeys = array();
        foreach($cols as $col){
            $pkeys[$col]=$item->$col;
        }
        return
            '<input type="checkbox" name="'.$this->table.'[]" value="'. base64_encode(serialize($pkeys)).'" />'//base64+serialize because arrays
        ;
    }
    public function prepare_items(){
        $this->process_bulk_action();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data =$this->getRows();
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

    //submission handling
    public function insert($data){
        global $wpdb;
        $params = $this->generate_params(array_keys($data));
        //print_r($this->table);
        //print_r($data);
        //print_r($params);
        return $wpdb->insert($this->table,$data,$params);
    }
    public function generate_params($cols){
        foreach($cols as $col){
            $ret[] = $this->cols_to_show[$col]['format'];
        }
        return $ret;
    }
    public function delete($pkeys){
        global $wpdb;
        $where = $pkeys;
        foreach($where as $key){
            $where_params[]="%d";//ids always numeric
        }
        return $wpdb->delete($this->table,$where,$where_params);
    }
    public function validateQuery($query_array){
        if(array_key_exists('StartDate',$query_array) && !$query_array['StartDate']){
            return false;
        }
        if(array_key_exists('EndDate',$query_array) && !$query_array['EndDate']){
            return false;
        }
        return true;
    }
    public function update($pkeys,$query){
        global $wpdb;
        if(!$this->validateQuery($query)){
            return false;
        }
        //check if record exists to update, otherwise insert
        if($this->recordExists($pkeys)){
            $where = $pkeys;
            for($i=0;$i<sizeof($pkeys);$i++){
                $where_params[] = "%d";//ids always numeric
            }

            $query_params = $this->generate_params(array_keys($query));
            //print_r($query);
            return $wpdb->update(
                $this->table,
                $query,
                $where,
                $query_params,
                $where_params
            );
        }
        else{
            $insert = array_merge($pkeys,$query);
            return $this->insert($insert);
        }
    }
    public function recordExists($pkeys){
        global $wpdb;
        foreach($pkeys as $col => $val){
            $where[] = $col." = %d";//ids always ints
            $params[] = $val;
        }
        $where = implode(" AND ",$where);
        $query = "SELECT COUNT(*) as count FROM ".$this->table." WHERE ".$where;
        $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
        $count = intval($res["count"]);
        return $count;
    }
}