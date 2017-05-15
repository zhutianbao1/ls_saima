<?php

namespace Salary\Controller;

class LoginController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
      
      
 	}
  

    //登录注册
    public function salary_regist(){
        $this->display();
    }
   
    //注册
    public function salary_user_reg(){     
        $m=M('user_salary_pwd');
        $user_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $reg_pwd=I('reg_pwd');
        $reg_pwd2=I('reg_pwd2');
        $reg_name=I('reg_name');
        $user=$m->where("user_id='%s'",$reg_name)->find();
        if(!empty($user_id)){
            if(empty($user)){
                if($user_id==$reg_name&&$reg_pwd==$reg_pwd2){
                      $data['user_pwd']=$reg_pwd;
                      $data['user_id']=$user_id;
                      $data['login_date']=time();
                      $data['status']='1';
                      $flag=$m->add($data);
                    if($flag){
                        $msg="密码设置成功";
                    }else{
                        $msg="账号或密码错误"; 
                    }            
                }else{
                    $msg="账号或密码错误!";
                }
            }else{
                $msg="此账号已注册,请直接登录!";
            }        
        }else{
            $msg='未获得您的OA信息,请登录OA';
        }
        $this->ajaxReturn($msg);
    } 

   

    //登录
    public function salary_user_log(){
        $user_id1=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $user_id=I('log_name');
        $user_pwd=I('log_pwd');
        $m=M('user_salary_pwd');
        if(!empty($user_id1)){
            if($user_id==$user_id1){
                $flag=$m->where("user_id='%s'",$user_id)->find();
                if(!empty($flag)){
                $flag=$m->where("user_id='%s' and user_pwd='%s'",$user_id,$user_pwd)->find();
                    if(!empty($flag)){
                        $msg="登录成功";
                        session_start();
                        $salary['login_time']=time();
                        $salary['salary_pwd']=$user_pwd;
                        $_SESSION['salary']=$salary;    
                    }else{
                        $msg="账号或密码错误";
                    }
                }else{
                    $msg="此账号尚未注册!请先注册!";
                }
            }else{
                $msg="您输入的手机号码与您OA信息不符合!";
            }
        }else{
            $msg="未获得您的OA信息,请登录OA";
        }
      $this->ajaxReturn($msg);
    }





  
  

  //验证码
    public function salary_user_yzm(){
        $req_name=I('req_name');
        $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        if(!empty($bill_id)){
            if($req_name==$bill_id){
            $yanzhengma=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);    
            $mesg=parent::sms($bill_id,'您正在重置密码,验证码为'.$yanzhengma.',五分钟内有效!');  
                if($mesg==true){
                    $data['send_mesg']=$yanzhengma;
                    $data['send_date']=time();
                    $m=M('user_salary_pwd'); 
                    $flag=$m->where("user_id='%s'",$bill_id)->save($data);
                    if($flag){
                       $msg='验证码已发送到您手机,请注意查收!';
                    }        
                }else{
                    $msg='验证码发送失败!';
                } 
            }else{
                $msg='您输入的手机号码与您OA信息不符合';
            }
        }else{
           $msg='未获得您的OA信息,请登录OA!';
        } 
        $this->ajaxReturn($msg);
    }



    //密码重置
    public function salary_user_mod(){ 
        $req_name=I('req_name'); 
        $req_pwd=I('req_pwd'); 
        $req_pwd2=I('req_pwd2'); 
        $req_ywd=I('req_ywd'); 
        $m=M('user_salary_pwd');
        $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        if(!empty($bill_id)){
            if($req_name==$bill_id){
                if($req_pwd==$req_pwd2){
                    $list=$m->where("user_id='%s'",$bill_id)->find();
                    if($req_ywd==$list['SEND_MESG']){
                        if(time()-$list['SEND_DATE']<=300){            
                            $data['user_pwd']=I('req_pwd');
                            $data['login_date']=time();
                            $flag=$m->where("user_id='%s'",$bill_id)->save($data);
                            if($flag){
                                $msg="密码重置成功";
                                session_start();
                                $salary['login_time']=time();
                                $salary['salary_pwd']=$req_pwd;
                                $_SESSION['salary']=$salary;    
                            }else{
                                $msg="密码重置失败";
                            }
                        }else{
                          $msg="验证码已过期,请重新申请";
                        }        
                    }else{
                       $msg="验证码错误";
                    } 
                }else{
                    $msg='您输入的两次密码不一致!';
                }
            }else{
                $msg='您输入的手机号码与您OA信息不符合'; 
            }
        }else{
            $msg='未获得您的OA信息,请登录OA!';
        } 
       $this->ajaxReturn($msg);
    }

    //公共账号 
    public function salary_user_pub(){
        $pub_name=I('pub_name'); 
        $pub_pwd=I('pub_pwd');
        if($pub_name=='13900000000'&&$pub_pwd=='OOoo88abc123'){
            session_start();        
            $salary['salary_pwd']='OOoo88abc123';
            $salary['login_time']=time();
            $_SESSION['salary']=$salary; 
            $user_auth['OPER_LOGIN_CODE']='13900000000';
            $_SESSION['user_auth']=$user_auth;
            $msg="登录成功!";
        }else{
            $msg="账号或密码错误!";
        }
        $this->ajaxReturn($msg);
    }








 
 

}
?>