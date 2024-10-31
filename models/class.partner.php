<?php
class Partner extends BasePromoObject {
    public $cols_to_show;
    public $primary_key = "PartnerID";
    public static $table_name;
    //set database table
    public static function init(){
        self::$table_name = PromocodeManager::$db_tables['partner'];
    }
    public function __construct(){
        global $wpdb;
        $links = array(
            "edit"=>"promocode_manager/partners/edit",
            "back"=>"promocode_manager/partners",
            "delete"=>"promocode_manager/partners/edit"
        );
        parent::__construct(self::$table_name,$this->primary_key,$links);
        //config to power the wptables/promocode functionalities
        $this->cols_to_show = array(
            "PartnerID"=>array(
                "type"=>"int",//general type
                "text"=>"Partner ID",//admin panel display text
                "format"=>"%d",//db insert/update format
                "edit_col"=>true//admin panel - (WPTable) have the edit/delete options show up in the column of the table
            ),
            "ShortCodeExample"=>array(
                "type"=>"text",//general type
                "text"=>"Shortcode Example",//admin panel display text
                "format"=>"%s",//db insert/update format
                "generated"=>true,
                "generated-fields"=>array(
                    "text"=>"partner",
                    "auto"=>"PartnerID",
                ),
                "generated-base"=>"partner",//auto generate example shortcode, partner is the base
                "generated-cols"=>array(//sets the shortcode attributes
                    "PartnerID"//ie. [partner partnerid="1"]
                ),
            ),
            "Name"=>array(
                "type"=>"text",//general type
                "text"=>"Partner Name",//admin panel display text
                "format"=>"%s",//db insert/update format
                "validate"=>array("not-empty")//validation, field shouldn't be empty
            ),
            "Active"=>array(
                "type"=>"tinyint",//general type
                "text"=>"Active",//admin panel display text
                "format"=>"%d",//db insert/update format
            )
        );
    }
    //basepromo override - multiple tables on one page caused conflicts with querystrings
    public function get_pagenum() {
        $pagenum = isset( $_REQUEST['partners_paged'] ) ? absint( $_REQUEST['partners_paged'] ) : 0;

        if( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
            $pagenum = $this->_pagination_args['total_pages'];

        return max( 1, $pagenum );
    }
    //basepromo override - multiple tables on one page caused conflicts with querystrings
    public function pagination( $which ) {
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
            esc_url( remove_query_arg( 'partners_paged', $current_url ) ),
            '&laquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
            'prev-page' . $disable_first,
            esc_attr__( 'Go to the previous page' ),
            esc_url( add_query_arg( 'partners_paged', max( 1, $current-1 ), $current_url ) ),
            '&lsaquo;'
        );

        if ( 'bottom' == $which )
            $html_current_page = $current;
        else
            $html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='partners_paged' value='%s' size='%d' />",
                esc_attr__( 'Current page' ),
                $current,
                strlen( $total_pages )
            );

        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
            'next-page' . $disable_last,
            esc_attr__( 'Go to the next page' ),
            esc_url( add_query_arg( 'partners_paged', min( $total_pages, $current+1 ), $current_url ) ),
            '&rsaquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
            'last-page' . $disable_last,
            esc_attr__( 'Go to the last page' ),
            esc_url( add_query_arg( 'partners_paged', $total_pages, $current_url ) ),
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


    public static function validatePartnerID($id){
        if(id){
            $id = intval($id);
            global $wpdb;
            $query = "SELECT * FROM ".self::$table_name." WHERE PartnerID = '%s' AND Active=1";
            $param = $id;
            $res = $wpdb->get_results( $wpdb->prepare($query,$param));
            if($res){
                return intval($res[0]->PartnerID);
            }else{
                return 1;
            }
        }
        return 1;
    }

    public static function getPartnerIDFromName($name){
        if($name){
            global $wpdb;
            $query = "SELECT * FROM ".self::$table_name." WHERE Name = '%s'";
            $param = $name;
            $res = $wpdb->get_results( $wpdb->prepare($query,$param));
            if($res){
                return intval($res[0]->PartnerID);
            }else{
                return 1;
            }
        }
        return 1;
    }



    public static function getPartnerIDFromlogoId($logoId){
        if($logoId){
            global $wpdb;
            $query = "SELECT * FROM ".self::$table_name." WHERE logoId = '%s'";
            $param = $logoId;
            $res = $wpdb->get_results( $wpdb->prepare($query,$param));
            if($res){
                return intval($res[0]->PartnerID);
            }else{
                return 1;
            }
        }
        return 1;
    }


    public static function getlogoIdFromPartnerID($id){
        if($id){
            global $wpdb;
            $query = "SELECT logoId FROM ".self::$table_name." WHERE PartnerID = '%s' AND Active=1";
            $param = $id;

            $res = $wpdb->get_results( $wpdb->prepare($query,$param));
            if($res){
                return intval($res[0]->logoId);
            }else{
                return 1;
            }
        }
        return 1;
    }



    //override basepromoobj - delete records from other tables
    public function delete($id){
        if($id&&$id!==1){//global cannot be deleted
            global $wpdb;
            $update_data = array($this->primary_key=>1,"Active"=>0);//set partner id to global in any promocodes
            $update_format = array("%d","%d");
            $where = array($this->primary_key=>$id);
            $where_params = array("%d");//ids always numeric
            $wpdb->update(PromocodeManager::$db_tables['promocode'],$update_data,$where,$update_format,$where_params);
            $wpdb->delete(PromocodeManager::$db_tables['partner_x_partnerattr'],$where,$where_params);
            return $wpdb->delete($this->table,$where,$where_params);
        }
        return false;
    }
    //shortcode functions
    public static function shortcode($attr){
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

        //echo partner attribute value
        if($attr&&$attr['partnerid'] && $attr['attribute']){
            $table = self::$table_name;
            $attr_table = PromocodeManager::$db_tables['partnerattr'];//"dkdPartnerAttribute";
            $x_table = PromocodeManager::$db_tables['partner_x_partnerattr'];//"dkdpartner_x_dkdpartnerattribute";
            //we can return any partner attribute as long as that partner is active
            $params = array($attr['partnerid'],$attr['attribute']);
            $query = "SELECT x.Value FROM ".$table." a,".$attr_table." b,".$x_table." x
            WHERE a.PartnerID = x.PartnerID AND b.PartnerAttributeID = x.PartnerAttributeID
            AND a.PartnerID = '%d' AND b.ShortCode = '%s' AND a.Active=1";
            $res = $wpdb->get_row($wpdb->prepare($query,$params),ARRAY_A);
            return $res["Value"];
        }
        //echo partner name
        else if($attr&&$attr['partnerid']){
            $table = self::$table_name;
            //we can return partner name as long as the partner is active
            $query = "SELECT * FROM ".$table." WHERE PartnerID=%d AND Active=1";
            $param = $attr['partnerid'];
            $res = $wpdb->get_row($wpdb->prepare($query,$param),ARRAY_A);
            return $res['Name'];
        }
    }
    //get active partners
    public function getActiveRows(){
        global $wpdb;
        $query = "SELECT * FROM ".self::$table_name." WHERE Active =1";
        return $wpdb->get_results( $query);
    }
}
Partner::init();//do NOT delete