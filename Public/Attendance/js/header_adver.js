var header = [];
header[1] = {
	'title':'暑假人气网游',
	'pic':'/a/Public/images/header3.png',
	'link':'http://www.163.com/'
};
header[2] = {
	'title':'生活家',
	'pic':'/a/Public/images/header2.png',
	'link':'http://www.tmall.com/'
};
header[3] = {
	'title':'水润BB霜',
	'pic':'/a/Public/images/header1.png',
	'link':'http://www.taobao.com/'
};
var i = Math.floor(Math.random()*3+1);
document.write('<a href="'+header[i].link+'" target="_blank" title="'+header[i].title+'"><img src="'+header[i].pic+'"></a>');
