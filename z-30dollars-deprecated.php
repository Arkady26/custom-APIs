<?php


$conn = mysqli_connect("localhost", "root", "", "test");


$data["lat"]=49;
$data["lon"]=41;
$data["rad"]=10;
$data["starttime"]=date('U');
$data["settime"]=5;
$data["endtime"]=date('U', time()+3600);
$data["currentdwell"]=60;
$data["foundprofileid"]=array(array("foundprofileid"=>"857", "name"=>"name1", "image"=>"image1"), 
array("foundprofileid"=>"858", "name"=>"name2", "image"=>"image2"),
array("foundprofileid"=>"859", "name"=>"name3", "image"=>"image3"));
$data["key"]=111;
$data["uniqueid"]=$uniqueid;


$users=new FindUsers($conn);
$users->FindUsers($data);



class FindUsers
{
    function __construct($con)
    {
        $this->db=$con;
    }
    function FindUsers($data)
    {
        extract($data);
        $R = 6371; // earth's mean radius, km
        if ($endtime==false)
        {
            $endtime=$starttime+$currentdwell;
        }
        //$start_plus_settime=$starttime+$settime;//
        $maxLat = $lat + rad2deg($rad/$R);
        $minLat = $lat - rad2deg($rad/$R);
        $maxLon = $lon + rad2deg(asin($rad/$R) / cos(deg2rad($lat)));
        $minLon = $lon - rad2deg(asin($rad/$R) / cos(deg2rad($lat)));
        $lat=deg2rad($lat);
        $lon=deg2rad($lon);
        $sql = "Select 'key', Lat, Lon,
acos(sin($lat)*sin(radians(Lat)) + cos($lat)*cos(radians(Lat))*cos(radians(Lon)-$lon)) * $R As D
From (
Select 'key', Lat, Lon
From gps
Where Lat Between $minLat And $maxLat
And Lon Between $minLon And $maxLon
AND datetime BETWEEN $starttime AND $endtime
) As FirstCut
Where acos(sin($lat)*sin(radians(Lat)) + cos($lat)*cos(radians(Lat))*cos(radians(Lon)-$lon)) * $R < $rad
Order by D";
        $result=mysqli_query($this->db, $sql);
        $keys=array();
        $t_users=array();
		
		
		
        while($arr=mysqli_fetch_array($result))
        {
            $key=$arr["key"];
            if(isset($users["$key"]))
                $t_users["$key"][]=$arr;
            else
                $t_users["key"]=array($arr);
        }
        foreach($t_users as $key=>$values)
        {
            if(count($values)<$settime)
                continue;
            $users["$key"]=$values;
        }
		
		
        $users["fo3hq38"]=array();
        $users["fo3hqd38"]=array();
		
		
        foreach($data["foundprofileid"] as $found)
        {
            if(isset($users["{$found["key"]}"]))
                unset($users["{$found["key"]}"]);
        }
        $out=array();
        foreach($users as $key=>$values)
        {
            $query="SELECT * FROM profile WHERE 'key'=$key";
            $result=mysqli_query($query);
            $arr=mysqli_fetch_array($result);
            $name=$arr["name"];
            $image=$arr["image"];
            $profile=array("foundprofileid"=>$key, "name"=>$name, "image"=>$image);
            $out[]=$profile;
            $data["foundprofileid"][]=$profile;
        }
        $profileids=mysqli_real_escape_string ($this->db, serialize($users));
        $query="UPDATE timeline SET foundprofileid='$profileids' WHERE 'key='$key' AND uniqueid='$uniqueid'";
        mysqli_query($this->db, $query);
    }
    return $out;
}

?>

