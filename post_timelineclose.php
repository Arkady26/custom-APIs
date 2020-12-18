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

unset ($_REQUEST);
unset ($_GET);$view="reject";
//unset $_SERVER;

if(isset($_GET["view"]))
	{exit();}else{}
		
if(isset($_POST["view"]))
{
	$view = $_POST["view"];
	//$view = strip_tags($view);
	//$view = stripslashes($view);
	$post_data=$_POST["q"];
}else{}	


require ('global_functions.php');
function get_function_timelineclose($post_data)
{
	
// clean array
$clean_data=clean_input($post_data);
if ($clean_data==false)
{
$function_output["status"]=404;
$function_output["output"]=404;
return	$function_output;
}	
$conn=conn();		

/*
 timelineclose
      timelinedate
      dwellstart
      dwellend
      dwellminutes
      uniqueid
	  */

 

$field_array=$clean_data["appheader"]["datablock"]["timelineclose"];
$where="WHERE uniqueid='".$clean_data["appheader"]["datablock"]["timelineclose"]["uniqueid"]."'";	

$sql_opd = build_sql_update("timeline", $field_array, $where);

if (mysqli_query($conn, $sql_opd)) 
{
} 
else 
{
$function_output["status"]=404;
$function_output["output"]=404;
return	$function_output;
}


// get timeline data to update//
	if ($result = mysqli_query($conn, "SELECT * FROM timeline WHERE uniqueid='".$clean_data["appheader"]["datablock"]["timelineclose"]["uniqueid"]."' LIMIT 1")) 
	{
		while($field_array = mysqli_fetch_array($result))
		 {  
		 } 	
	mysqli_free_result($result);
	}
	else 
	{
	$function_output["status"]=404;
	$function_output["output"]=404;
	return	$function_output;	
	}
	
	

$data["lat"]=$field_array["lat"];
$data["lon"]=$field_array["lon"];
$data["rad"]=($field_array["radius"]/1000); //convert to km divid by 1000 //
$data["starttime"]=$field_array["dwellstart"];
$data["settime"]=$field_array["settime"];
$data["endtime"]=$field_array["dwellend"];
$data["currentdwell"]=$field_array["dwellingcurrent"];
$data["foundprofileid"]=$field_array["foundprofileid"];
$data["key"]=$field_array["key"];
$data["uniqueid"]=$field_array["uniqueid"];
$data["lat"]=$field_array["connections"];

$profileusers=new FindUsers($conn);
$profileusers->FindUsers($data);
$profileusers["uniqueid"]=$field_array["uniqueid"];

mysqli_close($conn);	
$function_output["from"]="timelineclose";
$function_output["status"]=200;
$function_output["output"]=$profileusers;	
$function_output["clean_data"]=$clean_data;
return	$function_output;
}

/*
This checks the api function name - 
*/

		switch($view){
			
			case "reject":

			
$function_output["status"]=404;break;
				
			case "timelineclose":
	
			
				$function_output=get_function_timelineclose($post_data);
				break;

				
			case "" :
				//404 - not found;//
				$function_output["status"]=404;
				break;
		}




//$function_output["status"]==false/200
//$function_output["output"]==jsonencodeddata

// this is the controller, it will prepare the server_response header and json data//

		
		if($function_output["status"]==404) {
			$statusCode = 404;
		
		} else {
			$statusCode = 200;
		}

		setHttpHeaders('application/json', $statusCode);
			echo jsonwrapper($function_output,$function_output);    
			//server response //





?>
