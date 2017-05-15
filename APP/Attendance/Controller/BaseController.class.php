<?php 
namespace Attendance\Controller;
use Admin\Controller\AdminController;
class BaseController extends AdminController{
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function isLogin(){
		$user = sesion('user_auth');
		if($user == ''){
			echo "session过期，请重新登录OA";
		}else{
			if(!session('user_auth.OPER_ID')){
				$sql = "select oper_id from mz_user.t_sys_oper where oper_login_code = '".session('user_auth.OPER_LOGIN_CODE')."'";
				$oper = M()->query($sql);
				$oper_id = $oper[0]['OPER_ID'];
				$_SESSION['user_auth']['OPER_ID'] = $oper_id;
			}
			return $user;
		}
	}
	//当前用户是否有某角色
	public function hasRole($roleId = ''){
		$flag = false;
		if($roleId != ''){
			$sql = "select count(or_id) count from mz_user.t_sys_oper_role where or_oper_id=".session('user_auth.OPER_ID')."and or_role_id={$roleId}";
			$num = M()->query($sql);
			if($num[0][COUNT]>0){
				$flag = true;
			}
		}
		return $flag;
	}
	//左侧目录树菜单
	public function privileges(){
		$sql = "select e.id,e.menu_name,e.parent_id pid,e.url from (select id from tp_sys_user where status = '1' and login_code = '".session('loginUser.login_code')."') a left join tp_sys_user_role b on a.id = b.user_id left join tp_sys_role c on b.role_id = c.id left join tp_sys_role_right d on c.id = d.role_id left join tp_sys_right e on d.right = e.id";
		$ul = M('sysUser')->field('user_level')->where('id='.session('loginUser.id'))->find();
		if($ul['USER_LEVEL']>='4' || $this->hasRole(1)==true){
			$sql = $sql."union select id,menu_name,parent_id pid,url from tp_sys_right where id=6 and status = '1'";
		}
		$model = M();
		$arr = $model->query($sql);
		$privileges = $this->privileges_sub($arr);
		return $this->assign('privileges',json_encode($privileges));
	}
	//数据库查询出来的字段均为大写，转换成ztree目录树对应字段
	public function privileges_sub($arr=null){
		$arr1 = array();
		$m = M();
		if(is_array($arr)){
			foreach($arr as $key=>$v){
				if($v['ID'] == ''){
					unset($v);
					continue;
				}
				$v['id'] = $v['ID'];
				$v['pId'] = $v['PID'];
				$v['name'] = $v['NAME'];
				$v['file'] = $v['URL'];
				unset($v['ID']);
				unset($v['PID']);
				unset($v['NAME']);
				unset($v['URL']);
				$arr1[] = $v;
			}
		}
		return $arr1;
	}
}
?>