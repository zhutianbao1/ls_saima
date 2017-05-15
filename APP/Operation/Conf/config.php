<?php 
return array(
	// 'DEFAULT_MODULE'     => 'Home',
    /* 模板相关配置 */
	'TMPL_PARSE_STRING' => array(
			'__UPLOAD__' => __ROOT__ . '/Uploads/Picture/',
			'__PUBLIC__' => __ROOT__ . '/Public',
			'__STATIC__' => __ROOT__ . '/Public/static',
			'__PUBIMG__' => __ROOT__ . '/Public/images',
			'__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
			'__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
			'__IMG_TOP__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images_top',
			'__MOBILE__' => __ROOT__ . '/Public/Mobile',
			'__HEAD_IMG__'=>'http://lszc.zj.chinamobile.com/upload/headImg/'
	),
	'DB_PREFIX'          => 'rank_', // 数据库表前缀
	'FILE_UPLOAD'=>array(
		'mimes'=>'',
		'maxSize'=>50*1024*1024,//文件上传的最大文件大小（以字节为单位），0为不限大小
		'exts'=>array('jpg','gif','png','jpeg','zip','rar','doc','docx','xls','xlsx','ppt','pptx','pdf','txt'), //允许上传的文件后缀
		'subName'=>array('date','Ymd'),//子目录创建方式，采用数组或者字符串方式定义
		'sitePath'=>'./Uploads/', //网站根路径
		'rootPath'=>'./Uploads/', //保存根路径
		'savePath'=>'Attachment/Operation/', //保存路径 
		'autoSub'=>true,//自动使用子目录保存上传文件 默认为true
		'saveName'=>array('uniqid',''),//date('YmdHis',time()).getMicrotime().'_'.mt_rand(),
		'saveExt'=>'',//文件保存后缀，空则为原文件后缀
		'replace'=>false,//存在同名是否覆盖
		'hash'=>true,//是否生成哈希编码
		'callback'=>false,//检测是否存在回调函数，如果存在返回文件信息数组
	),
);