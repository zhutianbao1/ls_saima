<?php

namespace Read\Controller;

class VipController extends BaseController {


	public function index(){
		$this->display();
	}

	//我的借阅书籍
	protected function my_borrow(){
		
		return $borrows;
	}
}

?>