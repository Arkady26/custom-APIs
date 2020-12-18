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
function get_function_gps($post_data)
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
	
		//INSERT//
	$field_array=$clean_data["appheader"]["datablock"]["gps"];
	$field_array["key"]=$clean_data["appheader"]["key"];	
	$table="gps";

	$sql_opd = build_sql_insert($table, $field_array);

	if (mysqli_query($conn, $sql_opd)) 
	{
	} 
	else 
	{
	$function_output["status"]=404;
	$function_output["output"]=404;			
	}





mysqli_close($conn);
$function_output["from"]="gps";
$function_output["status"]=200;
$function_output["output"]=200;	
$function_output["clean_data"]=$clean_data;
return $function_output;	
}



/*
This checks the api function name - 
*/

		switch($view){
			
			case "reject":

			
$function_output["status"]=404;break;
				
			case "gps":
	
			
				$function_output=get_function_gps($post_data);
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
