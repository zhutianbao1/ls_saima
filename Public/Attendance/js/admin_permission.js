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

//验证等级表单
function checkForm(){
	var fm = document.add;
	if(fm.name.value == '' || fm.name.value.length < 2 || fm.name.value.length > 100){
		alert('警告：权限不得为空，并且不得小于2位，不得大于100位！');
		fm.name.focus();
		return false;
	}
	if(fm.info.value.length > 200){
		alert('警告：描述不得大于200位！');
		fm.info.focus();
		return false;
	}
	return true;
}