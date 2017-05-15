<?php
namespace Attendance\Controller;
use Attendance\Controller\BaseController;

class IndexController extends BaseController{
	//验证登录状态
	public function isLogin(){
		$oa = session('user_auth.OA');
		//$oa = 'zhaibo';
		if($oa){
			session('user_auth',null);
			$_SESSION['user_auth']['OA'] = $oa;
			$_SESSION['user_auth']['IS_LOGIN'] = true;
			$sql = "select dept_no,empl_no,bill_id,empl_name from ls_flow.ls_employee_leave_list where empl_oa = '".$oa."'";
			$oper = M()->query($sql);
			$_SESSION['user_auth']['DEPT'] = $oper[0]['DEPT_NO'];
			$_SESSION['user_auth']['USER_ID'] = $oper[0]['EMPL_NO'];
			$_SESSION['user_auth']['OPER_NAME'] = $oper[0]['EMPL_NAME'];
			$_SESSION['user_auth']['OPER_LOGIN_CODE'] = $oper[0]['BILL_ID'];
			return session('user_auth');
		}else{
			$this->display('login');
			//exit("会话超时，请在当前窗口新建选项卡重新登录OA，然后刷新本页面");
		}
	}

	public function login(){
		$model = M();
		$manager = I('manager');
		$password = I('password');
		$result = $model->table('mz_user.t_sys_oper')->where("oper_login_code = '".$manager."' and oper_login_pass = '".$password."'")->find();
		if($result){
			$rst = 1;
			$sql = "select * from ls_flow.ls_employee_leave_list where bill_id = '".$manager."'";
			$oper = $model->query($sql);
			session('user_auth',null);
			$_SESSION['user_auth']['OA'] = $oper[0]['EMPL_OA'];
			$_SESSION['user_auth']['IS_LOGIN'] = true;
			$_SESSION['user_auth']['DEPT'] = $oper[0]['DEPT_NO'];
			$_SESSION['user_auth']['USER_ID'] = $oper[0]['EMPL_NO'];
			$_SESSION['user_auth']['OPER_NAME'] = $oper[0]['EMPL_NAME'];
			$_SESSION['user_auth']['OPER_LOGIN_CODE'] = $oper[0]['BILL_ID'];
			//session('user_auth',$user_auth);
		}else{
			$rst = 0;
		}
		$this->ajaxReturn($rst);
	}
	
	public function index(){
		if(isset($_SESSION['user_auth']['OA'])){
			$this->display();
		}else{
			$this->display('login');
		}
		//$user = $this->isLogin();
		//$model = M();
		//$empl_oa = session('user_auth.OA');
		// //部门经理
		// $manager = $model->table('tp_invoice_manager_list')->where("empl_oa = '".$empl_oa."'")->find();
		// //管理员
		// $admin = $model->table('tp_invoice_admin_list')->where("empl_oa = '".$empl_oa."'")->find();
		// $flag = "no";
		// if($admin || $manager){
		// 	$flag = "yes";
		// }
		//$this->assign('manager',$flag);
		//$this->display();
	}

