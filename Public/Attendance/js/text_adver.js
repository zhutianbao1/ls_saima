var text = [];
text[1] = {
	'title':'新浪广告',
	'link':'http://www.sina.com.cn/'
};
text[2] = {
	'title':'京东广告',
	'link':'http://www.jd.com/'
};
text[3] = {
	'title':'腾讯广告',
	'link':'http://www.qq.com/'
};
text[4] = {
	'title':'淘宝广告',
	'link':'http://www.taobao.com/'
};
text[5] = {
	'title':'百度广告',
	'link':'http://www.baidu.com/'
};
var i = Math.floor(Math.random()*5+1);
document.write('<a href="'+text[i].link+'" class="adv" target="_blank">'+text[i].title+'</a>');
