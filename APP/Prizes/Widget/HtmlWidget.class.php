<?php

namespace Prizes\Widget;
use Prizes\Controller\BaseController;

class HtmlWidget extends BaseController  {
	
	protected function _initialize(){
		parent::intiParamsURL();
	}

	public function index(){
		$this->display('widget/index');
	}
	
}

?>