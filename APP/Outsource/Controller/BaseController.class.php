<?php

namespace Outsource\Controller;
use Admin\Controller\AdminController;

class BaseController extends AdminController {
	protected function _initialize(){
		parent::_initialize();
		$CONTROLLER_NAME = $Think.CONTROLLER_NAME;
		$ACTION_NAME  = $Think.ACTION_NAME;	 

		$this->login();
		$user = session('user_auth');
		$this->assign('user_auth',$user);

		parent::intiParams();

		//获取详细的用户信息
		$where=" and oper_login_code='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
		$this->loginUser($where);
	}

	public function isLogin(){
 		 $user = session('user_auth');
 		 if($user){
			session('user_auth',null);
			session('user_auth',$user);
			return $user;
		 }else{
			$this->display('Sys/login');
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
					$back = $user;
				}
		    }
 		}
 		// dump($back);
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
 
	/**
	 * 获取当前页面完整URL地址
	 */
	function get_url() {
	    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
	}	
}
?>