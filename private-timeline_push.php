<?php

//////////////////////////////////////////////////////////////////
/*

SELECT
	push_timeline_id,
	`key`,
	`all`,
	timelineidprefix,
	title,
	mapurl,
	textcontent,
	alertbox,
	alert_title,
	alert_content,
	deletetime,
	profileupdate,
	foundprofileid,
	uniqueid
FROM push_timeline 
	

*/
//////////////////////////////////////////////////////////////////

require ('global_functions.php');

function timeline_push($key=false)
{
	
$conn=conn();	
$i=0;
$timelinearray="";
$datetime=date("U");
if ($result = mysqli_query($conn, "SELECT * FROM push_timeline WHERE expiry<".$datetime." AND key='".$key."'")) 
{
    while($row = mysqli_fetch_array($result))
     {     
		$timelinearray[$i]=$row["pushdata"];
		$i++;
     } 	
mysqli_free_result($result);
}

mysqli_close($conn);

return $timelinearray;

}




/*

End File

*/
?>