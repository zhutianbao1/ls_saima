<?php
	$id = $_GET['id'];
	$prefix = $_GET['prefix'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>文件选择</title>
</head>
<body>
<div style="font-size:12px;color:#f00">图片大小不允许超过4M<br>仅允许上传图片</div>
<form action="upload1.php?pid=<?php echo $id ?>&prefix=<?php echo $prefix ?>" method="post" enctype="multipart/form-data">
<input type="file" name="file" id="file"/><br>
<input type="submit" value="上传"/>
</form>
</body>
</html>