<?php
header('content-type: text/html; charset=utf-8');

error_reporting(0);
//隐藏警告信息
error_reporting(E_ALL^E_NOTICE);

$pid = $_GET['pid'];
$prefix = $_GET['prefix'];

//echo $pid;

$allowedExts = array("gif", "jpeg", "JPEG", "jpg", "JPG","png","PNG");
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
|| ($_FILES["file"]["type"] == "image/png"))
&& ($_FILES["file"]["size"] < 4194304)
&& in_array($extension, $allowedExts)){
	if ($_FILES["file"]["error"] > 0){
		echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
	}else{
		list($rname,$ext) = explode('.',  $_FILES["file"]["name"] );
		$signName = $prefix.'.'.$ext;
		$suffix = array("png","xpng","jpg","jpeg","pjpeg","gif");
		foreach($suffix as $var){
			unlink('Uploads/Attachment/Employee/'.$prefix.'.'.$var);
		}

		move_uploaded_file($_FILES["file"]["tmp_name"],"Uploads/Attachment/Employee/" .$signName);
		echo "路径: " . "Uploads/Attachment/Employee/" . $signName;
		echo '<br><input type="button" value="关闭窗口" onclick="window.close();"/>';
		$result['fileName']="Uploads/Attachment/Employee/" . $signName;
	}

	echo "<script type='text/javascript'>";
	echo "parent.document.getElementById('".$pid."').value='".$_FILES["file"]["name"]."';";
	echo "parent.document.getElementById('".$pid."_1').value='".$signName."';";
	echo "parent.layer.closeAll();";
	echo "</script>";
}else{
	echo '不允许上传格式';
 
}

/*
function getMicrotime(){
    list($usec, $sec) = explode(" ", microtime());
    $usec = $usec * 1000;//计算毫秒数(微秒部分并不是微秒,这部分的单位是秒)
    return intval($usec);
}
*/
?>