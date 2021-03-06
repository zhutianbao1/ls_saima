<?php
/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
	// 'DEFAULT_MODULE'     => 'Home',
    /* 模板相关配置 */
	'TMPL_PARSE_STRING' => array(
			'__UPLOAD__' => __ROOT__ . '/upload/',
			'__PUBLIC__' => __ROOT__ . '/Public',
			'__STATIC__' => __ROOT__ . '/Public/static',
			'__PUBIMG__' => __ROOT__ . '/Public/images',
			'__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
			'__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
			'__IMG_TOP__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images_top',
			'__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
			'__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
			'__MOBILE__' => __ROOT__ . '/Public/Mobile',
			'__HEAD_IMG__'=>'http://lszc.zj.chinamobile.com:8007/upload/headImg/'
	),
	
	 /**/
	//'DB_TYPE'            => 'oracle', // 数据库类型  
	//'DB_HOST'            => '10.78.1.40', // 服务器地址  
	//'DB_NAME'            => 'ls40', // 服务器地址  
	//(DESCRIPTION =(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 10.78.1.40)(PORT = 1521)))(CONNECT_DATA =(SID = LSDBNEW)(SERVER = DEDICATED)))
	'DB_USER'            => 'mz_user', // 用户名  
	'DB_PWD'             => 'mzuser', // 密码  
	//'DB_PORT'            => 1521, // 端口  
	// 'DB_CHARSET'		 => 'ZHS16GBK',
	//'DB_PREFIX'          => 'rank_', // 数据库表前缀  



	//阅读会使用 mysql 数据库
		//'DB_TYPE'            => 'oracle', // 数据库类型  
		//'DB_HOST'            => '10.78.1.40', // 服务器地址  
		//'DB_NAME'            => 'ls40', // 服务器地址  
		//(DESCRIPTION =(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 10.78.1.40)(PORT = 1521)))(CONNECT_DATA =(SID = LSDBNEW)(SERVER = DEDICATED)))
		//'DB_USER'            => 'mz_crm', // 用户名  
		//'DB_PWD'             => 'lsydlsyd', // 密码  
		//'DB_PORT'            => 1521, // 端口  
		// 'DB_CHARSET'		 => 'ZHS16GBK',
		//'DB_PREFIX'          => 'rank_', // 数据库表前缀

	

);
