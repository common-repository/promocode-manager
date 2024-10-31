<?php
Abstract class BasePromoObject extends WP_List_Table{
    public $table;//database table
    public $cols_to_show = array();//columns to display on table, will probably get changed
    public $primary_key;//database table pkey
    public $links;
    public function __construct($table,$pkey,$links=array()){
        $this->setTable($table);
        $this->setPrimaryKey($pkey);
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
    public function setPrimaryKey($pkey){
        $this->primary_key = $pkey;
    }
    //edit form input fields on the admin pages
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
            <input  type='text' class='regular-text code generated-field' data-gen='".$col."'  readonly id='$col' value=\"$gen_val\" >";
        }
        //checkboxes
        else if($this->cols_to_show[$col]["type"]=='timestamp'){
            echo "<input class='timepicker' name='data[$col]' type='text' id='$col' value=\"$val\"";
        }
		else if($this->cols_to_show[$col]["type"]=='tinyint'){
            if($val ==1){
                $checked = 'checked="checked"';
            }
			echo "<input name='data[$col]' type='hidden' id='$col' value='0'><input name='data[$col]' type='checkbox' id='$col' value='1' ".$checked.">";
		}
		else{
            $maxlength = "";
            if($this->cols_to_show[$col]['maxlength']){
                $maxlength = ' maxlength="'.$this->cols_to_show[$col]['maxlength'].'" ';
            }
			echo "<input name='data[$col]' type='text' id='$col' value=\"".(stripslashes($val))."\" class='regular-text code' ".$maxlength.">";
		}
        if($this->cols_to_show[$col]['description']){
            echo "<p class='description'>".$this->cols_to_show[$col]['description']."</p>";
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
        else if($this->cols_to_show[$col]['stripslashes']){
            echo stripslashes($row->col);
        }
		else{
			echo $row->$col;
		}
	}
	//gets single record
	public function getByID($id){
		global $wpdb;
		$query = "SELECT * FROM ".$this->table." WHERE ".$this->primary_key." = %d";
        $param = array($id);
		return $wpdb->get_row($wpdb->prepare($query,$param),ARRAY_A);
	}
	//gets multiple records
	public function getRows(){
		global $wpdb;
        $query = "SELECT * FROM ".$this->table;
		return $wpdb->get_results( $query);
	}
    public function getRowsByActive($active=1){
        global $wpdb;
        $columns = $this->get_columns();
        $query = "SELECT * FROM ".$this->table." WHERE Active=%d";//tinyint
        $param = array($active);
        return $wpdb->get_results( $wpdb->prepare($query,$param));
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
    //WPListTable override - error message
    function no_items() {
        _e( 'No Records found.' );
    }
    //WPListTable override - sets the table header/footer
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
        );
        foreach($this->cols_to_show as $key => $arr){
            $columns[$key]=$arr['text'];
        }
        return $columns;
    }
    //WPListTable override - bulk actions override
    function get_bulk_actions() {
        $args = func_get_args();
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
    //WPListTable override - bulk actions processing override
    function process_bulk_action(){
        if("delete"===$this->current_action() && $_REQUEST[$this->table]){
            $id_list = $_REQUEST[$this->table];
            foreach($id_list as $id){
                $this->delete($id);
            }
        }
    }
    //WPListTable override - sets table cell values
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
            return $item->$column_name;
        }
        //return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
    //WPListTable override - sets the checkbox in the table for bulk actions
    function column_cb($item) {
        $col = $this->primary_key;
        return sprintf(
            '<input type="checkbox" name="'.$this->table.'[]" value="%s" />', $item->$col
        );
    }
    //WPListTable override - gets data before outputting the table
    public function prepare_items(){
        $this->process_bulk_action();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        if(isset($_REQUEST['active']) && $columns['Active']){
            $data = $this->getRowsByActive(intval($_REQUEST['active']));
        }
        else{
            $data = $this->getRows();
        }
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

    //submission handling - update POST data for submission
    public function updateParams($data,$id=null){
        foreach($data as $col=>$val){
            if(!$data[$col] && $this->cols_to_show[$col]['generatedFromFields']){
                $gen_val ="";
                foreach($this->cols_to_show[$col]['generatedFromFields'] as $genField){
                    $gen_val .= strtolower(preg_replace("/[^a-zA-Z]+/","",$data[$genField]));
                }
                $gen_val = lcfirst($gen_val);
                $data[$col] = $gen_val;
            }
            else if( $this->cols_to_show[$col]['generated-base'] &&  $this->cols_to_show[$col]['generated-cols']){
                $gen_val = array();
                $base = $this->cols_to_show[$col]['generated-base'];
                foreach($this->cols_to_show[$col]["generated-cols"] as $gen_col){
                    $gen_col_val = $data[$gen_col];
                    if(!$data[$gen_col] && $this->cols_to_show[$col]["generated-cols-defaults"][$gen_col]){
                        $gen_col_val = $this->cols_to_show[$col]["generated-cols-defaults"][$gen_col];
                    }
                    else if(!$data[$gen_col] && $id[$gen_col]){
                        $gen_col_val = $id[$gen_col_val];
                    }
                    $gen_val[] = strtolower($gen_col).'="'.$gen_col_val.'"';
                }
                $gen_val = "[".$base." ".implode(" ",$gen_val)."]";
                $data[$col] = $gen_val;
            }
        }
        return $data;
    }
    //submission handling - insert function
    public function insert($data){
        global $wpdb;
        $params = $this->generate_params(array_keys($data));
        $data = $this->updateParams($data);
        /*print_r($this->table);
        print_r($data);
        print_r($params);*/
        $res = $wpdb->insert($this->table,$data,$params);
        if($res){
            $msg = "New Record Added";
            //header("Location: ?page=".$obj->links['back']."&message=".$message."&message_level=".$message_level);//redirect out of edit
        }
        else{
            $msg = "Insert Failed - ".mysql_error();
        }
        $out= array(
            "success"=>$res?true:false,
            "message"=>$msg
        );
        return $out;
    }
    //submission handling - get the format (ie. %s,%d)
    public function generate_params($cols){
        foreach($cols as $col){
            $ret[] = $this->cols_to_show[$col]['format'];
        }
        return $ret;
    }
    //submission handling - delete function
    public function delete($id){
        global $wpdb;
        $where = array($this->primary_key=>$id);
        $where_params = array("%d");//ids always numeric
        return $wpdb->delete($this->table,$where,$where_params);
    }
    //submission handling - validation
    public function validateQuery($query_array){
        if(array_key_exists('StartDate',$query_array) && !$query_array['StartDate']){
            return false;
        }
        if(array_key_exists('EndDate',$query_array) && !$query_array['EndDate']){
            return false;
        }

        return true;
    }
    //submission handling - update record
    public function update($id,$query){
        global $wpdb;
        //print_r($query);
        if(!$this->validateQuery($query)){
            return false;
        }
        $query = $this->updateParams($query,$id);
        $where = array($this->primary_key=>$id);
        $where_params = array("%d");//ids always numeric
        $query_params = $this->generate_params(array_keys($query));
        return $wpdb->update(
            $this->table,
            $query,
            $where,
            $query_params,
            $where_params
        );
    }
}