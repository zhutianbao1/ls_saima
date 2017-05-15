<?php

namespace Station\Controller;

use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class BaseController extends AdminController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){

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

			// $model = M();
			// $where['OPER_LOGIN_CODE']=$oper_login_code;
			// $where['OA']=$oa;
			// $user = $model->table('mz_user.t_sys_oper')->where($where)->find();
			// if($user){
			// 	return $user;
			// }else{
			// 	return false;
			// }
		}
	}

	public function login_moa($oa=''){
		if(empty($oa)){
			$oa=I('userName');
		}
		$where['OA']=$oa;
		$u=M()->table('mz_user.t_sys_oper')->where($where)->find();
		$this->assign('u',$u);
		$user = session('user_auth');
		if(empty($user['OA'])){
			$arr['OA']=$oa;
			$arr['OPER_LOGIN_CODE']=$u['OPER_LOGIN_CODE'];

			if(isset($arr['OA'])){
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

	//访问日志
	public function viewLog(){
		$page = $this->get_url();
		$user = session('user_auth');
		if($page){
			$model = M('viewLog');
			$log['ID']="rank_seq.nextval";
			if(!empty($user)){
				$log['OPER_ID']=$user['OPER_ID'];
				$log['BILL_ID']=$user['OPER_LOGIN_CODE'];	
				$log['OA']=$user['OA'];	
			}
			$log['IP']=$_SERVER['REMOTE_ADDR'] ;
			$log['PAGE']=$page;
	 
			$logFlag = $model->orcAdd('rank_view_log',$log);
		}
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

	//条线配置列表
	public function ConfigList(){
		$config = M('config');
		$configs= $config->where('status=1 and month=201607')->order('id')->cache('configList',180)->select();
		$this->assign('configs',$configs);
		return $configs;
	}

	//获取用户清单的表头
	public function getUserCon($config_id,$month){
		$userCon = M('userCon');
		$config_idh = '100'.$config_id;
		$conWhere['a']=$config_idh;
		$conWhere['g']=$month;
		$head = $userCon->where($conWhere)->find();
		$this->assign('infoHead',$head);
		return $head;
		//dump($head);
	}

	//获取用户细项数据
	public function getUserData($bill_id,$config_id,$month){
		$userCon = M('userCon');
		$conW['a']=$config_id;
		$conW['e']=$bill_id;
		$conW['g']=$month;
		$data = $userCon->where($conW)->find();
		$this->assign('infoData',$data);
		return $data;
	}


	//短信发送
    public function sms($dest_bill,$content){
        $rank = M('book');
        $data['ID']='my_seq_sms_job.nextval';
        $data['BILL_ID']=$dest_bill;
        $data['SMS']=$content;
        $insert = $rank->db(1,'LS_CONFIG')->orcAdd('ls_sms_job',$data);
        if($insert){
            return true;
        }else{
            return false;
        }
    }


    

}
?>