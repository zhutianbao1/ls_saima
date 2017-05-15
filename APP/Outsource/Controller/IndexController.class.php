<?php
namespace Outsource\Controller;
/**
* 
*/
class IndexController extends BaseController
{
	
	protected function _initialize(){
		parent::_initialize();
	}

	public function index(){
		$this->display();
	}

	public function logins(){
		$j['success']=false;
		$j['msg']='姓名或者手机号码出错';
		$user_name=I('user_name');
		$bill_id=I('bill_id');
		$role=M('role');
		$login=$role->where("user_name='".$user_name."' and bill_id='".$bill_id."'")->find();
		$user_login=M('userInfo')->where("user_name='".$user_name."' and bill_id='".$bill_id."' and state=1 and status=1")->find();
		if($login){
			session('user_auth',$login);
			$j['success']=ture;
			$j['msg']='登录成功';
		}else{
			if($user_login){
				session('user_auth',$user_login);
				$j['success']=ture;
				$j['msg']='登录成功';
			}
		}
		$this->ajaxReturn($j);
	}

	public function main(){
		$bill_id=$_SESSION['user_auth']['BILL_ID'];
		$userInfo=M('userInfo');
		$where = 'status=1';
		$user_name=I('name');
		$county_name=I('county_name');
		$state=I('state');
		$role=M('role')->where("bill_id='".$bill_id."'")->find();
		if($role['COUNTY_NAME']!="丽水总部" && $role!=null){
			$where .= " and county_name='".$role['COUNTY_NAME']."'";
		}
		if(!empty($user_name)){
			$where .= " and user_name like '%".$user_name."%'";
		}
		if(!empty($county_name)){
			$where .= " and county_name='".$county_name."'";
		}
		if(!empty($state)){
			if($state==3){
				$where .= " and state=".$state."";
			}else{
				$where .= " and state<=".$state."";
			}
		}
		$order = 'id';
		$user = parent::lists($userInfo,'',$where,$order);
		$this->assign('user',$user);
		$btnGroup = self::btnGroup($bill_id);
		$this->assign('btnGroup',$btnGroup);
		$tjGroup = self::tjGroup($bill_id);
		$this->assign('tjGroup',$tjGroup);
		$this->display();
	}

	public function employee_quit(){
		$bill_id=$_SESSION['user_auth']['BILL_ID'];
		$userInfo=M('userInfo');
		$county_name=I('county_name');
		$user_name=I('user_name');
		$state=I('state');
		$where="status=2";
		$role=M('role')->where("bill_id='".$bill_id."'")->find();
		if($role['COUNTY_NAME']!="丽水总部" && $role!=null){
			$where .= " and county_name='".$role['COUNTY_NAME']."'";
		}
		if(!empty($county_name)){
			$where.=" and county_name='".$county_name."'";
		}
		if(!empty($user_name)){
			$where.=" and user_name like '%".$user_name."%'";
		}
		$orders='id';
		$user = parent::lists($userInfo,'',$where,$orders);
		$this->assign('user',$user);
		$tjGroup = self::tjGroup($bill_id);
		$this->assign('tjGroup',$tjGroup);
		$this->display();
	}

	public function employee_recruit(){
		$bill_id=$_SESSION['user_auth']['BILL_ID'];
		$apply=M('apply');
		$user_name=I('name');
		$state=I('state');
		$home_date=I('home_date');
		$end_date=I('end_date');
		$where="1=1";
		$orders="id";
		if(!empty($user_name)){
			$where .= " and user_name like '%".$user_name."%'";
		}
		if(!empty($state)){
			if($state==3){
				$where .= " and state=".$state."";
			}else{
				$where .= " and state<=".$state."";
			}
		}
		if(!empty($home_date)){
			$where .= " and create_date>='".$home_date."'";
		}
		if(!empty($end_date)){
			$where .= " and create_date<='".$end_date."'";
		}
		$user=parent::lists($apply,'',$where,$orders);
		$this->assign('user',$user);
		$btnGroup = self::btnGroup($bill_id);
		$this->assign('btnGroup',$btnGroup);
		$this->display();
	}

