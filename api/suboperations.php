<?php
//not directly callable but used by the main operations
function validatePromoCode($row,$partnerid=1,$silent=false){
    if($row['Active']){

        $gmtoffset = get_option('gmt_offset');
        $date_now =  strtotime($gmtoffset.' hours', strtotime("now"));
        $promo_startdate = strtotime($row['StartDate']);

        $promo_enddate =strtotime($row['EndDate']);
        $promo_endless = intval($row['NoEndDate']);

        if($date_now >= $promo_startdate){

            if($date_now > $promo_enddate && $promo_endless!==1 ){
                api_error(stripslashes(get_option("dkdGeneralExpired")),$silent);
            }

            if($row['NumberUsed']+1>$row['MaxUses'] || $row['MaxUses'] === 0){
                api_error(stripslashes(get_option("dkdGeneralLimit")),$silent);
            }

            $promoproduct = new PromoProduct();
            $res_count = $promoproduct->getPromoProductCount($row['PromoCodeID'],$partnerid);
            if(!$res_count){

                api_error(stripslashes(get_option("dkdGeneralWrongCode")),$silent);
            }

            return true;
        }
        else{
            api_error(stripslashes(get_option("dkdGeneralTooEarly")),$silent);
        }
    }
    else{
        api_error(stripslashes(get_option("dkdGeneralInactive")),$silent);
    }

}
//gets data to swap with promo prices/data/attributes
function updatePage($promocodeid){
    $promoproduct = new PromoProduct();
    $promoproduct_attr = new PromoProductAttribute();
    $res = $promoproduct->getByIDs(array("PromoCodeID"=>$promocodeid));
    $attr_res = $promoproduct_attr->getByIDs(array("PromoCodeID"=>$promocodeid));
    //print_r($rows);
    $rows = Array();
    //promoproduct
    foreach($res as $row){
        if(!$row['Disable']){
            $rows[$row['ProductID']]=$row;
        }
    }
    //attributes


        foreach($attr_res as $attr_row){

        if(function_exists("AQSM_ReplaceQSInLinks") && strpos($attr_row['Value'],"http")===0){
            header("X-LinkBefore: ".$attr_row['Value']);
            $attr_row['Value'] = AQSM_ReplaceQSInLinks($attr_row['Value'],$_SESSION['AQSM_TrackingQSVars']['allowedVariables']);
            header("X-LinkAfter: ".$attr_row['Value']);
        }
        $attr_rows[$attr_row['ProductID']][$attr_row['ProductAttributeID']] = $attr_row;
    }
    //attaching the right attributes to the right promoproducts, this is what we're returning
    foreach($rows as $prod_id => $row){
        if($attr_rows[$prod_id]){
            $rows[$prod_id]['Attributes']= $attr_rows[$prod_id];

        }
    }
    //setup output
    $output = array(
        "message"=>stripslashes(get_option('dkdGeneralSuccess')),
        "data"=>$rows,
    );
    api_success($output);
}
function subIncrementNumberUsed($promocodeid,$silent=false){
    $obj = new PromoCode();
    return $obj->incrementNumberUsed($promocodeid,$silent=false);
}
//takes an array and merges or inserts a string message, different from error
function api_success($input,$silent=false){
    $result = array(
        "success"=>true,
        "date"=>date('m/d/Y h:i:s'),
        "message_type"=>"success",
    );
    if(is_array($input)){
        $result = array_merge($result,$input);
    }
    else{
        $result['message']= $input;
    }
    if($silent==false){
        echo json_encode($result);
    }
}
//only accepts a string message, we don't want to accidentally overwrite success = false
function api_error($msg,$silent){

    if(!$msg){
        $msg = "General API error";
    }
    $output = array(
        "success"=>false,
        "date"=>date('m/d/Y h:i:s'),
        "message"=>$msg,
        "message_type"=>"error",
    );
    if($silent==false){
        echo json_encode($output);
        die();
    }

}