<?php
require("operations.php");//main callable api operations
require("suboperations.php");//helper functions used by api operations
$api_action = $_REQUEST['api_action'];
if($api_action){
    $data =$_REQUEST['data'];

    //operations for front end use
    if(function_exists($api_action) && in_array($api_action,$api_operations['public'])){
        call_user_func($api_action,$data);
    }
    //operations for backend/internal use
    else if(function_exists($api_action) && in_array($api_action,$api_operations['private'])){
        if($_REQUEST['dkdToken'] && $_REQUEST['dkdToken']===get_option('dkdToken') ){
            call_user_func($api_action,$data);
        }
        else if($_REQUEST['dkdToken']){
            api_error("Incorrect Token Value provided");
        }
        else{
            api_error("Token Value required to use this operation");
        }
    }
    else{
        api_error("Invalid API Action: ".$api_action);
    }
}
else{
    api_error("No API Action Called");
}
?>