	public function add($billId='',$userName=''){
		$userInfo=M('userInfo');
		$da=$userInfo->where("user_name='".$userName."' and bill_id='".$billId."'")->find();
		if(!empty($da)){
			$this->assign('da',$da);
		}
		if(IS_POST){
			$data['user_name']=I('user_name');
			$data['sex']=I('sex');
			$data['bill_id']=I('bill_id');
			$data['birth_date']=I('birth_date');
			$data['edu_degree']=I('edu_degree');
			$data['id_card']=I('id_card');
			$data['work_place']=I('work_place');
			$data['service_start']=I('service_start');
			$data['contract_start']=I('contract_start');
			$data['contract_end']=I('contract_end');
			$data['reference']=I('reference');
			$data['contract_report']=I('contract_report');
			$data['agreement_report']=I('agreement_report');
			$data['pledge_report']=I('pledge_report');
			$data['income']=I('income');
			$data['safe']=I('safe');
			$data['address']=I('address');
			$data['family_address']=I('family_address');
			$data['county_name']=I('county_name');
			$data['dept']=I('dept');
			$data['nation']=I('nation');
			$data['edu_school']=I('edu_school');
			$data['edu_major']=I('edu_major');
			$data['remark']=I('remark');
			$data['state']=3;
			$data['status']=1;
			if($billId==null || $userName==null){
				$da1=$userInfo->where("user_name='".$data['user_name']."' and bill_id='".$data['bill_id']."'")->find();
				if(!empty($da1)){
					echo "已有当前员工，查看是否信息有误！";
					exit();
				}else{
					$data['id']=array('exp','e_number.nextval');
					$odd=$userInfo->add($data);
				}
			}else{
				$ove=$userInfo->where("user_name='".$userName."' and bill_id='".$billId."'")->save($data);
			}
			if($odd){
				$this->success('保存成功','add');
			}else if($ove){
				echo "修改成功";
				exit();
			}else{
				$this->error ('保存失败');
			}
		}
		$this->display();
	}

	public function audit(){
		$Model=M();
		$Model->startTrans();
		//$j['success']=false;
		//$j['msg']='审批失败';
		$userInfo=M('userInfo');
		$bill_id=I('bill_id');
		$user_name=I('user_name');
		$data['user_id']=I('uid');
		$data['state']=I('sp');
		$content=I('content');
		$o=$userInfo->where("user_name='".$user_name."' and bill_id='".$bill_id."'")->save($data);
		$sql="insert into e_opinion(user_name,bill_id,content,create_date) values('".$user_name."','".$bill_id."','".$content."',sysdate)";
		$opinion=$userInfo->execute($sql);
		if($opinion && $o){
			//$j['success']=true;
			$Model->commit(); 
			$j['msg']='审批成功';
		}else{
			$Model->rollback();
			$j['msg']='审批失败';
		}
		$this->ajaxReturn($j);
	}

	public function audit2(){
		$j['success']=false;
		$j['msg']='审批失败';
		$apply=M('apply');
		$id=I('id');
		$data['state']=I('sp');
		$content=I('content');
		$o=$apply->where("id='".$id."'")->save($data);
		$sql="insert into e_opinion(id,content,create_date) values('".$id."','".$content."',sysdate)";
		$opinion=$apply->execute($sql);
		if($opinion){
			$j['success']=true;
			$j['msg']='审批成功';
		}
		$this->ajaxReturn($j);
	}

	public function fail_reason($user_name='',$bill_id='',$id=0){
		$opinion=M('opinion');
		if($id>0){
			$fail=$opinion->where("id='".$id."'")->order("create_date desc")->find();
		}else{
			$fail=$opinion->where("user_name='".$user_name."' and bill_id='".$bill_id."'")->order("create_date desc")->find();
		}
		echo $fail['CONTENT'];
	}

	public function quit(){
		$j['success']=false;
		$j['msg']='失败';
		$userInfo=M('userInfo');
		$user_name=I('user_name');
		$bill_id=I('bill_id');
		$data['pay_end']=I('pay_end');
		$data['contract_remove']=I('contract_remove');
		$data['explain']=I('explain');
		$data['status']=2;
		$o=$userInfo->where("user_name='".$user_name."' and bill_id='".$bill_id."' and state=1")->save($data);
		if($o){
			$j['success']=true;
			$j['msg']='成功';
		}
		$this->ajaxReturn($j);
	}

	public function employee_train(){
		$sql="SELECT * FROM e_employee_train";
		$user=parent::listsSqlByls($sql,10);
		$this->assign('user',$user);
		$this->display();
	}

