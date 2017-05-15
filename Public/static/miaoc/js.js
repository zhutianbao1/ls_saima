// JavaScript Document
// 判断不同的浏览器
var agt=navigator.userAgent.toLowerCase();
var is_ie=(agt.indexOf("msie")!=-1 && document.all);


//主导航切换
function bgswitch02(td,objtab)
{
//  var tr = td.parentElement.cells;
  var tr;
  if (is_ie) {
	tr = td.parentElement.cells;	  
  } else
  { tr = td.parentNode.cells; }
  

  var ob = document.getElementById(objtab).rows;
  for(var ii=0; ii<tr.length; ii++)
  {
    tr[ii].className = (td.cellIndex==ii)?"on2":"off2";
    ob[ii].style.display = (td.cellIndex==ii)?"block":"none";
  }
}

