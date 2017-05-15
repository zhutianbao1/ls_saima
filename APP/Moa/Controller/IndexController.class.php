<?php

namespace Moa\Controller;

class IndexController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
		$userName=I('userName');
		if($userName){
			session('userName',$userName);
		}else{
			$userName=session('userName');
		}
		 $this->assign('userName',$userName);
		 parent::intiParams();
 	}

	 
	public function index(){
		$userName=I('userName');
		$sign=I('sign');
		$time=date('Ymd',time());
		$oa=$_SESSION['user_auth']['OA'];
		if($oa==''){
			if($userName==null || strtolower($sign) != md5($userName.$time.'LSAPP')){
				dump(strtoupper(md5($userName.$time.'LSAPP')));
				dump($userName.$time.'LSAPP');
				dump(md5($userName.$time.'LSAPP'));
				dump(md5($userName.$time));
				exit();
				$this->display('rank_erray');
			}else{
			 	parent::login_moa($userName);
				$this->display();
			}
		}else{
			$this->display();
		}
	}

	public function login($mobile='',$pwd=''){
		$json['success']=false;
		$json['msg']='账号或密码错误';
		$flag = parent::login($mobile,$pwd);
		if($flag){
			$json['success']=true;
			$json['msg']='登陆成功';
			$json['obj']=$flag;
		}
		$this->ajaxReturn($json);
	}

	public function list_demo(){
		$userInfo=M('userInfo');
		$yearInfo=M('yearInfo');
		$pos_sql='SELECT * FROM rank_config WHERE MONTH=201609 and status=1 order by id';
		$month_sql="SELECT MONTH FROM rank_config GROUP BY MONTH ORDER BY MONTH";
		$jf_sql="select * from rank_jf_px ORDER BY config_id";
		
		$pos_name=$userInfo->query($pos_sql);
		$this->assign('pos_name',$pos_name);
		$rpt_month=$userInfo->query($month_sql);
		$this->assign('rpt_month',$rpt_month);
		$jf=$userInfo->query($jf_sql);
		$this->assign('jf',$jf);
		$this->display('index/list');
	}

	public function rank_load($month='',$id='',$num=''){
		$userInfo=M('userInfo');
		if(IS_GET){
			$sql="select count(*) sum from (SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm FROM rank_user_info a WHERE rpt_month=".$month." AND config_id=".$id.")";
			$user=$userInfo->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}else{
			$sql="select * from (select a.*,row_number()over(order by pm) n from (SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm FROM rank_user_info a WHERE rpt_month=".$month." AND config_id=".$id.") a) where n>10*".$num."-10 and n<=10*".$num."";
			$user=$userInfo->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}
	}

	public function new_load($id='',$num=''){
		$userInfo=M('userInfo');
		if(IS_GET){
			$sql="select count(*) sum from (select a.* from (SELECT a.*,rank()over(partition BY config_id order by amount desc nulls last) pm FROM(SELECT bill_id,user_name,config_id,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>201602 GROUP BY bill_id,user_name,config_id) a) a where a.config_id=".$id." order by a.amount desc)";
			$user=$userInfo->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}else{
			$sql="select * from (select a.*,row_number()over(order by pm) n from (select a.* from (SELECT a.*,rank()over(partition BY config_id order by amount desc nulls last) pm FROM(SELECT bill_id,user_name,config_id,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>201602 GROUP BY bill_id,user_name,config_id) a) a where a.config_id=".$id." order by a.amount desc) a) where n>10*".$num."-10 and n<=10*".$num."";
			$user=$userInfo->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}
	}

	public function rank_jf($id='',$num=''){
		$userInfo=M('userInfo');
		if(IS_GET){
			if($id<200){
				$sql="select count(*) sum from (SELECT a.*,rank()over (partition BY 单位 order by 个人总积分 desc) pm FROM(SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0)+nvl(SUM(创新积分),0) 个人总积分 FROM (SELECT 月份,员工编号,config_id,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分,创新积分 FROM (SELECT b.config_id,a.* FROM Rank_个人积分_Info a, rank_jf_px b WHERE a.单位=b.单位(+)) GROUP BY 月份,个人绩效积分,员工编号,config_id,单位,姓名,bill_id,竞赛积分,荣誉积分,创新积分) a WHERE config_id=".$id." GROUP BY 员工编号,单位,姓名,bill_id) a)";
			}else{
				$sql="select count(*) sum from (SELECT a.*,rank()over (partition BY 单位 order by 团队总积分 desc) pm FROM(SELECT 单位,县市,团队,bill_id,nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0)+nvl(SUM(创新积分),0) 团队总积分 FROM (SELECT b.config_id,a.* FROM Rank_团队积分 a, rank_jf_px b WHERE a.单位=b.单位(+)) WHERE config_id=".$id." GROUP BY 县市,单位,团队,bill_id) a)";
			}
			$user=$userInfo->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}else{
			if($id<200){
				$sql="select * from (select a.*,row_number()over(order by pm) n from (SELECT a.*,rank()over (partition BY 单位 order by 个人总积分 desc) pm FROM(SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0)+nvl(SUM(创新积分),0) 个人总积分 FROM (SELECT 月份,员工编号,config_id,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分,创新积分 FROM (SELECT b.config_id,a.* FROM Rank_个人积分_Info a, rank_jf_px b WHERE a.单位=b.单位(+)) GROUP BY 月份,个人绩效积分,员工编号,config_id,单位,姓名,bill_id,竞赛积分,荣誉积分,创新积分) a WHERE config_id=".$id." GROUP BY 员工编号,单位,姓名,bill_id) a) a) where n>10*".$num."-10 and n<=10*".$num."";
			}else{
				$sql="select * from (select a.*,row_number()over(order by pm) n from (SELECT a.*,rank()over (partition BY 单位 order by 团队总积分 desc) pm FROM(SELECT 单位,县市,团队,bill_id,nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0)+nvl(SUM(创新积分),0) 团队总积分 FROM (SELECT b.config_id,a.* FROM Rank_团队积分 a, rank_jf_px b WHERE a.单位=b.单位(+)) WHERE config_id=".$id." GROUP BY 县市,单位,团队,bill_id) a) a) where n>10*".$num."-10 and n<=10*".$num."";
			}
			$user=$userInfo->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}
	}

	public function rank_info($month,$id,$bill_id){
		$userInfo=M('userInfo');
		$rankUser=$userInfo->where("rpt_month=".$month." and config_id=".$id." and bill_id='".$bill_id."'")->find();
		$this->assign('rankUser',$rankUser);
		$sql="SELECT * FROM (SELECT a.*,rank()over(PARTITION BY rpt_month,config_id ORDER BY amount DESC) pm FROM rank_user_info a WHERE config_id=".$id.")WHERE bill_id='".$bill_id."'";
		$users=$userInfo->query($sql);
		$this->assign('users',$users);
		$this->display();
	}

	public function new_info($id,$bill_id){
		$yearInfo=M('yearInfo');
		$sql_jf="SELECT county_name,user_name,bill_id,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>201602 and config_id='".$id."' AND bill_id='".$bill_id."' GROUP BY county_name,user_name,bill_id";
		$rankUser=$yearInfo->query($sql_jf);
		$this->assign('rankUser',$rankUser);
		$sql="SELECT * FROM rank_year_info WHERE rpt_month>201602 and config_id='".$id."' AND bill_id='".$bill_id."' ORDER BY rpt_month";
		$users=$yearInfo->query($sql);
		$this->assign('users',$users);
		$this->display();
	}

	public function rank_jf_info($id,$bill_id){
		$userInfo=M('userInfo');
		if($id<200){
			$sql_jf="SELECT * FROM(SELECT a.*,rank()over (partition BY 单位 order by 个人总积分 desc) pm FROM (SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0)+nvl(SUM(创新积分),0) 个人总积分 FROM (SELECT 月份,员工编号,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分,创新积分 FROM rank_个人积分_info GROUP BY 月份,个人绩效积分,员工编号,单位,姓名,bill_id,竞赛积分,荣誉积分,创新积分) a GROUP BY 员工编号,单位,姓名,bill_id) a) WHERE bill_id='".$bill_id."'";
			$rankUser=$userInfo->query($sql_jf);
			$this->assign('rankUser',$rankUser);
			$sql="SELECT distinct a.* FROM (SELECT * FROM rank_个人积分_info WHERE bill_id='".$bill_id."') a,(SELECT 月份,MAX(全员赛马积分) 全员赛马积分 FROM rank_个人积分_info WHERE bill_id='".$bill_id."' GROUP BY 月份) b WHERE nvl(a.全员赛马积分,0)=nvl(b.全员赛马积分,0) AND a.月份=b.月份 ORDER BY a.月份";
			$users=$userInfo->query($sql);
			$this->assign('users',$users);
		}else{
			$sql_jf="SELECT * FROM (SELECT a.*,rank()over (partition BY 单位 order by 团队总积分 desc) pm FROM (SELECT 单位,县市,团队,bill_id,nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0) 团队总积分 FROM rank_团队积分 GROUP BY 县市,单位,团队,bill_id) a) WHERE bill_id='".$bill_id."'";
			$rankUser=$userInfo->query($sql_jf);
			$this->assign('rankUser',$rankUser);
			$sql="SELECT * FROM rank_团队积分 WHERE bill_id='".$bill_id."' order by 月份";
			$users=$userInfo->query($sql);
			$this->assign('users',$users);
		}
		$this->assign('id',$id);
		$this->display();
	}

	public function record_index(){
		$this->display('index/test');
	}

	public function rank_market(){
		$this->display('rank_market_index');
	}

	public function rank_market_list(){
		$this->display('rank_market');
	}

	public function rank_market_rpt(){
		if(IS_POST){
			$countyName = I('countyName');
			$mode = I('mode');
			$homeDate = I('homeDate');
			$endDate  = I('endDate');
			if(empty($homeDate) && empty($endDate)){
				$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market group by county_name";
			}

			if(empty($countyName) && empty($mode) && !empty($homeDate)){
				$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."'
					group by county_name";
			}

			if(!empty($countyName) && empty($mode)){
				$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."' 
					  and county_name='".$countyName."'  
					group by county_name";
			}

			if(empty($countyName) && !empty($mode)){
				$sql = "select county_name,服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."' 
					  and 服务类型='".$mode."'
					group by county_name,服务类型";
			}

			if(!empty($countyName) && !empty($mode)){
				$sql = "select county_name,服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."' 
					  and county_name='".$countyName."' and 服务类型='".$mode."'
					group by county_name,服务类型";
			}
			$market=M('market');
			$rpt=$market->query($sql);
			$this->assign('rpt',$rpt);
			$this->ajaxReturn($rpt);
		}else{
			$this->display('rank_market_rpt');
		}
	}

	public function rank_service(){
		$this->display();
	}

	public function rank_market_info($num){
		$market=M('market');
		$homeDate=$_POST['homeDate'];
		$endDate=$_POST['endDate'];
		$home=$_GET['homeDate'];
		$end=$_GET['endDate'];
		if(IS_GET){
			$sql="select count(*) sum from (SELECT rownum n,a.* FROM rank_market a where 1=1";
			if(!empty($home)){
				$sql .=" and create_date>='".$home."'";
			}
			if(!empty($end)){
				$sql .=" and create_date<='".$end."'";
			}
			$sql .=")";
			$user=$market->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}else{
			$sql="select * from (SELECT rownum n,a.* FROM rank_market a where 1=1";
			if(!empty($homeDate)){
				$sql .=" and create_date>='".$homeDate."'";
			}
			if(!empty($endDate)){
				$sql .=" and create_date<='".$endDate."'";
			}
			$sql .=") where n>10*".$num."-10 and n<=10*".$num."";
			$user=$market->query($sql);
			$this->assign('user',$user);
			$this->ajaxReturn($user);
		}
	}

	private function page(){

	}

	public function rank_market_det($url){
		$market=M('market');
		$img=$market->where("url='".$url."'")->find();
		$this->assign('img',$img);
		$this->display();
	}

	public function depn(){
		$m=M('market');
		$base64_string = $_POST['formFile'];
		$savename = uniqid().'.jpg';
    	$savepath = '//10.78.1.85/www/ranking/upload/'.$savename;
		$data['user_name']=$_POST['user_name'];
		$data['county_name']=$_POST['countyName'];
		$data['服务类型']=$_POST['mode'];
		$data['设摊数量']=$_POST['tan'];
		$data['终端销量']=$_POST['duan'];
		$data['孝道机销量']=$_POST['xdj'];
		$data['g2g3迁移量']=$_POST['qyl'];
		$data['address']=$_POST['address'];
		$data['create_date']=date('Y-m-d',time());
		$data['url']=$savename;
		$data['status']=1;
		$re=$m->add($data);
    	if($re){
    		$image = $this->base64_to_img( $base64_string, $savepath );
    		if($image){
    			echo '{"content":"提交成功"}';
    		}else{
    			echo '{"content":"请联系管理员！"}';
    		}
    	}else{
    		echo '{"content":"提交失败"}';
    	}
	}

	 function base64_to_img( $base64_string, $output_file ) {
        $ifp = fopen( $output_file, "wb" ); 
        fwrite( $ifp, base64_decode( $base64_string) ); 
        fclose( $ifp ); 
        return( $output_file ); 
	} 

	public function rank_effort($bill_id=''){
		$this->assign('user_auth',$_SESSION['user_auth']);
		if(empty($bill_id)){
			$bill_id = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
		}			
		$sql ="select * from mz_user.ls_yd_user where bill_id='".$bill_id."'";		   
		$m = M();
		$infos = $m->query($sql);		
		$this->assign('info',$infos[0]);
		$this->display();		
	}

    
	//便捷作业主页
	public function convenient_index(){	
		$OPER_LOGIN_CODE=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$this->assign('OPER_LOGIN_CODE',$OPER_LOGIN_CODE);	    
		$this->display();
	}

	//便捷查询主页
    public function convenient_search_index(){
    	$this->display();
    }

    //便捷电子流主页
    public function convenient_flow_index(){
    	$this->display();
    }

    //考勤审批
    public function convenient_flow_kaoqin(){
    	if(IS_GET){
	    	$OPER_LOGIN_CODE=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
	    	$m=M();
	        $list=$m->table('ls_flow.ls_employee_leave_list')->where("bill_id='".$OPER_LOGIN_CODE."'")->find();
	        $this->assign('list',$list);
	        $lists=$m->table('ls_flow.ls_employee_leave_list')->select();
	        $this->assign('lists',$lists);
	        $this->display();	       
        }

        if(IS_POST){        	
        	$data['apply_id']=date('YmdHis');
        	$data['applicant']=I('applicant');
        	$data['applicate_department']=I('applicate_department');
        	$data['applicant_mobile']=I('applicant_mobile');
        	$data['applicate_date']=I('applicate_date');
        	$data['leave_type']=I('leave_type');
        	$data['pre_approver_id']=I('pre_approver_id');
        	$data['outdoor_work']=I('outdoor_work');
        	$data['leave_name']=I('leave_name');
        	$data['leave_full_name']=I('leave_full_name');
        	$data['leave_phone']=I('leave_phone');
        	$data['leave_position']=I('leave_position');
        	$data['leave_days']=I('leave_days');
        	$data['start_time']=I('start_time');
        	$data['end_time']=I('end_time');
        	$data['start_time_2']=I('start_time_2');
        	$data['end_time_2']=I('end_time_2');
        	$data['am_start_time']=I('am_start_time');
        	$data['am_end_time']=I('am_end_time');
        	$data['pm_start_time']=I('pm_start_time');

        	$data['pm_end_time']=I('pm_end_time');
        	$data['county_vice_manager']=I('county_vice_manager');

        	$data['leave_reason']=I('leave_reason');

        	if(!empty($data['start_time_2'])&&!empty($data['end_time_2'])){
        		 $data['show_time_2']='yes';
        	}else{
        		 $data['show_time_2']='no';
        	} 
        	$data['status']=0;
        	
            $m=M();
        	$flag=$m->table('ls_attendance_approval_list')->add($data);

            if($flag){
            	$msg="申请提交成功,等待审核!";
            	//$this->success('成功');
            }else{
            	$msg="申请提交失败!";
            	//$this->error('失败');
            } 
            $this->ajaxReturn($msg);
            //dump($m->getLastSql());
        }
    }


    //派车申请
    public function convenient_flow_paiche(){
    	if(IS_GET){
	    	$OPER_LOGIN_CODE=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
	    	$m=M();
	        $list=$m->table('ls_flow.ls_employee_leave_list')->where("bill_id='".$OPER_LOGIN_CODE."'")->find();
	        $this->assign('list',$list);
	        $lists=$m->table('ls_flow.ls_employee_leave_list')->select();
	        $this->assign('lists',$lists);
	        $this->display();
        }

        if(IS_POST){        	
        	$data['applicate_department']=I('applicate_department');
        	$data['applicate_date']=I('applicate_date'); 


        	$applicant=I('applicant');
        	$data['applicant']=$applicant;
        	$applicant1=explode('|',$applicant);
            $data['applicant_name']=$applicant1[0];
        	$data['applicant_id']=$applicant1[1];



        	$data['apply_reason']=I('apply_reason');
        	$data['car_range']=I('car_range');
        	$data['destination']=I('destination');
        	$data['vehicle_location']=I('vehicle_location');

        	$contact_person=I('contact_person');
             if(substr($contact_person,-1)==','){
             	$contact_person=substr($contact_person,0,-1);
             }

            $contact_person=explode(',',$contact_person);	       
            $contact_person_name="";
            $contact_person_oa="";            
	        for($j=0;$j<count($contact_person);$j++){	        	
	        	$person=explode('|', $contact_person[$j]);
	        	if($j==count($contact_person)-1){
	        		$contact_person_name.=$person[0];
	        		$contact_person_oa.=$person[1];
	        	}else {
	        		$contact_person_name.=$person[0];
	        		$contact_person_name.=',';
	        		$contact_person_oa.=$person[1];
	        		$contact_person_oa.=',';
	        	}
	        }            
            $data['contact_person']=$contact_person_name;
            $data['contact_oa']=$contact_person_oa;


        	$data['transport_start_time']=I('transport_start_time');
        	$data['transport_end_time']=I('transport_end_time');

            $train_staff=I('train_staff');
             if(substr($train_staff,-1)==','){
             	$train_staff=substr($train_staff,0,-1);
             }

            $train_staff=explode(',',$train_staff);	       
            $train_staff_name="";
            $train_staff_oa="";            
	        for($i=0;$i<count($train_staff);$i++){	        	
	        	$name_oa=explode('|', $train_staff[$i]);
	        	if($i==count($train_staff)-1){
	        		$train_staff_name.=$name_oa[0];
	        		$train_staff_oa.=$name_oa[1];
	        	}else {
	        		$train_staff_name.=$name_oa[0];
	        		$train_staff_name.=',';
	        		$train_staff_oa.=$name_oa[1];
	        		$train_staff_oa.=',';
	        	}
	        }            
            $data['train_staff']=$train_staff_name;
            $data['train_staff_oa']=$train_staff_oa;



        	$dept_manager=I('dept_manager');
        	$data['dept_manager']=$dept_manager;
        	$dept_manager1=explode('|', $dept_manager);
        	$data['dept_manager_id']=$dept_manager1[1];
        	$data['dept_manager_name']=$dept_manager1[0];

        	$data['status']=0;
        	$data['apply_id']=date('YmdHis');

        	
           
            $m=M();         
            $flag=$m->table('ls_car_vehicle_manage')->data($data)->add();

            if($flag){
            	$msg="申请提交成功,等待审核!";
            	//$this->success('成功');
            }else{
            	$msg="申请提交失败!";
            	//$this->error('失败');
            } 
            $this->ajaxReturn($msg);
        }
    }

    


	 //长短号互查
	public function rank_cdhhc(){
        $m=M('userInfo');
        $bill_id = I('bill_id');
        $short_num = I('short_num');  

        If(empty($bill_id)&&empty($short_num)){
          $this->display();   	

        }else{           
           $sql="SELECT bill_id,short_num,group_name  FROM  ls_vpmn_user where 1=1 ";       
          if(!empty($bill_id)){
          	$sql .=" and bill_id = '".$bill_id."' ";
          }
          if(!empty($short_num)){
          	$sql .=" and short_num = '".$short_num."' ";
          }
          $arrs=$m->query($sql); 
          if(empty($arrs)){
          	$msg=array();
          	$msg[0]="未查询到符合条件的记录!";
          	$this->assign('msg',$msg);
          }         

		  $this->assign('arrs',$arrs);
		  $this->assign('bill_id',$bill_id);
		  $this->assign('short_num',$short_num);
		  $this->display();
        } 	
	}


	//计划录入
	public function plan_new(){
		$j['success']=false;
		$j['msg']='数据提交失败';
		if(IS_POST){
			$data['plan_type']=I('plan_type');
			$data['plan_date']=I('plan_date');
			$data['address']=I('address');
			$data['man']=I('man');
			$data['county']=I('county');
			$data['create_date']=date('Y-m-d H:i:s',time());
			//dump($data);
			$model = M('planQian');
			$re = $model->add($data);
			if($re){
				$j['success']=true;
				$j['msg']='保存成功';
			}
			$this->ajaxReturn($j);
		}else{
			$this->display();
		}
	}

	//计划查询 
	public function plan_query(){
		$this->display();
	}
	public function plan_querys($nm=''){
		$market=M('planQian');
		$homeDate=$_POST['homeDate'];
		$endDate=$_POST['endDate'];
		$planType=$_POST['plan_type'];
		$homeDate2=$_GET['homeDate'];
		$endDate2=$_GET['endDate'];
		$planType2=$_GET['plan_type'];
		if(IS_GET){
			$sql="SELECT count(*) sum FROM rank_plan_qian where 1=1";
			if(!empty($homeDate2)){
				$sql .=" and plan_date>='".$homeDate2."'";
			}
			if(!empty($endDate2)){
				$sql .=" and plan_date<='".$endDate2."'";
			}
			if(!empty($planType2)){
				$sql .=" and plan_type='".$planType2."'";
			}
			$sum=$market->query($sql);
			$this->ajaxReturn($sum);
		}else{
			$sql="select * from(SELECT rownum n,a.* FROM rank_plan_qian a where 1=1";
			if(!empty($homeDate)){
				$sql .=" and plan_date>='".$homeDate."'";
			}
			if(!empty($endDate)){
				$sql .=" and plan_date<='".$endDate."'";
			}
			if(!empty($planType)){
				$sql .=" and plan_type='".$planType."'";
			}
			$sql .=" order by plan_date desc,create_date desc nulls last) where n>10*".$nm." and n<=10+10*".$nm."";

			$plans=$market->query($sql);
			$this->ajaxReturn($plans);
		}
	}
}
?>