	public function employee_train_re($id=0,$ids=0){
		if($ids>0){
			$apply=M('apply');
			$da=$apply->where("id=".$id."")->find();
			if($da){
				$this->assign('da',$da);
			}
			if(IS_POST){
				$data['apply_dept']=I('apply_dept');
				$data['create_date']=I('create_date');
				$data['user_name']=I('user_name');
				$data['apply_ren']=I('apply_ren');
				$data['pos_name']=I('pos_name');
				$data['apply_num']=I('apply_num');
				$data['sex']=I('sex');
				$data['age']=I('age');
				$data['edu_degree']=I('edu_degree');
				$data['abily']=I('abily');
				$data['opinion']=I('opinion');
				$data['state']=3;
				if($id==null){
					$data['id']=array('exp','e_applys.nextval');
					//$data['bill_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
					$o=$apply->add($data);
				}else{
					$o=$apply->where("id=".$id."")->save($data);
				}
				if($o){
					echo "保存成功";
					exit();
				}else{
					$this->error("保存失败");
				}
			}
			$this->assign('errMsg','人员招聘申请');
			$this->display();
		}else{
			if(IS_POST){
				$employeeTrain=M('employeeTrain');
				$data['rpt_month']=I('rpt_month');
				$data['user_name']=I('user_name');
				$data['user_id']=I('user_id');
				$data['bill_id']=I('bill_id');
				$data['county_name']=I('county_name');
				$data['sex']=I('sex');
				$data['报道时间']=I('报道时间');
				$data['报道当日是否住宿']=I('报道当日是否住宿');
				$data['培训期间统一安排食住']=I('培训期间统一安排食住');
				$data['最后一天用餐安排']=I('最后一天用餐安排');
				$data['培训性质']=I('培训性质');
				$data['培训名称']=I('培训名称');
				$o=$employeeTrain->add($data);
				if($o){
					echo "提交成功";
					exit();
				}else{
					$this->error("提交失败");
				}
			}
			$this->assign('errMsg','培训申请');
			$this->display();
		}
	}

	public function employee_train_res(){
		$userInfo=M('userInfo');
		$user_id=I('user_id');
		$da=$userInfo->where("user_id='".$user_id."' and status=1")->select();
		$this->ajaxReturn($da);
	}

	public function employee_train_fy(){
		$this->display();
	}

	public function employee_pefa(){
		$bill_id=$_SESSION['user_auth']['BILL_ID'];
		$employeePefa=M('employeePefa0');
		$county_name=I('county_name');
		$user_id=I('user_id');
		$user_name=I('name');
		$where="1=1";
		$order="月份";
		$role=M('role')->where("bill_id='".$bill_id."'")->find();
		if($role['COUNTY_NAME']!="丽水总部" && $role!=null){
			$where .= " and county_name='".$role['COUNTY_NAME']."'";
		}
		if(!empty($county_name)){
			$where .=" and county_name='".$county_name."'";
		}
		if(!empty($user_id)){
			$where .=" and user_id='".$user_id."'";
		}
		if(!empty($user_name)){
			$where .=" and user_name like '%".$user_name."%'";
		}

		//$user=$employeePefa->select();
		$user = parent::lists($employeePefa,'',$where,$order);
		$this->assign('user',$user);
		$tjGroup = self::tjGroup($bill_id);
		$this->assign('tjGroup',$tjGroup);
		$this->display();
	}

	public function employee_kaoqin(){
		$bill_id=$_SESSION['user_auth']['BILL_ID'];
		if($_SESSION['user_auth']['USER_ID']==null || $_SESSION['user_auth']['USER_ID']==""){
			$user_id=I('user_id');
		}else{
			$user_id=$_SESSION['user_auth']['USER_ID'];
		}
		$county_name=I('county_name');
		$user_name=I('name');
		$sql="SELECT a.user_id,user_name,county_name,service_start,bill_id,dept,nvl(c.aaa,0) 年休天数,nvl(d.年休假,0) 已休假天数,nvl(c.aaa,0)-nvl(d.年休假,0) 剩余休假天数,b.本月请假天数 FROM(SELECT * FROM e_user_info where state=1 and status=1) a,(SELECT * FROM e_本月请假天数 where rpt_month=to_char(sysdate,'yyyymm')) b,e_年休天数 c,(SELECT user_id,sum(年休假) 年休假 FROM (SELECT user_id,to_date(end_date,'yyyy-mm-dd')-to_date(home_date,'yyyy-mm-dd') 年休假 FROM e_employee_kaoqin where leave_type='年休假' and to_char(to_date(home_date,'yyyy-mm-dd'),'yyyy')=to_char(sysdate,'yyyy')) group by user_id) d where a.user_id=b.USER_ID(+) and a.user_id=c.user_id(+) and a.user_id=d.user_id(+)";
		if(!empty($user_id)){
			$sql .= " and a.user_id='".$user_id."'";
		}
		if(!empty($county_name)){
			$sql .= " and a.county_name='".$county_name."'";
		}
		if(!empty($user_name)){
			$sql .= " and a.user_name like '%".$user_name."%'";
		}
		$role=M('role')->where("bill_id='".$bill_id."'")->find();
		if($role['COUNTY_NAME']!="丽水总部" && $role!=null){
			$sql .= " and a.county_name='".$role['COUNTY_NAME']."'";
		}
		$user=M()->query($sql);
		$this->assign('user',$user);
		$btnGroup = self::btnGroup($bill_id);
		$this->assign('btnGroup',$btnGroup);
		$tjGroup = self::tjGroup($bill_id);
		$this->assign('tjGroup',$tjGroup);
		$this->display();
	}