	public function index_list(){
		$user = $this->isLogin();
		if($user['IS_LOGIN']){
			$model = M();
			$empl_oa = session('user_auth.OA');
			$manager = $model->table('tp_attendance_manager')->where("empl_oa = '".$empl_oa."'")->find();
			//管理员查看所有人员，其他人员只能查看自己
			if(!$manager){
				$empl_name = session('user_auth.OPER_NAME');
			}else{
				$empl_name = I('empl_name');
			}
			$start_time = I('start_time');
			$end_time = I('end_time');
			//默认加载上个月数据
			// if($start_time == ""){
			// 	$start_time = date('Y-m-01', strtotime('-1 month'));
			// }
			// if($end_time == ""){
			// 	$end_time = date('Y-m-t',strtotime('-1 month'));
			// }
			$tiao = "";
			if(!empty($empl_name)){
				$tiao .= " and empl_name like '%".$empl_name."%'";
			}
			if(!empty($start_time)){
				$tiao .= " and clock_day >= '".$start_time."'";
			}
			if(!empty($end_time)){
				$tiao .= " and clock_day <= '".$end_time."'";
			}
			$total = $model->table('tp_attendance_list_view')->where("1 = 1".$tiao)->count();
			$query = "select a.*,b.reason from tp_attendance_list_view a left join tp_attendance_reason b on a.empl_no = b.empl_no and a.clock_day = b.clock_day where 1 = 1 ";
			if(!empty($empl_name)){
				$query .= " and a.empl_name like '%".$empl_name."%'";
			}
			if(!empty($start_time)){
				$query .= " and a.clock_day >= '".$start_time."'";
			}
			if(!empty($end_time)){
				$query .= " and a.clock_day <= '".$end_time."'";
			}
			$query .= " order by a.clock_day desc";
			$json = $model->query($query);
			$json = json_encode($json);
			echo '{"total":'.$total.',"rows":'.$json.'}';
			//$arr = array('total'=>$total,'rows'=>$json);
			//$this->ajaxReturn($arr);
		}
	}
	public function get_info(){
		$model = M();
		$empl_no = I('empl_no');
		$clock_day = I('clock_day');
		$data = $model->query("select * from tp_attendance_list_view where empl_no = '".$empl_no."' and clock_day = '".$clock_day."'");
		$data = json_encode($data);
		echo $data;
	}
	public function update_info(){
		$model = M();
		$empl_no = I('empl_no');
		$empl_name = I('empl_name');
		$empl_dept = I('empl_dept');
		$empl_type = I('empl_type');
		$clock_day = I('clock_day');
		$reason = I('reason');
		$data['empl_no'] = $empl_no;
		$data['empl_name'] = $empl_name;
		$data['empl_dept'] = $empl_dept;
		$data['empl_type'] = $empl_type;
		$data['clock_day'] = $clock_day;
		$data['reason'] = $reason;
		$tmp = $model->table('tp_attendance_reason')->where("empl_no = '".$empl_no."' and clock_day = '".$clock_day."'")->find();
		if($tmp){
			$result = $model->table('tp_attendance_reason')->where("empl_no = '".$empl_no."' and clock_day = '".$clock_day."'")->save($data);
		}else{
			$result = $model->table('tp_attendance_reason')->add($data);
		}
		//$data['reason'] = I('reason');
		//$result = $model->table('tp_attendance_list')->where("rowid = '".$rowid."'")->save($data);
		echo $result;
	}
	
	//获取发票信息列表
	public function queryString(){
		$model = M();
		$empl_oa = session('user_auth.OA');
		$empl_depta = session('user_auth.DEPT');
		$empl_dept = I('empl_dept');
		$status = I('status');
		$time1 = I('input_date1');
		$time2 = I('input_date2');
		$id = I('id');
		//部门经理
		//$manager = $model->table('tp_invoice_manager_list')->where("empl_oa = '".$empl_oa."'")->find();
		//管理员
		$admin = $model->table('tp_invoice_admin_list')->where("empl_oa = '".$empl_oa."'")->find();
		if(!empty($empl_oa)){
			$sql = "select invoice_id,empl_name,to_char(input_date,'yyyy-mm-dd') input_date,empl_dept,invoice_company,invoice_name,invoice_purpose,decode(invoice_status,1,'正常',2,'退回',3,'待作废',4,'作废',5,'核销中',6,'已核销') invoice_status,invoice_status status,invoice_amount from tp_invoice_list where 1 = 1";
		}
		if($admin){

		// }else{
		// 	$sql .= " and empl_dept like '%".$empl_depta."%'";
		}else{
			$sql .= " and empl_oa = '".$empl_oa."'";
		}
		if(!empty($id)){
			$sql .= " and invoice_id like '%".$id."%'";
		}
		if($admin && !empty($empl_dept)){
			$sql .= " and empl_dept like '%".$empl_dept."%'";
		}
		if(!empty($status)){
			$sql .= " and invoice_status = '".$status."'";
		}
		if(!empty($time1) && empty($time2)){
			$sql .= " and to_char(input_date,'yyyymmdd') >= to_char(to_date('".$time1."','yyyy-mm-dd'),'yyyymmdd')";
		}
		if(empty($time1) && !empty($time2)){
			$sql .= " and to_char(input_date,'yyyymmdd') <= to_char(to_date('".$time2."','yyyy-mm-dd'),'yyyymmdd')";
		}
		if(!empty($time1) && !empty($time2)){
			$sql .= " and to_char(input_date,'yyyymmdd') >= to_char(to_date('".$time1."','yyyy-mm-dd'),'yyyymmdd') and to_char(input_date,'yyyymmdd') <= to_char(to_date('".$time2."','yyyy-mm-dd'),'yyyymmdd')";
		}
		$sql .= " order by input_date";
		$role = $this->role();//本人所含角色
		$list = parent::listsSqlByls($sql,10);
		return $list;
	}
	
