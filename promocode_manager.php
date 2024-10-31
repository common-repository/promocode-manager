<?php
/**
 * @package Promocode Manager
 */
/**
 * Plugin Name: Promocode Manager
 * Description: Promotional Code manager
 * Version: 1.0.3
 * Author: Diego Ruiz
 * Author URI: http://diegruiz.com
 * License: GPLv2 or later
 */
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'This file cannot be called directly';
	exit;
}
if(!session_id()) {
   session_start();
}


//ini_set('display_errors',1);


//define constants
define( 'PCM_VERSION', '1.0.3' );
define( 'PCM_DB_VERSION', '1.0.1' );
define( 'PCM__MINIMUM_WP_VERSION', '3.5.1' );//wp list tables
define( 'PCM__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PCM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
//set db version
//add_site_option('PCM_DB_VERSION',PCM_DB_VERSION);

//activation hooks + upgrade check
register_activation_hook( __FILE__, array( 'PromocodeManager', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'PromocodeManager', 'plugin_deactivation' ) );
register_uninstall_hook(__FILE__,array('PromocodeManager','plugin_uninstall'));
add_action( 'plugins_loaded', array('PromocodeManager','plugin_upgrade_check') );



//we need this to power the tables in the admin panels
if(!function_exists('convert_to_screen')){
    //try to load wordpress version
    if(file_exists(ABSPATH . 'wp-admin/includes/template.php')){
        require_once(ABSPATH . 'wp-admin/includes/template.php');
    }
    //load our back up, if it still doesn't exist
    if(!function_exists('convert_to_screen')){
        require_once(PCM__PLUGIN_DIR . 'includes/template.php');
    }
}
if(!class_exists('WP_List_Table')){
    //try to load wordpress version
	if(file_exists(ABSPATH . 'wp-admin/includes/class-wp-list-table.php')){
		require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	}
    //load our back up, if it still doesn't exist
    if(!class_exists('WP_List_Table')){
		require_once(PCM__PLUGIN_DIR . 'includes/wp-list-table.php');
	}
}
if(!class_exists('WP_Screen')){
    //try to load wordpress version
    if(file_exists(ABSPATH . 'wp-admin/includes/screen.php')){
        require_once(ABSPATH . 'wp-admin/includes/screen.php');
    }
    //load our back up, if it still doesn't exist
    if(!class_exists('WP_Screen')){
        require_once(PCM__PLUGIN_DIR . 'includes/screen.php');
    }
}

//include main classes
require_once( PCM__PLUGIN_DIR . 'models/class.promocode_manager.php' );//this is the key file that sets all the admin pages
require_once( PCM__PLUGIN_DIR . 'models/class.basepromo_obj.php' );//base object that had overrides alot of the wplist table functions
require_once( PCM__PLUGIN_DIR . 'models/class.basepromo_cross_obj.php' );//similar to basepromo but modified for the cross db tables
require_once( PCM__PLUGIN_DIR . 'models/class.promocode.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.promoproduct.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.promoproduct_attr.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.partner.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.partner_attr.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.partner_x_partner_attr.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.product.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.product_attr.php' );
require_once( PCM__PLUGIN_DIR . 'models/class.product_x_product_attr.php' );

//util functions
require_once( PCM__PLUGIN_DIR . 'util/token.php' );//token generation

//setup admin menu
add_action( 'admin_menu', array('PromocodeManager','register_promocode_manager_page' ));

//setup shortcode
add_shortcode('product',array('product','shortcode'));
add_shortcode('productlink',array('product','shortcodelink'));
add_shortcode('partner',array('partner','shortcode'));
add_shortcode('promo',array('promocode','shortcode'));

//add public scripts/styles, powers the promocode swapping
add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );//hook
function theme_name_scripts() {
    $partnerid = $_SESSION['dkdpartnerid']?$_SESSION['dkdpartnerid']:1;//default to global partner if not set
    $parse_url = parse_url(PCM__PLUGIN_URL);
    $relative_url = $parse_url['path']."/";
    //these settings are passed to the js file as a javascript array
    $settings = array(
        "api_url"=>$relative_url."promocode_ajax.php",
        "partnerid"=>$partnerid
    );
    wp_enqueue_style( 'style-name',PCM__PLUGIN_URL .'/css/dkd_promocode_manager.css' );
    wp_register_script('dkd_promocode_manager-js', plugins_url( '/js/dkd_promocode_manager.js', __FILE__),array( 'jquery' ));
    wp_localize_script('dkd_promocode_manager-js', 'settings', $settings);
    wp_enqueue_script( 'dkd_promocode_manager-js');
}

//api callback (setup to go on ajax requests)
add_action( 'wp_ajax_nopriv_api','api_callback');
function api_callback(){
    require("api/api.php");
    die();//otherwise wordpress appends ajax code (0)
}