	public function employee_kaoqin_add($user_id='',$test=0){
		$employeeKaoqin=M('employeeKaoqin');
		$da=$employeeKaoqin->where("user_id='".$user_id."' and state=3")->find();
		if(!empty($da)){
			$this->assign('da',$da);
		}
		if(IS_POST){
			$employeeKaoqin=M('employeeKaoqin');
			$data['rpt_month']=I('rpt_month');
			$data['user_id']=I('user_id');
			$data['user_name']=I('user_name');
			$data['county_name']=I('county_name');
			$data['service_start']=I('service_start');
			$data['bill_id']=I('bill_id');
			$data['dept']=I('dept');
			$data['leave_type']=I('leave_type');
			$data['ace']=I('ace');
			$data['home_date']=I('home_date');
			$data['end_date']=I('end_date');
			$data['leave_reason']=I('leave_reason');
			$data['state']=3;
			if(!empty($user_id)){
				$o=$employeeKaoqin->where("user_id='".$user_id."' and state=3")->save($date);
			}else{
				$test=$employeeKaoqin->where("user_id='".$data['user_id']."' and state=3")->find();
				if(!empty($test)){
					echo "不允许重复提交请假清单";
					exit();
				}else{
					if($test==1 && $data['leave_type']=="年休假"){
						echo "你没有年休假";
						exit();
					}else{
						$o=$employeeKaoqin->add($data);
					}
				}
			}
			if($o){
				echo "提交成功";
				exit();
			}else{
				$this->error("提交失败");
			}
		}
		$this->display();
	}

	public function apply(){
		$j['success']=false;
		$j['msg']='提交失败';
		$apply=M('apply');
		$id=I('id');
		$data['apply_dept']=I('apply_dept');
		$data['create_date']=I('create_date');
		$data['user_name']=I('user_name');
		$data['apply_ren']=I('apply_ren');
		$data['pos_name']=I('pos_name');
		$data['apply_num']=I('apply_num');
		$data['sex']=I('sex');
		$data['age']=I('age');
		$data['edu_degree']=I('edu_degree');
		$data['abily']=I('abily');
		$data['opinion']=I('opinion');
		$data['state']=3;
		if($id!=null && $id!=""){
			$o=$apply->where("id=".$id."")->save($data);
		}else{
			$data['id']=array('exp','e_applys.nextval');
			$data['bill_id']=$_SESSION['user_auth']['BILL_ID'];
			$o=$apply->add($data);
		}
		if($o){
			$j['success']=true;
			$j['msg']='提交成功';
		}
		$this->ajaxReturn($j);
	}

	public function employee_see($user_name='',$user_id=''){
		$employeeKaoqin=M('employeeKaoqin');
		$user=$employeeKaoqin->where("user_id='".$user_id."' and user_name='".$user_name."'")->select();
		$this->assign('user',$user);
		$this->display();
	}

	protected function btnGroup($bill_id){
		$role=M('role');
		$user=$role->where("bill_id='".$bill_id."'")->find();
		if($user){
			if($user['USER_NAME']=='王晶'){
				$btnGroup['audit']=1;
			}
			if($user['REMARK']=='综合管理员'){
				$btnGroup['admins']=1;
			}
		}else{
			$btnGroup['全部']=1;
		}
		return $btnGroup;
	}

	protected function tjGroup($bill_id){
		$role=M('role');
		$user=$role->where("bill_id='".$bill_id."'")->find();
		if($user){
			if($user['REMARK']=='综合管理员'){
				$tjGroup['dept']=1;
			}
		}else{
			$tjGroup['全部']=1;
		}
		return $tjGroup;
	}
}