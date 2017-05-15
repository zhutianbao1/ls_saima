<?php

namespace Home\Controller;

class ConfigController extends BaseController {
	
	public function index(){
	 	parent::ConfigList();
		$this->display('rank/rank_config');
	} 
	
}

?>