//messages + settings
//http://codex.wordpress.org/Function_Reference/add_option
add_option('dkdGeneralSuccess','Promo Code Validated');
add_option('dkdGeneralWrongCode','Incorrect Promo Code');
add_option('dkdGeneralTooEarly','Promo has not started yet');
add_option('dkdGeneralExpired','Promo has ended');
add_option('dkdGeneralLimit','Promo Code limit reached');
add_option('dkdGeneralInactive','Promo is not active');
add_option('dkdAutoIncrement',1);//promo autoincrement
add_option('dkdToken',getToken());//generate token

//session for partner id
add_action('init', 'dkdStartSession', 15);

function dkdStartSession(){

    // AQSM Integration

    if(isset($_SESSION['AQSM_TrackingQSVars'])){
        $cookieLife = get_option( 'aqsm-cookie-life' );
        //setcookie("AQSM_ContentQSVars", base64_encode(json_encode($newCookie)), time()+$cookieLife, "/", str_ireplace("https://","",str_ireplace('http://','',get_bloginfo('url'))),false,true);
    }

    // Set PartnerID
    if(!$_SESSION['dkdpartnerid']){$_SESSION['dkdpartnerid']=1;}

    if($_REQUEST['logoId'] && is_numeric($_REQUEST['logoId'])){
        $_SESSION['dkdpartnerid']= Partner::getPartnerIDFromlogoId($_REQUEST['logoId']);
    }

    if($_REQUEST['partnerid'] && is_numeric($_REQUEST['partnerid'])){
        $_SESSION['dkdpartnerid']= Partner::validatePartnerID($_REQUEST['partnerid']);
    }
    elseif($_REQUEST['partner']){
        $_SESSION['dkdpartnerid'] = Partner::getPartnerIDFromName($_REQUEST['partner']);
    }

    if(isset($_SESSION['dkdpartnerid'])){
        header("PartnerID: ".$_SESSION['dkdpartnerid']);
        $logoid= Partner::getlogoIdFromPartnerID($_SESSION['dkdpartnerid']);
        //header("LogoID: ".$logoid);
        if(isset($_SESSION['AQSM_TrackingQSVars'])){
            if($logoid > 0){
            $_COOKIE['AQSM_TrackingQSVars']['allowedVariables']['logoId']=$logoid; // $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']= $promocode;
            $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['logoId']=$logoid; // $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']= $promocode;
            }else{
               unset($_COOKIE['AQSM_TrackingQSVars']['allowedVariables']['logoId']);
               unset($_SESSION['AQSM_TrackingQSVars']['allowedVariables']['logoId']);
            }
            $newCookie['vars']=$_SESSION['AQSM_TrackingQSVars']['allowedVariables'];
            $newCookie['allowedVariablesConfirmedDefaults']=$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults'];
        }

    }


    if(isset($_REQUEST['promocode'])){
        if($_REQUEST['promocode'] != preg_replace("|[^0-9A-Za-z]|", "", $_REQUEST['promocode'])){
            $promocode = null;
        }else{
            $promocode = $_REQUEST['promocode'];
        }
    }

    if($promocode !== null && isset($_SESSION['dkdpartnerid'])){
        require_once( PCM__PLUGIN_DIR . 'api/suboperations.php' );
        $l = strlen($promocode);
        if($l > 3 && $l <= 16){
            $obj = new PromoCode();
            $row = $obj->getByPromoCode($promocode);

            if($row && validatePromoCode($row,$_SESSION['dkdpartnerid'],true)){

                //updatePage($row['PromoCodeID']);
                if(isset($_SESSION['AQSM_TrackingQSVars']) && $row){
                    if($promocode!=""){
                        $_COOKIE['AQSM_TrackingQSVars']['allowedVariables']['mktp']=$promocode; // $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']= $promocode;
                        $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']=$promocode; // $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']= $promocode;
                        $newCookie['vars']=$_SESSION['AQSM_TrackingQSVars']['allowedVariables'];
                        $newCookie['allowedVariablesConfirmedDefaults']=$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults'];
                        $cookieLife = get_option( 'aqsm-cookie-life' );

                    }
                }

                if(get_option('dkdAutoIncrement')==1){
                    subIncrementNumberUsed($row['PromoCodeID'],true);
                }
            }
        }

    }

    if(isset($_SESSION['AQSM_TrackingQSVars'])){
        setcookie("AQSM_ContentQSVars", base64_encode(json_encode($newCookie)), time()+$cookieLife, "/", str_ireplace("https://","",str_ireplace('http://','',get_bloginfo('url'))),false,true);
    }


}

function dkdEndSession(){
    function myEndSession() {
        session_destroy ();
        //@header("X-Nihilism: true");
    }
}
