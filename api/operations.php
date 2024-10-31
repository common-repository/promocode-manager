<?php

if(!session_id()) {
    //session_start();
}

$api_operations = array(
    "public"=>array(//does not require api token
        "promocode_submission",
    ),
    "private"=>array(//requires api token
        "incrementNumberUsed",
        "getData"
        //define other operations here to get data, make insertions/updates to database, etc.
    )
);


function promocode_submission($postdata){
    foreach($postdata as $postitem){
        $data[$postitem['name']]=$postitem['value'];
    }
    $promocode = $data['promocode'];
    $partnerid = $data['PartnerID']?intval($data['PartnerID']):1;

    if($promocode != preg_replace("|[^0-9A-Za-z]|", "", $promocode)){
        api_error(stripslashes(get_option("dkdGeneralWrongCode")));

    }

    $l = strlen($promocode);
    if($l < 4 || $l > 16){
        api_error(stripslashes(get_option("dkdGeneralWrongCode")));
    }else{
        $obj = new PromoCode();
        $row = $obj->getByPromoCode($promocode);

        if($row && validatePromoCode($row,$partnerid)){

            if(isset($_SESSION['AQSM_TrackingQSVars']) && $row){
                if($promocode!=""){
                    $_COOKIE['AQSM_TrackingQSVars']['allowedVariables']['mktp']=$promocode; // $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']= $promocode;
                    $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']=$promocode; // $_SESSION['AQSM_TrackingQSVars']['allowedVariables']['mktp']= $promocode;
                    $newCookie['vars']=$_SESSION['AQSM_TrackingQSVars']['allowedVariables'];
                    $newCookie['allowedVariablesConfirmedDefaults']=$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults'];
                    $cookieLife = get_option( 'aqsm-cookie-life' );
                    setcookie("AQSM_ContentQSVars", base64_encode(json_encode($newCookie)), time()+$cookieLife, "/", str_ireplace("https://","",str_ireplace('http://','',get_bloginfo('url'))),false,true);
                }
            }
            updatePage($row['PromoCodeID']);

            if(get_option('dkdAutoIncrement')==1){
                subIncrementNumberUsed($row['PromoCodeID']);
            }
        }
        else{
            api_error(stripslashes(get_option("dkdGeneralWrongCode")));
        }
    }
}




function incrementNumberUsed($data){
    $promocode = $data['PromoCode'];
    $obj = new PromoCode();
    $row = $obj->getByPromoCode($promocode);
    $promocodeid = $row["PromoCodeID"];
    if(subIncrementNumberUsed($promocodeid)){
        $out = array("message"=>"Used field incremented for ".$promocode);
        api_success($out);
    }
    else{
        api_error("Could not increment Used field for ".$promocodeid);
    }
}
function getData($data){
    global $wpdb;
    $tables = explode(",",$data["tables"]);
    foreach($tables as $table){
        try{
            if(in_array($table,PromocodeManager::$db_tables)){
                $query = "SELECT * FROM ".preg_replace("|[^0-9A-Za-z_,]|", "", $table);
                $query_res = $wpdb->get_results($query, ARRAY_A);
                $res[$table] = $query_res;
            }
        }
        catch(Exception $e){
            //skip
        }
    }
    if($tables && $res){
        $output = array(
            "message"=>"Getting Data for the following tables: ".implode(",",$tables),
            "results"=>$res
        );
        api_success($output);
    }
    else if($tables && !$res){
        api_error("No data to return for requested tables: ".implode(",",$tables));
    }
    else{
        api_error("No requested tables");
    }
}
?>
