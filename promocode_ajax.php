<?php
//bootstrap wordpress
require_once( dirname(dirname(dirname( dirname( __FILE__ )))) . '/wp-load.php' );
if(!session_id()) {
    //session_start();
}

//load the main plugin
require_once('promocode_manager.php' );
//we need an action
if ( empty( $_REQUEST['action'] ) )
    die( '0' );
//some stuff taken from admin-ajax
@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );
//these functions are wp-includes
send_nosniff_header();
nocache_headers();

//we only want to run the non-admin version of this
$allowedActions[]="api";
$allowedActions[]="promocode_submission";

if(in_array($_REQUEST['action'],$allowedActions)){
    do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
}
// Default status
die( '0' );
?>