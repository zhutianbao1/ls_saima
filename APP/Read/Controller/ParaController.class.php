<?php

namespace Read\Controller;

// 常量参数转值
class ParaController extends BaseController {


	public function index(){
		$this->display();
	}

	//书籍类型
	public function attr_book_type($key=''){
		$web_attrs = parent::base_attr();
		$types = json_decode($web_attrs['BOOK_TYPE']);
        $ts = json_decode(json_encode($types),true);
        $type  = $ts[$key];
        return $type;
	}
	

}

?>