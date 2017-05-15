<?php

namespace Home\Controller;

class IndexController extends BaseController {

	protected function _initialize(){
		//parent::_initialize();
		parent::viewLog();
		 $id = I('id');
		 if($id){
		 	session('id',$id);
		 }else{
		 	$id = session('id');
		 }
		 $this->assign('id',$id);	 
	}

	public function warnning(){
		echo '系统维护中，请稍后';
	}

	public function navgo($flag='0'){
		$this->assign('flag',$flag);
		$this->display();
	}


	public function loginOut(){
		session(null);
		$this->redirect('index');
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
	
	public function index(){
		$this->display('rank/index');
	}

	public function index_lishui($month){ 
		$bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$lead=M('lead');
		$power=$lead->where("bill_id='".$bill_id."'")->find();
		$this->assign('pow',$power);

		$sql="SELECT * FROM rank_config ORDER BY MONTH DESC";
		$luna=$lead->query($sql);
		$this->assign('luna',$luna);
		$this->assign('month',$month);
		$this->display('rank/lishui');
	}
 
 	public function rank_left($rpt_month=''){

 		parent::intiParams();
		//二级类型
		//$rpt_month= I('month');
		//$datas = S('config_left');
		if(empty($datas)){
			$rank_config = M('config');
			//$sql = "select fst_type,sec_type,sec_type_id,name,id,status from rank_config where sec_type_id='".$sec_type."' ORDER BY des";

			$sql =  "select c.id msg_id,b.rpt_month,a.fst_type,a.sec_type,a.sec_type_id,a.name,a.id,a.status,a.more_link,a.info_link,nvl(b.p1,'-') p1,nvl(b.p2,'-') p2,nvl(b.p3,'-') p3,nvl(b.p4,'-') p4,nvl(b.p5,'-') p5,nvl(b.p6,'-') p6 from rank_config a,\n" .
					"(\n" .
					"       select rpt_month,config_id,max(p1) p1,max(p2) p2,max(p3) p3,max(p4) p4,max(p5) p5,max(p6) p6\n" .
					"        from (\n" .
					"           select rpt_month,config_id,\n" .
					"                            case when rn=1 then county_name||'/'||user_name end p1,\n" .
					"                            case when rn=2 then county_name||'/'||user_name end p2,\n" .
					"                            case when rn=3 then county_name||'/'||user_name end p3,\n" .
					"                            case when rn=103 then county_name||'/'||user_name end p4,\n" .
					"                            case when rn=102 then county_name||'/'||user_name end p5,\n" .
					"                            case when rn=101 then county_name||'/'||user_name end p6\n" .
					"          from\n" .
					"          (\n" .
					"            Select * from (select t.*,row_number()over (partition by rpt_month,config_id order by amount desc) rn from rank_user_info t) where rn<=3\n" .
					"            union\n" .
					"            Select * from (select t.*,row_number()over (partition by rpt_month,config_id order by amount)+100 rn from rank_user_info t) where rn<=103\n" .
					"          )\n" .
					"          where rpt_month='".$rpt_month."'\n" .
					"        ) group by rpt_month,config_id\n" .
					") b,\n" .
					"v_rank_config_msg c\n" .
					"where a.id = b.config_id(+)\n" .
					"  and a.id = c.config_id(+)\n" .
					"  and a.month='".$rpt_month."'\n" .
					"  and a.status=1\n" .
					"  order by sec_type_id,des";

			$datas = $rank_config->query($sql);
		}

		if($datas){
			S('config_left',$datas);
		}
		$this->assign('config_left',$datas);
	}

	public function rank_load(){
		//二级类型
		$sec_type = I('sec_type');
		$rpt_month= I('month');

		$rank_config = M('config');
		//$sql = "select fst_type,sec_type,sec_type_id,name,id,status from rank_config where sec_type_id='".$sec_type."' ORDER BY des";

		$sql =  "select c.id msg_id,b.rpt_month,a.fst_type,a.sec_type,a.sec_type_id,a.name,a.id,a.status,a.more_link,a.info_link,b1,b2,b3,b4,b5,b6,nvl(b.p1,'-') p1,nvl(b.p2,'-') p2,nvl(b.p3,'-') p3,nvl(b.p4,'-') p4,nvl(b.p5,'-') p5,nvl(b.p6,'-') p6 from rank_config a,\n" .
				"(\n" .
				"       select rpt_month,config_id,max(p1) p1,max(b1) b1,max(p2) p2,max(b2) b2,max(p3) p3,max(b3) b3,max(p4) p4,max(b4) b4,max(p5) p5,max(b5) b5,max(p6) p6,max(b6) b6\n" .
				"        from (\n" .
				"           select rpt_month,config_id,\n" .
				"                            case when rn=1 then county_name||'/'||user_name end p1,\n" .
				"	                         case when rn=1 then bill_id end b1,\n" .
				"                            case when rn=2 then county_name||'/'||user_name end p2,\n" .
				" 	                         case when rn=2 then bill_id end b2,\n" .
				"                            case when rn=3 then county_name||'/'||user_name end p3,\n" .
				"                        	 case when rn=3 then bill_id end b3,\n" .
				"                            case when rn=103 then county_name||'/'||user_name end p4,\n" .
				"                        	 case when rn=103 then bill_id end b4,\n" .
				"                            case when rn=102 then county_name||'/'||user_name end p5,\n" .
				"                        	 case when rn=102 then bill_id end b5,\n" .
				"                            case when rn=101 then county_name||'/'||user_name end p6,\n" .
				"                        	 case when rn=101 then bill_id end b6\n" .
				"          from\n" .
				"          (\n" .
				"            Select * from (select t.*,row_number()over (partition by rpt_month,config_id order by amount desc) rn from rank_user_info t) where rn<=3\n" .
				"            union\n" .
				"            Select * from (select t.*,row_number()over (partition by rpt_month,config_id order by amount)+100 rn from rank_user_info t) where rn<=103\n" .
				"          )\n" .
				"          where rpt_month='".$rpt_month."'\n" .
				"        ) group by rpt_month,config_id\n" .
				") b,\n" .
				"v_rank_config_msg c\n" .
				"where a.id = b.config_id(+)\n" .
				"  and a.id = c.config_id(+)\n" .
				//"  and b.rpt_month='".$rpt_month."'\n" .
				"  and a.sec_type_id='".$sec_type."'\n" .
				"  and a.status=1\n" .
				"  and a.month='".$rpt_month."'\n" .
				"  order by des";
				//echo $sql;
		$datas = $rank_config->query($sql);
		$this->ajaxReturn($datas);
	}

	public function rank_more(){
		$this->display();
	}

	public function rank_info($id=1001,$month='201601'){
		$userInfo = M('userInfo');
		$rankConfig = M('config');
		$config = $rankConfig->find($id);
		$this->assign('config',$config);

		$name=I('name');
		$countyName=I('countyName');
		$sql = "SELECT a.*,CASE WHEN qs>0 THEN qs ELSE 0 END qs,CASE WHEN a.config_id=1008 OR a.config_id=1009 OR a.config_id=1015 THEN 10 ELSE 3 END n FROM (select b.cnt zan,a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm from rank_user_info a,rank_user_zan b where a.bill_id=b.id(+) and a.rpt_month='".$month."' and a.config_id=".$id." order by a.amount desc) a,
			(SELECT bill_id,COUNT(*) qs FROM(SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm FROM rank_user_info a WHERE config_id=".$id.") WHERE (CASE WHEN config_id=1009 OR config_id=1015 OR config_id=1008 THEN 10 ELSE 3 END)>=pm GROUP BY bill_id ) b WHERE a.bill_id=b.bill_id(+)";
		if(!empty($name)){
			$sql.=" and user_name like '%".$name."%'";
		}
		if(!empty($countyName)){
			$sql.=" and county_name='".$countyName."'";
		}
			$sql.="  order by a.amount DESC";
		$counts = $userInfo->query($sql);
		$this->assign('counts',$counts);

		$s="select count(*) count from rank_user_info a,rank_user_zan b where a.bill_id=b.id(+) and a.rpt_month='".$month."' and a.config_id=".$id." ";
		$count = $userInfo->query($s);
		$this->assign('count',$count);

		$users = parent::listsSql($sql,$name,$countyName);
		$this->assign('users',$users);
		$this->rank_left($rpt_month=$month);

		$this->display('rank/rank_info');
	}

	//根据手机号码获取用户
	public function rank_bill($bill_id=''){
		$modelv= M('userInfoV');
		$user  = $modelv->where('bill_id='.$bill_id)->cache(true)->find();
		$this->assign('rankUser',$user);
	}

	//用户PK
	public function rank_pk(){
		$bill_ids = I('bill_ids');
		$config_id = I('id');
		$month = I('month');

		$bill_id = explode(',', $bill_ids);
		$infoHead= parent::getUserCon($config_id,$month);
		$usera   = parent::getUserData($bill_id[0],$config_id,$month);
		$userb   = parent::getUserData($bill_id[1],$config_id,$month);
		$json['head']=$infoHead;
		$json['usera']=$usera;
		$json['userb']=$userb;
		$this->assign('json',$json);
		$this->display('rank/rank_pk');
	}

	public function rank_user(){
		$bill_id = I('bill_id');
		$config_id = I('id');
		$month = I('month');
		$model = M('userInfo');
		$partyMember=M('partyMember');
		//获取用户基础信息 rank_user_info 表
		$w['bill_id']=$bill_id;
		$w['rpt_month']=$month;
		$w['config_id']=$config_id;
		$rankUser = $model->where($w)->find();	
		$this->assign('rankUser',$rankUser);
		//dump($rankUser);
		$dy=$partyMember->where('bill_id='.$bill_id)->find();
		$this->assign('dy',$dy);

		parent::getUserCon($config_id,$month);

		parent::getUserData($bill_id,$config_id,$month);
		//dump($data);

		//$this->rank_bill($bill_id);
		$this->display('rank/rank_user');
	}

	public function rank_line(){
		$bill_id = I('bill_id');
		$config_id = I('id');
		$month = I('month');
		$model = M('userInfo');
		$partyMember=M('partyMember');
		//获取用户基础信息 rank_user_info 表
		$w['bill_id']=$bill_id;
		$w['rpt_month']=$month;
		$w['config_id']=$config_id;
		$rankUser = $model->where($w)->find();		
		$this->assign('rankUser',$rankUser);
		//dump($rankUser);
		$dy=$partyMember->where('bill_id='.$bill_id)->find();
		$this->assign('dy',$dy);

		parent::getUserCon($config_id,$month);

		parent::getUserData($bill_id,$config_id,$month);
			$sql="SELECT * FROM(SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm  
				FROM rank_user_info a WHERE rpt_month>=201601 AND config_id='".$config_id."' ORDER BY rpt_month DESC) 
				WHERE bill_id='".$bill_id."' ORDER BY county_name,rpt_month";
			$lines = $model->query($sql);
			$this->assign('lines',$lines);
			$this->display('rank/rank_line');

	}

