window.onload = function(){
	var title = document.getElementById('title');
	var ol = document.getElementsByTagName('ol');
	var a = ol[0].getElementsByTagName('a');
	for(i=0;i<a.length;i++){
		a[i].className = null;
		if(title.innerHTML == a[i].innerHTML){
			a[i].className = 'selected';
		}
	}
};

function centerWindow(url,name,width,height){
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2 - 50;
	window.open(url,name,'width='+width+',height='+height+',top='+top+',left='+left);
}

//验证addContent
function checkAddContent(){
	var fm = document.content;
	if(fm.title.value == '' || fm.title.value < 2 || fm.title.value > 50){
		alert('警告：标题不得为空，并且不得小于2位，不得大于50位！');
		fm.title.focus();
		return false;
	}
	if(fm.nav.value == ''){
		alert('警告：必须选择一个栏目！');
		fm.nav.focus();
		return false;
	}
	if(fm.tag.value > 30){
		alert('警告：标签不得大于30位！');
		fm.tag.focus();
		return false;
	}
	if(fm.keyword.value > 30){
		alert('警告：关键字不得大于30位！');
		fm.keyword.focus();
		return false;
	}
	if(fm.source.value > 20){
		alert('警告：文章来源不得大于20位！');
		fm.source.focus();
		return false;
	}
	if(fm.author.value > 10){
		alert('警告：作者不得大于10位！');
		fm.author.focus();
		return false;
	}
	if(fm.info.value > 200){
		alert('警告：内容摘要不得大于200位！');
		fm.info.focus();
		return false;
	}
	if(CKEDITOR.instances.TextArea1.getData() == ''){
		alert('警告：详细内容不得为空！');
		fm.CKEDITOR.instances.TextArea1.focus();
		return false;
	}
	if(!is_NaN(fm.count.value)){
		alert('警告：浏览次数必须是数字！');
		fm.count.focus();
		return false;
	}
	if(is_NaN(fm.gold.value)){
		alert('警告：消费金币必须是数字！');
		fm.gold.focus();
		return false;
	}
	return true;
}