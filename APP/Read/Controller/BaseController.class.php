<?php

namespace Read\Controller;

use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class BaseController extends AdminController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){

		$CONTROLLER_NAME = $Think.CONTROLLER_NAME;
		$ACTION_NAME  = $Think.ACTION_NAME;	 
 
		//导航菜单
		$this->base_nav();
		 
		//web网站属性加载
		$web_attrs = $this->base_attr();
		$this->assign('web_attrs',$web_attrs);

		//书本类型
		$book_info = $web_attrs['BOOK_TYPE'];
		$book_types = json_decode($book_info);
		$this->assign('book_types',$book_types);

		//县市配置
		$county_info = $web_attrs['COUNTY_CODE'];
		$county_list = json_decode($county_info);
		$this->assign('county_list',$county_list);

		$user = session('user_auth');
		$this->assign('user_auth',$user);

		//补充用户县市 头像 部门 等信息
		if(empty($user['COUNTY_CODE'])){
			$sql="select a.oper_name,a.oper_id,a.pic,b.dept_id,b.dept_name,b.dept_county_id county_code,b.dept_parent_id
				  from mz_user.t_sys_oper a , mz_user.t_sys_dept b where a.oper_dept_id=b.dept_id and a.oper_login_code='".$user['OPER_LOGIN_CODE']."'";		    
			$m = M();
			$info = $m->db(1,'LS_CONFIG')->query($sql);
			if($info){
				$user['OPER_NAME']=$info[0]['OPER_NAME'];
				$user['OPER_ID']=$info[0]['OPER_ID'];
				$user['PIC']=$info[0]['PIC'];
				$user['DEPT_ID']=$info[0]['DEPT_ID'];
				$user['DEPT_NAME']=$info[0]['DEPT_NAME'];
				$user['COUNTY_CODE']=$info[0]['COUNTY_CODE'];
				$user['COUNTY_NAME']=$this->countyName($info[0]['COUNTY_CODE']);
				$user['DEPT_PARENT_ID']=$info[0]['DEPT_PARENT_ID'];
				
				session('user_auth',$user);
				$this->assign('user_auth',$user);
			}
		}

		$cty = I('cty');
		//设置当前阅读会县市会话
		if(!empty($cty)){
			session('COUNTY_CODE',$cty);
		}

		if(!session('?COUNTY_CODE')){
			session('COUNTY_CODE',$user['COUNTY_CODE']);
		}

		parent::intiParams();

		//测试删除 user_auth 会话
		// session('user_auth',null);
		// dump($user);
		// die;

		//获取详细的用户信息
		 // $where=" and oper_login_code='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
		 // $this->loginUser($where);		
		parent::viewLog();

		if(!isset($_SESSION['user_auth'])||empty($_SESSION['user_auth'])){
           redirect("/ranking/Read/Notice/index",0);
        }

 	}

 	public function countyName($county_code=''){
 		$county_name = '未获取';
 		switch ($county_code) {
 			case '5780':
 				$county_name='市公司';
 				break;
 			case '5781':
 				$county_name='莲都';
 				break;
 			case '5782':
 				$county_name='缙云';
 				break;
 			case '5783':
 				$county_name='青田';
 				break;
 			case '5784':
 				$county_name='云和';
 				break;
 			case '5785':
 				$county_name='庆元';
 				break;
 			case '5786':
 				$county_name='龙泉';
 				break;
 			case '5787':
 				$county_name='遂昌';
 				break;
 			case '5788':
 				$county_name='松阳';
 				break;
 			case '5789':
 				$county_name='景宁';
 				break;
 			case '578B':
 				$county_name='南城';
 				break;
 			default:
 				break;
 		}
 		return $county_name;
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

	//访问日志
	public function viewLog(){
		$page = $this->get_url();
		$user = session('user_auth');
		if($page){
			$model = M('viewLog');
			$log['ID']="rank_seq.nextval";
			if(!empty($user)){
				$log['OPER_ID']=$user['OPER_ID'];
				$log['BILL_ID']=$user['OPER_LOGIN_CODE'];	
				$log['OA']=$user['OA'];	
			}
			$log['IP']=$_SERVER['REMOTE_ADDR'] ;
			$log['PAGE']=$page;
			$logFlag = $model->orcAdd('rank_view_log',$log);
		}
	}


	//获取导航菜单
	public function base_nav(){
		$m = M('nav');
		$navs = $m->where('status=1')->order('ord')->select();
		$ma = M('manager');
		$w['bill_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$managers = $ma->where($w)->select();

		if($managers){
			$navs2 = $m->where('status=2')->order('ord')->select();
		}

		if($navs2){
			$navs = array_merge($navs,$navs2);
		}
		

		$this->assign('navs',$navs);
	}

	//系统设置加载
	public function base_attr(){
		$m = M('attr');
		$attrs = $m->where('status=1')->cache(false,1800)->select();
		$arr = array();
		if($attrs){
			foreach ($attrs as $key => $val) {
				$arr[$val['attr']] = $val['val'];
			}
		}
		return $arr;
	}

	//书籍查询
	public function query_book($page_size=10,$page_num=1){
		$sql = "select * from rank_book where status=1";
		$books = parent::listsBySql($sql,$page_size);
		return $books;
	}

	//心得查询
	public function query_xinde($page_size=10,$page_num=1){
		$m = M('article');
		$w['status']=1;
		$sql = "select a.*,b.img,b.book_name from rank_article as a left join rank_book as b on a.book_id=b.id 
		where 1=1 ";
		$book_id = I('book_id');
		if(!empty($book_id)){
			$sql.=" and a.book_id={$book_id}";
		}
		$sql.=" and a.status=1 and a.shenhe=1 and a.county_code='".$_SESSION['COUNTY_CODE']."' order by a.oper_date desc";
		// $xindes = $m->where($w)->order('oper_date desc')->limit(0,3)->select();
		$xindes = parent::listsBySql($sql,$page_size);
		return $xindes;

		
	}

	//心得查询
	public function query_xinde_book($book_id=0){
		$m = M('article');
		$w['status']=1;
		$sql = "select a.*,b.img,b.book_name from rank_article as a left join rank_book as b on a.book_id=b.id 
		where 1=1 ";
		if(!empty($book_id)){
			$sql.=" and a.book_id={$book_id}";
		}
		$sql.=" and a.status=1 order by a.oper_date desc";
		
		// $xindes = $m->where($w)->order('oper_date desc')->limit(0,3)->select();
		$xindes = parent::listsBySql($sql,50);
		return $xindes;
	}

	//阅读会查询
	public function query_meeting($page_size=10,$page_num=1){
		$m = M('meeting');
		$sql = "select a.*,b.img book_img,b.book_name,b.county_code from rank_meeting as a inner join rank_book as b on a.book_id=b.id where a.meet_date<DATE_FORMAT(NOW(),'%Y-%m-%d') and and b.status=1 and a.status=1 and b.county_code='".$_SESSION['COUNTY_CODE']."' order by a.create_date desc";
		// $xindes = $m->where($w)->order('oper_date desc')->limit(0,3)->select();
		$meetings = parent::listsBySql($sql,$page_size);
		return $meetings;
	}

	//读书会报名情况
	public function query_meeting_bao($meet_id=0){
		//报名情况
		$meet = M('meeting');
		$sql = "SELECT a.* , b.bao_oper,b.bao_date,b.id FROM rank_meeting a , rank_meeting_bao b 
		WHERE a.id=b.meeting_id and a.id=".$meet_id." and b.status='1' order by b.bao_date desc";		
		$bms = $meet->query($sql);

		$bms_2 = array();
		foreach ($bms as $i => $bm) {
			$model = M();
			$where['oper_login_code']=$bm['bao_oper'];
			$oper = $model->db(1,'LS_CONFIG')->table('mz_user.t_sys_oper')->where($where)->find();
			if($oper['OPER_NAME']){
				$bm['bao_oper_name']=$oper['OPER_NAME'];
			}
			$bms_2[] = $bm;
		}
		if ($bms_2) {
			return $bms_2;
		}else{
			return false;
		}


	}
	




	//读书会报名导出
	public function meeting_bao_exp($meet_id=0){
		$m = M('meeting');
		$meeting = $m->find($meet_id);

		$bms = self::query_meeting_bao($meet_id);
		$file_type = "vnd.ms-excel"; // excel表头固定写法
		$file_ending = "xls"; // excel表的后缀名
		header("Content-Type: application/".$file_type."; charset=UTF-8");
		header("Content-Disposition: attachment; filename=".$meeting['title'].".$file_ending"); // agentfile到处的表名
		header("Pragma: no-cache"); // 缓存
		header("Expires: 0");

		$head = "<tr><th colspan=3>".$meeting['title']."</th></tr>
		<tr><th>姓名</th>
		<th>手机号码</th>
		<th>报名时间</th>
		</tr>";

		foreach ($bms as $key => $bm) {
			$data.="<tr><td>".$bm['bao_oper_name']."</td> ";
			$data.="<td>".$bm['bao_oper']."</td> ";
			$data.="<td>".date('Y-m-d',$bm['bao_date'])."</td></tr> ";
		}

		$e = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
		xmlns:x="urn:schemas-microsoft-com:office:excel"
		xmlns="http://www.w3.org/TR/REC-html40">
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<style id="Classeur1_16681_Styles"></style>
		</head>
		<body style="background-color:#fff;">
		<div id="Classeur1_16681" align=center x:publishsource="Excel">
		<table x:str border=1 cellpadding=0 cellspacing=0 width=60% style="border-collapse: collapse">
		'.$head.'
		'.$data.'
		</table>
		</div>
		</body>
		</html>';

		echo $e;
		exit; 
	}

	//书籍库存 和 借出量更新
	public function book_store_modify($book_id,$flag=true){
		// $flag = true 借出 , $flag = false 归还
		$m = M('book');
		$where['id']=$book_id;
		if($flag){
			$data['store']=array('exp','store-1');
			$data['lend']=array('exp','lend+1');
			$m->where($where)->save($data);
		}else{
			$data['store']=array('exp','store+1');
			$data['lend']=array('exp','lend-1');
			$m->where($where)->save($data);
		}
	}

	

	//短信发送
    public function sms($dest_bill,$content){
        $rank = M('book');
        $data['ID']='my_seq_sms_job.nextval';
        $data['BILL_ID']=$dest_bill;
        $data['SMS']=$content;
        $insert = $rank->db(1,'LS_CONFIG')->orcAdd('ls_sms_job',$data);
        if($insert){
            return true;
        }else{
            return false;
        }
    }

    

}
?>