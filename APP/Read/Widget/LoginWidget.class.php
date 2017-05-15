<?php

namespace Read\Widget;
use Read\Controller\BaseController;

class LoginWidget extends BaseController  {
 
	public function login(){
		$this->display('Sys/widget_login');
	}
}

?>