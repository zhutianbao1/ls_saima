function checkForm(){
	var fm = document.add;
	var invoice_id = fm.invoice_id.value;
	if(invoice_id == ""){
		alert("发票编号不能为空！");
		fm.invoice_id.focus();
		return false;
	}
	var dept_manager = fm.dept_manager.value;
	if(dept_manager == ""){
		alert("部门经理不能为空！");
		fm.dept_manager.focus();
		return false;
	}
	var cancel_reason = fm.cancel_reason.value;
	if(cancel_reason == ""){
		alert("作废原因不能为空！");
		fm.cancel_reason.focus();
		return false;
	}
}