	public function rank_zan($bill_id=0){
		$json['flag']=false;
		$json['msg']='点赞失败';
		$model = M('userZan');
		if($bill_id!=null){
			$zan = $model->where("id='".$bill_id."' and status=1")->find();
			if($zan){
				$re = $model->where("id='".$bill_id."'")->setInc('cnt',1);
				if($re){
					$json['flag']=true;
					$json['msg']='点赞成功';
				}else{
					$json['msg']='更新失败';
				}
			}else{
				$data['id']=$bill_id;
				$data['cnt']=1;
				$re = $model->add($data);
				if($re){
					$json['flag']=true;
					$json['msg']='点赞成功';
				}else{
					$json['msg']='添加失败';
				}
			}
		}else{
			$json['msg']='目标用户信息获取失败';
		}
		$this->ajaxReturn($json);
	}

	public function orcTest(){
		 $rank = M('rptTable');

		 echo 'test success .... '.'<BR>';

		 //增加
		 //add(1);
		 //add(2);

		 //查询
		 $pre_list =$rank->query('select sysdate from dual');
		 $rpts = $rank->page(1,5)->order('oper_create_date desc')->select();
		 echo '获取最近执行的sql: '.$rank->getLastSql().'<BR>';

		 $this->assign('rpts',$rpts);
		 $this->display('rank/index');
	}

	public function add($flag=1){
		if($flag==1){
				//插入方式1
				 $rank = M('rptTable');
				 $rpt['OPER_ID']='rank_seq.nextval';
				 $rpt['OPER_NAME']='中文名'.time();
				 $rpt['OPER_LOGIN_CODE']='test';
				 $rpt['OPER_LOGIN_PASS']='test';
				 $rpt['oper_create_date']='date.sysdate';
				 $insert = $rank->orcAdd('rank_rpt_table',$rpt);
				 echo '获取最近执行的sql: - '.$insert.' - '.$rank->getLastSql().'<BR>';
				 if($insert){
				 	echo '插入成功'.'<BR>';
				 }else{
				 	echo '插入失败'.'<BR>';
				 }
			}else{
				//sql 插入
				 $sql = "insert into rank_rpt_table (oper_id,oper_login_code,oper_name,oper_login_pass,oper_create_date) values (rank_seq.nextval,'test','中文名".time()."','test',sysdate)";
				 echo '获取最近执行的sql: '.$sql.'<BR>';
				 $insert2 = $rank->execute($sql);
				 echo '插入2结果：'.$insert2.'<br>';
				 if($insert2){
				 	echo '插入成功2'.'<BR>';
				 }else{
				 	echo '插入失败2'.'<BR>';
				 } 
			}
	}
	public function delete($id=0){
		$rank = M('rptTable');
		$dwhere['oper_id']=$id;
		$delete = $rank->where($dwhere)->delete();
		if($delete){
			echo '<script>alert("删除成功");</script>';
		 }else{
		 	echo '<script>alert("删除失败");</script>';
		 }
		$this->redirect('index');
	}

	public function find($id){
		$rank = M('rptTable');
		$info = $rank->find($id);
		dump($info);
		echo '<a href="javascript:history.back();">返回</a>';
	}

