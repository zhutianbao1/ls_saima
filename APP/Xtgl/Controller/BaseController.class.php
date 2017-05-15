<?php

namespace Xtgl\Controller;

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
				$this->redirect('Form/Base/isLogin',null,2,'正在跳转..');
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
 			$sql = "select * from mz_user.t_sys_oper where 1=1 ".$where; 		 
 			$users = $m->query($sql);
 			if($users){
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

 	//菜单信息
 	public function menu_info($id=0){
 		$m = M();
 		$where['menu_id']=$id;
 		$menu = $m->table('mz_user.t_sys_menu')->where($where)->find();
 		return $menu;
 	}

	//分类菜单
	public function menu_type($menu_parent_id=10,$menu_func='B'){
		if($menu_func=='ALL'){
			$sql = "select * from mz_user.t_sys_menu where menu_status=1  order by menu_func,menu_no";
		}else{
			$sql = "select * from mz_user.t_sys_menu where menu_func='{$menu_func}' and menu_status=1  order by menu_func,menu_no";
		}
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

	//组织信息
	public function dept_info($id=0){
		$m = M();
 		$where['dept_id']=$id;
 		$dept = $m->table('mz_user.t_sys_dept')->where($where)->find();
 		return $dept;
	}

	//分类组织
	public function dept_type($dept_parent_id=0){
		$sql = "select * from mz_user.t_sys_dept where  dept_status=1 order by dept_id";
		$m = M();
		$depts = $m->query($sql);
		$depts = self::dept_sub($dept_parent_id,$depts);
		return $depts;
	}

	public function dept_sub($dept_parent_id=0,$depts = Array(),&$dest_depts){
		if(is_array($depts)){
			foreach ($depts as $key => $dept) {
				if($dept['DEPT_PARENT_ID']==$dept_parent_id){
					$dest_depts[]=$dept;
					$this->dept_sub($dept['DEPT_ID'],$depts,$dest_depts);
				}
			}
		}
		return $dest_depts;
	}

	public function create_table($table_name,$table_cols){
		if(is_string($table_name) && is_array($table_cols)){
			$sql = "create table ".$table_name." ( ";
			foreach ($table_cols as $key => $col) {
				if(!empty($col->name) && !empty($col->type)){
					$sql_tmp.= $col->name." ".$col->type.(empty($col->length)?'':'('.$col->length.')')." ".$col->isNull;
					if(!empty($col->moren)){
						$sql_tmp.=" default ".$col->moren;
					}
					$sql_tmp.=",";
				}
			}
			$sql.=substr($sql_tmp,0,-1);
			$sql.= " )";
			$m = M();
			$flag = $m->execute($sql);
			return $flag;
		}
		return false;
	}

	public function table_exists($table_name=''){
		$table_name=trim($table_name);
		if(empty($table_name)){
			return false;
		}

		$db_type = C('DB_type');
		if(strtoupper($db_type)=='MYSQL'){
			$rs = M()->query("SHOW TABLES LIKE '".$table_name."'");
			if(!$rs){
				return false;
			}else{
				return true;
			}
		}

		if(strtoupper($db_type)=='ORACLE'){
			$sql = "select * from user_all_tables where table_name='".strtoupper($table_name)."'";
			$m = M();
			$re = $m->query($sql);
			if($re){ return true; }else{ return false; }
		}

	}


	public function seqId(){
		return date('YmdHis',time()).rand(1000,9999);
	}

	//所有用户
	public function sysOpers(){
		$m = M();
		$sql = "select a.* , b.dept_name from mz_user.t_sys_oper a , mz_user.t_sys_dept b where a.oper_dept_id=b.dept_id order by a.oper_dept_id";
		$opers = $m->query($sql);
		return $opers;
	}

	public function sysOper($oper_id=0){
		$m = M();
		$sql = "select a.* , b.dept_name from mz_user.t_sys_oper a , mz_user.t_sys_dept b where a.oper_id={$oper_id} and a.oper_dept_id=b.dept_id order by a.oper_dept_id";
		$opers = $m->query($sql);
		return $opers[0];	
	}

	//所有菜单
	public function sysMenus(){
		$m = M();
		$where['menu_status']=1;
		$menus = $m->table('mz_user.t_sys_menu')->where($where)->select();
		return $menus;
	}

	//所有角色
	public function sysRoles(){
		$m = M();
		$where['role_status']=1;
		$roles = $m->table('mz_user.t_sys_role')->where($where)->select();
		return $roles;
	}

	public function sysRole($id=0){
		$m = M();		 
		$role = $m->table('mz_user.t_sys_role')->where('role_id='.$id)->find();
		return $role;
	}

	//所有组织
	public function sysDepts(){
		$m = M();
		$where['dept_status']=1;
		$depts = $m->table('mz_user.t_sys_dept')->where($where)->select();
		return $depts;
	}

	//所有报表
	public function lsRpts(){}

	//所有表单
	public function lsForms(){}
 
	//角色拥有的用户
	public function sysRoleOpers($role_id=0){
		$m = M();
		$sql = "select a.* , b.dept_name from mz_user.t_sys_oper a , mz_user.t_sys_dept b,mz_user.t_sys_oper_role c where c.or_role_id={$role_id} and a.oper_id=c.or_oper_id and a.oper_dept_id=b.dept_id order by a.oper_dept_id";
		$opers = $m->query($sql);
		return $opers;
	}

	//用户拥有角色
	public function sysOperRoles(){}

	//用户拥有的菜单树
	public function sysOperMenus($oper_id=0){
		
	}

	//用户拥有报表
	public function sysOperRpts(){}

	//用户拥有表单
	public function sysOperForms(){}

	//报表对应角色
	public function rptRoles(){}

	//报表对应用户
	public function rptUsers(){}

	//表单对应角色
	public function formRoles($form_id=0){
		$m = M();
		$form = $m->table('ls_form_role_right')->where('form_id='.$form_id)->find();

		$where['_string']=' role_id in ('.$form['ROLE_ID'].')';
		$roles = $m->table('mz_user.t_sys_role')->where($where)->select();
		return $roles;
	}

	//表单对应用户
	public function formUsers($form_id=0){
		$m = M();
		$form = $m->table('ls_form_users')->where('form_id='.$form_id)->find();
		// echo $m->getLastSql();

		$where['_string']=' oper_id in ('.$form['OPER_ID'].')';
		$users = $m->table('mz_user.t_sys_oper')->where($where)->select();
		// echo $m->getLastSql();
		return $users;
	}
}
?>