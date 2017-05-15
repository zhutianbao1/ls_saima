<?php

namespace Dagl\Controller;
use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class BaseController extends AdminController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
        parent::_initialize();
 	}

    public function isLogin(){
        $user  = session('user_auth');
        if($user==''){
            $this->display('./login');
            return false;
        }else{
            if(!session('user_auth.OPER_ID')){
                $sql="select oper_id from mz_user.t_sys_oper where oper_login_code='".session('user_auth.OPER_LOGIN_CODE')."'";
                $oper=M()->query($sql);
                $oper_id=$oper[0]['OPER_ID'];
                $_SESSION['user_auth']['OPER_ID']=$oper_id;
            }
            return $user;
        }

    }
 	//当前用户是否有某角色
    public function hasRole($roleId=''){
        $flag = false;
        if($roleId != ''){
            $sql="select count(or_id) count from mz_user.t_sys_oper_role where or_oper_id=".session('user_auth.OPER_ID')." and or_role_id={$roleId}";
            $num = M()->query($sql);
            if($num[0][COUNT] > 0){
                $flag = true;
            }
        }
        return $flag;
    }


	//左侧目录树菜单
 	public function privileges(){
        $TempSql = "select e.id,e.menu_name name,e.parent_id pid,e.url from (select id from tp_sys_user where status='1' and login_code='".session('loginUser.login_code')."') a 
            left join tp_sys_user_role b on a.id=b.user_id left join tp_sys_role c on b.role_id= c.id 
            left join tp_sys_role_right d on c.id=d.role_id left join tp_sys_right e on d.right_id=e.id";

        //用户级别>=4的有档案管理模块
        $ul=M('sysUser')->field('user_level')->where('id='.session('loginUser.id'))->find();
        if($ul['USER_LEVEL'] >='4' || $this->hasRole(1)==true){
            $TempSql = $TempSql." union select id,menu_name name,parent_id pid,url from tp_sys_right where id=6 and status='1'";
        }

        $model = M();
        $arr = $model->query($TempSql);
        $privileges = $this->privileges_sub($arr);
        //dump($privileges);//PHP输出格式为Array;
        //dump(json_encode($privileges));//PHP输出格式由Array转为JSON;

        return $this->assign('privileges',json_encode($privileges));;
 	}

    //数据库查出的数组字段均为大写，须转成ztree目录树对应的字段
    public function privileges_sub($arr=null){
        $arr1 = array();
        $m = M();
        if(is_array($arr)){
            foreach ($arr  as $key => $v) {
                //删除空行
                if($v['ID']==''){
                    unset($v);
                    continue;
                }
                $v['id'] = $v['ID'];//为数组增添字段
                $v['pId'] = $v['PID'];
                $v['name'] = $v['NAME'];
                $v['file'] = $v['URL'];
                
                /*
                $v['isParent']=false;
                foreach ($arr as $key => $v2) {
                    if($v['id']==$v2['pid']){
                        $v['isParent']=true;
                        continue;
                    }
                }
                */
                unset($v['ID']);//删除数组字段
                unset($v['PID']);
                unset($v['NAME']);
                unset($v['URL']);
                $arr1[]=$v;
            }
        }
        return $arr1;
    }
 

}
?>