	public function new_left(){

 		parent::intiParams();
		//二级类型
		//$datas = S('config_left');
		if(empty($datas)){
			$rank_config = M('config');
			//$sql = "select fst_type,sec_type,sec_type_id,name,id,status from rank_config where sec_type_id='".$sec_type."' ORDER BY des";

			$sql =  "select c.id msg_id,a.fst_type,a.sec_type,a.sec_type_id,a.name,a.id,a.status,a.more_link,a.info_link,nvl(b.p1,'-') p1,nvl(b.p2,'-') p2,nvl(b.p3,'-') p3,nvl(b.p4,'-') p4,nvl(b.p5,'-') p5,nvl(b.p6,'-') p6 from rank_config a,\n" .
					"(\n" .
					"       select config_id,max(p1) p1,max(p2) p2,max(p3) p3,max(p4) p4,max(p5) p5,max(p6) p6\n" .
					"        from (\n" .
					"           select config_id,\n" .
					"                            case when rn=1 then county_name||'/'||user_name end p1,\n" .
					"                            case when rn=2 then county_name||'/'||user_name end p2,\n" .
					"                            case when rn=3 then county_name||'/'||user_name end p3,\n" .
					"                            case when rn=103 then county_name||'/'||user_name end p4,\n" .
					"                            case when rn=102 then county_name||'/'||user_name end p5,\n" .
					"                            case when rn=101 then county_name||'/'||user_name end p6\n" .
					"          from\n" .
					"          (\n" .
					"   Select * from (select t.*,row_number()over (partition BY config_id order by amount desc) rn from \n".
                   	"	(SELECT bill_id,config_id,user_name,county_name,SUM(全员赛马积分)amount FROM rank_year_info \n".
                   	"	GROUP BY bill_id,config_id,user_name,county_name) t) where rn<=3\n" .
					"   union\n" .
					"   Select * from (select t.*,row_number()over (partition BY config_id order by amount)+100 rn from 
				        (SELECT bill_id,config_id,user_name,county_name,SUM(全员赛马积分)amount FROM rank_year_info 
				        GROUP BY bill_id,config_id,user_name,county_name) t) where rn<=103\n" .
					"          )\n" .
					"        ) group by config_id\n" .
					") b,\n" .
					"v_rank_config_msg c\n" .
					"where a.id = b.config_id(+)\n" .
					"  and a.id = c.config_id(+)\n" .
					"  and a.month=201612\n" .
					"  and a.status=1\n" .
					"  order by sec_type_id,des";

			$datas = $rank_config->query($sql);
		}

