window.onload = function(){
	var level = document.getElementById('level');
	var options = document.getElementsByTagName('option');
	
	if(level){
		for(i=0;i<options.length;i++){
			if(options[i].value == level.value){
				options[i].setAttribute('selected','selected');
			}
		}
	}
	
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


//验证Manageupdate
function checkUpdateForm(){
	var fm = document.update;
	if(fm.admin_pass.value != ''){
		if(fm.admin_pass.value.length < 6){
			alert('警告：密码不得小于6位！');
			fm.admin_pass.focus();
			return false;
		}
	}
	return true;
}

//验证Manageadd
function checkAddForm(){
	var fm = document.add;
	if(fm.admin_user.value == '' || fm.admin_user.value < 2 || fm.admin_user.value > 20){
		alert('警告：用户名不得为空，并且不得小于2位，不得大于20位！');
		fm.admin_user.focus();
		return false;
	}
	if(fm.admin_pass.value == '' || fm.admin_pass.value < 6){
		alert('警告：密码不得为空，并且不得小于6位！');
		fm.admin_pass.focus();
		return false;
	}
	if(fm.admin_pass.value != fm.admin_notpass.value){
		alert('警告：两次输入的密码不一致！');
		fm.admin_notpass.focus();
		return false;
	}
	return true;
}