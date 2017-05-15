<?php

namespace Baob\Controller;

class LoginController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
		
 	}

    
    //渠道经营信息主页
    public function channel_index(){ 
        $m=M();
        $w['bill_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $w['status']='1';
        $user=$m->table('t_channel_user')->where($w)->find();
        if(!empty($user)){
            $_SESSION['channel']=$user;
            $msg='1';
        }else{
            $msg='0';
        }
        $this->assign('msg',$msg);
        $this->display();
    } 

  
    //营销活动缴费卡打印平台首页 
    public function payment_index(){
        $m=M();
        $oa=$_SESSION['user_auth']['OA'];
        if(!empty($oa)){
            $where['oa']=$oa;
            $where['status']='1';
            $user=$m->table('t_pcard_auth_oper')->where($where)->find();
            if(!empty($user)){
                $_SESSION['payment']=$user;
                $_SESSION['payment']['CATE']='1';
                $msg='1';
                if(strpos($user['AUTH_NO'],'2')!== false){
                    $auth2='2';
                    $this->assign('auth2',$auth2);
                }
                if(strpos($user['AUTH_NO'],'3')!== false){
                    $auth3='3';
                    $this->assign('auth3',$auth3);
                }
                if(strpos($user['AUTH_NO'],'4')!== false){
                    $auth4='4';
                    $this->assign('auth4',$auth4);
                }
                if(strpos($user['AUTH_NO'],'5')!== false){
                    $auth5='5';
                    $this->assign('auth5',$auth5);
                }
                if(strpos($user['AUTH_NO'],'6')!== false){
                    $auth6='6';
                    $this->assign('auth6',$auth6);
                }
                if(strpos($user['AUTH_NO'],'7')!== false){
                    $auth7='7';
                    $this->assign('auth7',$auth7);
                }

                if(strpos($user['AUTH_NO'],'8')!== false){
                    $auth8='8';
                    $this->assign('auth8',$auth8);
                }
                $this->assign('msg',$msg);
            }
            
            $w['manager_oa']=$oa;
            $oper=$m->table('t_pcard_org_info')->where($w)->find();
            if(!empty($oper)){
                $payment['COUNTY_CODE']=$oper['COUNTY_CODE'];
                $payment['COUNTY_NAME']=$oper['COUNTY_NAME'];
                $payment['OA']=$oper['MANAGER_OA'];
                $payment['OPER_LOGIN_CODE']=$oper['MANAGER_PHONE'];
                $payment['OPER_NAME']=$oper['MANAGER_NAME'];
                $_SESSION['payment']=$payment;
                $_SESSION['payment']['CATE']='2';
                $msg2='1';
                $auth='2';
                $this->assign('auth',$auth);
                $this->assign('msg2',$msg2);
            }
        }else{
            $msg3='11';
            $this->assign('msg3',$msg3);
        }
       
        $info=I('info');
        if(!empty($info)){
            $this->assign('info',$info);
        }

        if(!empty($_SESSION['payment']['OPER_LOGIN_CODE'])){
            $status='1';
        }
        $this->assign('status',$status);
       
        $this->display('payment/index');
       // dump($_SESSION);
      
    }




}
?>