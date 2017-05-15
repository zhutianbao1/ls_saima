<?php

namespace Read\Widget;
use Read\Controller\BaseController;

class XindeWidget extends BaseController  {
	
	public function info($book_id=0){
	}

	public function index_xinde(){

		//首页心得
		$xindes = parent::query_xinde(3);
		 //dump($xindes);
		$this->assign('xindes',$xindes);
		$this->display('index/index_xinde');
	}

}

?>