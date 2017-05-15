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
);