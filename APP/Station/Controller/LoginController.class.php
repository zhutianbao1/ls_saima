<?php

namespace Station\Controller;

use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class LoginController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
 	}

    //注册
    public function regist(){
        $node_login_code=I('node_login_code');
        $bill_id=I('bill_id');
        $user_pwd=I('user_pwd');
        $w['node_login_code']=$node_login_code;
        $w['bill_id']=$bill_id;
        $m=M();
        $user=$m->table('station_flow_nodes')->where($w)->find();
        $data['user_pwd']=$user_pwd;
        if(!empty($user)){
            if(empty($user['USER_PWD'])){
                $flag=$m->table('station_flow_nodes')->where($w)->save($data);
                if($flag){
                    $j['status']="1";
                    $j['msg']="注册成功!请妥善保管自己的账号密码!";
                }else{
                    $j['status']="0";
                    $j['msg']="注册失败!";
                }
            }else{
                $j['status']="0";
                $j['msg']="您已注册!请不要重复注册!";
            }
        }else{
            $j['status']="0";
            $j['msg']="未检测到您输入的平台账号和手机号码的信息!";
        }
        $this->ajaxReturn($j);
    } 



 //登录 
 	public function  login(){
        if(IS_GET){
            $this->display();
        }

        if(IS_POST){
            $node_login_code=I('node_login_code');
            $bill_id=I('bill_id');
            $user_pwd=I('user_pwd');

            $result=array('status'=>'','msg'=>'');
            if(empty($node_login_code)){
                $result['status']='0';
                $result['msg']="请输入平台账号!";
            }
            if(empty($bill_id)){
                $result['status']='0';
                $result['msg']="请输入手机号码!";
            }

            if(empty($user_pwd)){
                $result['status']='0';
                $result['msg']="请输入账号密码!";
            }
            if(!empty($node_login_code)&&!empty($bill_id)&&!empty($user_pwd)){
                $m=M();
                $w['node_login_code']=$node_login_code;
                $w['bill_id']=$bill_id;
                $w['status']='1';
                $w['user_pwd']=$user_pwd;

                $list=$m->table('station_flow_nodes')->where($w)->find();
                    if(!empty($list)){
                        $result['status']='1'; 
                        $result['msg']="登录成功!";
                        session_start();
                        $_SESSION['node']=$list;
                    }else{
                        $result['status']='0';
                        $result['msg']="平台账号或手机号码或密码不正确!";
                    }
            }
            $this->ajaxReturn($result);
        }
    }

    //密码修改
    public function user_pwd_mod(){
        if(IS_GET){
            $this->display();
        }
        if(IS_POST){
            $node_login_code=I('node_login_code');
            $bill_id=I('bill_id');
            $verfy_code=I('verfy_code');
            $user_pwd=I('user_pwd');

            $w['node_login_code']=$node_login_code;
            $w['bill_id']=$bill_id;
            $w['status']='1';
            $w['verfy_code']=$verfy_code;
            $m=M();
            $data['user_pwd']=$user_pwd;
            $list=$m->table('station_flow_nodes')->where($w)->find();
            if(!empty($list)){
                $verfy_date=$list['VERFY_DATE'];
                if(time()-$verfy_date<=300){
                    $flag=$m->table('station_flow_nodes')->where($w)->save($data); 
                    if($flag){
                        $j['status']='1';
                        $j['msg']='密码重置成功!请使用新密码登录!';
                    }else{
                        $j['status']='0';
                        $j['msg']="密码重置失败!";
                    } 
                }else{
                    $j['status']='0';
                    $j['msg']='验证码已失效!请重新获取新的验证码!';
                }
            }else{
                $j['status']='0';
                $j['msg']='平台账号或手机号码或验证码不正确!';
            }
            $this->ajaxReturn($j);
        }
    }

    //验证码
    public function getverfy_code(){
        $node_login_code=I('node_login_code');
        $bill_id=I('bill_id');
        $verfy_code=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        if(empty($node_login_code)){
            $j['status']='0';
            $j['msg']='请输入平台账号!';
        }
        if(empty($bill_id)){
            $j['status']='0';
            $j['msg']='请输入手机号码!';
        }

        if(!empty($node_login_code)&&!empty($bill_id)){
            $m=M();
            $w['node_login_code']=$node_login_code;
            $w['bill_id']=$bill_id;
            $w['status']='1';
            $user=$m->table('station_flow_nodes')->where($w)->find();
            if(!empty($user)){
                $data['verfy_code']=$verfy_code;
                $data['verfy_date']=time();
                $flag=$m->table('station_flow_nodes')->where($w)->save($data);
                if($flag){
                    $j['status']='1';
                    parent::sms($bill_id,$user['NODE_USERNAME'].'您好,您正在重置无线基站平台的登录密码!,验证码为'.$verfy_code.',有效时间为五分钟!');
                    $j['msg']="验证码获取成功!并发送到手机,请注意查收!";
                }else{
                    $j['status']='0';
                    $j['msg']="验证码获取失败!";
                }
            }else{
                $j['status']='0';
                $j['msg']="平台账号或手机号码或密码不正确!";
            }
        }
    $this->ajaxReturn($j);
    }

}
?>