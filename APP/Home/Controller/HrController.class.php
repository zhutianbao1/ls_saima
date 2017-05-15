<?php  
	namespace Home\Controller;

	class HrController extends BaseController {
		

		protected function _initialize(){
			 // parent::viewLog();
			$ACTION_NAME  = $Think.ACTION_NAME;	 
			if($ACTION_NAME!='webList'){
				$bill_id = I('bill_id');
				if($bill_id){
					session('bill_id',$bill_id);
				}else{
					$bill_id = session('bill_id');
				}
				$this->assign('bill_id',$bill_id);
				parent::intiParams();

				$oa = I('oa');
				$bill_id = I('bill_id');
				parent::loginByOa($bill_id,$oa);

				//获取详细的用户信息
				$where=" and oper_login_code='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
				parent::loginUser($where);
			}
		}

		public function webList(){
			$this->display();
		}

		public function index(){
			parent::isLogin();
			$this->display('hr/index');
		}

		public function hr_kh_que($bill_id=''){
			
		  parent::intiParamsURL();

		  $hrRole = M('hrRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $hrInfo = M('hrInfo');
		  $where = ' status=1 ';

		 
		  if($role['COUNTY_CODE']!='5780' && !empty($role['COUNTY_CODE'])){
		  	$where.=" and county_code='".$role['COUNTY_CODE']."'";
		  }
 
		  if(!empty($name)){
		  	$where.="and pos_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }

		  $order = I('order');
		  $orders = '测评排名';
		  if(!empty($order)){
		  	$orders = $order;
		  }
		  //$hrs = $hrInfo->where($where)->order($orders)->select();
		  $hrs = parent::lists($hrInfo,'',$where,$orders);
 		  
		  $this->assign('hrs',$hrs);
		  parent::viewLog();
		  $this->display('Hr/hr_kh_que');
		}

		public function hr_kh_index($billId=''){
			$oa = I('oa');
			$bill_id = I('bill_id');
			parent::loginByOa($bill_id,$oa);

			$hrInfo = M('hrInfo');
			//$hr = $hrInfo->find();
			$hr = $hrInfo->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			$sql="select count(*) a from rank_hr_info";
			$info=$hrInfo->query($sql);
			$this->assign('info',$info);
			

			$sqls="SELECT a.* FROM rank_hr_train a,rank_hr_info b WHERE a.user_name=b.pos_name AND 
			bill_id=".$billId."";
			$train = $hrInfo->query($sqls);
			$this->assign('train',$train);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='客户经理' AND 备注='未培' and status=0";
			$cour = $hrInfo->query($db);
			$this->assign('cour',$cour);

			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%客户经理%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%客户经理%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfo->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display('hr/hr_kh_index');
		}

		public function hr_xs_que($bill_id=''){
		// parent::isLogin();
		  $hrInfoXs = M('hrInfoXs');

		  $hrRole = M('qdRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $where = ' status=1 ';
		  if(!empty($name)){
		  	$where.="and user_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }

		  $orders='测评排名';
		  $hrs = parent::lists($hrInfoXs,'',$where,$orders);
		  $this->assign('hrs',$hrs);
		  parent::viewLog();
		  $this->display('hr/hr_xs_que');
		}

		public function hr_xs_index($billId=''){
			$hrInfoXs = M('hrInfoXs');
			$hr = $hrInfoXs->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			$sql="select count(*) a from rank_hr_info_xs";
			$info=$hrInfoXs->query($sql);
			$this->assign('info',$info);
			

			$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_xs b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
			$train = $hrInfoXs->query($sqls);
			$this->assign('train',$train);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='销售经理' AND 备注='未培'";
			$cour = $hrInfoXs->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%社会渠道经理%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%社会渠道经理%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoXs->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display('hr/hr_xs_index');
		}

		public function hr_xs_que2($bill_id=''){
		// parent::isLogin();
		  $hrInfoXs2 = M('hrInfoXs2');

		  $hrRole = M('qdRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $where = ' status=1 ';
		  if(!empty($name)){
		  	$where.="and user_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }

		  $orders='排名';
		  $hrs = parent::lists($hrInfoXs2,'',$where,$orders);
		  $this->assign('hrs',$hrs);
		  parent::viewLog();
		  $this->display('hr/hr_xs_que2');
		}

		public function hr_xs_index2($billId=''){
			$hrInfoXs2 = M('hrInfoXs2');
			$hr = $hrInfoXs2->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			$sql="select count(*) a from rank_hr_info_xs2";
			$info=$hrInfoXs2->query($sql);
			$this->assign('info',$info);
			

			$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_xs b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
			$train = $hrInfoXs2->query($sqls);
			$this->assign('train',$train);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='销售经理' AND 备注='未培'";
			$cour = $hrInfoXs2->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%社会渠道经理%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%社会渠道经理%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoXs2->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display('hr/hr_xs_index2');
		}

		public function hr_xm_que($bill_id=''){
		// parent::isLogin();
		  $hrInfoXm = M('hrInfoXm');

		  $hrRole = M('xmRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $where = ' status=1 ';
		  if(!empty($name)){
		  	$where.="and user_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }

		  $orders='排名';
		  $hrs = parent::lists($hrInfoXm,'',$where,$orders);
		  $this->assign('hrs',$hrs);
		  parent::viewLog();
		  $this->display('hr/hr_xm_que');
		}

		public function hr_xm_index($billId=''){
			$hrInfoXm = M('hrInfoXm');
			$hr = $hrInfoXm->where('bill_id='.$billId)->find();
			$this->assign('hr',$hr);

			$sql="select count(*) a from rank_hr_info_xm";
			$info=$hrInfoXm->query($sql);
			$this->assign('info',$info);
			

			$sqls="SELECT a.* FROM rank_hr_train_xm a,rank_hr_info_xm b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
			$train = $hrInfoXm->query($sqls);
			$this->assign('train',$train);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='项目经理' AND 备注='未培'";
			$cour = $hrInfoXm->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%项目经理%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%项目经理%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoXm->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display('hr/hr_xm_index');
		}

		public function hr_yy_que($bill_id='',$id=0){
		// parent::isLogin();
		  $hrInfoYy = M('hrInfoYy');
		  $hrInfoYy2 = M('hrInfoYy2');

		  $hrRole = M('hrRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $where = ' status=1 ';
		  if(!empty($name)){
		  	$where.="and user_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }

		  $orders='测评排名';
		  if($id==1){
		  	$hrs = parent::lists($hrInfoYy2,'',$where,$orders);
			$this->assign('hrs',$hrs);
			parent::viewLog();
			$this->display('hr/hr_yy_que2');
		  }else{
		  	$hrs = parent::lists($hrInfoYy,'',$where,$orders);
			$this->assign('hrs',$hrs);
			parent::viewLog();
			$this->display('hr/hr_yy_que');
		  }
		}

		public function hr_yy_index($billId=''){
			$hrInfoYy = M('hrInfoYy');
			$hr = $hrInfoYy->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			$sql="select count(*) a from rank_hr_info_yy";
			$info=$hrInfoYy->query($sql);
			$this->assign('info',$info);
			

			$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
			$train = $hrInfoYy->query($sqls);
			$this->assign('train',$train);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='营业主管' AND 备注='未培'";
			$cour = $hrInfoYy->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%营业主管%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%营业主管%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoYy->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display('hr/hr_yy_index');
		}

		public function hr_yy_index2($billId=''){
			$hrInfoYy2 = M('hrInfoYy2');
			$hr = $hrInfoYy2->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			$sql="select count(*) a from rank_hr_info_yy2";
			$info=$hrInfoYy2->query($sql);
			$this->assign('info',$info);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='营业主管' AND 备注='未培'";
			$cour = $hrInfoYy2->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%营业主管%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%营业主管%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoYy2->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display();
		}

		public function hr_qyw_que($bill_id='',$id=0){
		// parent::isLogin();
		  $hrInfoQyw2 = M('hrInfoQyw2');
		  $hrInfoQyw1 = M('hrInfoQyw1');
		  $hrInfoQyw3 = M('hrInfoQyw3');
		  $hrInfoQyw12 = M('hrInfoQyw12');
		  $hrInfoQyw22 = M('hrInfoQyw22');
		  $hrInfoQyw32 = M('hrInfoQyw32');

		  $hrRole = M('hrRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $where = ' 1=1 ';
		  if(!empty($name)){
		  	$where.="and user_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }
		  $orders='测评综合排名';
		  if($id==1){
			  $hrs = parent::lists($hrInfoQyw3,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_qyw_que3');
		  }
		  else if($id==2){
			  $hrs = parent::lists($hrInfoQyw2,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_qyw_que2');
		  }
		  else if($id==3){
			  $hrs = parent::lists($hrInfoQyw1,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_qyw_que1');
		  }
		  else if($id==4){
			  $hrs = parent::lists($hrInfoQyw32,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_qyw_que32');
		  }
		  else if($id==5){
			  $hrs = parent::lists($hrInfoQyw22,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_qyw_que22');
		  }
		  else if($id==6){
			  $hrs = parent::lists($hrInfoQyw12,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_qyw_que12');
		  }
		}

		public function hr_qyw_index($billId=''){
			$hrInfoQyw1 = M('hrInfoQyw1');
			$hr = $hrInfoQyw1->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				//$this->assign('errMsg','未获取到'.$billId.'用户信息');
				$this->hr_qyw_index2($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_qyw1";
				$info=$hrInfoQyw1->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_qyw1 b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoQyw1->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='全业务维护' AND 备注='未培'";
				$cour = $hrInfoQyw1->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%全业务维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%全业务维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoQyw1->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display('hr/hr_qyw_index1');
			}
		}

		public function hr_qyw_index2($billId=''){
			$hrInfoQyw2 = M('hrInfoQyw2');
			$hr = $hrInfoQyw2->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				//$this->assign('errMsg','未获取到'.$billId.'用户信息');
				$this->hr_qyw_index3($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_qyw2";
				$info=$hrInfoQyw2->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_qyw2 b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoQyw2->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='全业务维护' AND 备注='未培'";
				$cour = $hrInfoQyw2->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%全业务维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%全业务维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoQyw2->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display('hr/hr_qyw_index2');
			}
		}

		public function hr_qyw_index3($billId=''){
			$hrInfoQyw3 = M('hrInfoQyw3');
			$hr = $hrInfoQyw3->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			$sql="select count(*) a from rank_hr_info_qyw3";
			$info=$hrInfoQyw3->query($sql);
			$this->assign('info',$info);
			

			$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_qyw3 b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
			$train = $hrInfoQyw3->query($sqls);
			$this->assign('train',$train);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='全业务维护' AND 备注='未培'";
			$cour = $hrInfoQyw3->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%全业务维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%全业务维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoQyw3->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display('hr/hr_qyw_index3');
		}

		public function hr_qyw_index12($billId=''){
			$hrInfoQyw12 = M('hrInfoQyw12');
			$hr = $hrInfoQyw12->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				//$this->assign('errMsg','未获取到'.$billId.'用户信息');
				$this->hr_qyw_index22($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_qyw12";
				$info=$hrInfoQyw12->query($sql);
				$this->assign('info',$info);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='全业务维护' AND 备注='未培'";
				$cour = $hrInfoQyw12->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%全业务维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%全业务维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoQyw12->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display();
			}
		}

		public function hr_qyw_index22($billId=''){
			$hrInfoQyw22 = M('hrInfoQyw22');
			$hr = $hrInfoQyw22->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				//$this->assign('errMsg','未获取到'.$billId.'用户信息');
				$this->hr_qyw_index32($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_qyw22";
				$info=$hrInfoQyw22->query($sql);
				$this->assign('info',$info);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='全业务维护' AND 备注='未培'";
				$cour = $hrInfoQyw22->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%全业务维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%全业务维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoQyw22->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display();
			}
		}

		public function hr_qyw_index32($billId=''){
			$hrInfoQyw32 = M('hrInfoQyw32');
			$hr = $hrInfoQyw32->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);
			
			$sql="select count(*) a from rank_hr_info_qyw32";
			$info=$hrInfoQyw32->query($sql);
			$this->assign('info',$info);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='全业务维护' AND 备注='未培'";
			$cour = $hrInfoQyw32->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%全业务维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%全业务维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoQyw32->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();
			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			$this->display();
		}

		public function hr_zh_que($bill_id='',$id=0){
		// parent::isLogin();
		  $hrInfoZh1 = M('hrInfoZh1');
		  $hrInfoZh2 = M('hrInfoZh2');
		  $hrInfoZh3 = M('hrInfoZh3');
		  $hrInfoZh4 = M('hrInfoZh4');
		  $hrInfoZh12 = M('hrInfoZh12');
		  $hrInfoZh32 = M('hrInfoZh32');

		  $hrRole = M('hrRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $where = ' status=1 ';
		  if(!empty($name)){
		  	$where.="and user_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }

		  $orders='排名';
		  if($id==1){
			  $hrs = parent::lists($hrInfoZh1,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_zh_que1');
		  }
		  else if($id==2){
			  $hrs = parent::lists($hrInfoZh2,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_zh_que2');
		  }
		  else if($id==3){
			  $hrs = parent::lists($hrInfoZh3,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_zh_que3');
		  }
		  else if($id==4){
			  $hrs = parent::lists($hrInfoZh4,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_zh_que4');
		  }
		  else if($id==5){
			  $hrs = parent::lists($hrInfoZh12,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_zh_que12');
		  }
		  else if($id==6){
		  	$where.=" and 岗位='综合维护（无线）'";
			  $hrs = parent::lists($hrInfoZh32,'',$where,$orders);
			  $this->assign('hrs',$hrs);
			  $this->assign('id',$id);
		  parent::viewLog();
			  $this->display('hr/hr_zh_que32');
		  }
		  else if($id==7){
		  	$where.=" and 岗位='综合维护（传输）'";
			  $hrs = parent::lists($hrInfoZh32,'',$where,$orders);
			  $this->assign('hrs',$hrs);
			  $this->assign('id',$id);
		  parent::viewLog();
			  $this->display('hr/hr_zh_que32');
		  }
		}

		public function hr_zh_index($billId=''){
			$hrInfoZh1 = M('hrInfoZh1');
			$hr = $hrInfoZh1->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				//$this->assign('errMsg','未获取到'.$billId.'用户信息');
				$this->hr_zh_index2($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_zh1";
				$info=$hrInfoZh1->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoZh1->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='综合维护' AND 备注='未培'";
				$cour = $hrInfoZh1->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoZh1->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display('hr/hr_zh_index1');
			}
		}

		public function hr_zh_index2($billId=''){
			$hrInfoZh2 = M('hrInfoZh2');
			$hr = $hrInfoZh2->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				$this->hr_zh_index3($billId);
			}else{
				$sql="select count(*) a from rank_hr_info_zh2";
				$info=$hrInfoZh2->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoZh2->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='综合维护' AND 备注='未培'";
				$cour = $hrInfoZh2->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoZh2->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();

				$this->display('hr/hr_zh_index2');
			}
		}

		public function hr_zh_index3($billId=''){
			$hrInfoZh3 = M('hrInfoZh3');
			$hr = $hrInfoZh3->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				$this->hr_zh_index4($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_zh3";
				$info=$hrInfoZh3->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoZh3->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='综合维护' AND 备注='未培'";
				$cour = $hrInfoZh3->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoZh3->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display('hr/hr_zh_index3');
			}
		}

		public function hr_zh_index4($billId=''){
			$hrInfoZh4 = M('hrInfoZh4');
			$hr = $hrInfoZh4->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}

			$sql="select count(*) a from rank_hr_info_zh4";
			$info=$hrInfoZh4->query($sql);
			$this->assign('info',$info);
			

			$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
			$train = $hrInfoZh4->query($sqls);
			$this->assign('train',$train);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='综合维护' AND 备注='未培'";
			$cour = $hrInfoZh4->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoZh4->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();
			$this->display('hr/hr_zh_index4');
		}

		public function hr_zh_index12($billId=''){
			$hrInfoZh12 = M('hrInfoZh12');
			$hr = $hrInfoZh12->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}

			$sql="select count(*) a from rank_hr_info_zh12";
			$info=$hrInfoZh12->query($sql);
			$this->assign('info',$info);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='综合维护' AND 备注='未培'";
			$cour = $hrInfoZh12->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoZh12->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();
			$this->display('hr/hr_zh_index12');
		}

		public function hr_zh_index32($billId=''){
			$hrInfoZh32 = M('hrInfoZh32');
			$hr = $hrInfoZh32->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}
			if($hr['岗位']=='综合维护（无线）'){
				$sql="select count(*) a from rank_hr_info_zh32 where 岗位='综合维护（无线）'";
			}else{
				$sql="select count(*) a from rank_hr_info_zh32 where 岗位='综合维护（传输）'";
			}
			$info=$hrInfoZh32->query($sql);
			$this->assign('info',$info);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='综合维护' AND 备注='未培'";
			$cour = $hrInfoZh32->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoZh32->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();
			$this->display('hr/hr_zh_index32');
		}

		public function hr_gc_que($bill_id='',$id=0){
		// parent::isLogin();
		  $hrInfoGc1 = M('hrInfoGc1');
		  $hrInfoGc2 = M('hrInfoGc2');
		  $hrInfoGc3 = M('hrInfoGc3');
		  $hrInfoGc = M('hrInfoGc');

		  $hrRole = M('hrRole');
		  $name = I('name');
		  $countyName = I('countyName');
		  $billId = I('billId');
		  $userId = I('userId');
		  $pro = I('pro');
		  $xl = I('xl');
		  $dat = I('dat');
		  $dat1 = I('dat1');
		  $age = I('age');
		  $age1 = I('age1');
		  
		  $role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		  if(empty($role)){
		  	$this->assign('errMsg',$bill_id.'暂无权限访问，请联系管理员');
		  }

		  $where = ' 1=1 ';
		  if(!empty($name)){
		  	$where.="and user_name like '%".$name."%'";
		  }
		  if(!empty($countyName) && !empty($role['COUNTY_CODE'])){
		  	$where.="and county_name='".$countyName."'";
		  }
		  if(!empty($billId)){
		  	$where.="and bill_id like '%".$billId."%'";
		  }
		  if(!empty($userId)){
		  	$where.="and user_id like '%".$userId."%'";
		  }
		  if(!empty($pro)){
		  	$where.="and 用工性质 like '%".$pro."%'";
		  }
		  if(!empty($xl)){
		  	$where.="and 最高学历 like '%".$xl."%'";
		  }
		  if(!empty($dat)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') >= '".$dat."'";
		  }
		  if(!empty($dat1)){
		  	$where.="and to_char(入职时间,'yyyy-mm-dd') <= '".$dat1."'";
		  }
		  if (!empty($age1)) {
		  	$where.="and 岗位工龄 <= ".$age1."";
		  }
		  if(!empty($age)) {
		  	$where.="and 岗位工龄 >= ".$age."";
		  }

		  $orders='排名';
		  if($id==1){
			  $hrs = parent::lists($hrInfoGc1,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_gc_que1');
		  }
		  else if($id==2){
			  $hrs = parent::lists($hrInfoGc2,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_gc_que2');
		  }
		  else if($id==3){
			  $hrs = parent::lists($hrInfoGc3,'',$where,$orders);
			  $this->assign('hrs',$hrs);
		  parent::viewLog();
			  $this->display('hr/hr_gc_que3');
		  }
		  else if($id==4){
		  	$where.=" and 岗位='工程建设—有线'";
			  $hrs = parent::lists($hrInfoGc,'',$where,$orders);
			  $this->assign('hrs',$hrs);
			  $this->assign('id',4);
		  parent::viewLog();
			  $this->display('hr/hr_gc_que');
		  }
		  else if($id==5){
		  	$where.=" and 岗位='工程建设—无线'";
			  $hrs = parent::lists($hrInfoGc,'',$where,$orders);
			  $this->assign('hrs',$hrs);
			  $this->assign('id',5);
		  parent::viewLog();
			  $this->display('hr/hr_gc_que');
		  }
		}

		public function hr_gc_index1($billId=''){
			$hrInfoGc1 = M('hrInfoGc1');
			$hr = $hrInfoGc1->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				//$this->assign('errMsg','未获取到'.$billId.'用户信息');
				$this->hr_gc_index2($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_gc1";
				$info=$hrInfoGc1->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoGc1->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='综合事务' AND 备注='未培'";
				$cour = $hrInfoGc1->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合事务%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合事务%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoGc1->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display();
			}
		}

		public function hr_gc_index2($billId=''){
			$hrInfoGc2 = M('hrInfoGc2');
			$hr = $hrInfoGc2->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				//$this->assign('errMsg','未获取到'.$billId.'用户信息');
				$this->hr_gc_index3($billId);
			}else{

				$sql="select count(*) a from rank_hr_info_gc2";
				$info=$hrInfoGc2->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoGc2->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='综合事务' AND 备注='未培'";
				$cour = $hrInfoGc2->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合事务%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合事务%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoGc2->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display('hr/hr_gc_index2');
			}
		}

		public function hr_gc_index3($billId=''){
			$hrInfoGc3 = M('hrInfoGc3');
			$hr = $hrInfoGc3->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户信息');
			}

				$sql="select count(*) a from rank_hr_info_gc3";
				$info=$hrInfoGc3->query($sql);
				$this->assign('info',$info);
				

				$sqls="SELECT a.* FROM rank_hr_train_xs a,rank_hr_info_yy b WHERE a.user_name=b.user_name AND bill_id=".$billId."";
				$train = $hrInfoGc3->query($sqls);
				$this->assign('train',$train);
				
				$db="SELECT * FROM rank_hr_course WHERE 岗位='综合事务' AND 备注='未培'";
				$cour = $hrInfoGc3->query($db);
				$this->assign('cour',$cour);
				$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%综合事务%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%综合事务%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
				$ty_cour = $hrInfoGc3->query($ty);
				$this->assign('ty_cour',$ty_cour);

				parent::viewLog();
				$this->display('hr/hr_gc_index3');
		}

		public function hr_gc_index($billId=''){
			$hrInfoGc = M('hrInfoGc');
			$hr = $hrInfoGc->where('bill_id='.$billId)->cache(true)->find();
			$this->assign('hr',$hr);

			if(empty($hr)){
				$this->assign('errMsg','未获取到'.$billId.'用户二次评测信息');
			}
			if($hr['岗位']=='工程建设—有线'){
				$sql="select count(*) a from rank_hr_info_gc where 岗位='工程建设—有线'";
			}else{
				$sql="select count(*) a from rank_hr_info_gc where 岗位='工程建设—无线'";
			}
			$info=$hrInfoGc->query($sql);
			$this->assign('info',$info);
			
			$db="SELECT * FROM rank_hr_course WHERE 岗位='综合事务' AND 备注='未培'";
			$cour = $hrInfoGc->query($db);
			$this->assign('cour',$cour);
			$ty="select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where training_object like '%全业务维护%' AND userid IS NULL AND course_name NOT IN (SELECT b.course_name FROM(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."') a,(select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a WHERE training_object like '%全业务维护%' AND userid IS NULL) b WHERE a.course_name=b.course_name) union select decode(train_approve,0,'已报名',1,'请假中',2,'已请假',3,'已参加',4,'迟到',5,'旷课','报名') 情况,a.* from ls_flow.ls_flow_train_baoming a where userid='".$hr['USER_ID']."'";
			$ty_cour = $hrInfoGc->query($ty);
			$this->assign('ty_cour',$ty_cour);

			parent::viewLog();
			$this->display();
		}


		public function hr_kh_policy(){
			$this->display('hr/hr_kh_policy');
		}

		public function hr_kh_mba(){
			$this->display('hr/hr_kh_mba');
		}

		public function hr_kh_policy1(){
			$this->display('hr/hr_kh_policy1');
		}

		public function hr_kh_pie(){
			$this->display('hr/hr_kh_pie');
		}

		public function hr_kh_shouce(){
			$this->display('hr/hr_kh_shouce');
		}

		public function hr_kh_shouce1(){
			$this->display('hr/hr_kh_shouce1');
		}

		public function hr_xm_mba(){
			$this->display('hr/hr_xm_mba');
		}

		public function hr_xm_ict(){
			$this->display('hr/hr_xm_ict');
		}

		public function hr_xm_assess(){
			$this->display('hr/hr_xm_assess');
		}

		public function hr_xm_photo(){
			$this->display('hr/hr_xm_photo');
		}

		public function hr_xm_shouce(){
			$this->display('hr/hr_xm_shouce');
		}

		public function hr_xm_shouce1(){
			$this->display('hr/hr_xm_shouce1');
		}

		public function hr_xs_policy1(){
			$this->display('hr/hr_xs_policy1');
		}

		public function hr_xs_pos(){
			$this->display('hr/hr_xs_pos');
		}

		public function hr_xs_policy(){
			$this->display('hr/hr_xs_policy');
		}

		public function hr_xs_shouce(){
			$this->display('hr/hr_xs_shouce');
		}

		public function hr_xs_shouce1(){
			$this->display('hr/hr_xs_shouce1');
		}

		public function hr_yy_pie(){
			$this->display();
		}

		public function hr_yy_policy1(){
			$this->display();
		}

		public function hr_yy_policy2(){
			$this->display();
		}

		public function hr_yy_policy3(){
			$this->display();
		}

		public function hr_yy_policy(){
			$this->display();
		}

		public function hr_yy_pos(){
			$this->display();
		}

		public function hr_yy_shouce(){
			$this->display();
		}

		public function hr_yy_shouce1(){
			$this->display();
		}

		public function hr_yy_shouce2(){
			$this->display();
		}

		public function hr_yy_shouce3(){
			$this->display();
		}

		public function hr_yy_shouce4(){
			$this->display();
		}

		public function hr_qyw_shouce1(){
			$this->display();
		}

		public function hr_qyw_shouce(){
			$this->display();
		}

		public function hr_qyw_pos(){
			$this->display();
		}

		public function hr_qyw_photo($id=0){
			$this->assign('id',$id);
			$this->display();
		}

		public function hr_qyw_mba(){
			$this->display();
		}

		public function hr_qyw_post(){
			$this->display();
		}

		public function hr_zh_pos(){
			$this->display();
		}

		public function hr_zh_mba(){
			$this->display();
		}

		public function hr_zh_post(){
			$this->display();
		}

		public function hr_zh_shouce(){
			$this->display();
		}

		public function hr_zh_shouce1(){
			$this->display();
		}

		public function hr_gc_mba(){
			$this->display();
		}

		public function hr_gc_step(){
			$this->display();
		}
		
		public function hr_gc_shouce(){
			$this->display();
		}
		
		public function hr_gc_shouce1(){
			$this->display();
		}
		
		public function hr_gc_shouce2(){
			$this->display();
		}
		
		public function hr_gc_shouce3(){
			$this->display();
		}
		
		public function hr_gc_shouce4(){
			$this->display();
		}


		public function hr_course($cid='',$bill_id=''){
			$hrCourse=M('hrCourse');
			//$sql="select * from rank_hr_course where 岗位='客户经理' and 课程编号=".$cid." ";
			$kc=$hrCourse->where("课程编号='".$cid."'")->find();
			$this->assign('kc',$kc);

			if(substr($cid,0,1)=='K'){
				$sql="SELECT * FROM rank_hr_info WHERE bill_id='".$bill_id."'";
			}
			if(substr($cid,0,1)=='S'){
				$sql="SELECT * FROM rank_hr_info_xs WHERE bill_id='".$bill_id."'";
			}
			if(substr($cid,0,1)=='M'){
				$sql="SELECT * FROM rank_hr_info_xm WHERE bill_id='".$bill_id."'";
			}
			$name=$hrCourse->query($sql);
			$this->assign('name',$name);

			$hrEntr=M('hrEnter');
			$bm =$hrEntr->where("cours_id='".$cid."' and user_name='".$name[0]['POS_NAME'].$name[0]['USER_NAME']."'")->find();
			if(!empty($bm)){
			  	$this->assign('errMsg',$name[0]['POS_NAME'].$name[0]['USER_NAME'].'  你本课程已经报过名，不能重复报名');
			  }
			$this->display();
		}

		public function hr_enter($cours_id=''){
			$hrEntr=M('hrEnter');

			$json['success']=false;
			$json['msg']='保存错误';
			$user_name=I('user_name');
			$bill_id=I('bill_id');
			$email=I('email');
			$remark=I('remark');
			$cours=I('cours');
			$data['cours_id']=$cours_id;
			$data['cours']=$cours;
			$data['user_name']=$user_name;
			$data['bill_id']=$bill_id;
			$data['email']=$email;
			$data['remark']=$remark;
			$re=$hrEntr->orcAdd('rank_hr_enter',$data);
			if($re){
				$json['success']=true;
				$json['msg']='保存成功';
				$this->assign('errMsg','报名成功');
			}
			$sql="select count(*) sum from rank_hr_enter where cours_id='".$cours_id."'";
			$count=$hrEntr->query($sql);
			$this->assign('count',$count);
			//$this->ajaxReturn($json);
			$this->display('hr_course');
		}

		public function hr_que(){
			$hrEntr=M('hrEntr');
			$sql="SELECT a.*,b.sum 报名人数 FROM (SELECT 课程编号,课程 FROM rank_hr_course WHERE 备注<>'已培' and to_char(expire_date)>sysdate GROUP BY 课程编号,课程 HAVING 课程编号 IS NOT NULL) a,(SELECT cours_id,COUNT(*) SUM FROM rank_hr_enter GROUP BY cours_id) b WHERE a.课程编号=b.cours_id(+)";
			$que=$hrEntr->query($sql);
			$this->assign('que',$que);
			$this->display();
		}

		

		//人力自助查询
		public function hr_user_info($bill_id=''){
			$this->assign('user_auth',$_SESSION['user_auth']);
			if(empty($bill_id)){
				$bill_id = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
			}
			// $bill_id='18205788896';
			$this->assign('bill_id',$bill_id);
			$sql ="select * from mz_user.ls_yd_user where bill_id='".$bill_id."'";
			$m = M();
			$infos = $m->query($sql);
			$this->assign('info',$infos[0]);
			$this->display();
		}

		public function hr_user_info_test(){
			$bill_id='13905788030';
			$this->assign('bill_id',$bill_id);
			$sql ="select * from mz_user.ls_yd_user where bill_id='".$bill_id."'";
			$m = M();
			$infos = $m->query($sql);
			$this->assign('info',$infos[0]);
			$this->display('hr_user_info');
		}

		public function rongyubang(){
			$this->display('Hr/rongyubang');
		}


		//宽极化
		public function hr_kjh_index($clck=''){
			$sql = "select 部门,岗位,姓名,bill_id from   mz_crm.ls_kjh_user_2017 a order by 岗位,部门";
			$m = M();
			$mans = $m->query($sql);
			$this->assign('mans',$mans);
			if($clck!=null || $clck!=''){
				$ls_kh=M();
				$sql_clck="insert into rank_khjl_clck values('".$_SESSION['user_auth']['OPER_LOGIN_CODE']."','".$_SESSION['user_auth']['OA']."','".$clck."','".$_SERVER['REMOTE_ADDR']."',sysdate)";
				$ls_kh->execute($sql_clck);
			}
			$this->display();
		}

		public function hr_kjh_data($mobile){
			$sql = "select * from (select A.*,count(*)over(partition by 岗位) region_cnt,dense_rank() over(partition by 岗位 ORDER BY 综合总得分 DESC NULLS LAST) region_pai,count(*)over(partition by 岗位, 现星级, 现个人职等2016) lvl_cnt,dense_rank()over(PARTITION BY 岗位,现星级,现个人职等2016 ORDER BY 综合总得分 DESC NULLS LAST) lvl_pai  from mz_crm.ls_kjh_user_2017 a) WHERE bill_id='".$mobile."'";
			$m = M();
			$infos = $m->query($sql);
			$this->ajaxReturn($infos[0]);
		}


		




}
?>