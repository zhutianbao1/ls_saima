var sidebar = [];
sidebar[1] = {
	'title':'车优惠伴我行',
	'pic':'/cms/uploads/20150729/20150729104439777.png',
	'link':'http://www.360.cn/'
};
sidebar[2] = {
	'title':'M绅士',
	'pic':'/cms/uploads/20150729/20150729104326339.png',
	'link':'http://www.vmall.com/'
};
sidebar[3] = {
	'title':'爱制造旗舰店',
	'pic':'/cms/uploads/20150729/20150729104255596.png',
	'link':'http://www.jd.com/'
};
var i = Math.floor(Math.random()*3+1);
document.write('<a href="'+sidebar[i].link+'" target="_blank" title="'+sidebar[i].title+'"><img border="0" src="'+sidebar[i].pic+'"></a>');
