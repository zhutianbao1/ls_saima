<?php
header('content-type: text/html; charset=utf-8');

error_reporting(0);
//隐藏警告信息
error_reporting(E_ALL^E_NOTICE);

$pid = $_GET['pid'];

//echo $pid;

$allowedExts = array("gif", "jpeg", "jpg", "png","docx","rar","pdf","text","zip","rar","xls","mp3");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

// var_dump($_FILES);
 
//var_dump($allowedExts);

$result=array('flag'=>true,'msg'=>'','fileName'=>'');

echo "文件: " . $_FILES["file"]["name"] . "<br>";
echo "类型: " . $_FILES["file"]["type"] . "<br>";
echo "大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";	
 
if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/jpg")
|| ($_FILES["file"]["type"] == "image/pjpeg")
|| ($_FILES["file"]["type"] == "image/x-png")
|| ($_FILES["file"]["type"] == "image/png")
|| ($_FILES["file"]["type"] == "application/pdf")
|| ($_FILES["file"]["type"] == "audio/mpeg")
|| ($_FILES["file"]["type"] == "text/plain")
|| ($_FILES["file"]["type"] == "application/msword")
|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
|| ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
|| ($_FILES["file"]["type"] == "application/octet-stream"))
&& ($_FILES["file"]["size"] < 1000000)
&& in_array($extension, $allowedExts))
{
	if ($_FILES["file"]["error"] > 0)
	{
		echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
	}
	else
	{
	list($rname,$ext) = explode('.',  $_FILES["file"]["name"] );
	$signName = time().'.'.$ext;
	
	// echo "文件: " . $_FILES["file"]["name"] . "<br>";
	// echo "类型: " . $_FILES["file"]["type"] . "<br>";
	// echo "大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
	// echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";	
	// echo "路径: " . file_exists("upload1/" . $_FILES["file"]["name"]) . "<br>";
	
	if (file_exists("Uploads/Picture/" . $signName))
	{
	//echo $signName . " already exists. ";
	}
	else
	{
	move_uploaded_file($_FILES["file"]["tmp_name"],"Uploads/Picture/" .$signName);
	echo "路径: " . "Uploads/Picture/" . $signName;
	echo '<br><input type="button" value="关闭窗口" onclick="window.close();"/>';
	$result['fileName']="Uploads/Picture/" . $signName;
	}
	}

	echo "<script type='text/javascript'>";
	echo "window.opener.document.getElementById('".$pid."').value='".$result['fileName']."';";
	echo "window.opener.document.getElementById('img".$pid."').src='http://10.78.1.85:9000/ranking/".$result['fileName']."';";
	echo "this.close();";
	echo "</script>";
}
else
{
	echo '不允许上传格式';
 
}
?>