<?php

namespace Zxyxsb\Controller;
use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class BaseController extends AdminController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
        parent::_initialize();
 	}

    //判断用户是否登录
    public function isLogin(){
        $user = session('user_auth');//OA登录没有OPER_ID字段
        if($user==''){
            $this->display('Index/login');
            return false;
        }else{
            if(!session('user_auth.OPER_ID')){
                $sql="select oper_id from mz_user.t_sys_oper where oper_login_code='".session('user_auth.OPER_LOGIN_CODE')."'";
                $oper=M()->query($sql);
                $oper_id=$oper[0]['OPER_ID'];
                $_SESSION['user_auth']['OPER_ID']=$oper_id;
            }
            return true;
        }
    }

    //用户登录
    public function login($loginCode='',$loginPwd=''){
        session('user_auth',null);
        $sql="select oper_id,oper_login_code,oa from mz_user.t_sys_oper where oper_login_code='{$loginCode}' and oper_login_pass='{$loginPwd}'";
        $user=M()->query($sql);
        if($user){
            $user_auth['OPER_LOGIN_CODE']=$user[0]['OPER_LOGIN_CODE'];
            $user_auth['OA']=$user[0]['OA'];
            $user_auth['IS_LOGIN']=true;
            $user_auth['OPER_ID']=$user[0]['OPER_ID'];
            session('user_auth',$user_auth);
            $this->redirect('./index');
        }else{
            $this->display('Index/login');
        }
    }
	
    //含当前模块所须角色(传数组)
    public function hasRoles($roleIds=array()){
        $arr1 = array();
        $isLogin = $this->isLogin();//判断是否登录,未登录返回登录页面
        if(!$isLogin){
            exit;
        }
        
        $i = 0;//个人含角色个数
        if(count($roleIds) > 0){
            $operId = session('user_auth.OPER_ID');
            foreach($roleIds as $k => $roleId){
                $sql="select count(or_id) count from mz_user.t_sys_oper_role where or_oper_id={$operId} and or_role_id={$roleId}";
                $num = M()->query($sql);
                if($num[0][COUNT] > 0){
                    $arr1[$k] = true;
                    $i++;
                }else{
                    $arr1[$k] = false;
                }
            }
        }

        if($i == 0){
            echo '您无此模块权限';
            exit;
        }else{
            return $arr1;
        }
    }
}