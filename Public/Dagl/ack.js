
//table隔行换色
$("table.altrowstable tr:even").addClass("evenrow");
$("table.altrowstable tr:odd").addClass("oddrow");

//on绑定hover事件在jquery1.8中已不建议使用,jquery1.9中被移除
$("table tr").on("mouseover mouseout",function(event){
  if(event.type=="mouseover"){
    $(this).addClass("hoverrow");
  }else if(event.type=="mouseout"){
    $(this).removeClass("hoverrow");
  }
});

$(".readInput").attr("readOnly","readOnly");//只读
//对只读input框、textare、checkbox、radio、select不让光标聚焦
//$(".readInput").attr("unselectable","on");
$(".readonly").attr("readOnly","readOnly");

//防止退格键退回上一页面
$(".readonly").keydown(function(e){
  var doPrevent;
  if (e.keyCode == 8){//退格键
	var d = e.srcElement || e.target;
	if (d.tagName.toUpperCase() == 'INPUT' || d.tagName.toUpperCase() == 'TEXTAREA') {
		doPrevent = d.readOnly || d.disabled;
	}else doPrevent = true;
  }
  
  if (doPrevent) e.preventDefault();

});

/*测试键盘按键动作类型和动作编号
$(".readonly").on("keydown",function(event){
  alert(event.type+event.which);
}*/