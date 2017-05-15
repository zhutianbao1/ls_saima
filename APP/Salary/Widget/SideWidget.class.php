<?php

namespace Salary\Widget;
use Salary\Controller\BaseController;

class SideWidget extends BaseController  {
	
	public function  title_tab(){
		$tab=I('tab');
		$this->assign('tab',$tab);	

	    $this->display('pub/title_tab');

	} 


	public function  footer_tab(){
		$tabb=I('tabb');
		$this->assign('tabb',$tabb);

        if($_SESSION['user_auth']['OPER_LOGIN_CODE'] =='18862243446' or
           $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='18358885089' or 
           $_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000' 
           ){
           	$info='1';
        }
        $this->assign('info',$info);
        $this->assign('tabb',$tabb);
	    $this->display('pub/footer_tab');
	} 
}

?>