	//退回操作
	public function invoice_back(){
		if(isset($_POST)){
			$model = M();
			$id = I('id');
			$status = I('status');
			if(!empty($id) && $status != 2 && $status != 3 && $status != 4 && $status != 6){
				$data['invoice_status'] = 2;
				$datas['operate_type'] = '退回';
				$result = $model->table('tp_invoice_list')->where("invoice_id='".$id."'")->save($data);
				if($result){
					$id = date('YmdHis');
					$invoice_id = I('id');
					$operate_type = 2;
					$operate_way = '退回';
					$empl_oa = session('user_auth.OA');
					$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
					$results = $model->execute($sql);
					$this->redirect('index_list');
				}else{
					$this->error('操作失败！');
				}
			}else{
				$this->error('当前发票状态无法进行此操作！');
			}
		}
	}
	
	//待作废操作
	public function invoice_cancel(){
		if(isset($_POST)){
			$model = M();
			$id = I('invoice_id');
			$status = I('status');
			if(!empty($id) && $status != 3 && $status != 4){
				$data['invoice_status'] = 3;
				$result = $model->table('tp_invoice_list')->where("invoice_id='".$id."'")->save($data);
				if($result){
					$dept_manager = I('dept_manager');
					$manager_info = explode('|', $dept_manager);
					$datas['id'] = date('YmdHis');
					$datas['invoice_id'] = I('invoice_id');
					$datas['dept_manager_name'] = $manager_info[0];
					$datas['dept_manager_oa'] = $manager_info[1];
					$datas['cancel_reason'] = I('cancel_reason');
					$model->table('tp_invoice_cancel_list')->add($datas);
					$id = date('YmdHis');
					$invoice_id = I('invoice_id');
					$operate_type = 3;
					$operate_way = '作废';
					$empl_oa = session('user_auth.OA');
					$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
					$results = $model->execute($sql);
					$this->redirect('index_list');
				}else{
					$this->error('操作失败！');
				}
			}else{
				$this->error('当前发票状态无法进行此操作！');
			}
		}
	}

	//填写作废原因
	public function cancel_edit(){
		$model = M();
		$id = I('id');
		$status = I('status');
		$dept_manager = I('dept_manager');
		$cancel_reason = I('cancel_reason');
		$empl_info = $model->table('ls_flow.ls_employee_leave_list')->select();
		$this->assign('empl_info',$empl_info);
		if(!empty($id) && $status != 3 && $status != 4){
			$result = $model->table('tp_invoice_list')->where("invoice_id = '".$id."'")->find();
			$this->assign('result',$result);
			$this->display();
		}else{
			$this->error("当前发票状态无法进行此操作！");
		}
	}

	//确认作废
	public function cancel_confirm(){
		if(isset($_POST)){
			$model = M();
			$id = I('id');
			$status = I('status');
			if(!empty($id)){
				$data['invoice_status'] = 4;
				$result = $model->table('tp_invoice_list')->where("invoice_id='".$id."'")->save($data);
				if($result){
					$id = date('YmdHis');
					$invoice_id = I('id');
					$operate_type = 4;
					$operate_way = '确认作废';
					$empl_oa = session('user_auth.OA');
					$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
					$results = $model->execute($sql);
					$this->redirect('wait_list');
				}else{
					$this->error('操作失败！');
				}
			}
		}
	}
	
	//核销操作
	public function invoice_verify(){
		if(isset($_POST)){
			$model = M();
			$id = I('invoice_id');
			$invoice_amount = I('invoice_amount');
			$writeoff_amount = I('writeoff_amount');
			$amount = I('amount');
			if($invoice_amount-$writeoff_amount-$amount>0){
				$sql = "update tp_invoice_list set writeoff_amount = writeoff_amount+$amount,invoice_status = 5 where invoice_id = '".$id."'";
			}else{
				$sql = "update tp_invoice_list set writeoff_amount = writeoff_amount+$amount,invoice_status = 6 where invoice_id = '".$id."'";
			}
			$result = $model->execute($sql);
			if($result){
				$id = date('YmdHis');
				$invoice_id = I('invoice_id');
				$operate_type = 6;
				$operate_way = '核销';
				$receive_date = I('receive_date');
				$empl_oa = session('user_auth.OA');
				$remark = '0';
				$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
				$results = $model->execute($sql);
				$sql2 = "insert into tp_invoice_write_off_log (id,invoice_id,writeoff_date,writeoff_amount,receive_date,remark,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$amount."','".$receive_date."','".$remark."','".$empl_oa."')";
				$results2 = $model->execute($sql2);
				$this->success('核销成功！');
			}else{
				$this->error('核销失败！');
			}
		}
	}

