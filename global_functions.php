<?php

//////////////////////////////////////////////////////////////////
/*


leave()
success()
clean_input()
conn()

create_blockhash()
check_blockhash()

encrypt_block()
decrypt_block()
create_keypair()

build_sql_update()
build_sql_insert()

jsonwrapper()
*/
//////////////////////////////////////////////////////////////////

error_reporting(0);
ini_set('display_errors', 'off');

function leave($error = false)
{
    
    return false;
    
    exit();
}



function success($success = false)
{
    
    return true;
    
    exit();
}





function conn()
{
    
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "flapi";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if (mysqli_connect_errno()) {
        leave("database_connection_fail");
    }
    
    return;
}




function check_originator($clean_data = false)
{
    // transmisison timediff check 3 second rule//
    $timenow = date("U");
    if (($timenow - $clean_data["appheader"]["transmissiondatetime"]) > 3600) {
        return false;
    }
    
    
    
    $conn                         = conn();
    $clean_data["privatekeypair"] = false;
    
    if ($clean_data["appheader"]["key"] == "newlatcherinstallation") {
        return $clean_data;
    }
    
    if ($result = mysqli_query($conn, "SELECT * FROM session WHERE key='" . $clean_data["appheader"]["key"] . "'  LIMIT 1")) {
        while ($row = mysqli_fetch_array($result)) {
            $clean_data["privatekeypair"] = $row["key"];
        }
        mysqli_free_result($result);
    }
    
    mysqli_close($conn);
    
    if ($clean_data["privatekeypair"] === false) {
        return false;
    }
    
    return $clean_data;
}







function clean_input($cleanme = false)
{
    
    //check for valid json chars //
    
    // decode the JSON data
    $result = json_decode($cleanme, true);
    
    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }
    
    if ($error !== '') {
        
        return false;
    }
    // everything is OK
    if (array_key_exists('appheader', $result)) {
        $block = implode(" ", $result["appheader"]["datablock"]);
        $hash  = implode(" ", $result["appheader"]["hashofdatablock"]);
        
    } else {
        return false;
    }
    
    
    $check = (!empty($block) && !empty($hash)) ? check_blockhash($block, $hash) : false;
    if ($check == false) {
        return false;
    }
    $check = check_originator($result);
    if ($check == false) {
        return false;
    }
    return $check;
    
    
    //example of use: $output = clean_input($json_string);          
    
}









function create_blockhash($data_to_hash = false)
{
    $options      = array(
        'cost' => 12
    );
    $data_to_hash = implode($data_to_hash);
    $hash         = password_hash($data_to_hash, PASSWORD_BCRYPT, $options);
    return $hash;
}




function check_blockhash($data_to_check = false, $blockhash = false)
{
    
    if (create_blockhash($data_to_check) == $blockhash) {
        return true;
    } else {
        return false;
        
    }
}



function encrypt_block($data_to_encrypt = false, $public_key = false)
{
    
    
    $encrypted_block = sodium_crypto_box_seal($data_to_encrypt, $public_key);
    
    return $encrypted_block;
}



function decrypt_block($data_to_decrypt = false, $keypar = false)
{
    
    $decrypted_block = sodium_crypto_box_seal_open($data_to_decrypt, $keypair);
    
    return $decrypted_block;
}



function create_keypair()
{
    
    $key["privatekeypair"] = sodium_crypto_box_keypair();
    $key["public_key"]     = sodium_crypto_box_publickey($key["privatekeypair"]);
    
    return $key;
}





function build_sql_update($table, $field_array, $where)
{
    $cols        = array();
    $field_array = array_filter($field_array);
    foreach ($field_array as $key => $val) {
        $cols[] = "$key = '$val'";
    }
    $sql = "UPDATE $table SET " . implode(', ', $cols) . " WHERE $where";
    
    return ($sql);
}




function build_sql_insert($table, $field_array)
{
    $key = array_keys($field_array);
    $val = array_values($field_array);
    $sql = "INSERT INTO $table (" . implode(', ', $key) . ") " . "VALUES ('" . implode("', '", $val) . "')";
    
    return ($sql);
}




function jsonwrapper($clean_data = false, $result = false)
{
    // check if cleandata array is passed//    
    if ($clean_data == false) {
        return false;
    }
    
    // check if cleandata array has been passed complete//
    if (array_key_exists("clean_data", $clean_data)) {
        $clean_data = $clean_data["clean_data"];
    } else {
        return false;
    }
    
    
    // return a complete json output //
    $appheader["appheader"]["appid"]                = $clean_data["appheader"]["appid"];
    $appheader["appheader"]["key"]                  = $clean_data["appheader"]["key"];
    $appheader["appheader"]["transmissiondatetime"] = date("U");
    
    $appheader["appheader"]["datablocktype"]                   = "response";
    $appheader["appheader"]["datablock"]["response"]["from"]   = $result["from"];
    $appheader["appheader"]["datablock"]["response"]["status"] = $result["status"];
    $appheader["appheader"]["datablock"]["response"]["output"] = encrypt_block($result["output"], $clean_data["privatekeypair"]);
    $appheader["appheader"]["hashofdatablock"]                 = create_blockhash($clean_data["appheader"]["datablock"]);
    
    // unset all variable data from memory//
    unset($clean_data);
    return json_encode($appheader);
}



/*

End File

*/
?>