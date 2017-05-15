$(function(){
	$("#login").dialog({
		closable:false,
		title:'后台登录',
		width:300,
		height:180,
		modal:true,
		iconCls:'icon-login',
		buttons:'#btn'
	});

	$("#manager").validatebox({
		required:true,
		missingMessage:'请输入帐号',
		invalidMessage:'帐号不得为空'
	});

	$("#password").validatebox({
		required:true,
		missingMessage:'请输入密码',
		invalidMessage:'密码不得为空'
	});

	if(!$("#manager").validatebox('isValid')){
		$("#manager").focus();
	}else if(!$("#password").validatebox('isValid')){
		$("#password").focus();
	}

});