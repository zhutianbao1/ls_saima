function writeOff(){
	var e = $(this);
	var id = e.attr('para');
	var status = e.attr('status');
	if(status == 2 || status == 3){
		alert("当前发票状态无法进行此操作！");
		return false;
	}else{
		//var url = "{:U('Index/invoice_edit')}?id="+id+"&status="+status;
		var url = "http://www.baidu.com";
		var win = window.parent.layer.open({
			type:2,
			title:"{$il['INVOICE_ID']} - 核销",
			shadeClose:true,
			shade:0.2,
			area:['500px','260px'],
			content:url
		})
	}
}