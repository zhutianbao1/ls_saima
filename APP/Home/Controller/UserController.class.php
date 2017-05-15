<?php

namespace Home\Controller;

use Home\Controller\BaseController;
use Admin;

class UserController extends BaseController {

	


	public function index(){
		$this->redirect('Task/main');
	}

	//管理员更新信息
	public function modify(){
		$flag = false;

		$t  = D('UserInfo');
		$hisUser = $t->find(I('id'));

		if(empty($hisUser)){
			$this->error('用户不存在');
		}

		if(IS_POST){
			$user = D("UserInfo");
			$userInfo['id']=I('id');
			$userInfo['nc']=I('nc');
			$userInfo['qq']=I('qq');
			$userInfo['phone']=I('phone');
			$userInfo['email']=I('email');
			$userInfo['status']=I('status');
			$userInfo['manager']=I('manager');
			if($userInfo['id']){
				$re = $user->where('id='.$userInfo['id'])->save($userInfo);
				//echo $user->getLastSql();
				if($re){
					$flag=true;
				}
			}
		}
		if($flag){
			echo '更新成功';
			$this->redirect('users');
		}else{
			$this->error('更新失败');
		}
	}
	
	//删除
	public function delete(){
		$t = D('userInfo');
		$id = I('id');
		$t->delete($id);
		$c = D('userCharge');
		$w['user_id']=$id;
		$c->where($w)->delete();
		$this->redirect('users');
	}

	//用户列表
	public function users(){
		C('TOKEN_ON',false);
		$t = M('userInfo');
		$nc = I('post.nc');
		$email = I('post.email');
		$phone= I('post.phone');
		if(!empty($nc)){
			$w['nc']=array('like','%'.$nc.'%');
		}
		if(!empty($email)){
			$w['email']=$email;
		}
		if(!empty($phone)){
			$w['phone']=$phone;
		}
		$list = $t->where($w)->select();
		$this->assign('users',$list);
		$this->display();
	}

	//基本信息
	public function info(){
		$this->display();
	}

	public function relogin(){
		$this->display('user/login');
	}

	public function login(){
		if(IS_POST){
			$userInfo = parent::login();
			if($userInfo){
				if($userInfo['status']==0){
					$this->error('登录失败,失效用户不允许登陆,请联系我们');
				}else if($userInfo['manager']==0){
					$this->error('提示：您的账号审核中..请联系客服。');
				}else{
					session('user_auth',$userInfo);
					$this->assign('user',$userInfo);
					$this->redirect('disp/index');	
				}
			}else{
				$this->error('登录失败,用户名或密码错误');
			}
		}else{
			$this->display();
		}
	}

	//注册
	public function register(){
		if(IS_POST){
		
			$user = D("UserInfo");
			$userInfo=$user->create();

			$userInfo['manager']=1;
			$users = $user->where("nc='".trim($userInfo['nc'])."' or phone='".trim($userInfo['phone'])."'")->select();
			if(!empty($users)){
				$this->error('用户名或手机号码已经被注册,请重新填写');
			}

			if($userInfo){
				if(empty($userInfo['id'])){				 
					$userInfo['pwd']=md5($userInfo['pwd']);
					$id = $user->add($userInfo);
					if($id){
						//添加账户
						$t = M('chargeFee');
						$acc['fee_user_id']=$id;
						$acc['fee_amount']=0;
						$acc['fee_status']=1;
						$t->add($acc);

						$userInfo['id']=$id;
						session('user_auth',null);
						$this->redirect('relogin',null,3,'注册成功,请重新登录，跳转中..');						
					}else{
						$this->error('注册失败');
					}
				}else{
					if($user->save()){
						$this->success("信息更新成功,请重新登录",'relogin',3);
					}else{
						$this->error("更新失败");
					}
				}
			}else{
				$this->error("注册失败:".$user->getError());
			}
			exit();
		}
		$this->display('register');
		
	}
	 
	
	public function loginOut(){
		session('user_auth',null);
		$this->display('login');
	}
	
	
	//店铺管理
	public function shop($del=0){
		$c    = M('shopConfig');
		if(IS_POST){
				$config['id']=I('id');
				$config['pid']=I('pid');
				$config['name']=str_replace(" ","", I('name'));
				$config['url']=str_replace(" ","", I('url'));
				$config['create_date']=time();	

				$where['name']=$config['name'];
				$where['url'] =$config['url'];
				$where['_logic'] = 'OR';	

				if(!empty($config['id'])){

					$exitShop = $c->where($where)->select();
					if($exitShop){
						if(!($c->where('id='.$config['id'])->save($config))){
							$this->error('编辑失败'.$c->getError());
						}
					}else{
						$this->error('指定店铺信息不存在');
					}					
				}else{

					$exitShop = $c->where($where)->select();
					if($exitShop){
						$this->error("您输入的信息有误或已存在，请重新提交");
					}

					$config['status']=1;
					$config['user_id']=$_SESSION['user_auth']['id'];

					if(!($c->add($config))){
						$this->error('添加失败'.$c->getError());
					}
				}
		}
		
		$id = I('id');
		
		if(!($del==0)){
			$d['status']=0;
			$c->where('id='.$id)->save($d);
		}

		$shenhe = I('shenhe');
		if(!($shenhe==0)){
			$d['is_check']=1;
			$c->where('id='.$id)->save($d);
		}
		$this->shops(false);
		C('TOKEN_ON',false);
		$this->display();
	}

	//店铺列表
	public function shops($flag){
		$config = M('shopConfig');
        if($flag){
            $w='status=1 and LENGTH(NAME)>0 and is_check=1 and user_id='.$_SESSION['user_auth']['id'];    
        }else{
            if($_SESSION['user_auth']['manager']==C('IS_ADMIN')){
                $w='status=1 and LENGTH(NAME)>0';  
            }   else{
                $w='status=1 and LENGTH(NAME)>0 and user_id='.$_SESSION['user_auth']['id'];
            }
        }
        $shops = $this->lists($config,'',$w,' create_date desc,name',true,$base='1=1');
        $this->assign('shops',$shops);
	}
 	
 	//用户协议
 	function xy(){
 		$this->display();
 	}

 	//获取店铺归属平台
    function getShopWeb($id=0){
        $t = D('typeConfig');
        $web_name = $t->where("id=".$id)->field('name')->find();
        return $web_name;
    }
}

?>