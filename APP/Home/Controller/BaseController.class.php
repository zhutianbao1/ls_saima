<?php

namespace Home\Controller;

use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class BaseController extends AdminController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){

		parent::_initialize();
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
 		// dump($back);
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
		$configs= $config->where('status=1 and month=201612')->order('id')->cache('configList',180)->select();
		$this->assign('configs',$configs);
		return $configs;
	}

	//获取用户清单的表头
	public function getUserCon($config_id,$month){
		$userCon = M('userCon');
		$config_idh = '100'.$config_id;
		$conWhere['a']=$config_idh;
		$conWhere['g']=$month;
		$head = $userCon->where($conWhere)->cache(true,3600)->find();
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
		$data = $userCon->where($conW)->cache(true,3600)->find();
		$this->assign('infoData',$data);
		return $data;
	}


	public function user($month,$id){
		$userInfo=M('userInfo');
		$sql_front="SELECT * FROM (SELECT a.*,row_number()over(partition by rpt_month,config_id order by amount desc) rn,rank()over(partition by rpt_month,config_id order by amount desc) pm FROM(SELECT b.cnt,a.* FROM rank_user_info a,rank_user_zan b where rpt_month=".$month." and a.bill_id=b.id(+) order by config_id,user_name) a) where 1=1";
		if($id==null || $id==''){
			$sql_front .=" and rn<=3";
			$user_front=$userInfo->db('LS_READ')->query($sql_front);
		}else{
			$sql_front .=" and config_id=".$id."";
			$user_front = parent::listsSqlByls($sql_front,10);
		}
		$this->assign('user_front',$user_front);

		$sql_back="SELECT * FROM (SELECT a.*,row_number()over(partition by rpt_month,config_id order by amount)+100 rn,rank()over(partition by rpt_month,config_id order by amount) pm FROM(SELECT * FROM rank_user_info where rpt_month=".$month." order by config_id,user_name) a) where rn<=103 order by config_id,amount desc,user_name";
		$user_back=$userInfo->db('LS_READ')->query($sql_back);
		$this->assign('user_back',$user_back);
	}

	public function year($id,$year){
		$yearInfo=M('yearInfo');
		if($year==2017){
			$sql_front="select * from (select t.*,row_number()over (partition BY config_id order by amount desc) rn,rank()over(partition by config_id order by amount desc) pm from(SELECT b.cnt,a.* FROM (SELECT bill_id,config_id,user_name,county_name,pos_name,SUM(全员赛马积分)amount FROM rank_year_info where rpt_month>=201701 GROUP BY bill_id,config_id,user_name,county_name,pos_name) a,rank_user_zan b where a.bill_id=b.id(+)) t) where 1=1";

			$sql_back="select * from (select t.*,row_number()over (partition BY config_id order by amount)+100 rn,rank()over(partition by config_id order by amount) pm from(SELECT bill_id,config_id,user_name,county_name,SUM(全员赛马积分)amount FROM rank_year_info where rpt_month>=201701 GROUP BY bill_id,config_id,user_name,county_name) t) where rn<=103 order by config_id,amount desc,user_name";
		}else{
			$sql_front="select * from (select t.*,row_number()over (partition BY config_id order by amount desc) rn,rank()over(partition by config_id order by amount desc) pm from(SELECT b.cnt,a.* FROM (SELECT bill_id,config_id,user_name,county_name,pos_name,SUM(全员赛马积分)amount FROM rank_year_info where rpt_month>201602 AND rpt_month<=201612 GROUP BY bill_id,config_id,user_name,county_name,pos_name) a,rank_user_zan b where a.bill_id=b.id(+)) t) where 1=1";

			$sql_back="select * from (select t.*,row_number()over (partition BY config_id order by amount)+100 rn,rank()over(partition by config_id order by amount) pm from(SELECT bill_id,config_id,user_name,county_name,SUM(全员赛马积分)amount FROM rank_year_info where rpt_month>201602 AND rpt_month<=201612 GROUP BY bill_id,config_id,user_name,county_name) t) where rn<=103 order by config_id,amount desc,user_name";
		}
		if($id==null || $id==''){
			$sql_front .=" and rn<=3";
			$user_front=$yearInfo->db('LS_READ')->query($sql_front);
		}else{
			$sql_front .=" and config_id=".$id."";
			$user_front = parent::listsSqlByls($sql_front,10);
		}
		$this->assign('user_front',$user_front);

		$user_back=$yearInfo->db('LS_READ')->query($sql_back);
		$this->assign('user_back',$user_back);
	}

	public function khjl($num,$name,$bill_id){
		$userInfo=M('userInfo');
		$sql_khjl="SELECT * FROM (SELECT DISTINCT 更新时间, 县市, 客户经理, 客户经理电话, 总分值,排名 FROM LS_PANCM.LS_KJH_客户经理汇总_01 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.LS_KJH_客户经理汇总_01) ORDER BY 总分值 DESC) WHERE Rownum<=".$num."";
		if(!empty($name)){
			$sql_khjl .= " and 客户经理 like '%".$name."%'";
		}
		if(!empty($bill_id)){
			$sql_khjl .= " and 客户经理电话='".$bill_id."'"; 
		}
		//$khjl=$userInfo->db('LS_READ')->query($sql_khjl);
		$khjl=parent::listsSqlByls($sql_khjl,20);
		$this->assign('khjl',$khjl);
		return $khjl;
	}

	public function xsjl($num,$name,$bill_id){
		$userInfo=M('userInfo');
		$sql_xsjl="SELECT * FROM (SELECT * FROM LS_KJH_CHNL_MANAGER  WHERE RPT_DATE = (SELECT MAX(RPT_DATE) FROM LS_KJH_CHNL_MANAGER) ORDER BY TOP) WHERE Rownum<=".$num."";
		if(!empty($name)){
			$sql_xsjl .= " and MANAGER_NAME like '%".$name."%'";
		}
		if(!empty($bill_id)){
			$sql_xsjl .= " and bill_id='".$bill_id."'"; 
		}
		//$xsjl=$userInfo->db('LS_READ')->query($sql_xsjl);
		$xsjl=parent::listsSqlByls($sql_xsjl,20);
		$this->assign('xsjl',$xsjl);
		return $xsjl;
	}

	public function yyy($num,$name,$bill_id){
		$userInfo=M('userInfo');
		$sql_yyy="SELECT * FROM (select * from v_ls_y_kpi_oper_pai order by 总得分 desc nulls last) WHERE Rownum<=".$num."";
		if(!empty($name)){
			$sql_yyy .= " and obj_name like '%".$name."%'";
		}
		if(!empty($bill_id)){
			$sql_yyy .= " and bill_id='".$bill_id."'"; 
		}
		//$yyy=$userInfo->db('LS_READ')->query($sql_yyy);
		$yyy=parent::listsSqlByls($sql_yyy,20);
		$this->assign('yyy',$yyy);
		return $yyy;
	}

	public function qlm($num,$name,$config_id){
		$userInfo=M('userInfo');
		$sql_qlm="SELECT * FROM (SELECT * FROM rank_qlm order by config_id,score desc,user_name) WHERE Rownum<=".$num."";
		if(!empty($name)){
			$sql_qlm .= " and user_name like '%".$name."%'";
		}
		if(!empty($config_id)){
			$sql_qlm .= " and config_id='".$config_id."'"; 
		}
		//$user_qlm=$userInfo->db('LS_READ')->query($sql_qlm);
		$user_qlm=parent::listsSqlByls($sql_qlm,20);
		$this->assign('user_qlm',$user_qlm);
		return $user_qlm;
	}

	public function jm($num,$name,$config_id){
		$userInfo=M('userInfo');
		$sql_jm="SELECT * FROM (SELECT * FROM rank_jm order by config_id,amount desc,user_name) WHERE Rownum<=".$num."";
		if(!empty($name)){
			$sql_jm .= " and user_name like '%".$name."%'";
		}
		if(!empty($config_id)){
			$sql_jm .= " and config_id='".$config_id."'"; 
		}
		//$user_jm=$userInfo->db('LS_READ')->query($sql_jm);
		$user_jm=parent::listsSqlByls($sql_jm,20);
		$this->assign('user_jm',$user_jm);
		return $user_jm;
	}

	public function hm($num,$name,$config_id){
		$userInfo=M('userInfo');
		$sql_hm="SELECT * FROM (SELECT * FROM rank_hm order by config_id,amount desc,user_name) WHERE Rownum<=".$num."";
		if(!empty($name)){
			$sql_hm .= " and user_name like '%".$name."%'";
		}
		if(!empty($config_id)){
			$sql_hm .= " and config_id='".$config_id."'"; 
		}
		//$user_hm=$userInfo->db('LS_READ')->query($sql_hm);
		$user_hm=parent::listsSqlByls($sql_hm,20);
		$this->assign('user_hm',$user_hm);
		return $user_hm;
	}

	public function rank_config($month,$oa,$num=0){
		$userInfo=M('userInfo');
		$sql_con="SELECT a.*,b.id config_id FROM(SELECT a.*,rank()over(partition by fst_type,sec_type_id order by des) a FROM (SELECT a.*,b.zan FROM rank_config a,(SELECT id,count(*) zan FROM rank_config_zan group by id) b where a.id=b.id(+)) a where month=".$month.")a,v_rank_config_msg b";

		if($oa == null || $oa == ""){
			$sql_con .=" where a.id=b.CONFIG_ID(+) and a.id in(SELECT config_id FROM rank_user_info where rpt_month=".$month." group by config_id) order by a.id";
		}else{
			$sql_con .= ",(SELECT * FROM rank_follow_line where oa='".$oa."') c where a.id=b.CONFIG_ID(+) and a.id=c.config_id(+)";
			//关注线条的总数
			$sql="SELECT count(*) con FROM rank_follow_line where oa='".$oa."'";
			$sum=$userInfo->db('LS_READ')->query($sql);
			//我所在的线条
			$sql_my="SELECT min(config_id) con FROM rank_year_info where bill_id='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
			$my=$userInfo->db('LS_READ')->query($sql_my);
			if(!empty($my[0]['CON'])){
				if($sum[0]['CON']>=3){
					$sql_con .=" and a.id in (SELECT config_id FROM rank_follow_line where oa='".$oa."')";
					if($num >0){
						$sql_con .= " and rownum<=3";
					}
					$sql_con .= " order by decode(a.id,'".$my[0]['CON']."',1)";
				}else if($sum[0]['CON']==2){
					$sql_con .=" and (a.id=".$my[0]['CON']." or a.id in (SELECT config_id FROM rank_follow_line where oa='".$oa."')) order by decode(a.id,'".$my[0]['CON']."',1)";
				}else if($sum[0]['CON']==1){
					$sql_con .=" and (a.id=".$my[0]['CON']." or a.id=(SELECT config_id FROM rank_follow_line where oa='".$oa."') or a.id=1016) order by decode(a.id,'".$my[0]['CON']."',1)";
				}else if($sum[0]['CON']==0){
					if($my[0]['CON']==1016){
						$sql_con .=" and (a.id=".$my[0]['CON']." or a.id=1013 or a.id=1021) order by decode(a.id,'".$my[0]['CON']."',1)";
					}else if($my[0]['CON']==1021){
						$sql_con .=" and (a.id=".$my[0]['CON']." or a.id=1016 or a.id=1013) order by decode(a.id,'".$my[0]['CON']."',1)";
					}else{
						$sql_con .=" and (a.id=".$my[0]['CON']." or a.id=1016 or a.id=1021) order by decode(a.id,'".$my[0]['CON']."',1)";
					}
				}
				$sql_con .= " ,c.create_date desc nulls last";
			}else{
				if($sum[0]['CON']>=3){
					$sql_con .=" and a.id in (SELECT distinct config_id FROM rank_follow_line where oa='".$oa."')";
					if($num >0){
						$sql_con .= " and rownum<=3";
					}
				}else if($sum[0]['CON']==2){
					$sql_con .=" and (a.id in (SELECT distinct config_id FROM rank_follow_line where oa='".$oa."') or a.id=1016)";
				}else if($sum[0]['CON']==1){
					$sql_con .=" and (a.id in (SELECT distinct config_id FROM rank_follow_line where oa='".$oa."') or a.id=1016 or a.id=1021)";
				}else if($sum[0]['CON']==0){
					$sql_con .=" and (a.id=1016 or a.id=1013 or a.id=1021)";
				}
				$sql_con .= " order by c.create_date desc nulls last";
			}
		}
		$con=$userInfo->db('LS_READ')->query($sql_con);
		$this->assign('con',$con);
	}
}
?>