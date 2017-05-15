<?php  
	namespace Home\Controller;

	class GroupController extends BaseController {
		

		protected function _initialize(){
			 parent::intiParams();
			 parent::viewLog();

			 $oa = I('oa');
			 $bill_id = I('bill_id');
			 parent::loginByOa($bill_id,$oa);

			 //获取详细的用户信息
			 $where=" and oper_login_code='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
			 parent::loginUser($where);
		}


		//首页
		public function index(){
			$bill_id = I('bill_id');
			if(empty($bill_id)) $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$m = M();

			//宽级化
			$sql = "SELECT DISTINCT 更新时间, 县市, 客户经理, 客户经理电话, 总分值,排名 FROM LS_PANCM.LS_KJH_客户经理汇总_01 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.LS_KJH_客户经理汇总_01)  and 客户经理电话='".$bill_id."'";
			$kjh = $m->query($sql);
			if($kjh) $this->assign('kjh',$kjh[0]);

			//计件
			$sql = "select * from ls_pancm.mz_客户经理计件汇总 where 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.mz_客户经理计件汇总)  and 手机号码='".$bill_id."'";
			$jj = $m->query($sql);
			if($jj) $this->assign('jj',$jj[0]);

			//个人信息
			$hrInfo=M('hrInfo');
			$hr = $hrInfo->where('bill_id='.$bill_id)->cache(true)->find();
			$this->assign('hr',$hr);

			//宽级化排名
			$khjl_sql="SELECT * FROM (SELECT DISTINCT 更新时间, 县市, 客户经理, 客户经理电话, 总分值,排名 FROM LS_PANCM.LS_KJH_客户经理汇总_01 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.LS_KJH_客户经理汇总_01) ORDER BY 总分值 DESC) WHERE Rownum<=12";
			$khjl = $m->query($khjl_sql);
			$this->assign('khjl',$khjl);

			//计件排名
			$jj_sql = "SELECT * FROM (SELECT DISTINCT 更新时间, 县市, 姓名, 手机号码, 总金额,全市排名 FROM LS_PANCM.mz_客户经理计件汇总 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.mz_客户经理计件汇总) ORDER BY 总金额 DESC) WHERE Rownum<=12";
			$jj_pai = $m->query($jj_sql);
			$this->assign('jj_pai',$jj_pai);

			$clck_sql="SELECT c.dom_name,a.mid,a.总点击,nvl(b.本月点击,0) 本月点击量 FROM
			(SELECT mid,count(*) 总点击 FROM rank_khjl_clck group by mid) a,
			(SELECT mid,count(*) 本月点击 FROM rank_khjl_clck group by mid,to_char(create_date,'yyyymm') having to_char(create_date,'yyyymm')=to_char(sysdate,'yyyymm')) b,
			(SELECT * FROM rank_khjl_clckid) c
			where a.mid=b.mid(+) and a.mid=c.id(+)";
			$clcks=$m->query($clck_sql);
			$this->assign('clcks',$clcks);

			$this->display();
		}

		//宽级化和计件
		public function kjh_word($rpt_month='',$type='',$title='',$clck=''){
			$group=M('configGroup');
			if(!empty($rpt_month) || !empty($title)){
				if(!empty($title)){
					$sql="SELECT * FROM rank_config_group where title='".$title."'";
				}else{
					$sql="SELECT * FROM rank_config_group where rpt_month='".$rpt_month."' and type='".$type."'";
				}
				$msg=$group->query($sql);
				$this->assign('msg',$msg[0]);
			}else{
				$kjh="SELECT * FROM rank_config_group where type='宽极化' order by rpt_month";
				$jj="SELECT * FROM rank_config_group where type='计件' order by rpt_month";
				$msg=$group->query($kjh);
				$msgs=$group->query($jj);
				$this->assign('msg',$msg);
				$this->assign('msgs',$msgs);
			}
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function add($title1=''){
			$msg=M('configGroup');
			import('Org.Util.OciUtil');
	        $oci=new \Org\Util\OciUtil();
	        $oci->table='rank_config_group';
		 	$data['rpt_month']=I('rpt_month');
		 	$data['title']=I('title');
		 	$data['type']=I('type');
		 	$data['msg']=$_POST['msg'];
		 	$data['oper_name']=I('oper_name');
		 	$data['create_date']=date('Y-m-d');
			$data['status']='1';
			$oci->data = $data;	
		 	if($data['rpt_month']==null || $data['rpt_month'] ==''){
		 		$this->error ( '日期不能为空!' );
		 	}else if($data['title']==null || $data['title'] ==''){
		 		$this->error ( '标题不能为空!' );
		 	}else if($data['oper_name']==null || $data['oper_name'] ==''){
		 		$this->error ( '管理人不能为空!' );
		 	}else{
		 		if($title1!=null && $title1!=""){
		 			$oci->where="title='".$title1."'";
		 			//$insert=$msg->where("title='".$title1."'")->save($data);
				 	if($oci->update()){
				 		$this->success('修改成功');
				 	}else{
				 		$this->error ('修改失败');
				 	}
		 		}else{
		 			$insert=$msg->add($data);
				 	if($oci->insert()){
				 		$this->success('保存成功');
				 	}else{
				 		$this->error ('保存失败');
				 	}
		 		}
		 	}
		}

		public function group_delete($rpt_month='',$type=''){
			$group=M('configGroup');
			$res = $group->where("rpt_month='".$rpt_month."' and type='".$type."'")->delete();
			$this->redirect('kjh_word');
		}

		public function kjh_my($clck=''){
			$bill_id = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$ls_kh=M('LS_PANCM.LS_KJH_客户经理汇总_01');
			$sql_s="select * from rank_khjl_宽级化 where bill_id='".$bill_id."'";
			$v=$ls_kh->query($sql_s);
			$this->assign('v',$v);
			if($v[0]==null){
				$bill_id='13905789868';
			}
			$name=I('name');
			$sql_n="select * from rank_khjl_宽级化 where user_name='".$name."'";
			$n=$ls_kh->query($sql_n);
			$this->assign('n',$n);
			if(!empty($name)){
				$bill_id=$n[0]['BILL_ID'];
			}
			$wid_sql="SELECT * FROM (SELECT Column_name FROM all_tab_columns WHERE Table_Name='LS_KJH_客户经理汇总_01') WHERE column_name<>'排名' AND column_name<>'县市' AND column_name<>'客户经理' AND column_name<>'客户经理电话' AND column_name<>'总分值' AND column_name<>'更新时间' and column_name<>'两网得分'";
			$khjl=$ls_kh->query($wid_sql);
			$this->assign('khjl',$khjl);
			$sql_lisi="SELECT * FROM rank_khjl_宽级化 WHERE bill_id='".$bill_id."' ORDER BY rpt_month";
			$lines=parent::listsSqlByls($sql_lisi,12);
			$this->assign('lines',$lines);
			$this->assign('bill_id',$bill_id);
			if(IS_POST){
				$bill_id = I('bill_id');
				$m = M();
				$sql = "select a.*,'/upload/headImg/'||a.客户经理电话||'.jpg' pic from ls_pancm.ls_kjh_客户经理汇总_01 a,mz_user.t_sys_oper b WHERE  a.客户经理电话=b.oper_login_code(+) and a.客户经理电话='".$bill_id."' and 更新时间=(SELECT MAX(更新时间) FROM ls_pancm.ls_kjh_客户经理汇总_01 a)";
				$kjh = $m->query($sql);
				$this->ajaxReturn($kjh[0]);
			}else{
				if($clck!=null || $clck!=''){
					$this->clck($clck);
				}
				$this->display();
			}
		}

		public function xsjl_my(){
			$bill_id = I('bill_id');
			$m = M();
			$sql = "select a.*,'/upload/headImg/'||a.bill_id||'.jpg' pic from LS_KJH_CHNL_MANAGER a WHERE a.bill_id='".$bill_id."' and rpt_date=(SELECT MAX(rpt_date) FROM LS_KJH_CHNL_MANAGER a)";
			$kjhs = $m->query($sql);
			$this->ajaxReturn($kjhs[0]);
		
		}

		public function kjh_jj($clck=''){
			$bill_id = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$ls_kh=M('LS_PANCM.mz_客户经理计件汇总');
			$sql_s="select * from rank_khjl_计件 where bill_id='".$bill_id."'";
			$v=$ls_kh->query($sql_s);
			$this->assign('v',$v);
			if($v[0]==null){
				$bill_id='13905789868';
			}
			$name=I('name');
			$sql_n="select * from rank_khjl_计件 where user_name='".$name."'";
			$n=$ls_kh->query($sql_n);
			$this->assign('n',$n);
			if(!empty($name)){
				$bill_id=$n[0]['BILL_ID'];
			}
			$sql_lisi="SELECT * FROM rank_khjl_计件 WHERE  bill_id='".$bill_id."' ORDER BY rpt_month";
			//$lines=$ls_kh->query($sql_lisi);
			$lines=parent::listsSqlByls($sql_lisi,12);
			$this->assign('lines',$lines);
			$this->assign('bill_id',$bill_id);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function kjh_rank($clck=''){
			$m=M();
			$user_name=I('user_name');
			$bill_ids=I('bill_ids');
			// $khjl_sql="SELECT * FROM (SELECT DISTINCT 更新时间, 县市, 客户经理, 客户经理电话, 总分值,排名 FROM LS_PANCM.LS_KJH_客户经理汇总_01 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.LS_KJH_客户经理汇总_01) ORDER BY 总分值 DESC)";
			$khjl_sql="SELECT a.*,b.m1月宽极化,b.m2月宽极化,b.m3月宽极化,b.m4月宽极化,b.m5月宽极化,b.m6月宽极化,b.m7月宽极化,b.m8月宽极化,b.m9月宽极化,b.m10月宽极化,b.m11月宽极化,b.m12月宽极化,c.平均分,c.pm FROM (SELECT distinct county_name,user_name,user_id,bill_id,pos_name,性质 FROM rank_khjl_宽级化 where  rpt_month>=201701) a,(SELECT user_id,max(p1) m1月宽极化,max(p2) m2月宽极化,max(p3) m3月宽极化,max(p4) m4月宽极化,max(p5) m5月宽极化,max(p6) m6月宽极化,max(p7) m7月宽极化,max(p8) m8月宽极化,max(p9) m9月宽极化,max(p10) m10月宽极化,max(p11) m11月宽极化,max(p12) m12月宽极化 FROM (SELECT user_id,
			case when rpt_month=201701 then amount else null end p1,
			case when rpt_month=201702 then amount else null end p2,
			case when rpt_month=201703 then amount else null end p3,
			case when rpt_month=201704 then amount else null end p4,
			case when rpt_month=201705 then amount else null end p5,
			case when rpt_month=201706 then amount else null end p6,
			case when rpt_month=201707 then amount else null end p7,
			case when rpt_month=201708 then amount else null end p8,
			case when rpt_month=201709 then amount else null end p9,
			case when rpt_month=201710 then amount else null end p10,
			case when rpt_month=201711 then amount else null end p11,
			case when rpt_month=201712 then amount else null end p12 
			FROM rank_khjl_宽级化 where rpt_month>=201701) group by user_id) b,(SELECT user_id,round(avg(amount),2) 平均分,rank()over(order by round(avg(amount),2) desc nulls last) pm FROM rank_khjl_宽级化 where rpt_month>=201701 group by user_id) c where a.user_id=b.user_id(+) and a.user_id=c.user_id(+)";
			if(!empty($user_name)){
				$khjl_sql .=" and user_name='".$user_name."'";
			}
			if(!empty($bill_ids)){
				$khjl_sql .=" and bill_id='".$bill_ids."'";
			}
			$khjl_sql .=" order by c.pm";
			//$khjl = $m->query($khjl_sql);
			$khjl =parent::listsSqlByls($khjl_sql,20);
			$this->assign('khjl',$khjl);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function kjh_jj_rank($clck=''){
			$m=M();
			$user_name=I('user_name');
			$bill_ids=I('bill_ids');
			// $jj_sql = "SELECT * FROM (SELECT DISTINCT 更新时间, 县市, 姓名, 手机号码, 总金额,全市排名 FROM LS_PANCM.mz_客户经理计件汇总 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.mz_客户经理计件汇总) ORDER BY 总金额 DESC)";
			$jj_sql="SELECT a.*,b.m1月计件,b.m2月计件,b.m3月计件,b.m4月计件,b.m5月计件,b.m6月计件,b.m7月计件,b.m8月计件,b.m9月计件,b.m10月计件,b.m11月计件,b.m12月计件,c.平均分,c.pm FROM (SELECT distinct county_name,user_name,user_id,bill_id,pos_name,性质 FROM rank_khjl_计件 where  rpt_month>=201701) a,(SELECT user_id,max(p1) m1月计件,max(p2) m2月计件,max(p3) m3月计件,max(p4) m4月计件,max(p5) m5月计件,max(p6) m6月计件,max(p7) m7月计件,max(p8) m8月计件,max(p9) m9月计件,max(p10) m10月计件,max(p11) m11月计件,max(p12) m12月计件 FROM (SELECT user_id,
			case when rpt_month=201701 then amount else null end p1,
			case when rpt_month=201702 then amount else null end p2,
			case when rpt_month=201703 then amount else null end p3,
			case when rpt_month=201704 then amount else null end p4,
			case when rpt_month=201705 then amount else null end p5,
			case when rpt_month=201706 then amount else null end p6,
			case when rpt_month=201707 then amount else null end p7,
			case when rpt_month=201708 then amount else null end p8,
			case when rpt_month=201709 then amount else null end p9,
			case when rpt_month=201710 then amount else null end p10,
			case when rpt_month=201711 then amount else null end p11,
			case when rpt_month=201712 then amount else null end p12 
			FROM rank_khjl_计件 where rpt_month>=201701) group by user_id) b,(SELECT user_id,round(avg(amount),2) 平均分,rank()over(order by round(avg(amount),2) desc nulls last) pm FROM rank_khjl_计件 where rpt_month>=201701 group by user_id) c where a.user_id=b.user_id(+) and a.user_id=c.user_id(+)";
			if(!empty($user_name)){
				$jj_sql .=" and user_name='".$user_name."'";
			}
			if(!empty($bill_ids)){
				$jj_sql .=" and bill_id='".$bill_ids."'";
			}
			$jj_sql .=" order by c.pm";
			//$jj_pai = $m->query($jj_sql);
			$jj_pai =parent::listsSqlByls($jj_sql,20);
			$this->assign('jj_pai',$jj_pai);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}


		//个人能力评测和提升
		public function nl($clck=''){
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function nl_info($clck=''){
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}
 
		public function nl_rpt($bill_id='',$clck=''){
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			parent::intiParams();
			parent::viewLog();
			$this->display();
		}

		//我的集团
		public function my(){
			$this->display();
		}

		public function my_group($clck=''){
			$group_name = I('group_name');
			$group_id   = I('group_id');
			$bill_id    = I('bill_id');
			$manger_bill= I('manger_bill');

			$county_code= I('county_code');

			$sql = "select b.bill_id,b.staff_name,get_county_name(a.chnl_region_type) county_name,a.* from ls_group_info a,ls_group_manager b where a.mgr_id = b.staff_id and region_name='大客户中心'";

			$hrRole = M('hrRole');
			$role   = $hrRole->where("bill_id='".$bill_id."'")->find();
		    // if(!empty($role)){
		  	 //  $sql .= " and b.bill_id='".$bill_id."'";
		    // }

		    if(!empty($group_name)){
		    	$sql.=" and a.cust_name like '%".$group_name."%'";
		    }
		    if(!empty($group_id)){
		    	$sql.=" and a.group_id =".$group_id;
		    }
		    if(!empty($county_code)){
		    	$sql.=" and a.chnl_region_type like '%".$county_code."%'";
		    }
		    if(!empty($manger_bill)){
		    	$sql .= " and b.bill_id='".$manger_bill."'";
		    }

			$sql.=" order by a.create_date desc ,chnl_region_type";

			$m = M();
			$groups = parent::listsSqlByls($sql,18);
			$this->assign('groups',$groups);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function my_user(){
			$this->display();
		}

		//实用工具
		public function tool(){
			$this->display();
		}

		public function tool_zchb($clck=''){
			$files=M('file');
			$text=I('text');
			$file=$files->where("分类='zc' and file_name like '%".$text."%'")->select();
			$this->assign('file',$file);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function tool_group_query(){
			$files=M('file');
			$file=$files->where("分类='jt'")->select();
			$this->assign('file',$file);
			$this->display();
		}

		public function tool_price_query($clck=''){
			$files=M('file');
			$text=I('text');
			$file=$files->where("分类='jg' and file_name like '%".$text."%'")->select();
			$this->assign('file',$file);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function tool_word($clck=''){
			$files=M('file');
			$text=I('text');
			//$file=$files->where("分类='gl' and file_name like '%".$text."%'")->select();
			$sql="select * from rank_file where 分类='gl' and file_name like '%".$text."%'";
			$file=parent::listsSqlByls($sql,20);
			$this->assign('file',$file);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function tool_ages($clck=''){
			$files=M('file');
			$text=I('text');
			$file=$files->where("分类='al' and file_name like '%".$text."%'")->select();
			$this->assign('file',$file);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function tool_table($clck=''){
			$files=M('file');
			$text=I('text');
			$file=$files->where("分类='bg' and file_name like '%".$text."%'")->select();
			$this->assign('file',$file);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function tool_staff($clck=''){
			$work=M('岗位工作职责');
			$text=I('text');
			$sql="SELECT a.*,ROWID FROM rank_岗位工作职责 a WHERE 工作职责 LIKE '%".$text."%'";
			$num=$work->query($sql);
			$this->assign('num',$num);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function tool_shouc($clck=''){
			$files=M('file');
			$text=I('text');
			$file=$files->where("分类='sc' and file_name like '%".$text."%'")->select();
			$this->assign('file',$file);
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		//客户经理管理
		public function gl(){
			$this->display();
		}

		public function gl_group($clck=''){
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function gl_kjh($clck=''){
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function gl_jj($clck=''){
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function gl_nl($clck=''){
			if($clck!=null || $clck!=''){
				$this->clck($clck);
			}
			$this->display();
		}

		public function delete_tool(){
			//$this->error('未开始');
			$fname=I('fname');
			$ftype=I('ftype');
			$file=M('file');
			$dor=$file->where("file_name='".$fname."' and file_type='".$ftype."'")->delete();
			if($dor){
				$this->success("删除成功");
			}else{
				$this->error("删除失败");
			}
		}



	//导入上传
	public function read($filename,$file_type,$encode='utf-8'){
		Vendor('Classes.PHPExcel'); 
		if (strtolower ( $file_type ) == "xls"){
		    $objReader = \PHPExcel_IOFactory::createReader('Excel5'); 
		}
		else if(strtolower ( $file_type ) == "xlsx"){
			$objReader = \PHPExcel_IOFactory::createReader('Excel2007'); 
		}
        
        $objReader->setReadDataOnly(true); 
        $objPHPExcel = $objReader->load($filename); 
        $objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn); 
		$excelData = array(); 
		for ($row = 1; $row <= $highestRow; $row++) { 
			for ($col = 0; $col < $highestColumnIndex; $col++) { 
                 $excelData[$row][] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
           } 
        } 
        return $excelData; 
	} 

	public function fille(){
		$tmp_name=$_FILES['file_exl']['name'];
		if(!empty($tmp_name)){
			$tmp_file=$_FILES['file_exl']['tmp_name'];
			$file_types=explode('.', $tmp_name);
			$file_type=$file_types[1];

			 /*判别是不是.xls文件，判别是不是excel文件*/
		    if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
		        $this->error ( '不是Excel文件，重新上传' );
		    }

		    /*设置上传路径*/
	    	$savePath = '//10.78.1.85/www/ranking/Public/upfile/Execl/';

		    /*以时间来命名上传的文件*/
		    $str =date ( 'H' ); //date ( 'Ymdhis' );
		    $file_name = $str . "." . $file_type;

		    /*是否上传成功*/
		    if (! copy ( $tmp_file, $savePath . $file_name )){
		        $this->error ( '上传失败' );
		    }

		    $ress = $this->read($savePath.$file_name,$file_type);
		    $res=array();

		    for($i=1;$i<=count($ress);$i++){
		    	for($j=0;$j<count($ress[1]);$j++){
		    		$temp=explode('.', $ress[$i][$j]);
		    		if(is_numeric($ress[$i][$j]) && $temp[1]>99){
		    			 $num=sprintf('%.2f',$ress[$i][$j]);
		    		}else{
		    			$num=$ress[$i][$j];
		    		}
		    			 $res[$i][$j]=$num;
		    	}
		    }
		}
		return $res;
	}

	public function file($sort=''){
		$khjl=M('userCon');
		if($sort=='宽级化'){
			$sql="SELECT max(rpt_month) month FROM rank_khjl_宽级化";
			$khjls=M('khjl_宽级化');
		}else if($sort=='计件'){
			$sql="SELECT max(rpt_month) month FROM rank_khjl_计件";
			$khjls=M('khjl_计件');
		}else{
			$this->error('请与管理员联系！');
		}
		$con=$khjl->query($sql);
		$this->assign('con',$con);

		$res=$this->fille();

	    for($i=2;$i<=count($res);$i++){
	    	if($res[$i][0]<$con[0]['MONTH']+1){
	    		$this->error('请检查月份是否正确');
	    	}
	    	$corr=1;
	    }
	    if($corr==1){
		    foreach($res as $k => $v){
		    	if($k>1){
		    		// $data ['rpt_month'] = $v[0];
		    		// $data ['county_name'] = $v[1];
		    		// $data ['user_name'] = $v[2];
		    		// $data ['user_id'] = $v[3];
		    		// $data ['bill_id'] = $v[4];
		    		// $data ['pos_name'] = $v[5];
		    		// $data ['性质'] = $v[6];
		    		// $data ['amount'] = $v[7];
		    		// $data ['pm'] = $v[8];
		    		// $result = $khjls->add ( $data );
		    		if($sort=='宽级化'){
			    		$sql="insert into rank_khjl_宽级化 values('".$v[0]."','".$v[1]."','".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."','".$v[7]."','".$v[8]."')";
			    	}else if($sort=='计件'){
			    		$sql="insert into rank_khjl_计件 values('".$v[0]."','".$v[1]."','".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."','".$v[7]."','".$v[8]."')";
			    	}
		    		$result=$khjls->execute($sql);
			        if (! $result){
			            $this->error ( '导入数据库失败' );
			        }
		    	}
		    }
		    $this->success('导入成功');
		}
	}

	public function file_staff(){
		$res=$this->fille();
		foreach($res as $k => $v){
	    	if($k>1){
	    		$data ['编号'] = $v[0];
	    		$data ['部门'] = $v[1];
	    		$data ['二级部门'] = $v[2];
	    		$data ['岗位'] = $v[3];
	    		$data ['姓名'] = $v[4];
	    		$data ['工作职责'] = $v[5];
	    		$data ['bill_id'] = $v[6];
	    		$result = M('岗位工作职责')->add ( $data );
		        if (! $result){
		            $this->error ( '导入数据库失败' );
		        }
	    	}
	    }
	    $this->success('导入成功');
	}


	public function upload_file($divide=''){
		$tmp_name=$_FILES['file_exl']['name'];	
		if(!empty($tmp_name)){
			$tmp_file=$_FILES['file_exl']['tmp_name'];
			$file_types=explode('.', $tmp_name);
			$file_type=$file_types[count ( $file_types ) - 1];
			if(count($file_types)>2){
				$str=substr($tmp_name,0,-strlen($file_type)-1);
				if(strpos($str," ")){
					$this->error("文件名称中存在空格，请删除空格后重新上传！");
				}
			}else{
				$str=$file_types[count ( $file_types ) - 2];
				if(strpos($str," ")){
					$this->error("文件名称中存在空格，请删除空格后重新上传！");
				}
			}
		    $strs =iconv("utf-8", "GB2312", $str);
		    if($strs==null){
		    	$file_name = $str . "." . $file_type;
		    }else{
		    	$file_name = $strs . "." . $file_type;
		    }
		    /*设置上传路径*/
	    	$savePath = '//10.78.1.85/www/ranking/Public/images/tool/';

		    /*是否上传成功*/
		    if (! copy ( $tmp_file, $savePath . $file_name )){
		        $this->error ( '上传失败' );
		    }

		    $sql="SELECT * FROM rank_file where file_name='".$str."' and file_type='".$file_type."'";
		    $distinct=M('file')->query($sql);
		    $this->assign('distinct',$distinct);
		    if($distinct[0]['FILE_NAME']!=null){
	        	$this->error('已存在相同的文件，请删除后再上传！');
		    }
		    $data['file_name'] = $str;
		    $data['file_type'] = $file_type;
		    $data['分类'] = $divide;
		    $data['create_date'] = date('Y-m-d');
		    $data['status'] = 1;
		    $result = M('file')->add($data);
		    if(!$result){
		    	$this->error('失败');
		    }
		    $this->success('上传成功');
		}else{
			echo "上传文件不能为空！";
			exit();
		}
	}

	//成绩分项表
	public function scorep($clck=''){
		if($clck!=null || $clck!=''){
			$this->clck($clck);
		}
		$this->display();
	}

	//科目成绩汇总表
	public function scoreall($clck=''){
		if($clck!=null || $clck!=''){
			$this->clck($clck);
		}
		$this->display();
	}

	protected function clck($clck){
		$ls_kh=M();
		$sql_clck="insert into rank_khjl_clck values('".$_SESSION['user_auth']['OPER_LOGIN_CODE']."','".$_SESSION['user_auth']['OA']."','".$clck."','".$_SERVER['REMOTE_ADDR']."',sysdate)";
		$ls_kh->execute($sql_clck);
	}

	//PHPExcel
	public function exportp(){
        $sql = "select * from ls_mz.ls_chenhf_20161026_82";
        $elist = M()->query($sql);
        $filename="个人年度成绩.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');//attachment和inline的方式就是保存时的弹窗不一样
        ob_end_clean();//清除缓冲区,避免乱码（不清除缓冲区,下载的数据就会乱码）

        //创建一个excel对象
        vendor("PHPExcel.PHPExcel");//导入PHPExcel类库
        $objPHPExcel = new \PHPExcel();
        // Set properties  设置文件属性（右键文件属性看到的内容）
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        // set width
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(28);
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:T1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:T1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:T1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '部门')
            ->setCellValue('B1', '工号')
            ->setCellValue('C1', '姓名')
            ->setCellValue('D1', '科目')
            ->setCellValue('E1', '年份')
            ->setCellValue('F1', '一月')
            ->setCellValue('G1', '二月')
            ->setCellValue('H1', '三月')
            ->setCellValue('I1', '四月')
            ->setCellValue('J1', '五月')
            ->setCellValue('K1', '六月')
            ->setCellValue('L1', '七月')
            ->setCellValue('M1', '八月')
            ->setCellValue('N1', '九月')
            ->setCellValue('O1', '十月')
            ->setCellValue('P1', '十一月')
            ->setCellValue('Q1', '十二月')
            ->setCellValue('R1', '总分')
            ->setCellValue('S1', '个人科目年度平均分')
            ->setCellValue('T1', '个人科目年度平均分排名');
        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['DEPT']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['USER_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['USER_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['EXAM_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['YEAR1']);  
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['一月']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['二月']);  
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['三月']);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['四月']);  
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['五月']);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['六月']);  
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['七月']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['八月']);  
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['九月']);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['十月']);  
                $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+2), $elist[$k]['十一月']);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.($k+2), $elist[$k]['十二月']); 
                $objPHPExcel->getActiveSheet()->setCellValue('R'.($k+2), $elist[$k]['总分']); 
                $objPHPExcel->getActiveSheet()->setCellValue('S'.($k+2), $elist[$k]['年度平均分']);
                $objPHPExcel->getActiveSheet()->setCellValue('T'.($k+2), $elist[$k]['PM']);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('个人年度成绩');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function exportall(){
        $sql = "select * from ls_mz.ls_chenhf_20161026_07";
        $elist = M()->query($sql);
        $filename="科目成绩汇总表.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');//attachment和inline的方式就是保存时的弹窗不一样
        ob_end_clean();//清除缓冲区,避免乱码（不清除缓冲区,下载的数据就会乱码）

        //创建一个excel对象
        vendor("PHPExcel.PHPExcel");//导入PHPExcel类库
        $objPHPExcel = new \PHPExcel();
        // Set properties  设置文件属性（右键文件属性看到的内容）
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        // set width
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(18);
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(35);
        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->getStyle('E1:P1')->getAlignment()->setWrapText(true);//单元格自动换行
        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '部门')
            ->setCellValue('B1', '工号')
            ->setCellValue('C1', '姓名')
            ->setCellValue('D1', '年份')
            ->setCellValue('E1', '业务知识')
            ->setCellValue('F1', '业务知识测试次数')
            ->setCellValue('G1', '客户管理')
            ->setCellValue('H1', '客户管理测试次数')
            ->setCellValue('I1', '通讯技术')
            ->setCellValue('J1', '通讯技术测试次数')
            ->setCellValue('K1', '市场敏感')
            ->setCellValue('L1', '市场敏感测试次数')
            ->setCellValue('M1', '市场营销')
            ->setCellValue('N1', '市场营销测试次数')
            ->setCellValue('O1', '自我管理与沟通合作')
            ->setCellValue('P1', '自我管理与沟通合作测试次数');
        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['DEPT']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['USER_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['USER_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['YEAR1']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['业务知识']);  
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['业务知识测试次数']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['客户管理']);  
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['客户管理测试次数']);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['通讯技术']);  
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['通讯技术测试次数']);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['市场敏感']);  
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['市场敏感测试次数']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['市场营销']);  
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['市场营销测试次数']);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['自我管理与沟通合作']);  
                $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+2), $elist[$k]['自我管理与沟通合作测试次数']);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('科目成绩汇总表');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}






?>