		if($datas){
			S('config_left',$datas);
		}
		$this->assign('config_left',$datas);
	}

		public function new_load(){
		//二级类型
			$sec_type = I('sec_type');

			$rank_config = M('config');
			//$sql = "select fst_type,sec_type,sec_type_id,name,id,status from rank_config where sec_type_id='".$sec_type."' ORDER BY des";

			$sql =  "select c.id msg_id,a.fst_type,a.sec_type,a.sec_type_id,a.name,a.id,a.status,a.more_link,a.info_link,nvl(b.p1,'-') p1,b1,b2,b3,b4,b5,b6,nvl(b.p2,'-') p2,nvl(b.p3,'-') p3,nvl(b.p4,'-') p4,nvl(b.p5,'-') p5,nvl(b.p6,'-') p6 from rank_config a,\n" .
					"(\n" .
					"       select config_id,max(p1) p1,max(b1) b1,max(p2) p2,max(b2) b2,max(p3) p3,max(b3) b3,max(p4) p4,max(b4) b4,max(p5) p5,max(b5) b5,max(p6) p6,max(b6) b6\n" .
					"        from (\n" .
					"           select config_id,\n" .
					"                            case when rn=1 then county_name||'/'||user_name end p1,\n" .
					"	                         case when rn=1 then bill_id end b1,\n" .
					"                            case when rn=2 then county_name||'/'||user_name end p2,\n" .
					" 	                         case when rn=2 then bill_id end b2,\n" .
					"                            case when rn=3 then county_name||'/'||user_name end p3,\n" .
					"                        	 case when rn=3 then bill_id end b3,\n" .
					"                            case when rn=103 then county_name||'/'||user_name end p4,\n" .
					"                        	 case when rn=103 then bill_id end b4,\n" .
					"                            case when rn=102 then county_name||'/'||user_name end p5,\n" .
					"                        	 case when rn=102 then bill_id end b5,\n" .
					"                            case when rn=101 then county_name||'/'||user_name end p6,\n" .
					"                        	 case when rn=101 then bill_id end b6\n" .
					"          from\n" .
					"          (\n" .
					"            Select * from (select t.*,row_number()over (partition BY config_id order by amount desc) rn from \n".
                   	"			(SELECT bill_id,config_id,user_name,county_name,SUM(全员赛马积分)amount FROM rank_year_info WHERE rpt_month>201602 \n".
                   	"			GROUP BY bill_id,config_id,user_name,county_name) t) where rn<=3\n" .
					"            union\n" .
					"            Select * from (select t.*,row_number()over (partition BY config_id order by amount)+100 rn from 
				                (SELECT bill_id,config_id,user_name,county_name,SUM(全员赛马积分)amount FROM rank_year_info WHERE rpt_month>201602 
				                   GROUP BY bill_id,config_id,user_name,county_name) t) where rn<=103\n" .
					"          )\n" .
					"        ) group by config_id\n" .
					") b,\n" .
					"v_rank_config_msg c\n" .
					"where a.id = b.config_id(+)\n" .
					"  and a.id = c.config_id(+)\n" .
					"  and a.sec_type_id='".$sec_type."'\n" .
					"  and a.status=1\n" .
					"  and a.month=201612\n" .
					"  order by des";
					//echo $sql;
			$datas = $rank_config->query($sql);
			$this->ajaxReturn($datas);
		}

	public function new_info($id=1001){
		$userInfo = M('userInfo');
		$rankConfig = M('config');
		$config = $rankConfig->find($id);
		$this->assign('config',$config);


		$name=I('name');
		$countyName=I('countyName');
		$sql = "SELECT a.*,CASE WHEN qs>0 THEN qs ELSE 0 END qs,CASE WHEN a.config_id=1008 OR a.config_id=1009 OR a.config_id=1015 OR a.config_id=1013 THEN 10 ELSE 3 END n FROM (select * from (
		select b.cnt zan,a.* from (SELECT a.*,rank()over(partition BY config_id order by amount desc nulls last) pm FROM
		(SELECT bill_id,user_name,config_id,county_name,SUM(全员赛马积分) amount
		FROM rank_year_info WHERE rpt_month>201602 GROUP BY bill_id,user_name,config_id,county_name) a) a,
		rank_user_zan b where a.bill_id=b.id(+) and a.config_id=".$id." order by a.amount desc)) a,
		(SELECT bill_id,COUNT(*) qs FROM(SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm 
		FROM rank_year_info a WHERE config_id=".$id." AND rpt_month>201602) WHERE (CASE WHEN config_id=1009 OR config_id=1015 OR config_id=1008 OR config_id=1013 THEN 10 ELSE 3 END)>=pm GROUP BY bill_id ) b 
		WHERE a.bill_id=b.bill_id(+)";
		if(!empty($name)){
			$sql.=" and user_name like '%".$name."%'";
		}
			$sql.="order by pm";
		$counts = $userInfo->query($sql);
		$this->assign('counts',$counts);

		$s="select count(*) count from (SELECT a.*,rank()over(partition BY config_id order by amount desc nulls last) pm FROM
			(SELECT bill_id,user_name,county_name,config_id,SUM(全员赛马积分) amount
			FROM rank_year_info GROUP BY bill_id,user_name,county_name,config_id) a) a,
			rank_user_zan b where a.bill_id=b.id(+) and a.config_id=".$id." ";
		$count = $userInfo->query($s);
		$this->assign('count',$count);

		$users = parent::listsSql($sql,$name,$countyName);
		$this->assign('users',$users);

		$this->new_left();

		parent::viewLog();
		$this->display('rank/new_info');
	}

	public function new_line(){
		$bill_id=I('bill_id');
		$config_id = I('id');
		$line=M('yearInfo');
		$partyMember=M('partyMember');
		$sqls="SELECT * FROM rank_year_info WHERE rpt_month>201602 and config_id='".$config_id."' AND bill_id='".$bill_id."' ORDER BY rpt_month ";
		$year=$line->query($sqls);
		$this->assign('year',$year);
		$dy=$partyMember->where('bill_id='.$bill_id)->find();
		$this->assign('dy',$dy);


		$sql="SELECT county_name,user_name,bill_id,SUM(全员赛马积分) amount FROM rank_year_info 
			WHERE rpt_month>201602 and config_id='".$config_id."' AND bill_id='".$bill_id."' GROUP BY county_name,user_name,bill_id ";
		$lines = $line->query($sql);
		$this->assign('lines',$lines);
		$this->display('rank/new_line');

	}

	public function new_user(){
		$bill_id = I('bill_id');
		$config_id = I('id');
		$month = I('month');
		$model = M('userInfo');
		//获取用户基础信息 rank_user_info 表
		$w['bill_id']=$bill_id;
		$w['rpt_month']=$month;
		$rankUser = $model->where($w)->find();		
		$this->assign('rankUser',$rankUser);
		//dump($rankUser);

		parent::getUserCon($config_id,$month);

		parent::getUserData($bill_id,$config_id,$month);
		//dump($data);

		//$this->rank_bill($bill_id);
		//$this->display('rank/new_user');


		$sql="SELECT * FROM(SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm  
			FROM rank_user_info a WHERE rpt_month>=201601 AND config_id='".$config_id."' ORDER BY rpt_month DESC) 
			WHERE bill_id='".$bill_id."' ORDER BY county_name,rpt_month";
		$lines = $model->query($sql);
		$this->assign('lines',$lines);
		$this->display('rank/new_user');
	}



	///////////////////////////////////////////赛马问题解答///////////////////////////
	public function rank_answer($id=0){
		$m = M('configQuestion');
		$ques = $m->find($id);
		$this->assign('ques',$ques);
		$this->display('rank/rank_answer');
	}	

	public function rank_question($config_id=1001){
		parent::isLogin();
		$where = " and oper_login_code='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
		parent::loginUser($where);

		$m = M('configQuestion');

		if(IS_POST){
			$json['success']=false;
			$json['msg']='保存错误';
			$content = I('content');
			$data['id']=time().rand();
			$data['config_id']=$config_id;
			$data['content']=$content;
			$data['create_oper_id']=$_SESSION['user_auth']['OPER_ID'];
			$re = $m->orcAdd('rank_config_question',$data);
			if($re){
				$json['success']=true;
				$json['msg']='保存成功';
				$json['nid']=$data['id'];
			}
			$this->ajaxReturn($json);
		}else{
			$mc = M('config');
			$sql = "select * from rank_config where month=(select max(month) from rank_config)  and id=".$config_id;
			$config = $mc->query($sql);
			if(count($config)>0){
				$this->assign('config',$config[0]);
			}

			$sql = "select a.*,to_char(create_date,'yyyy-mm-dd hh:mi') create_date_char,b.oper_name from rank_config_question a , mz_user.t_sys_oper b where a.status=1 and a.create_oper_id=b.oper_id(+) and a.config_id=".$config_id." ORDER BY CREATE_DATE DESC";
			$qs = parent::listsSqlByls($sql,10);
			$this->assign('qs',$qs);
			$this->display('rank/rank_question');
		}
	}

	public function rank_jf_question($config_id=1001){
		parent::isLogin();
		$where = " and oper_login_code='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
		parent::loginUser($where);

		$m = M('configQuestion');

		if(IS_POST){
			$json['success']=false;
			$json['msg']='保存错误';
			$content = I('content');
			$data['id']=time().rand();
			$data['config_id']=$config_id;
			$data['content']=$content;
			$data['create_oper_id']=$_SESSION['user_auth']['OPER_ID'];
			$re = $m->orcAdd('rank_config_question',$data);
			if($re){
				$json['success']=true;
				$json['msg']='保存成功';
				$json['nid']=$data['id'];
			}
			$this->ajaxReturn($json);
		}else{
			$mc = M('config');
			$sql = "select 单位 NAME,config_id ID from rank_jf_px where config_id=".$config_id;
			$config = $mc->query($sql);
			if(count($config)>0){
				$this->assign('config',$config[0]);
			}

			$sql = "select a.*,to_char(create_date,'yyyy-mm-dd hh:mi') create_date_char,b.oper_name from rank_config_question a , mz_user.t_sys_oper b where a.status=1 and a.create_oper_id=b.oper_id(+) and a.config_id=".$config_id." ORDER BY CREATE_DATE DESC";
			$qs = parent::listsSqlByls($sql,10);
			$this->assign('qs',$qs);
			$this->display('rank/rank_question');
		}
	}

	public function rank_jf($year){
		$jf=M('chengzhangjfInfo');
		$sql="SELECT config_id,pos_name,max(p1) p1,max(b1) b1,max(p2) p2,max(b2) b2,max(p3) p3,max(b3) b3,max(p4) p4,max(b4) b4,max(p5) p5,MAX(b5) b5 FROM(SELECT config_id,pos_name,CASE WHEN rn=1 THEN user_name END p1,CASE WHEN rn=1 THEN '第'||pm||'名' END b1,CASE WHEN rn=2 THEN user_name END p2, CASE WHEN rn=2 THEN '第'||pm||'名' END b2,CASE WHEN rn=3 THEN user_name END p3,CASE WHEN rn=3 THEN '第'||pm||'名' END b3,CASE WHEN rn=4 THEN user_name END p4,CASE WHEN rn=4 THEN '第'||pm||'名' END b4,CASE WHEN rn=5 THEN user_name END p5, CASE WHEN rn=5 THEN '第'||pm||'名' END b5 FROM(SELECT * FROM(SELECT a.*,rank()over (partition BY pos_name order by 个人总积分 desc) pm,row_number()over (partition BY pos_name order by 个人总积分 desc) rn FROM(SELECT user_name,oper_code,bill_id,pos_name,config_id, nvl(SUM(个人绩效积分),0)+nvl(SUM(赛马积分),0)+nvl(SUM(ry积分),0)+nvl(SUM(js积分),0)+nvl(SUM(cx积分),0)+nvl(SUM(xx积分),0)个人总积分 FROM(SELECT a.*,b.ry积分,c.js积分,d.cx积分,e.xx积分 FROM(SELECT rpt_month,config_id,pos_name,user_name,bill_id,oper_code,max(赛马积分) 赛马积分,个人绩效积分 FROM rank_chengzhangjf_info where substr(rpt_month,1,4)=".$year." group by rpt_month,config_id,pos_name,user_name,bill_id,oper_code,个人绩效积分) a,(SELECT rpt_month,bill_id,sum(ry积分) ry积分 FROM rank_ry_jf where bill_id is not null group by rpt_month,bill_id) b,(SELECT rpt_month,bill_id,sum(js积分) js积分 FROM rank_js_jf where bill_id is not null group by rpt_month,bill_id) c,(SELECT rpt_month,bill_id,sum(cx积分) cx积分 FROM rank_cx_jf where bill_id is not null group by rpt_month,bill_id) d,(SELECT rpt_month,bill_id,sum(xx积分) xx积分 FROM rank_xx_jf where bill_id is not null group by rpt_month,bill_id) e where a.bill_id=b.bill_id(+) and a.bill_id=c.bill_id(+) and a.bill_id=d.bill_id(+) and a.bill_id=e.bill_id(+) and a.rpt_month=b.rpt_month(+) and a.rpt_month=c.rpt_month(+) and a.rpt_month=d.rpt_month(+) and a.rpt_month=e.rpt_month(+)) group by user_name,oper_code,bill_id,pos_name,config_id) a) where rn<6)) group by config_id,pos_name order by config_id";
		$czjf=$jf->query($sql);
		$this->assign('czjf',$czjf);
		$this->assign('year',$year);
		parent::viewLog();
		$this->display('rank/rank_jf');
	}

	public function rank_jf_info($year,$id=''){
		$jf=M('chengzhangjfInfo');
		$user_name=I('user_name');
		$user_id=I('user_id');
		$sql="SELECT a.*,b.bill_id cc,rank()over (partition BY pos_name order by 个人总积分 desc) pm FROM(SELECT user_name,oper_code,bill_id,pos_name,config_id, nvl(SUM(个人绩效积分),0)+nvl(SUM(赛马积分),0)+nvl(SUM(ry积分),0)+nvl(SUM(js积分),0)+nvl(SUM(cx积分),0)+nvl(SUM(xx积分),0)个人总积分 FROM(SELECT a.*,b.ry积分,c.js积分,d.cx积分,e.xx积分 FROM(SELECT rpt_month,config_id,pos_name,user_name,bill_id,oper_code,max(赛马积分) 赛马积分,个人绩效积分 FROM rank_chengzhangjf_info where substr(rpt_month,1,4)=".$year." group by rpt_month,config_id,pos_name,user_name,bill_id,oper_code,个人绩效积分) a,(SELECT rpt_month,bill_id,sum(ry积分) ry积分 FROM rank_ry_jf where bill_id is not null group by rpt_month,bill_id) b,(SELECT rpt_month,bill_id,sum(js积分) js积分 FROM rank_js_jf where bill_id is not null group by rpt_month,bill_id) c,(SELECT rpt_month,bill_id,sum(cx积分) cx积分 FROM rank_cx_jf where bill_id is not null group by rpt_month,bill_id) d,(SELECT rpt_month,bill_id,sum(xx积分) xx积分 FROM rank_xx_jf where bill_id is not null group by rpt_month,bill_id) e where a.bill_id=b.bill_id(+) and a.bill_id=c.bill_id(+) and a.bill_id=d.bill_id(+) and a.bill_id=e.bill_id(+) and a.rpt_month=b.rpt_month(+) and a.rpt_month=c.rpt_month(+) and a.rpt_month=d.rpt_month(+) and a.rpt_month=e.rpt_month(+)) group by user_name,oper_code,bill_id,pos_name,config_id) a,rank_party_member b where a.bill_id=b.bill_id(+) and config_id=".$id."";
		if(!empty($user_name)){
		  	$sql .= " and a.user_name like '%".$user_name."%'";
		}
		if(!empty($user_id)){
		  	$sql .= " and oper_code='".$user_id."'";
		}
		$det=$jf->query($sql);
		$this->assign('det',$det);
		$this->assign('year',$year);
		$this->display('rank/rank_jf_info');
	}

	public function rank_jf_gr($bill_id='',$year){
		$jf=M('chengzhangjfInfo');
		$sql="SELECT * FROM(SELECT a.*,rank()over (partition BY pos_name order by 个人总积分 desc) pm FROM(SELECT user_name,oper_code,county_name,bill_id,pos_name,config_id,cc,dd, nvl(SUM(个人绩效积分),0)+nvl(SUM(赛马积分),0)+nvl(SUM(ry积分),0)+nvl(SUM(js积分),0)+nvl(SUM(cx积分),0)+nvl(SUM(xx积分),0)个人总积分 FROM(SELECT a.*,b.ry积分,c.js积分,d.cx积分,e.xx积分,f.bill_id cc,g.bill_id dd FROM(SELECT rpt_month,config_id,county_name,pos_name,user_name,bill_id,oper_code,max(赛马积分) 赛马积分,个人绩效积分 FROM rank_chengzhangjf_info where substr(rpt_month,1,4)=".$year." group by rpt_month,config_id,county_name,pos_name,user_name,bill_id,oper_code,个人绩效积分) a,(SELECT rpt_month,bill_id,sum(ry积分) ry积分 FROM rank_ry_jf where bill_id is not null group by rpt_month,bill_id) b,(SELECT rpt_month,bill_id,sum(js积分) js积分 FROM rank_js_jf where bill_id is not null group by rpt_month,bill_id) c,(SELECT rpt_month,bill_id,sum(cx积分) cx积分 FROM rank_cx_jf where bill_id is not null group by rpt_month,bill_id) d,(SELECT rpt_month,bill_id,sum(xx积分) xx积分 FROM rank_xx_jf where bill_id is not null group by rpt_month,bill_id) e,rank_party_member f,(SELECT distinct bill_id FROM rank_chengzhangjf_info where rpt_month>201602 and substr(rpt_month,1,4)=".$year." and 赛马积分 is not null and config_id<200) g where a.bill_id=b.bill_id(+) and a.bill_id=c.bill_id(+) and a.bill_id=d.bill_id(+) and a.bill_id=e.bill_id(+) and a.rpt_month=b.rpt_month(+) and a.rpt_month=c.rpt_month(+) and a.rpt_month=d.rpt_month(+) and a.rpt_month=e.rpt_month(+) and a.bill_id=f.bill_id(+) and a.bill_id=g.bill_id(+)) group by user_name,oper_code,county_name,bill_id,pos_name,config_id,cc,dd) a) where bill_id='".$bill_id."'";
		$gr=$jf->query($sql);
		$this->assign('gr',$gr);

		$sqls="SELECT * FROM(SELECT a.*,b.ry积分,c.js积分,d.cx积分,e.xx积分 FROM(SELECT rpt_month,config_id,pos_name,user_name,bill_id,oper_code,max(赛马积分) 赛马积分,个人绩效积分 FROM rank_chengzhangjf_info where substr(rpt_month,1,4)=".$year." group by rpt_month,config_id,pos_name,user_name,bill_id,oper_code,个人绩效积分) a,(SELECT rpt_month,bill_id,sum(ry积分) ry积分 FROM rank_ry_jf where bill_id is not null group by rpt_month,bill_id) b,(SELECT rpt_month,bill_id,sum(js积分) js积分 FROM rank_js_jf where bill_id is not null group by rpt_month,bill_id) c,(SELECT rpt_month,bill_id,sum(cx积分) cx积分 FROM rank_cx_jf where bill_id is not null group by rpt_month,bill_id) d,(SELECT rpt_month,bill_id,sum(xx积分) xx积分 FROM rank_xx_jf where bill_id is not null group by rpt_month,bill_id) e where a.bill_id=b.bill_id(+) and a.bill_id=c.bill_id(+) and a.bill_id=d.bill_id(+) and a.bill_id=e.bill_id(+) and a.rpt_month=b.rpt_month(+) and a.rpt_month=c.rpt_month(+) and a.rpt_month=d.rpt_month(+) and a.rpt_month=e.rpt_month(+)) where bill_id='".$bill_id."' order by rpt_month";
		$gr_det=$jf->query($sqls);
		$this->assign('gr_det',$gr_det);

		$rank="SELECT distinct a.rpt_month,a.bill_id,a.同岗位排名,a.同岗位人数,a.全员赛马积分,a.n FROM 
			(SELECT a.*,floor(同岗位人数/2) n FROM rank_year_info a WHERE bill_id='".$bill_id."' AND substr(rpt_month,1,4)=".$year." AND 同岗位人数<100) a,
			(SELECT rpt_month,bill_id,MAX(全员赛马积分) aa FROM rank_year_info GROUP BY rpt_month,bill_id) b
			WHERE b.bill_id=a.bill_id(+) AND b.rpt_month=a.rpt_month(+)AND aa=全员赛马积分(+) AND a.bill_id IS NOT NULL ORDER BY a.rpt_month";
		$xq=$jf->query($rank);
		$this->assign('xqs',$xq);

		$ryjf="SELECT * FROM rank_ry_jf WHERE bill_id='".$bill_id."' and substr(rpt_month,1,4)=".$year."";
		$jsjf="SELECT * FROM rank_js_jf WHERE bill_id='".$bill_id."' and substr(rpt_month,1,4)=".$year."";
		$cxjf="SELECT * FROM rank_cx_jf WHERE bill_id='".$bill_id."' and substr(rpt_month,1,4)=".$year."";
		$xxjf="SELECT * FROM rank_xx_jf WHERE bill_id='".$bill_id."' and substr(rpt_month,1,4)=".$year."";
		$ry=$jf->query($ryjf);
		$js=$jf->query($jsjf);
		$cx=$jf->query($cxjf);
		$xx=$jf->query($xxjf);
		$this->assign('ry',$ry);
		$this->assign('js',$js);
		$this->assign('cx',$cx);
		$this->assign('xx',$xx);
		$this->assign('year',$year);
		$this->display('rank/rank_jf_gr');
	}

	public function rank_unions(){
		$unionsInfo=M('unionsInfo');
		$sql="select * from rank_union";
		$user=$unionsInfo->query($sql);
		$this->assign('user',$user);
		$this->display('rank/rank_unions');
	}

	public function rank_unions_info($id=0){
		$jf=M('unionsInfo');
		$user_name=I('user_name');
		$user_id=I('user_id');
		$sql="SELECT a.*,rank()over(partition by pos_name order by jf desc) pm FROM(SELECT id,user_name,user_id,bill_id,pos_name,sum(hjjf+cyjf) jf FROM(SELECT b.id,a.* FROM rank_unions_info a,rank_unions_px b where a.pos_name=b.name) group by id,user_name,user_id,bill_id,pos_name) a where id=".$id."";
		if(!empty($user_name)){
		  	$sql .= " and a.user_name like '%".$user_name."%'";
		}
		if(!empty($user_id)){
		  	$sql .= " and user_id='".$user_id."'";
		}
		$det=$jf->query($sql);
		$this->assign('det',$det);
		$this->display('rank/rank_unions_info');
	}

	public function rank_unions_user_info($bill_id=''){
		$unionsInfo=M('unionsInfo');
		$sql="SELECT a.*,rank()over(partition by pos_name order by jf desc) pm FROM(SELECT user_name,user_id,bill_id,pos_name,sum(hjjf+cyjf) jf FROM rank_unions_info group by user_name,user_id,bill_id,pos_name) a where bill_id='".$bill_id."'";
		$gr=$unionsInfo->query($sql);
		$this->assign('gr',$gr);
		$sql_det="select * from rank_unions_info where bill_id='".$bill_id."'";
		$gr_det=$unionsInfo->query($sql_det);
		$this->assign('gr_det',$gr_det);
		$this->display('rank/rank_unions_user_info');
	}

	public function rank_jm(){
		$rank_config = M('config');
		$sec_type = I('sec_type');
		$sql="SELECT a.*,nvl(p1,'-') p1,nvl(p2,'-') p2,nvl(p3,'-') p3,nvl(p4,'-') p4,nvl(p5,'-') p5 FROM rank_config a,(SELECT config_id,max(CASE WHEN rn=1 THEN county_name||'\'||user_name END) p1,max(CASE WHEN rn=2 THEN county_name||'\'||user_name END) p2,max(CASE WHEN rn=3 THEN county_name||'\'||user_name END) p3,max(CASE WHEN rn=4 THEN county_name||'\'||user_name END) p4,max(CASE WHEN rn=5 THEN county_name||'\'||user_name END) p5 FROM(SELECT a.*,row_number()over (partition BY config_id order by amount desc) rn FROM rank_jm a) GROUP BY config_id) b WHERE a.id=b.config_id(+) and month=201610 and sec_type_id='".$sec_type."' order by des";
		$datas = $rank_config->query($sql);
		$this->ajaxReturn($datas);
	}

		public function rank_hm(){
		$rank_config = M('config');
		$sec_type = I('sec_type');
		$sql="SELECT a.*,nvl(p1,'-') p1,nvl(p2,'-') p2,nvl(p3,'-') p3,nvl(p4,'-') p4,nvl(p5,'-') p5 FROM rank_config a,(SELECT config_id,max(CASE WHEN rn=1 THEN county_name||'\'||user_name END) p1,max(CASE WHEN rn=2 THEN county_name||'\'||user_name END) p2,max(CASE WHEN rn=3 THEN county_name||'\'||user_name END) p3,max(CASE WHEN rn=4 THEN county_name||'\'||user_name END) p4,max(CASE WHEN rn=5 THEN county_name||'\'||user_name END) p5 FROM(SELECT a.*,row_number()over (partition BY config_id order by amount desc) rn FROM rank_hm a) GROUP BY config_id) b WHERE a.id=b.config_id(+) and month=201610 and sec_type_id='".$sec_type."' order by des";
		$datas = $rank_config->query($sql);
		$this->ajaxReturn($datas);
	}

		public function rank_qlm(){
		$rank_config = M('config');
		$sec_type = I('sec_type');
		$sql="SELECT a.*,nvl(p1,'-') p1,nvl(p2,'-') p2,nvl(p3,'-') p3,nvl(p4,'-') p4,nvl(p5,'-') p5 FROM rank_config a,(SELECT config_id,max(CASE WHEN rn=1 THEN county_name||'\'||user_name END) p1,max(CASE WHEN rn=2 THEN county_name||'\'||user_name END) p2,max(CASE WHEN rn=3 THEN county_name||'\'||user_name END) p3,max(CASE WHEN rn=4 THEN county_name||'\'||user_name END) p4,max(CASE WHEN rn=5 THEN county_name||'\'||user_name END) p5 FROM(SELECT a.*,row_number()over (partition BY config_id order by score desc,user_name) rn FROM rank_qlm a) GROUP BY config_id) b WHERE a.id=b.config_id(+) and month=201610 and sec_type_id='".$sec_type."' order by des";
		$datas = $rank_config->query($sql);
		$this->ajaxReturn($datas);
	}

	public function rank_jm_info($id=1001){
		$userInfo = M('userInfo');
		$rankConfig = M('config');
		$config = $rankConfig->find($id);
		$this->assign('config',$config);

		$sql="SELECT b.cnt zan,a.* FROM rank_jm a,rank_user_zan b WHERE a.bill_id=b.id(+) AND a.config_id=".$id." ORDER BY a.amount DESC";
		$users = $userInfo->query($sql);
		$this->assign('users',$users);
		$this->new_left();
		$this->display('rank/rank_jm_info');
	}

	public function rank_hm_info($id=1001){
		$userInfo = M('userInfo');
		$rankConfig = M('config');
		$config = $rankConfig->find($id);
		$this->assign('config',$config);

		$sql="SELECT b.cnt zan,a.* FROM rank_hm a,rank_user_zan b WHERE a.bill_id=b.id(+) AND a.config_id=".$id." ORDER BY a.amount DESC";
		$users = $userInfo->query($sql);
		$this->assign('users',$users);
		$this->new_left();
		$this->display('rank/rank_hm_info');
	}

	public function rank_qlm_info($id=1001){
		$userInfo = M('userInfo');
		$rankConfig = M('config');
		$config = $rankConfig->find($id);
		$this->assign('config',$config);

		$sql="SELECT b.cnt zan,a.* FROM rank_qlm a,rank_user_zan b WHERE a.bill_id=b.id(+) AND a.config_id=".$id." ORDER BY a.score DESC,user_name";
		$counts = $userInfo->query($sql);
		$this->assign('counts',$counts);
		$users = parent::listsSql($sql,$name,$countyName);
		$this->assign('users',$users);
		$this->new_left();
		$this->display('rank/rank_qlm_info');
	}

	public function rank_pc(){
		$bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$lead=M('lead');
		$power=$lead->where("gen='jpg' and bill_id='".$bill_id."'")->find();
		$this->assign('pow',$power);

		$jf=M('year');
		$sql_gr="SELECT a.config_id,b.* FROM rank_jf_px a,(SELECT 单位,max(p1) p1,max(p2) p2,max(p3) p3,max(p4) p4,max(p5) p5 FROM(SELECT 单位,CASE WHEN rn=1 THEN 姓名 END p1,CASE WHEN rn=2 THEN 姓名 END p2,CASE WHEN rn=3 THEN 姓名 END p3,CASE WHEN rn=4 THEN 姓名 END p4,CASE WHEN rn=5 THEN 姓名 END p5 FROM(SELECT * FROM(SELECT a.*,row_number()over(partition BY 单位 order by nvl(cai,0)-nvl(zan,0),bill_id ) rn FROM (SELECT a.cnt zan,a.cnc cai,b.* FROM rank_jf_zan a,(SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0) 个人总积分 FROM (SELECT 月份,员工编号,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分 FROM rank_pc_info GROUP BY 月份,个人绩效积分,员工编号,单位,姓名,bill_id,竞赛积分,荣誉积分) a GROUP BY 员工编号,单位,姓名,bill_id) b where b.bill_id=a.id(+)) a) WHERE rn<6)) where 单位 is not null GROUP BY 单位) b WHERE a.单位(+)=b.单位 AND config_id>110 ORDER BY a.config_id";
		$gr=$jf->query($sql_gr);
		$this->assign('gr',$gr);
		$this->display('rank/rank_pc');
	}

	public function rank_pc_info($id=''){
		$jf=M('year');
		$user_name=I('user_name');
		$user_id=I('user_id');
		$sql_gr="SELECT a.*,rank()over(partition BY 单位 order by nvl(cai,0)-nvl(zan,0)) pm FROM (SELECT a.cnt zan,a.cnc cai,b.*,c.bill_id cc FROM rank_jf_zan a,(SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0) 个人总积分 FROM (SELECT 月份,员工编号,config_id,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分 FROM (SELECT b.config_id,a.* FROM rank_pc_info a, rank_jf_px b WHERE a.单位=b.单位(+)) GROUP BY 月份,个人绩效积分,员工编号,config_id,单位,姓名,bill_id,竞赛积分,荣誉积分) a WHERE a.config_id=".$id." GROUP BY 员工编号,单位,姓名,bill_id) b,rank_party_member c where b.bill_id=a.id(+) AND b.bill_id=c.bill_id(+)) a where 1=1";
		  if(!empty($user_name)){
		  	$sql_gr .= " and 姓名 like '%".$user_name."%'";
		  }
		  if(!empty($user_id)){
		  	$sql_gr .= " and 员工编号='".$user_id."'";
		  }
		  $sql_gr .=" order by pm,bill_id";
		$gr=$jf->query($sql_gr);
		$this->assign('gr',$gr);
		$this->display('rank/rank_pc_info');

	}

	public function rank_pc_line($bill_id='',$id=0){
		$jf=M('year');
		$partyMember=M('partyMember');
		$dy=$partyMember->where('bill_id='.$bill_id)->find();
		$this->assign('dy',$dy);
		$sql="SELECT * FROM(SELECT a.*,rank()over(partition BY 单位 order by nvl(cai,0)-nvl(zan,0)) pm FROM (SELECT a.cnt zan,a.cnc cai,b.* FROM rank_jf_zan a,(SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0) 个人总积分 FROM (SELECT 月份,员工编号,config_id,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分 FROM (SELECT b.config_id,a.* FROM rank_pc_info a, rank_jf_px b WHERE a.单位=b.单位(+)) GROUP BY 月份,个人绩效积分,员工编号,config_id,单位,姓名,bill_id,竞赛积分,荣誉积分) a  GROUP BY 员工编号,单位,姓名,bill_id) b where b.bill_id=a.id(+)) a order by pm,bill_id) WHERE bill_id='".$bill_id."'";
		$gr=$jf->query($sql);
		$this->assign('gr',$gr);

		$sql_count="SELECT COUNT(*) 总人数 FROM(SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0) 个人总积分 FROM (SELECT 月份,员工编号,config_id,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分 FROM (SELECT b.config_id,a.* FROM rank_pc_info a, rank_jf_px b WHERE a.单位=b.单位(+) AND b.config_id=".$id.") GROUP BY 月份,个人绩效积分,员工编号,config_id,单位,姓名,bill_id,竞赛积分,荣誉积分) a  GROUP BY 员工编号,单位,姓名,bill_id)";
		$count=$jf->query($sql_count);
		$this->assign('count',$count);

		$sql1="SELECT a.*,row_number()over(order by create_date) rank FROM (SELECT * FROM rank_zan_det where id='".$bill_id."' and 区别=1) a";
		$gr_det=$jf->query($sql1);
		$this->assign('gr_det',$gr_det);
		$sql2="SELECT a.*,row_number()over(order by create_date) rank FROM (SELECT * FROM rank_zan_det where id='".$bill_id."' and 区别=2) a";
		$gr_det2=$jf->query($sql2);
		$this->assign('gr_det2',$gr_det2);
		$this->display('rank/rank_pc_line');
	}

	public function rank_jf_zan($bill_id=0){
		$json['flag']=false;
		$json['msg']='点赞失败';
		$model = M('jfZan');
		if($bill_id!=0){
			$zan = $model->where("id='".$bill_id."' and status=1")->find();
			if($zan){
				$re = $model->where("id='".$bill_id."'")->setInc('cnt',1);
				if($re){
					$json['flag']=true;
					$json['msg']='点赞成功';
				}else{
					$json['msg']='更新失败';
				}
			}else{
				$data['id']=$bill_id;
				$data['cnt']=1;
				$data['status']=1;
				$data['cnc']=0;
				$re = $model->add($data);
				if($re){
					$json['flag']=true;
					$json['msg']='点赞成功';
				}else{
					$json['msg']='添加失败';
				}
			}
		}else{
			$json['msg']='目标用户信息获取失败';
		}
		$this->ajaxReturn($json);
	}

	public function rank_jf_cai($bill_id=0){
		$json['flag']=false;
		$json['msg']='吐槽失败';
		$model = M('jfZan');
		if($bill_id!=0){
			$zan = $model->where("id='".$bill_id."' and status=1")->find();
			if($zan){
				$re = $model->where("id='".$bill_id."'")->setInc('cnc',1);
				if($re){
					$json['flag']=true;
					$json['msg']='吐槽成功';
				}else{
					$json['msg']='更新失败';
				}
			}else{
				$data['id']=$bill_id;
				$data['cnt']=0;
				$data['status']=1;
				$data['cnc']=1;
				$re = $model->add($data);
				if($re){
					$json['flag']=true;
					$json['msg']='吐槽成功';
				}else{
					$json['msg']='添加失败';
				}
			}
		}else{
			$json['msg']='目标用户信息获取失败';
		}
		$this->ajaxReturn($json);
	}

	public function rank_jf_det($bill_id=''){
		parent::isLogin();
		$where = " and oper_login_code='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
		parent::loginUser($where);
		$oa=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$user_name=$_SESSION['user_auth']['OPER_NAME'];
		$m = M('zanDet');
		$time=date('Ymd',time());
		$sql1="select * from rank_zan_det where bill_id='".$oa."' and 区别=1 order by create_date desc";
		$det1=$m->query($sql1);
		$sql2="select * from rank_zan_det where bill_id='".$oa."' and 区别=2 order by create_date desc";
		$det2=$m->query($sql2);

		if(IS_POST){
			$json['success']=false;
			$json['msg']='保存错误';
			$dzan = I('dzan');
			$data['id']=$bill_id;
			$data['cnt_det']=$dzan;
			$data['bill_id']=$oa;
			$data['user_name']=$user_name;
			$data['区别']=1;
			if($det1[0]['BILL_ID']==$oa && date('Ymd',strtotime($det1[0]['CREATE_DATE']))==$time){
				$json['msg']=1;
				$json['success']=true;
			}else{
				$re = $m->orcAdd('rank_zan_det',$data);
				if($re){
					$json['success']=true;
					$json['msg']='保存成功';
				}
			}
			$this->ajaxReturn($json);
		}else if(IS_GET){
			$json['success']=false;
			$json['msg']='保存错误';
			$tcao = I('tcao');
			$data['id']=$bill_id;
			$data['cnt_det']=$tcao;
			$data['bill_id']='';
			$data['user_name']='';
			$data['区别']=2;
			$a['id']=$bill_id;
			$a['cnt_det']='';
			$a['bill_id']=$oa;
			$a['user_name']=$user_name;
			$a['区别']=0;
			if($det2[0]['BILL_ID']==$oa && date('Ymd',strtotime($det2[0]['CREATE_DATE']))==$time){
				$json['msg']=1;
				$json['success']=true;
			}else{
				$re = $m->orcAdd('rank_zan_det',$data);
				$rr = $m->orcAdd('rank_cai_det',$a);
				if($re){
					$json['success']=true;
					$json['msg']='保存成功';
				}
			}
			$this->ajaxReturn($json);
		}else{
			$json['msg']='有问题';
		}
	}

	public function rank_jf_sc(){
		$this->display('rank/rank_jf_sc');
	}




	//导入
	public function lead(){
		$bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$lead=M('lead');
		$power=$lead->where("gen='jpg' and bill_id='".$bill_id."'")->find();
		$this->assign('pow',$power);
		$this->display('rank/lead');
	}

	public function img($id=''){
		if($_FILES['file_img']['name'][0] == ""){
			$this->error('请选择上传的图片！');
		}else{
			if($id<2000){
				for($i=0;$i<count($_FILES['file_img']['name']);$i++){
				    $tmp_file = $_FILES['file_img']['tmp_name'][$i];
				    $file_types = explode ( ".", $_FILES['file_img']['name'][$i]);
				    //$file_type = $file_types [count ( $file_types ) - 1];
				    $file_type=$file_types[1];
		     	    $str = $file_types[0];
				    if($file_type != "jpg"){
				    	$this->error($str .'格式不正确或者后缀大写，重新上传');
				    }
					if(strlen($str)=="11"){
				    	$file_name = $str . "." . $file_type;
					}else{
						$this->error($str.'位数不对，手机号码为11位！');
					}
					/*设置上传路径*/
				     //$savePath = '//10.78.1.85/apache-tomcat-7.0.42/webapps/ROOT/upload/headImg/';
				     $savePath='//10.78.1.85/www/headImg/';
					/*是否上传成功*/
				    if (! copy ( $tmp_file, $savePath . $file_name )){
				        $this->error ( '上传失败！' );
				    }
				}
			}else{
				for($i=0;$i<count($_FILES['file_img']['name']);$i++){
				    $tmp_file = $_FILES['file_img']['tmp_name'][$i];
				    $file_types = explode ( ".", $_FILES['file_img']['name'][$i]);
				    //$file_type = $file_types [count ( $file_types ) - 1];
				    $file_type=$file_types[1];
		     	    $strs = $file_types[0];
		     	    $str=iconv("utf-8", "GB2312", $strs);
				    if(strtolower($file_type) != "jpg"){
				    	$this->error($str .'格式不正确，重新上传');
				    }
				    $file_name = $str . "." . $file_type;
					//设置上传路径
				     //$savePath = '//10.78.1.85/apache-tomcat-7.0.42/webapps/ROOT/upload/headImg/';
				     $savePath='//10.78.1.85/www/headImg/';
					//是否上传成功
				    if (! copy ( $tmp_file, $savePath . $file_name )){
				        $this->error ( '上传失败！' );
				    }
				}
			}
		    $this->success('上传成功！');
		}
	}

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
		for ($row = 2; $row <= $highestRow; $row++) { 
			for ($col = 0; $col < $highestColumnIndex; $col++) { 
                 $excelData[$row][] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
           } 
        } 
        return $excelData; 
	} 

	public function file(){
		$unionsInfo=M('unionsInfo');
		if (! empty ( $_FILES ['file_stu'] ['name'] )){
		    $tmp_file = $_FILES ['file_stu'] ['tmp_name'];
		    $file_types = explode ( ".", $_FILES ['file_stu'] ['name'] );
		    $file_type = $file_types [count ( $file_types ) - 1];
		    //$str = $file_types [count ( $file_types ) - 2];
		     /*判别是不是.xls文件，判别是不是excel文件*/
		    if (strtolower ( $file_type ) != "xls" && strtolower ( $file_type ) != "xlsx"){
		        $this->error ( '不是Excel文件，重新上传' );
		    }
		    /*设置上传路径*/
		    $savePath = '//10.78.1.85/www/ranking/Public/upfile/Execl/';
		    /*以时间来命名上传的文件*/
		    $str = date ( 'h' ); 
		    $file_name = $str . "." . $file_type;
		     /*是否上传成功*/
		    if (! copy ( $tmp_file, $savePath . $file_name )){
		        $this->error ( '上传失败' );
		    }
		  	$res = $this->read ( $savePath . $file_name,$file_type );
		  	$sql="SELECT max(rpt_month) 月份 FROM rank_unions_info";
		  	$info=$unionsInfo->query($sql);
		  	foreach ($res as $k => $v) {
		  		if($v[0]<=$info[0]['月份']){
		  			$this->error('日期格式存在问题');
		  		}
		  	}
		  	$sq="insert into rank_unions_info select '".$res[2][0]."','".$res[2][1]."','".$res[2][2]."','".$res[2][3]."','".$res[2][4]."','".$res[2][5]."','".$res[2][6]."','".$res[2][7]."','".$res[2][8]."','".$res[2][9]."' from dual";
		  	foreach($res as $k => $v){
		  		if($k>2){
		  			$sq .= " union all select '".$v[0]."','".$v[1]."','".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."','".$v[7]."','".$v[8]."','".$v[9]."' from dual";
		  		}
		  	}
		  	$result=$unionsInfo->execute($sq);
		  	if(!$result){
		  		$this->error('导入数据库失败');
		  	}
		    // foreach ( $res as $k => $v ){
		    // 	$sq="insert into rank_unions_info select '".$v[0]."','".$v[1]."','".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."','".$v[7]."','".$v[8]."','".$v[9]."' from dual";
	     //      	//$sqls="insert into rank_unions_info values('".$v[0]."','".$v[1]."','".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."','".$v[7]."','".$v[8]."','".$v[9]."')";
	     //     	$result = $unionsInfo->execute($sq);
	     //     	echo $unionsInfo->getLastSql();
		    // 	exit();
		    //     if (! $result){
		    //         $this->error ('导入数据库失败');
		    //     }
		    // }
		    $this->success('导入成功');
		}else{
			$this->error ( '请选择导入的Execl数据！' );
		}
	}
}

?>