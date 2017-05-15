window.onload = function(){
	var title = document.getElementById('title');
	var ol = document.getElementsByTagName('ol');
	var a = ol[0].getElementsByTagName('a');
	for(i=0;i<a.length;i++){
		a[i].className = null;
		if(title.innerHTML == a[i].innerHTML){
			a[i].className = 'selected';
		}
	}
};

function centerWindow(url,name,width,height){
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2 - 50;
	window.open(url,name,'width='+width+',height='+height+',top='+top+',left='+left);
}

function adver(type){
	
	var fm = document.content;
	if (fm.adv.value == type) return;
	
	var thumbnail = document.getElementById('thumbnail');
	var up = document.getElementById('up');
	
	fm.thumbnail.value = '';
	fm.pic.src = '';
	fm.pic.style.display = 'none';
	
	switch(type){
		case 1:
			thumbnail.style.display = 'none';
			up.innerHTML = '';
			fm.adv.value=1;
			break;
		case 2:
			thumbnail.style.display = 'block';
			up.innerHTML = "<input type=\"button\" value=\"上传头部广告\" onclick=\"centerWindow('../config/upfile.php?type=adver&size=690x80','upfile','400','100')\" />";
			fm.adv.value=2;
			break;
		case 3:
			thumbnail.style.display = 'block';
			up.innerHTML = "<input type=\"button\" value=\"上传侧栏广告\" onclick=\"centerWindow('../config/upfile.php?type=adver&size=270x200','upfile','400','100')\" />";
			fm.adv.value=3;
			break;
	}
	
}

//checkAdver
function checkAdver(){
	var fm = document.content;
	if(fm.title.value == '' || fm.title.value < 2 || fm.title.value > 20){
		alert('警告：标题不得为空，并且不得小于2位，不得大于20位！');
		fm.title.focus();
		return false;
	}
	if(fm.link.value ==''){
		alert('警告：链接不得为空！');
		fm.link.focus();
		return false;
	}
	if(fm.type[1].checked || fm.type[2].checked){
		if(fm.thumbnail.value ==''){
			alert('警告：广告图片不得为空！');
			fm.thumbnail.focus();
			return false;
		}
	}
	if(fm.info.value.length > 200){
		alert('警告：描述不得大于200位！');
		fm.info.focus();
		return false;
	}
	return true;
}