	//撤回核销
	public function verify_back(){
		if(isset($_POST)){
			$model = M();
			$data['id'] = I('id');
			$data['remark'] = '1';
			$result = $model->table('tp_invoice_write_off_log')->where("id='".$data['id']."'")->save($data);
			if($result){
				$invoice_id = I('invoice_id');
				$writeoff_amount = I('amount');
				//把之前核销的金额去掉
				$model->table('tp_invoice_list')->where("invoice_id='".$invoice_id."'")->setDec('writeoff_amount',$writeoff_amount);
				$id = date('YmdHis');
				$operate_type = 6;
				$operate_way = '撤回核销';
				$empl_oa = session('user_auth.OA');
				//操作日志
				$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
				$model->execute($sql);
				$this->success('撤回核销成功！');
			}else{
				$this->error('撤回核销失败！');
			}
		}
	}
	
	//核销记录列表
	public function invoice_edit(){
		$model = M();
		$id = I('id');
		$status = I('status');
		if(!empty($id) && $status != 2 && $status != 3 && $status != 4){
			$result = $model->table('tp_invoice_list')->where("invoice_id='".$id."'")->find();
			$this->assign('result',$result);
			$sql = "select id,invoice_id,to_char(writeoff_date,'yyyy/mm/dd hh24:mi:ss') writeoff_date,writeoff_amount,receive_date from tp_invoice_write_off_log where invoice_id = '".$id."' and remark = '0' order by writeoff_date desc";
			$writeoff = parent::listsSqlByls($sql,5);
			$this->assign('writeoff',$writeoff);
			$this->display('Index/invoice_verify');
		}else{
			$this->error('当前发票状态无法进行此操作！');
		}
		if(isset($_POST)){
			$id = I('id');
			$invoice_amount = I('invoice_amount');
			$invoice_writeoff = I('invoice_writeof');
			if($invoice_writeoff-$invoice_amount>0){
				echo "<script>alert('核销金额不得大于发票金额');</script>";
			}
		}
	}
	
	//新增页面
	public function add(){
		$this->display('Index/invoice_add');
		
	}
	
	//新增操作
	public function invoice_add(){
		if(isset($_POST)){
			$model = M();
			$invoice_id = I('invoice_id');
			$empl_oa = session('user_auth.OA');
			$empl_name = session('user_auth.OPER_NAME');
			$empl_dept = session('user_auth.DEPT');
			$invoice_amount = I('invoice_amount');
			$writeoff_amount = 0;
			$invoice_company = I('invoice_company');
			$invoice_name = I('invoice_name');
			$invoice_purpose = I('invoice_purpose');
			$invoice_status = 1;
			$temp = $model->table('tp_invoice_list')->where("invoice_id = '".$invoice_id."'")->find();
			if($temp){
				$this->error('已存在该发票信息，请重新录入！');
			}else{
				$sql = "insert into tp_invoice_list (invoice_id,empl_oa,empl_name,invoice_company,invoice_name,invoice_purpose,invoice_status,invoice_amount,writeoff_amount,input_date,empl_dept) values('".$invoice_id."','".$empl_oa."','".$empl_name."','".$invoice_company."','".$invoice_name."','".$invoice_purpose."','".$invoice_status."','".$invoice_amount."','".$writeoff_amount."',sysdate,'".$empl_dept."')";
				$result = $model->execute($sql);
				if($result){
					$id = date('YmdHis');
					$invoice_id = I('invoice_id');
					$operate_type = 1;
					$operate_way = '新增';
					$empl_oa = session('user_auth.OA');
					$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
					$results = $model->execute($sql);
					$this->redirect('index_list');
				}else{
					$this->error('添加失败！');
				}
			}
		}
	}
	
