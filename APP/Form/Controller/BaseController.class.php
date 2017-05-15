<?php

namespace Form\Controller;

use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class BaseController extends AdminController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){

		$CONTROLLER_NAME = $Think.CONTROLLER_NAME;
		$ACTION_NAME  = $Think.ACTION_NAME;	 
		parent::intiParams();	 

		//由85系统传登录参数过来实现表单程序自动登录
		$c = I('n');
		$p = I('p');
		if(!empty($c) && !empty($p)){
			$where = " and oper_login_code='{$c}' and lower(md5(oper_login_pass))='{$p}'";
			self::loginUser($where);
		}

		$user = self::isLogin();
 	}

 	//使用用户名 和 密码登录
	public function isLogin(){

		$user = session('user_auth');
	 
 		 if($user['OPER_ID']){
			session('user_auth',null);
			session('user_auth',$user);			
		 }else{
		 	//获取详细的用户信息
		 	$loginCode = I('loginCode');
		 	$loginPass = I('loginPass');
			$where=" and oper_login_code='".$loginCode."' and oper_login_pass='".$loginPass."'";
			$user = $this->loginUser($where);
			if(empty($user)){
				$this->assign('errmsg','登录失败或超时，请重新登录');
				$this->display('index/login');
				die;
			}
			return $user;
		 }
 	}

 	//根据条件查询用户信息
 	public function loginUser($where=""){
 		$back = false;
 		$oa = $_SESSION['user_auth']['OA'];
 		if(is_string($where) && strlen($where)>0){
 			$m = M();
 			$sql = "select a.* , b.dept_name,b.dept_county_id county_code from mz_user.t_sys_oper a , mz_user.t_sys_dept b where a.oper_dept_id=b.dept_id and a.oper_status=1 ".$where;
 			// echo $sql;
 			$users = $m->query($sql);
 			$user = $users[0];
 			if($oa){
				$user['OA']=$oa;
			}
			if($user){
				session('user_auth',null);
				session('user_auth',$user);
				$user['oper_login_pass']=null;
				$back = $user;
			}
 		}
 		return $back;
 	}

	public  function login($oper_login_code='',$oper_login_pass=''){
		$user = session('user_auth');
		// dump($user);
		if(IS_GET){
			return false;
		}else{
			$model = M();
			$where['OPER_LOGIN_CODE']=$oper_login_code;
			$where['OPER_LOGIN_PASS']=$oper_login_pass;
			$user = $model->table('mz_user.t_sys_oper')->where($where)->find();
			if($user){
				session('user_auth',null);
				session('user_auth',$user);
				return $user;
			}else{
				return false;
			}
		}
	}

	public  function loginByOa($oper_login_code='',$oa=''){
		if(empty($oper_login_code)){
			$oper_login_code = I('bill_id');
			$oa = I('oa');
		}
		$user = session('user_auth');
		if(empty($user['OPER_LOGIN_CODE'])){
			$arr['OPER_LOGIN_CODE']=$oper_login_code;
			$arr['OA']=$oa;

			if(isset($arr['OPER_LOGIN_CODE']) && isset($arr['OA'])){
				$arr['IS_LOGIN']=true;
			}
			session('user_auth',null);
			session('user_auth',$arr);
		}
	}
 
	public  function loginOut(){
		$json['success']=false;
		$json['msg']='注销失败';

		$user = session('user_auth');
		if(!empty($user)){
			 session('user_auth',null);
			 $json['success']=true;
			 $json['msg']='注销成功';
		}
		$this->ajaxReturn($json);
	}  


	//登录用户拥有的业务受理程序菜单
	public function menu_type_role($oper_id=0){
		$sql = "select distinct a.* from mz_user.t_sys_menu a , mz_user.t_sys_oper b ,mz_user.t_sys_oper_role c,mz_user.t_sys_role_menu d 
				where a.menu_func='C' 
				  and a.menu_status=1  
				  and a.menu_id=d.rm_menu_id
				  and d.rm_role_id=c.or_role_id
				  and c.or_oper_id=b.oper_id
				  and b.oper_id={$oper_id}
				  order by menu_no";
		$m = M();
		$menus = $m->query($sql);
		$menus = self::menu_sub(10,$menus);
		return $menus;
	}
 
	//分类菜单
	public function menu_type($menu_parent_id=10){
		$sql = "select * from mz_user.t_sys_menu where menu_func='C' and menu_status=1  order by menu_no";
		$m = M();
		$menus = $m->query($sql);
		$menus = self::menu_sub($menu_parent_id,$menus);
		return $menus;
	}

	public function menu_sub($menu_parent_id=0,$menus = Array(),&$dest_menus){
		if(is_array($menus)){
			foreach ($menus as $key => $menu) {
				if($menu['MENU_PARENT_ID']==$menu_parent_id){
					$dest_menus[]=$menu;
					$this->menu_sub($menu['MENU_ID'],$menus,$dest_menus);
				}
			}
		}
		return $dest_menus;
	}
}
?>