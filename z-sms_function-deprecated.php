<?php

//////////////////////////////////////////////////////////////////
/*


sms_send()

API
SMS Gateway
Login
Password



*/
//////////////////////////////////////////////////////////////////




require ('global_functions.php');
require('textlocal.class.php');
function sms_send($code=false,$phonenumber=false)
{



if ($clean_data["appheader"]["datablock"]["sendverify"]["phone"]!==false){}
else
{
$function_output["status"]=404;
$function_output["output"]=404;
return	$function_output;		
}


//get phone number//
if ($result = mysqli_query($conn, "SELECT mobilenumber FROM profile WHERE key='".$clean_data["appheader"]["key"]."'  LIMIT 1")) 
{
    while($row = mysqli_fetch_array($result))
     {     
		$clean_data["mobilenumber"]=$row["mobilenumber"];
     } 	
	mysqli_free_result($result);
	}
	else
	{
	$function_output["status"]=404;
	$function_output["output"]=404;
	return	$function_output;			
		
	}
mysqli_close($conn);



		if ($clean_data["mobilenumber"]==false)
			{
			$function_output["status"]=404;
			$function_output["output"]=404;
			return	$function_output;
			}
		else
		{
			// SEND code //
			
				$textlocal = new Textlocal('demo@txtlocal.com', 'apidemo123');

				$numbers = array($clean_data["mobilenumber"]);
				$sender = 'LACHTR';
				$message =rand(100204, 995919);

				try {
					$result = $textlocal->sendSms($numbers, $message, $sender);
					//print_r($result);//
					
				} catch (Exception $e) {
					$function_output["status"]=404;
					$function_output["output"]=404;
					return	$function_output;
				}
		
			//Store CODE in database verify //
			$conn=conn();
			$sql = "INSERT INTO 'verify'('timedate','key','code') VALUES ('".date("U")."','".$clean_data["appheader"]["key"]."','".$message."')";
			mysqli_query($conn,$sql);
		}
		
	
		
mysqli_close($conn);
$function_output["from"]="smsverify";
$function_output["status"]=200;
$function_output["output"]=200;	
return $function_output;			
}




// this is the controller, it will prepare the server_response header and json data//

		
		if($function_output["status"]==404) {
			$statusCode = 404;
		
		} else {
			$statusCode = 200;
		}

		setHttpHeaders('application/json', $statusCode);
			echo jsonwrapper($function_output,$function_output);    
			//server response //





/*

End File

*/
?>