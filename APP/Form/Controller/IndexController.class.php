<?php

namespace Form\Controller;

class IndexController extends BaseController {

	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
 		parent::_initialize();
 	}
 	
 	public function demo(){
 		$this->display();
 	}

 	public function login(){
 		if(IS_POST){
 			$user = parent::isLogin();
 			$this->assign('userInfo',$user);
 			$this->redirect('index:index',null,2,'登录成功,正在跳转..');
 		}else{
 			$this->display();
 		}
 	}

 	public function loginOut(){
 		session('user_auth',null);
 		$this->display('login');
 	}

 	//主页
	public function index(){
		$menus = parent::menu_type_role($_SESSION['user_auth']['OPER_ID']);
		$this->assign('menuList',$menus);
		// foreach ($menus as $key => $menu) {
		// 	$m['id']=$menu['MENU_ID'];
		// 	$m['pId']=$menu['MENU_PARENT_ID'];
		// 	$m['name']=$menu['MENU_NAME'];
		// 	$m['url']=$menu['MENU_URL'];
		// 	$m['icon']=$menu['MENU_URL'];
		// 	$menuList[]=$m;
		// }
		// $this->assign('menuList',json_encode($menuList));
		$this->display();
	}

	//获取子菜单方法
	public function menu_sub_tree($menu_parent_id=0){
		$menus = parent::menu_type($menu_parent_id);

	}

}
?>