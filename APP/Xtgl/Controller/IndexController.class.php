<?php

namespace Xtgl\Controller;

class IndexController extends BaseController {

	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
 		parent::_initialize();
 	}
 	
 	public function demo(){
 		$this->display();
 	}

 	public function login(){
 		if(IS_POST){
 			$user = parent::isLogin();
 			$this->assign('userInfo',$user);
 			$this->redirect('index:index',null,2,'登录成功,正在跳转..');
 		}else{
 			$this->display();
 		}
 	}

 	public function loginOut(){
 		session('user_auth',null);
 		$this->display('login');
 	}

 	//主页
	public function index(){
		$menus = parent::menu_type();
		$this->assign('menuList',$menus);
		// foreach ($menus as $key => $menu) {
		// 	$m['id']=$menu['MENU_ID'];
		// 	$m['pId']=$menu['MENU_PARENT_ID'];
		// 	$m['name']=$menu['MENU_NAME'];
		// 	$m['url']=$menu['MENU_URL'];
		// 	$m['icon']=$menu['MENU_URL'];
		// 	$menuList[]=$m;
		// }
		// $this->assign('menuList',json_encode($menuList));
		$this->display();
	}

	//获取子菜单方法
	public function menu_sub_tree($menu_parent_id=0){
		$menus = parent::menu_type($menu_parent_id);

	}

	//用户
	public function oper(){
		$depts = parent::dept_type(10);
		$this->assign('deptList',$depts);
		$this->display();
	}

	public function oper_edit($id=0){
		$m = M();
		if(IS_POST){
			$oper_name = I('post.oper_name');
			$oper_login_code = I('post.oper_login_code');
			$oper_dept_id = I('post.oper_dept_id');
			$oper_login_pass = I('post.oper_login_pass');
			$d['oper_name']=$oper_name;
			$d['oper_login_code']=$oper_login_code;
			$d['oper_dept_id']=$oper_dept_id;
			$d['oper_status']=1;
			if(!empty($oper_login_pass)){
				$d['oper_login_pass']=$oper_login_pass;
			}
			if($id==0){
				$d['oper_id']=parent::seqId();
				$re = $m->table('mz_user.t_sys_oper')->add($d);
				if($re){
					$this->success('新增成功');
				}else{
					$this->error('新增失败');
				}
			}else{
				$re = $m->table('mz_user.t_sys_oper')->where('oper_id='.$id)->save($d);
				if($re){
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			}
		}else{
		   $oper = parent::sysOper($id);
		   $this->assign('oper',$oper);
		   $this->display();
		}
	}

	public function oper_delete($id=0){
		$json['success']=false;
		$m = M();
		$re = $m->table('mz_user.t_sys_oper')->where('oper_id='.$id)->delete();	
		if($re){
			$json['success']=true;
		}
		$this->ajaxReturn($json);
	}

	public function oper_list($dept_id=0){
		$m = M();
		$sql = "select a.* , b.dept_name from mz_user.t_sys_oper a , mz_user.t_sys_dept b where a.oper_dept_id=b.dept_id ";		
		$oper_name = I('oper_name');
		if(!empty($oper_name)){$sql.= " and a.oper_name like '%{$oper_name}%'";}
		$oper_login_code = I('oper_login_code');
		if(!empty($oper_login_code)){$sql.= " and a.oper_login_code='{$oper_login_code}'";}
		 
		
		if($dept_id>0){$sql.= " and a.oper_dept_id={$dept_id}";}
		

		$sql.=" order by a.oper_id";
		$opers = parent::listsSqlByls($sql,10);		 
		$this->assign('opers',$opers);
		$this->display();
	}

	//组织
	public function dept(){
		$depts = parent::dept_type(10);
		$this->assign('deptList',$depts);
		$this->display();
	}

	public function dept_select(){
		$depts = parent::dept_type(10);
		$this->assign('deptList',$depts);
		$this->display();
	}

	public function dept_edit($id=0){
		if(IS_POST){
			$d['dept_name']=I('post.dept_name');
			$d['dept_parent_id']=I('post.dept_parent_id');
			$d['dept_status']=I('post.status');
			$d['dept_remark']=I('post.remark');

			$m = M();
			if($id==0){
				$d['dept_id']=parent::seqId();
				$re = $m->table('mz_user.t_sys_dept')->add($d);
				if($re){
					$this->success('新增成功');
				}else{
					$this->error('新增失败');
				}
			}else{
				$re = $m->table('mz_user.t_sys_dept')->where('dept_id='.$id)->save($d);
				// echo $m->getLastSql(); die;
				if($re){
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			} 
		}else{
			$dept = parent::dept_info($id);
			$this->assign('dept',$dept);
 
			$parentDept = parent::dept_info($dept['DEPT_PARENT_ID']);
			$this->assign('deptParent',$parentDept);

			$this->display();
		}
	}

	//菜单
	public function menu($menu_func='B'){
		$menus = parent::menu_type(10,$menu_func);
		$this->assign('menuList',$menus);
		$this->display();
	}

	//菜单选择
	public function menu_select($menu_func='B'){
		$menus = parent::menu_type(10,$menu_func);
		$this->assign('menuList',$menus);
		$this->display();
	}

	public function menu_edit($id=0){
		if(IS_POST){
			$d['menu_no']=I('post.menu_no');
			$d['menu_func']=I('post.menu_func');
			$d['menu_name']=I('post.menu_name');
			$d['menu_parent_id']=I('post.menu_parent_id');
			$d['menu_url']=I('post.menu_url');
			$d['img']=I('post.img');
			$d['menu_status']=I('post.status');
			$d['remark']=I('post.remark');
			$m = M();
			if($id==0){
				$d['menu_id']=parent::seqId();
				$re = $m->table('mz_user.t_sys_menu')->add($d);
				if($re){
					$this->success('新增成功');
				}else{
					$this->error('新增失败');
				}
			}else{
				$re = $m->table('mz_user.t_sys_menu')->where('menu_id='.$id)->save($d);
				// echo $m->getLastSql(); die;
				if($re){
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			}
		}else{
			$menu = parent::menu_info($id);
			$this->assign('menu',$menu);

			$parentMenu = parent::menu_info($menu['MENU_PARENT_ID']);
			$this->assign('menuParent',$parentMenu);

			$menus = parent::menu_type();
			$this->assign('menuList',$menus);

			$this->display();
		}
	}

	//角色
	public function role(){
		$this->display();
	}

	public function role_list(){
		$m = M();
		$roles = $m->table('mz_user.t_sys_role')->where('role_status=1')->order('role_name')->select();
		$this->assign('roles',$roles);
		$this->display();
	}

	public function role_tree($role_id=0){
		$m = M();
		if(IS_POST){
			$codes = I('post.rightCode');
			//清除之前的保存数据
			$m->table('mz_user.t_sys_role_menu')->where('rm_role_id='.$role_id)->delete();

			//保存新的数据
			if(is_array($codes)){
				foreach ($codes as $key => $code) {
					$d['rm_id']=array('exp','mz_user.MY_SEQ_ROLE_RIGHT.nextval');
					$d['rm_role_id']=$role_id;
					$d['rm_menu_id']=$code;
					$d['rm_status']=1;
					$m->table('mz_user.t_sys_role_menu')->add($d);
					// echo $m->getLastSql();
				}
			}
			$this->success('保存完成');
		}else{
			$menus = parent::menu_type(10,'ALL');
			$this->assign('menuList',$menus);

			$role_menus = $m->field('rm_menu_id')->table('mz_user.t_sys_role_menu')->where('rm_role_id='.$role_id)->select();
			$this->assign('role_menus',$role_menus);
			$this->display();
		}
	}

	public function role_opers($id=0){
		$sql = "select a.* , b.dept_name from mz_user.t_sys_oper a , mz_user.t_sys_dept b,mz_user.t_sys_oper_role c where c.or_role_id={$id} and a.oper_id=c.or_oper_id and a.oper_dept_id=b.dept_id order by a.oper_dept_id";
		$opers = parent::listsSqlByls($sql,10);
		$this->assign('opers',$opers);
		$this->display();
	}

	public function role_edit($id=0){
		$m = M();
		if(IS_POST){
			$d['role_name']=I('post.role_name');
			$d['role_remark']=I('post.role_remark');
			$d['role_status']=1;
			$m = M();
			if($id==0){
				$d['role_id']=parent::seqId();
				$re = $m->table('mz_user.t_sys_role')->add($d);
				if($re){
					$this->success('新增成功');
				}else{
					$this->error('新增失败');
				}
			}else{
				$re = $m->table('mz_user.t_sys_role')->where('role_id='.$id)->save($d);
				if($re){
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			}
		}else{
			$role = parent::sysRole($id);
			$this->assign('role',$role);
			$this->display();
		}
	}
}
?>