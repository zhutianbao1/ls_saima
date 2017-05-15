<?php

namespace Baob\Widget;
use Baob\Controller\BaseController;

class SideWidget extends BaseController  {

	public function side_tabs(){
		$this->display('payment/side_tabs');
	}



	public function top_tabs(){
		$this->display('payment/top_tabs');
	}

	public function top_tabs2(){
		$this->display('payment/top_tabs2');
	}


}



?>