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
	if(fm.level_name.value == '' || fm.level_name.value.length < 2 || fm.level_name.value.length > 20){
		alert('警告：等级不得为空，并且不得小于2位，不得大于20位！');
		fm.level_name.focus();
		return false;
	}
	if(fm.level_info.value.length > 200){
		alert('警告：描述不得大于200位！');
		fm.level_info.focus();
		return false;
	}
	return true;
}