	//待作废发票列表，管理员查看所有
	public function wait_list(){
		$empl_oa = session('user_auth.OA');
		$empl_dept = session('user_auth.DEPT');
		$model = M();
		$id = I('id');
		//部门经理
		//$manager = $model->table('tp_invoice_manager_list')->where("empl_oa = '".$empl_oa."'")->find();
		//管理员
		$admin = $model->table('tp_invoice_admin_list')->where("empl_oa = '".$empl_oa."'")->find();
		if(!empty($empl_oa)){
			$sql = "select a.invoice_id,a.empl_name,a.empl_dept,a.invoice_company,a.invoice_name,a.invoice_purpose,decode(a.invoice_status,1,'正常',2,'退回',3,'待作废',4,'作废',5,'核销中',6,'已核销') invoice_status,a.invoice_status status,a.invoice_amount,b.cancel_reason from tp_invoice_list a left join tp_invoice_cancel_list b on a.invoice_id = b.invoice_id where a.invoice_status = 3 ";
		}
		if($admin){
			$sql .= " and 1 = 1";
		}else{
			$sql .= " and b.dept_manager_oa = '".$empl_oa."'";
		}
		if(!empty($id)){
			$sql .= " and a.invoice_id like '%".$id."%'";
		}
		$sql .= " order by input_date";
		$list = parent::listsSqlByls($sql,10);
		$this->assign('wait_list',$list);
		$this->display('Index/wait_list');
	}

	//统计
	public function count_list(){
		$model = M();
		$time1 = I('input_date1');
		$time2 = I('input_date2');
		if(!empty($time1) && empty($time2)){
			$tiao = " and to_char(input_date,'yyyymmdd') >= to_char(to_date('".$time1."','yyyy-mm-dd'),'yyyymmdd')";
		}
		if(empty($time1) && !empty($time2)){
			$tiao = " and to_char(input_date,'yyyymmdd') <= to_char(to_date('".$time2."','yyyy-mm-dd'),'yyyymmdd')";
		}
		if(!empty($time1) && !empty($time2)){
			$tiao = " and to_char(input_date,'yyyymmdd') >= to_char(to_date('".$time1."','yyyy-mm-dd'),'yyyymmdd') and to_char(input_date,'yyyymmdd') <= to_char(to_date('".$time2."','yyyy-mm-dd'),'yyyymmdd')";
		}
		$sql = "select empl_dept,count(*) all_invoice,sum(case when invoice_status = 1 then 1 else 0 end) normal,sum(case when invoice_status = 2 then 1 else 0 end) back,sum(case when invoice_status = 3 then 1 else 0 end) wait_cancel,sum(case when invoice_status = 4 then 1 else 0 end) cancel,sum(case when invoice_status = 5 then 1 else 0 end) writeoff,sum(case when invoice_status = 6 then 1 else 0 end) writeoff_ok from tp_invoice_list where 1 = 1 ".$tiao." group by empl_dept union select '总计' empl_dept,count(*) all_invoice,sum(case when invoice_status = 1 then 1 else 0 end) normal,sum(case when invoice_status = 2 then 1 else 0 end) back,sum(case when invoice_status = 3 then 1 else 0 end) wait_cancel,sum(case when invoice_status = 4 then 1 else 0 end) cancel,sum(case when invoice_status = 5 then 1 else 0 end) writeoff,sum(case when invoice_status = 6 then 1 else 0 end) writeoff_ok from tp_invoice_list where 1 = 1 ".$tiao." order by all_invoice ";

		$result = $model->query($sql);
		$this->assign('count_list',$result);
		$this->display();
	}

	//hasRole
	public function hasRole($roleId=''){
		$flag = false;
		$sql = "select count(or_id) count from mz_user.t_sys_oper_role where or_oper_id = (select oper_id from mz_user.t_sys_oper where oa = '".session('user_auth.OA')."') and or_role_id={$roleId}";
		$num = M()->query($sql);
		if($num[0][COUNT]>0){
			$flag = true;
		}
		return $flag;
	}
	
	//role
	protected function role(){
		$role['1'] = $this->hasRole(5020001522);
		$role['2'] = $this->hasRole(5020001523);
		$role['3'] = $this->hasRole(5020001502);
		$role['4'] = $this->hasRole(5020001524);
		$role['5'] = $this->hasRole(5020001503);
		$role['6'] = $this->hasRole(5020000382);
		return $role;
	}
}

?>