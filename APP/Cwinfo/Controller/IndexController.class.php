<?php
namespace Cwinfo\Controller;
use Cwinfo\Controller\BaseController;

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

	public function main_business(){
		$this->display('index/main_business');
	}

	public function bill_info(){
		$this->display('index/bill_info');
	}

	public function index_list(){
		$user = $this->isLogin();
		if($user['IS_LOGIN']){
			$model = M();
			$subject_name = I('subject_name');
			$duty_person = I('duty_person');
			$duty_dept = I('duty_dept');
			$tiao = "";
			if(!empty($subject_name)){
				$tiao .= " and subject_name like '%".$subject_name."%'";
			}
			if(!empty($duty_person)){
				$tiao .= " and duty_person = '".$duty_person."'";
			}
			if(!empty($duty_dept)){
				$tiao .= " and duty_dept = '".$duty_dept."'";
			}
			$total = $model->table('ls_mz.cw_main_business_info')->where("1 = 1".$tiao)->count();
			$query = "select subject_name,subject_name_new,duty_person,duty_dept,rowidtochar(rowid) id from ls_mz.cw_main_business_info where 1 = 1 ";
			if(!empty($subject_name)){
				$query .= " and subject_name like '%".$subject_name."%'";
			}
			if(!empty($duty_person)){
				$query .= " and duty_person = '".$duty_person."'";
			}
			if(!empty($duty_dept)){
				$query .= " and duty_dept = '".$duty_dept."'";
			}
			$json = $model->query($query);
			$json = json_encode($json);
			echo '{"total":'.$total.',"rows":'.$json.'}';
			//echo $model->getLastSql();
			//$arr = array('total'=>$total,'rows'=>$json);
			//$this->ajaxReturn($arr);
		}
	}
	public function get_main_business(){
		$model = M();
		$id = I('id');
		$data = $model->query("select a.*,rowidtochar(rowid) id from ls_mz.cw_main_business_info a where rowid = '".$id."'");
		$data = json_encode($data);
		echo $data;
	}

	//更新主营业务信息
	public function update_main_business(){
		$model = M();
		$id = I('id');
		$subject_name = I('subject_name');
		$subject_name_new = I('subject_name_new');
		$duty_person = I('duty_person');
		$duty_dept = I('duty_dept');
		$data['subject_name'] = $subject_name;
		$data['subject_name_new'] = $subject_name_new;
		$data['duty_person'] = $duty_person;
		$data['duty_dept'] = $duty_dept;
		$result = $model->table('ls_mz.cw_main_business_info')->where("rowid = '".$id."'")->save($data);
		//$result = $model->execute("update ls_mz.cw_main_business_info set subject_name = '".$subject_name."' where rowid = '".$id."'");
		echo $result;
	}
	//主营业务新增
	public function main_business_add(){
		$model = M();
		$data['subject_name'] = I('subject_name');
		$data['subject_name_new'] = I('subject_name_new');
		$data['duty_person'] = I('duty_person');
		$data['duty_dept'] = I('duty_dept');
		$result = $model->table('ls_mz.cw_main_business_info')->add($data);
		$this->ajaxReturn($result);
	}

	//主营业务删除
	public function main_business_delete(){
		$model = M();
		$ids = I('ids');
		$sql = "delete from ls_mz.cw_main_business_info where rowid in $ids";
		$result = $model->execute($sql);
		//$result = $model->table('ls_mz.cw_main_business_info')->where("rowid in (".$ids.")")->delete();
		echo $result;
	}

	//账单科目明细列表
	public function bill_info_list(){
		$model = M();
		$subject_code = I('subject_code');
		$subject_name = I('subject_name');
		$finance_subject_name = I('finance_subject_name');
		$tiao = "";
		if(!empty($subject_code)){
			$tiao .= " and subject_code like '%".$subject_code."%'";
		}
		if(!empty($subject_name)){
			$tiao .= " and subject_name like '%".$subject_name."%'";
		}
		if(!empty($finance_subject_name)){
			$tiao .=" and finance_subject_name like '%".$finance_subject_name."%'";
		}
		$total = $model->table('ls_mz.cw_bill_info')->where("1 = 1".$tiao)->count();
		$query = "select a.*,rowidtochar(rowid) id from ls_mz.cw_bill_info a where 1 = 1";
		if(!empty($subject_code)){
			$query .= " and a.subject_code like '%".$subject_code."%'";
		}
		if(!empty($subject_name)){
			$query .= " and a.subject_name like '%".$subject_name."%'";
		}
		if(!empty($finance_subject_name)){
			$query .= " and a.finance_subject_name like '%".$finance_subject_name."%'";
		}
		$json = $model->query($query);
		$json = json_encode($json);
		echo '{"total":'.$total.',"rows":'.$json.'}';
	}

	//账单科目新增
	public function bill_info_add(){
		$model = M();
		$data['subject_code'] = I('subject_code');
		$data['subject_name'] = I('subject_name');
		$data['tax_rate'] = I('tax_rate');
		$data['finance_subject_name'] = I('finance_subject_name');
		$result = $model->table('ls_mz.cw_bill_info')->add($data);
		$this->ajaxReturn($result);
	}

	//账单科目修改
	public function bill_info_update(){
		$model = M();
		$id = I('id');
		$subject_code = I('subject_code');
		$subject_name = I('subject_name');
		$tax_rate = I('tax_rate');
		$finance_subject_name = I('finance_subject_name');
		$data['subject_code'] = $subject_code;
		$data['subject_name'] = $subject_name;
		$data['tax_rate'] = $tax_rate;
		$data['finance_subject_name'] = $finance_subject_name;
		$result = $model->table('ls_mz.cw_bill_info')->where("rowid = '".$id."'")->save($data);
		echo $result;
	}

	//获取账单科目信息
	public function get_bill_info(){
		$model = M();
		$id = I('id');
		$data = $model->query("select a.*,rowidtochar(rowid) id from ls_mz.cw_bill_info a where rowid = '".$id."'");
		$data = json_encode($data);
		echo $data;
	}

	//账单科目删除
	public function bill_info_delete(){
		$model = M();
		$ids = I('ids');
		$sql = "delete from ls_mz.cw_bill_info where rowid in $ids";
		$result = $model->execute($sql);
		echo $result;
	}

	//数据导入页面
	public function import_data(){
		$this->display('index/import_data');
	}

	//执行导入
	public function data_import(){
		$model = M();
		$table_name = I('table_name');
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('xlsx','xls');
		//$upload->savePath = '/Uploads/';
		$upload->saveName = date('ymdhis');
		$info = $upload->upload();
		vendor("PHPExcel.PHPExcel");
		vendor("PHPExcel.PHPExcel.IOFactory");
		vendor("PHPExcel.PHPExcel.Reader.Excel5");
		vendor("PHPExcel.PHPExcel.Reader.Excel2007");
		$objPHPExcel = new \PHPExcel;
		$PHPReader = new \PHPExcel_Reader_Excel5();
		$path = getcwd();
		$path = str_replace("\\", "/", $path);
		$file_name = $path."/Uploads/".$info['up_file']['savepath'].$info['up_file']['savename'];
		$objReader = $PHPReader->load($file_name);
		$extension = strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
		if($extension == 'xlsx'){
			$objReader = \IOFactory::createReader('Excel2007');
			$objPHPExcel = $objReader->load($file_name,$encode = 'utf-8');
		}elseif($extension == 'xls'){
			$objReader = \IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load($file_name,$encode = 'utf-8');
		}
		$sheet = $objPHPExcel->getSheet(0);	//获取excel第一个工作表
		$highestRow = $sheet->getHighestRow(); //取得总行数
		$highestColumn = $sheet->getHighestColumn(); //取得总列数
		for($currentRow = 1;$currentRow<=$highestRow;$currentRow++){
			for($currentColumn = 'A';$currentColumn<=$highestColumn;$currentColumn++){
				$address = $currentColumn.$currentRow;
				$arr[$currentRow][$currentColumn]=$sheet->getCell($address)->getValue();
			}
		}
		if($table_name == "全市主营业务收入"){
			for($i = 2;$i<=$highestRow;$i++){
				$business_date = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				if(is_numeric($business_date)){
					$business_date = intval(($business_date - 25569)*3600*24);
					$business_date = gmdate('Y年n月',$business_date);
					$data['business_date'] = $business_date;
				}else{
					$data['business_date'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				}
				$data['subject_code'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
				$data['area_name'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
				$data['county_name'] = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
				$data['subject_name'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
				$data['tax_rate'] = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
				$data['amount'] = $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
				$data['tax'] = $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
				$data['no_tax_income'] = $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
				$result = $model->table('ls_mz.cw_main_business')->add($data);
			}
		}elseif($table_name == "全省主营业务收入"){
			for($i = 2;$i<=$highestRow;$i++){
				$business_date = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				if(is_numeric($business_date)){
					$business_date = intval(($business_date - 25569)*3600*24);
					$business_date = gmdate('Y年n月',$business_date);
					$data['business_date'] = $business_date;
				}else{
					$data['business_date'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				}
				$data['subject_code'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
				$data['area_name'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
				$data['subject_name'] = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
				$data['tax_rate'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
				$data['amount'] = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
				$data['tax'] = $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
				$data['no_tax_income'] = $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
				$result = $model->table('ls_mz.cw_province_main_business')->add($data);
			}
		}elseif($table_name == "全市账单科目明细"){
			for($i = 2;$i<=$highestRow;$i++){
				$business_date = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				if(is_numeric($business_date)){
					$business_date = intval(($business_date - 25569)*3600*24);
					$business_date = gmdate('Y年n月',$business_date);
					$data['business_date'] = $business_date;
				}else{
					$data['business_date'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				}
				$data['subject_code'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
				$data['area_name'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
				$data['county_name'] = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
				$data['subject_name'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
				$data['tax_rate'] = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
				$data['amount'] = $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
				$data['tax'] = $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
				$data['no_tax_income'] = $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
				$data['split_cost'] = $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
				$data['finance_subject_name'] = $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
				$result = $model->table('ls_mz.cw_city_bill_subject')->add($data);
			}
		}elseif($table_name == "全省账单科目明细"){
			for($i =2;$i<=$highestRow;$i++){
				$business_date = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				if(is_numeric($business_date)){
					$business_date = intval(($business_date - 25569)*3600*24);
					$business_date = gmdate('Y年n月',$business_date);
					$data['business_date'] = $business_date;
				}else{
					$data['business_date'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				}
				$data['subject_code'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
				$data['area_name'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
				$data['subject_name'] = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
				$data['tax_rate'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
				$data['amount'] = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
				$data['tax'] = $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
				$data['no_tax_income'] = $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
				$data['split_cost'] = $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
				$data['finance_subject_name'] = $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
				$result = $model->table('ls_mz.cw_main_business_list')->add($data);
			}
		}
		if($result){
			echo "导入成功";
		}else{
			echo "导入失败";
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