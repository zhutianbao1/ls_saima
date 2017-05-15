<?php

namespace Dagl\Controller;
use Dagl\Controller\BaseController;

class IndexController extends BaseController {

 

    public function index(){
        if(parent::isLogin()){
            /*
            if(!session('user_auth.OPER_ID')){
                $sql="select oper_id from mz_user.t_sys_oper where oper_login_code='".session('user_auth.OPER_LOGIN_CODE')."'";
                $oper=M()->query($sql);
                $oper_id=$oper[0]['OPER_ID'];
                $_SESSION['user_auth']['OPER_ID']=$oper_id;
            }
            */
            $this->display('./index');
            //$dagl=A('Dagl');
            //$dagl->daList();
        }else{
            $this->display('./login');
        }
        
    }

    //用户登录
    public function login($loginCode='',$loginPwd=''){
        $sql="select oper_id,oper_login_code,oa from mz_user.t_sys_oper where oper_login_code='{$loginCode}' and oper_login_pass='{$loginPwd}'";
        $user=M()->query($sql);
        if($user){
            session('user_auth',null);
            $user_auth['OPER_LOGIN_CODE']=$user[0]['OPER_LOGIN_CODE'];
            $user_auth['OA']=$user[0]['OA'];
            $user_auth['IS_LOGIN']=true;
            $user_auth['OPER_ID']=$user[0]['OPER_ID'];
            session('user_auth',$user_auth);
            self::index();
        }else{
            $this->display('./login');
        }
    }
    
}

?>