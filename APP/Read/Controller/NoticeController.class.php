<?php

namespace Read\Controller;

// 常量参数转值
class NoticeController extends BaseController {

	protected function _initialize(){

	}
	
	//阅读会登录提示信息
	public function index(){
		$this->display();
	}
	
}

?>