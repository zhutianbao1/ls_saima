//选择头像
function sface(){
	var fm = document.reg;
	var index = fm.face.selectedIndex;
	fm.faceimg.src = '__PUBLIC__/images/'+fm.face.options[index].value;
}

//验证登录
function checkReg(){
	var fm = document.reg;
	if(fm.user.value == '' || fm.user.value.length < 2 || fm.user.value.length > 20){
		alert('警告：用户名不得为空，并且不得小于2位，不得大于20位！');
		fm.user.focus();
		return false;
	}
	if(fm.pass.value == '' || fm.pass.value.length < 6){
		alert('警告：密码不得为空，并且不得小于6位！');
		fm.pass.focus();
		return false;
	}
	if(fm.pass.value !=fm.notpass.value){
		alert('警告：两次输入的密码不一致！');
		fm.notpass.focus();
		return false;
	}
	if(!/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/.test(fm.email.value)){
		alert('警告：电子邮件格式不正确!');
		fm.email.value = '';
		fm.email.focus();
		return false;
	}
	if(fm.code.value.length != 4){
		alert('警告：验证码必须为4位！');
		fm.code.focus();
		return false;
	}
	return true;
}



function checkLogin(){
	var fm = document.login;
	if(fm.user.value == '' || fm.user.value.length < 2 || fm.user.value.length > 20){
		alert('警告：用户名不得为空，并且不得小于2位，不得大于20位！');
		fm.user.focus();
		return false;
	}
	if(fm.pass.value == '' || fm.pass.value.length < 6){
		alert('警告：密码不得为空，并且不得小于6位！');
		fm.pass.focus();
		return false;
	}
	if(fm.code.value.length != 4){
		alert('警告：验证码必须为4位！');
		fm.code.focus();
		return false;
	}
	return true;
}