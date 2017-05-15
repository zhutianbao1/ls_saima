<?php

function fileupload(){
  	header('content-type: text/html; charset=utf-8');
	error_reporting(0);
	//隐藏警告信息
	error_reporting(E_ALL^E_NOTICE);

	$allowedExts = array("gif", "jpeg", "jpg", "png","docx","rar","pdf","text","zip","rar","xls","mp3","xlsx");
	$temp = explode(".", $_FILES["file"]["name"]);
	$extension = end($temp);
	$result=array('flag'=>true,'msg'=>'','fileName'=>'');
    if($_FILES["file"]["error"]!=4){
    	if ((($_FILES["file"]["type"] == "image/gif")||
		 	($_FILES["file"]["type"] == "image/jpeg")|| 
		 	($_FILES["file"]["type"] == "image/jpg")||
		 	($_FILES["file"]["type"] == "image/pjpeg")|| 
			($_FILES["file"]["type"] == "image/x-png")|| 
			($_FILES["file"]["type"] == "image/png")|| 
			($_FILES["file"]["type"] == "application/pdf")|| 
		    ($_FILES["file"]["type"] == "audio/mpeg")||
		    ($_FILES["file"]["type"] == "text/plain")|| 
		    ($_FILES["file"]["type"] == "application/msword")||
		    ($_FILES["file"]["type"] == "application/vnd.ms-excel")|| 
		    ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")||
		    ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")||
		    ($_FILES["file"]["type"] == "application/octet-stream"))&& 
		    ($_FILES["file"]["size"] < 10000000)&& in_array($extension, $allowedExts)){

				if ($_FILES["file"]["error"] > 0){
					echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
				}else{
				    $signName =iconv('utf-8','gb2312',$_FILES["file"]["name"]);
					$signName1=$_FILES["file"]["name"]; 

					if (file_exists("Public/Station/files/". $signName)){
						$result['msg']='0';
						$result['fileName']="Public/Station/files/". $signName;
					}else{
						move_uploaded_file($_FILES["file"]["tmp_name"],"Public/Station/files/" .$signName);
					    $result['msg']='1';
						$result['fileName']="Public/Station/files/". $signName; 
						$result['pathname'] ="Public/Station/files/". $signName1;
					}
				}
		}
		else{
			$result['msg']='2';
		}
    }else{
    	$result['msg']='4';
    }
return $result;
}


?>