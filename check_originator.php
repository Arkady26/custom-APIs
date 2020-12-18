<?php

//////////////////////////////////////////////////////////////////
/*


check_originator()







*/
//////////////////////////////////////////////////////////////////

require ('global_functions.php');

function check_originator($clean_data=false)
{	
// optional $rawdata["Appheader"]["TransmissionDateTime"]	timediff check 3 second rule//
// optional store Key as HASH -> hash the clean data block KEY field.//

$conn=conn();
$clean_data["privatekeypair"]=false;

if ($result = mysqli_query($conn, "SELECT * FROM session WHERE appid='".$clean_data["appheader"]["appsid"]."' AND key='".$clean_data["appheader"]["key"]."'  LIMIT 1")) 
{
    while($row = mysqli_fetch_array($result))
     {     
		$clean_data["privatekeypair"]=$row["key"];
		
     } 	
mysqli_free_result($result);
}

mysqli_close($conn);

if ($clean_data["privatekeypair"]===false){leave("Originator_void");}

return $clean_data;
}



























/*

End File

*/
?>