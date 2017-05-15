<?php

namespace Station\Widget;
use Station\Controller\BaseController;

class SideWidget extends BaseController  {
  
/**
	public function html(){
		//读者活动 
		$m = M('ploy');
		$ploys = $m->where('status=1')->order('oper_date desc')->limit(0,3)->select();
		$this->assign('ploys',$ploys);		
		$this->display('index/index_side');
	}

   //个人中心左侧栏目
	public function vip_html(){
		//读者活动 
		$m = M('ploy');
		$ploys = $m->where('status=1')->order('oper_date desc')->limit(0,2)->select();
		$this->assign('ploys',$ploys);
		$this->display('vip/index_side');
	}

	//好书推荐
	public function haoshu(){
		$m = M('book');
		$w['status']=1;
		$w['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
		$books = $m->where($w)->limit(0,10)->order('create_date desc ,star desc')->select();
		$this->assign('books',$books);
		$this->display('index/index_haoshu');		
	}


	

	//管理也菜单
	public function manager_menu(){
		$this->display('Manager/manager_menu');
	}

	//管理也菜单
	public function manager_tab(){
		$this->display('Manager/manager_tab');
	}

	//个人信息菜单
	public function user_tab(){
		$this->display('Vip/user_tab');
	}

	//会员管理菜单
	public function oper_tab(){
		$this->display('Vip/oper_tab');
	}


	public function  xinde_category(){
		$this->display('index/xinde_category');
	}
	**/


	public function side_tabs(){
		$this->display('Index/side_tabs');
	}



}

?>