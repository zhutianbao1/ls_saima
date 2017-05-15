function checkForm(){
	var fm = document.add;
	var invoice_id = fm.invoice_id.value;
	if(invoice_id == ""){
		alert("发票编号不能为空！");
		fm.invoice_id.focus();
		return false;
	}
	var invoice_amount = fm.invoice_amount.value;
	if(invoice_amount == "" || isNaN(invoice_amount)){
		alert("发票金额不能为空，且必须是数字！");
		fm.invoice_amount.focus();
		return false;
	}
	var invoice_company = fm.invoice_company.value;
	if(invoice_company == ""){
		alert("发票抬头单位不能为空！");
		fm.invoice_company.focus();
		return false;
	}
	var invoice_name = fm.invoice_name.value;
	if(invoice_name == ""){
		alert("发票内容名称不能为空！");
		fm.invoice_name.focus();
		return false;
	}
	var invoice_purpose = fm.invoice_purpose.value;
	if(invoice_purpose == ""){
		alert("发票内容用途不能为空！");
		fm.invoice_purpose.focus();
		return false;
	}
}