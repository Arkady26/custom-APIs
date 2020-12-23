<?php
/*

this is the formate for the api call. 
Put file in API Root on Server

Only allow POST Variable
unset all other global variables
parameter [view] api_name
parameter [q] JSON data only
*/

require_once("SimpleRest.php");
unset($_REQUEST);
unset($_GET);
$view = "reject";
//unset $_SERVER;
if (isset($_GET["view"])) {
    exit();
} else {
}

if (isset($_POST["view"])) {
    $view      = $_POST["view"];
    //$view = strip_tags($view);
    //$view = stripslashes($view);
    $post_data = $_POST["q"];
} else {
}

require('global_functions.php');
function get_function_registration($post_data)
{
    
    // clean array
    $clean_data = clean_input($post_data);
    if ($clean_data == false) {
        $function_output["status"] = 404;
        $function_output["output"] = 404;
        return $function_output;
    }
    $conn = conn();
    
    $check = "Denied";
    $val   = 8;
    
    // validate the user inputs //
    if (empty($clean_data["appheader"]["datablock"]["registration"]["name"])) {
        $val--;
    }
    
    if (filter_var($clean_data["appheader"]["datablock"]["registration"]["Email"], FILTER_VALIDATE_EMAIL)) {
    } else {
        $val--;
    }
    if (empty($clean_data["appheader"]["datablock"]["registration"]["mobilenumber"])) {
        $val--;
    }
    if (empty($clean_data["appheader"]["datablock"]["registration"]["dob"])) {
        $val--;
    }
    if (empty($clean_data["appheader"]["datablock"]["registration"]["password"])) {
        $val--;
    }
    
    if (empty($clean_data["appheader"]["datablock"]["registration"]["profileimage"])) {
        $val--;
    }
    if (is_int($clean_data["appheader"]["datablock"]["registration"]["distance"]) === true) {
    } else {
        $val--;
    }
    if (is_int($clean_data["appheader"]["datablock"]["registration"]["time"]) === true) {
    } else {
        $val--;
    }
    if (empty($clean_data["appheader"]["datablock"]["registration"]["profileimage"])) {
        $val--;
    }
    if (is_bool($clean_data["appheader"]["datablock"]["registration"]["terms"]) === true) {
    } else {
        $val--;
    }
    if (is_bool($clean_data["appheader"]["datablock"]["registration"]["playstoreterms"]) === true) {
    } else {
        $val--;
    }
    if (is_bool($clean_data["appheader"]["datablock"]["registration"]["locationpermission"]) === true) {
    } else {
        $val--;
    }
    if (is_bool($clean_data["appheader"]["datablock"]["registration"]["memorypermission"]) === true) {
    } else {
        $val--;
    }
    
    if ($val == 8) {
    } else {
        $function_output["status"] = 404;
        $function_output["output"] = 404;
        return $function_output;
    }
    
    $clean_data["appheader"]["datablock"]["registration"]["password"] = sodium_crypto_pwhash_str($clean_data["Appheader"]["Datablock"]["Registration"]["Password"], SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);
    
    //Generate keypair//
    $keypair    = sodium_crypto_box_keypair();
    $public_key = sodium_crypto_box_publickey($keypair);
    
    //Generate Key (this is the permenant userid)
    $Key = sodium_crypto_pwhash_str(openssl_random_pseudo_bytes(265, $cstrong) . date("U"), SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);
    
    //store new user profile//
    //INSERT//
    $field_array        = $clean_data["appheader"]["datablock"]["registration"];
    $field_array["key"] = $Key;
    $table              = "setting";
    
    $sql_opd = build_sql_insert($table, $field_array);
    
    if (mysqli_query($conn, $sql_opd)) {
    } else {
        $function_output["status"] = 404;
        $function_output["output"] = 404;
        return $function_output;
    }
    
    //store Keypair in Session//
    //INSERT
    $sql_opd = "INSERT INTO session ('key','keypair') VALUES ('" . $Key . "','" . $public_key . "')";
    if (mysqli_query($conn, $sql_opd)) {
    } else {
        $function_output["status"] = 404;
        $function_output["output"] = 404;
        return $function_output;
    }
    
    /*
    updateapp
    appuid= app generated id
    date= now date("U")
    statuscode = complete = 1
    */
    //INSERT//
    $field_array["appuid"]      = $clean_data["appheader"]["appid"];
    $field_array["complete"]    = 1;
    $field_array["failcode"]    = 0;
    $field_array["key"]         = $clean_data["appheader"]["key"];
    $field_array["requestdate"] = date("U");
    $table                      = "appdata";
    
    $sql_opd = build_sql_insert($table, $field_array);
    
    if (mysqli_query($conn, $sql_opd)) {
    } else {
        $function_output["status"] = 404;
        $function_output["output"] = 404;
        return $function_output;
    }
    
    mysqli_close($conn);
    $output["Key"]                  = $Key;
    $output["Public_Key"]           = $public_key;
    $clean_data["appheader"]["key"] = $Key;
    $clean_data["privatekeypair"]   = $keypair;
    $function_output["from"]        = "registration";
    $function_output["status"]      = 200;
    $function_output["output"]      = $output;
    $function_output["clean_data"]  = $clean_data;
    return $function_output;
}

/*
This checks the api function name - 
*/

switch ($view) {
    
    case "reject":
        
        $function_output["status"] = 404;
        break;
    
    case "registration":
        
        $function_output = get_function_registration($post_data);
        break;
    
    case "":
        //404 - not found;//
        $function_output["status"] = 404;
        break;
}

//$function_output["status"]==false/200
//$function_output["output"]==jsonencodeddata
// this is the controller, it will prepare the server_response header and json data//


if ($function_output["status"] == 404) {
    $statusCode = 404;
    
} else {
    $statusCode = 200;
}
var_dump(jsonwrapper($function_output, $function_output));
die;
setHttpHeaders('application/json', $statusCode);
echo jsonwrapper($function_output, $function_output);
//server response //
/*

$function_output["Key"]=$Key;
$function_output["Public_Key"]=$public_key
*/

?>