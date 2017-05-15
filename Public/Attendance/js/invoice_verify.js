function checkForm(){
	var fm = document.editForm;
	var receive_date = fm.receive_date.value;
	if(receive_date == ""){
		alert("收款日期不得为空！");
		fm.receive_date.focus();
		return false;
	}
	var amount = fm.amount.value;
	if(amount == "" || isNaN(amount)){
		alert("核销金额必须为数字！");
		fm.amount.focus();
		return false;
	}
	var invoice_amount = fm.invoice_amount.value;
	var writeoff_amount = fm.writeoff_amount.value;
	if(Number(invoice_amount)-Number(writeoff_amount)-Number(amount)<0){
		alert("累计核销金额不得大于发票总金额!");
		return false;
	}
}