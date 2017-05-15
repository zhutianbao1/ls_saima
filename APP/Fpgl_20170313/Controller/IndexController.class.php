<?php
namespace Fpgl\Controller;
use Fpgl\Controller\BaseController;

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
			echo "会话超时，请在当前窗口新建选项卡重新登录OA，然后刷新本页面";
		}
	}
	
	//普通用户查看本人添加的发票信息，管理员查看部门信息
	public function index(){
		$user = $this->isLogin();
		if($user['IS_LOGIN']){
			$result = self::queryString();
			$this->assign('invoice_list',$result);
			$this->display();
		}
	}
	
	//获取发票信息列表
	public function queryString(){
		$admin = array('hanya','zhangweixing1','lumf','zhaibo');
		$empl_oa = session('user_auth.OA');
		$status = I('status');
		$id = I('id');
		if(!empty($empl_oa)){
			$sql = "select invoice_id,empl_name,empl_dept,invoice_company,invoice_name,invoice_purpose,decode(invoice_status,1,'正常',2,'退回',3,'作废',4,'核销中',5,'已核销') invoice_status,invoice_status status,invoice_amount from tp_invoice_list where 1 = 1";
		}
		if(!in_array($empl_oa,$admin)){
			$sql .= " and empl_oa = '".$empl_oa."'";
		}
		if(!empty($id)){
			$sql .= " and invoice_id like '%".$id."%'";
		}
		if(!empty($status)){
			$sql .= " and invoice_status = '".$status."'";
		}
		$sql .= " order by input_date";
		$role = $this->role();//本人所含角色
		$list = parent::listsSqlByls($sql,20);
		return $list;
	}
	
	//退回操作
	public function invoice_back(){
		if(isset($_POST)){
			$model = M();
			$id = I('id');
			$status = I('status');
			if(!empty($id) && $status != 2 && $status != 3 && $status !=5){
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
					$this->redirect('index');
				}else{
					$this->error('操作失败！');
				}
			}else{
				$this->error('当前发票状态无法进行此操作！');
			}
		}
	}
	
	//作废操作
	public function invoice_cancel(){
		if(isset($_POST)){
			$model = M();
			$id = I('id');
			$status = I('status');
			if(!empty($id) && $status != 3 && $status != 5){
				$data['invoice_status'] = 3;
				$result = $model->table('tp_invoice_list')->where("invoice_id='".$id."'")->save($data);
				if($result){
					$id = date('YmdHis');
					$invoice_id = I('id');
					$operate_type = 3;
					$operate_way = '作废';
					$empl_oa = session('user_auth.OA');
					$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
					$results = $model->execute($sql);
					$this->redirect('index');
				}else{
					$this->error('操作失败！');
				}
			}else{
				$this->error('当前发票状态无法进行此操作！');
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
				$sql = "update tp_invoice_list set writeoff_amount = writeoff_amount+$amount,invoice_status = 4 where invoice_id = '".$id."'";
			}else{
				$sql = "update tp_invoice_list set writeoff_amount = writeoff_amount+$amount,invoice_status = 5 where invoice_id = '".$id."'";
			}
			$result = $model->execute($sql);
			if($result){
				$id = date('YmdHis');
				$invoice_id = I('invoice_id');
				$operate_type = 5;
				$operate_way = '核销';
				$receive_date = I('receive_date');
				$empl_oa = session('user_auth.OA');
				$sql = "insert into tp_invoice_operate_log (id,invoice_id,operate_date,operate_type,operate_way,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$operate_type."','".$operate_way."','".$empl_oa."')";
				$results = $model->execute($sql);
				$sql2 = "insert into tp_invoice_write_off_log (id,invoice_id,writeoff_date,writeoff_amount,receive_date,empl_oa) values('".$id."','".$invoice_id."',sysdate,'".$amount."','".$receive_date."','".$empl_oa."')";
				$results2 = $model->execute($sql2);
				//echo $model->getLastSql();
				$this->success('核销成功！');
			}else{
				$this->error('核销失败！');
			}
		}
	}
	
	public function invoice_edit(){
		$model = M();
		$id = I('id');
		$status = I('status');
		if(!empty($id) && $status != 2 && $status != 3){
			$result = $model->table('tp_invoice_list')->where("invoice_id='".$id."'")->find();
			$this->assign('result',$result);
			//$writeoff = $model->table('tp_invoice_write_off_log')->field(to_char(WRITEOFF_DATE,'yyyy-mm-dd'))->where("invoice_id='".$id."'")->select();
			$sql = "select invoice_id,to_char(writeoff_date,'yyyy/mm/dd hh24:mi:ss') writeoff_date,writeoff_amount,receive_date from tp_invoice_write_off_log where invoice_id = '".$id."' order by writeoff_date";
			$writeoff = $model->query($sql);
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
	
	//新增
	public function add(){
		$this->display('Index/invoice_add');
		
	}
	
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
					$this->redirect('index');
				}else{
					$this->error('添加失败！');
				}
			}
		}
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