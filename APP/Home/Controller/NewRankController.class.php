<?php

namespace Home\Controller;

class NewRankController extends BaseController {
	protected function _initialize(){
		parent::_initialize();
		parent::viewLog();
		 $id = I('id');
		 if($id){
		 	session('id',$id);
		 }else{
		 	$id = session('id');
		 }
		 $this->assign('id',$id);

		// $billId = I('billId');
		// $oa = I('oa');
		// parent::loginByOa($billId,$oa);
	}

	public function index(){
		$this->display('NewRank/index2');
	}

	public function rank_whole($month){
		if($month=='2016year'){
			parent::rank_config('201612');
			parent::year();
		}else if($month=='2017year'){
			parent::rank_config('201701');
			parent::year('',2017);
		}else{
			parent::rank_config($month);
			parent::user($month);
		}
		parent::viewLog();
		$this->display();
	}
	
	// public function rank_year_whole(){
	// 	parent::rank_config('201612');
	// 	parent::year();
	// 	$this->display('NewRank/rank_whole');
	// }

	public function rank_info($month,$id){
		parent::rank_config($month);
		parent::user($month,$id);
		$sql="insert into rank_config_zan(id,bill_id,oa,ip,cdate) values(".$id.",'".$_SESSION['user_auth']['OPER_LOGIN_CODE']."','".$_SESSION['user_auth']['OA']."','".$_SERVER['REMOTE_ADDR']."',sysdate)";
		$o=M()->execute($sql);
		parent::viewLog();
		$this->display();
	}

	public function rank_year_info($month,$id){
		if($month=='2016year'){
			parent::rank_config('201612');
			parent::year($id);
		}else if($month=='2017year'){
			parent::rank_config('201701');
			parent::year($id,2017);
		}
		$sql="insert into rank_config_zan(id,bill_id,oa,ip,cdate) values(".$id.",'".$_SESSION['user_auth']['OPER_LOGIN_CODE']."','".$_SESSION['user_auth']['OA']."','".$_SERVER['REMOTE_ADDR']."',sysdate)";
		$o=M()->execute($sql);
		parent::viewLog();
		$this->display('NewRank/rank_info');
	}

	public function rank_new($month){
		$oa=$_SESSION['user_auth']['OA'];
		parent::rank_config($month,$oa);
		parent::user($month);
		parent::viewLog();
		$this->display();
	}

	public function rank_user_info($id,$month){
		$userInfo=M('userInfo');
		$bill_id=I('bill_id');
		$sql="SELECT a.*,b.a,b.b FROM(SELECT * FROM rank_user_info where rpt_month=".$month." and config_id=".$id." and bill_id='".$bill_id."') a,(SELECT * FROM(SELECT a.*,b.bill_id b,row_number()over(partition by b.config_id order by b.amount desc nulls last) pm FROM(SELECT config_id,max(amount) a FROM rank_user_info where rpt_month=".$month." group by config_id) a,rank_user_info b where a.config_id=b.config_id and a=b.amount) where pm=1) b where a.config_id=b.config_id(+)";
		$user=$userInfo->db('LS_READ')->cache(true)->query($sql);
		$this->assign('user',$user[0]);

		$sql_line="SELECT * FROM(SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm  
		FROM rank_user_info a WHERE rpt_month>=201701  AND config_id='".$id."' ORDER BY rpt_month DESC) 
		WHERE bill_id='".$bill_id."' ORDER BY county_name,rpt_month";

		$sql_jf="SELECT a.*,b.总积分,b.pm FROM (SELECT * FROM rank_year_info where rpt_month=".$month." and config_id=".$id.") a,(SELECT bill_id,sum(全员赛马积分) 总积分,rank()over(order by sum(全员赛马积分) desc) pm FROM rank_year_info where rpt_month>=201701 and config_id=".$id." group by bill_id) b where a.bill_id=b.bill_id and a.bill_id='".$bill_id."'";
		
		//$lines = $userInfo->db('LS_READ')->cache(true)->query($sql_line);
		$lines = parent::listsSqlByls($sql_line,20);
		$this->assign('lines',$lines);
		$jf = $userInfo->db('LS_READ')->cache(true)->query($sql_jf);
		$this->assign('jf',$jf[0]);

		$infoHead= parent::getUserCon($id,$month);
		$usera   = parent::getUserData($bill_id,$id,$month);
		$userb   = parent::getUserData($user[0]['B'],$id,$month);
		$json['head']=$infoHead;
		$json['usera']=$usera;
		$json['userb']=$userb;
		$this->assign('json',$json);
		parent::viewLog();
		$this->display();
	}

	public function rank_year_user_info($id,$month){
		$yearInfo=M('yearInfo');
		$bill_id=I('bill_id');
		if($month=="2017year"){
			$sql="SELECT a.*,b.a FROM (SELECT * FROM (SELECT a.*,rank()over(order by amount desc) pm FROM (SELECT county_name,user_name,bill_id,config_id,pos_name,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>=201701 and config_id='".$id."' GROUP BY county_name,user_name,bill_id,config_id,pos_name) a) where bill_id='".$bill_id."') a,(SELECT config_id,max(amount) a FROM (SELECT user_name,bill_id,config_id,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>=201701 GROUP BY user_name,bill_id,config_id) group by config_id) b where a.config_id=b.config_id(+)";

			$sql_line="SELECT * FROM rank_year_info WHERE rpt_month>=201701 and config_id='".$id."' AND bill_id='".$bill_id."' ORDER BY rpt_month";
		}else{
			$sql="SELECT a.*,b.a FROM (SELECT county_name,user_name,bill_id,config_id,pos_name,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>201602 AND rpt_month<=201612 and config_id='".$id."' AND bill_id='".$bill_id."' GROUP BY county_name,user_name,bill_id,config_id,pos_name) a,(SELECT config_id,max(amount) a FROM (SELECT user_name,bill_id,config_id,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>201602 GROUP BY user_name,bill_id,config_id) group by config_id) b where a.config_id=b.config_id(+)";

			$sql_line="SELECT * FROM rank_year_info WHERE rpt_month>201602 AND rpt_month<=201612 and config_id='".$id."' AND bill_id='".$bill_id."' ORDER BY rpt_month";
		}
		$year=$yearInfo->db('LS_READ')->query($sql);
		$this->assign('year',$year[0]);

		$lines = $yearInfo->db('LS_READ')->query($sql_line);
		$this->assign('lines',$lines);
		parent::viewLog();
		$this->display();
	}

	public function rank_user($bill_id,$id){
		$userInfo=M('userInfo');
		$month=I('month');
		parent::getUserCon($id,$month);
		parent::getUserData($bill_id,$id,$month);

		$tag_sql="SELECT rpt_month FROM rank_user_info GROUP BY rpt_month ORDER BY rpt_month DESC";
		$tag=$userInfo->db('LS_READ')->query($tag_sql);
		$this->assign('tag',$tag);
		$this->display();
	}
// 是否关注
	public function fouFL(){
		$oa=$_SESSION['user_auth']['OA'];
		$bill_id=I('bill_id');
		$id=I('id');
		if(($bill_id==null && $id==null) || ($bill_id=="" && $id=="")){
			$follow=M('followLine');
			$datas=$follow->where("oa='".$oa."'")->select();
		}else{
			$follow=M('follow');
			if($bill_id==null || $bill_id==""){
				$datas=$follow->where("oa='".$oa."' and config_id=".$id." ")->select();
			}else{
				$datas=$follow->where("oa='".$oa."' and follow_id='".$bill_id."' and config_id=".$id." ")->find();	
			}
		}
		$this->ajaxReturn($datas);
	}
// 取消关注
	public function delFL(){
		$oa=$_SESSION['user_auth']['OA'];
		$bill_id=I('bill_id');
		$id=I('id');
		$json['flag']=false;
		$json['msg']='取消关注失败';
		if($bill_id==null || $bill_id==""){
			$follow = M('followLine');
			$delete=$follow->where("oa='".$oa."' and config_id=".$id."")->delete();
		}else{
			$follow = M('follow');
			$delete = $follow->where("oa='".$oa."' and follow_id='".$bill_id."' and config_id=".$id." ")->delete();
		}
		if($delete){
			$json['flag']=true;
			$json['msg']='取消关注成功';
		}else{
			$json['msg']='取消关注失败';
		}
		$this->ajaxReturn($json);
	}
// 添加关注
	public function addFL(){
		$oa=$_SESSION['user_auth']['OA'];
		$follow_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$bill_id=I('bill_id');
		$id=I('id');
		$json['flag']=false;
		$json['msg']='关注失败';
		$userInfo=M('userInfo');
		$sql_a="select * from rank_follow_line where oa='".$oa."' and config_id=".$id."";
		$a=$userInfo->db('LS_READ')->query($sql_a);
		if($bill_id==null || $bill_id==""){
			if(!empty($a)){

			}else{
				$sql="insert into rank_follow_line values('".$follow_id."','".$oa."',".$id.",sysdate,1)";
			}
		}else{
			$sql_user="SELECT distinct oper_code FROM rank_user_info where bill_id='".$bill_id."' and config_id=".$id." and rpt_month=201612";
			$user=$userInfo->db('LS_READ')->query($sql_user);
			$oper_code=$user[0]['OPER_CODE'];
			$sql="insert into rank_follow values('".$follow_id."','".$oa."','".$bill_id."','".$oper_code."',".$id.",sysdate,1)";
		}
		$add=$userInfo->execute($sql);
		if($add){
			$json['flag']=true;
			$json['msg']='关注成功';
		}else{
			$json['msg']='关注失败';
		}
		$this->ajaxReturn($json);
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

	public function pk(){
		$bill_ids=I('bill_ids');
		$config_id=I('id');
		$month=I('month');
		$bill_id=explode(',', $bill_ids);
		$infoHead= parent::getUserCon($config_id,$month);
		$usera   = parent::getUserData($bill_id[0],$config_id,$month);
		$userb   = parent::getUserData($bill_id[1],$config_id,$month);
		$json['head']=$infoHead;
		$json['usera']=$usera;
		$json['userb']=$userb;
		$this->assign('json',$json);
		$this->display();
	}

	public function rank_follow_line($month){
		parent::rank_config($month);
		parent::viewLog();
		$this->display();
	}

	public function khjl_my(){
		if(IS_POST){
			$bill_id=I('bill_id');
			$m = M();
			$sql = "select a.*,'/upload/headImg/'||a.客户经理电话||'.jpg' pic from ls_pancm.ls_kjh_客户经理汇总_01 a,mz_user.t_sys_oper b WHERE  a.客户经理电话=b.oper_login_code(+) and a.客户经理电话='".$bill_id."' and 更新时间=(SELECT MAX(更新时间) FROM ls_pancm.ls_kjh_客户经理汇总_01 a)";
			$kjh = $m->db('LS_READ')->query($sql);
			$this->ajaxReturn($kjh[0]);
		}
	}

	public function xsjl_my(){
		$bill_id = I('bill_id');
		$m = M();
		$sql = "select a.*,'/upload/headImg/'||a.bill_id||'.jpg' pic from LS_KJH_CHNL_MANAGER a WHERE a.bill_id='".$bill_id."' and rpt_date=(SELECT MAX(rpt_date) FROM LS_KJH_CHNL_MANAGER a)";
		$kjhs = $m->db('LS_READ')->query($sql);
		$this->ajaxReturn($kjhs[0]);
	}

	public function yyy_my(){
		$bill_id = I('bill_id');
		$m = M();
		$sql = "select to_char(sysdate-2,'yyyy-mm-dd') kpi_date2,a.*,'/upload/headImg/' || b.bill_id || '.jpg' pic,get_county_name(county_code) county_name from 
					   (select a.*,rank()over(partition by kpi_date order by 总得分 desc) s_paiming,
           						   rank()over(partition by kpi_date,county_id order by 总得分 desc) x_paiming from ls_y_kpi_oper_rpt a) a ,
				       (select * from ls_y_kpi_oper where rpt_month=(select max(rpt_month) from ls_y_kpi_oper) ) b 
				       where a.obj_id=b.oper_id
				       and b.bill_id='{$bill_id}'  
				       order by kpi_date desc";
		$yyy = $m->db('LS_READ')->cache(true,1800)->query($sql);
		$this->ajaxReturn($yyy[0]);
	}

	public function rank_xfb1(){
		$this->display();
	}

	public function rank_xfb2(){
		$name=$_GET['user_name'];
		$bill_id=$_GET['bill_id'];
		$khjl=parent::khjl(1200,$name,$bill_id);
		$xsjl=parent::xsjl(1200,$name,$bill_id);
		$yyy=parent::yyy(1200,$name,$bill_id);
		$json['khjl']=$khjl;
		$json['xsjl']=$xsjl;
		$json['yyy']=$yyy;
		$this->ajaxReturn($json);
	}

	public function rank_ryb1(){
		$this->display();
	}

	public function rank_ryb2(){
		$name=$_GET['user_name'];
		$bill_id=$_GET['bill_id'];
		$hm=parent::hm(1200,$name,$bill_id);
		$qlm=parent::qlm(1200,$name,$bill_id);
		$jm=parent::jm(1200,$name,$bill_id);
		$json['qlm']=$qlm;
		$json['jm']=$jm;
		$json['hm']=$hm;
		$this->ajaxReturn($json);
	}

	public function rank_ryb(){
		$lx=I('qlx');
		$name=I('qname');
		$config_id=I('config_id');
		//条线
		$configs = parent::ConfigList();
		foreach ($configs as $key => $val) {
			$rank_configs[$val['NAME']]=$val['ID'];
		}
		$this->assign('rank_configs',$rank_configs);
		
		if($lx=="jm"){
			$jm=parent::jm(1200,$name,$config_id);
		}
		if($lx=="hm"){
			$hm=parent::hm(1200,$name,$config_id);
			if(empty($hm)){
			  	$this->assign('errMsg','本月没有黑马数据');
			  }
		}
		if($lx=="qlm" || $lx==""){
			$qlm=parent::qlm(1200,$name,$config_id);
		}
		$this->display();
	}

	public function rank_xfb(){
		$lx=I('qlx');
		$name=I('qname');
		$bill_id=I('qid');
		if($lx=="yyy"){
			$yyy=parent::yyy(1200,$name,$bill_id);
		}
		if($lx=="xsjl"){
			$xsjl=parent::xsjl(1200,$name,$bill_id);
		}
		if($lx=="khjl" || $lx==""){
			$khjl=parent::khjl(1200,$name,$bill_id);
		}
		$this->display();
	}

	// public function my_info($bill_id){
	// 	$sql="SELECT a.*,b.同岗位排名-a.同岗位排名 上升,a.amount-c.amount 相差,d.总积分,d.总积分-e.max jf FROM
	// 	(SELECT * FROM rank_year_info where rpt_month=201611 and bill_id='13905787004') a,
	// 	(SELECT * FROM rank_year_info where rpt_month=201610) b,
	// 	(SELECT * FROM rank_year_info where rpt_month=201611 and 同岗位排名=1) c,
	// 	(SELECT bill_id,config_id,sum(全员赛马积分) 总积分 FROM rank_year_info group by bill_id,config_id) d,
	// 	(SELECT config_id,max(a) max FROM (SELECT bill_id,config_id,sum(全员赛马积分) a FROM rank_year_info group by bill_id,config_id) group by config_id) e
	// 	where a.bill_id=b.bill_id(+) 
	// 	and a.config_id=b.config_id(+) 
	// 	and a.config_id=c.config_id(+) 
	// 	and a.bill_id=d.bill_id(+) 
	// 	and a.config_id=d.config_id(+)
	// 	and a.config_id=e.config_id(+)";
	// 	$my_info=M()->query($sql);
	// 	$this->ajaxReturn($my_info[0]);
	// 	//$this->assign('my_info',$my_info[0]);
	// }

	// public function rank_xfb(){
	// 	$userInfo=M('userInfo');
	// 	$conuty_name=I('countyName');
	// 	$sql_yyy="SELECT * FROM (SELECT distinct a.bill_id,c.* FROM ls_sys_oper_yyy a ,(SELECT a.* FROM mz_crm.ls_yy_kpi_oper_rpt_szx a WHERE 数据日期=(SELECT MAX(数据日期) md FROM mz_crm.ls_yy_kpi_oper_rpt_szx)) c WHERE c.操作人编号=a.oper_id ORDER BY 全市排名  NULLS LAST) WHERE Rownum<=12";
	// 	$sql_khjl="SELECT * FROM (SELECT DISTINCT 更新时间, 县市, 客户经理, 客户经理电话, 总分值,排名 FROM LS_PANCM.LS_KJH_客户经理汇总_01 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.LS_KJH_客户经理汇总_01) ORDER BY 总分值 DESC) WHERE Rownum<=12";
	// 	$sql_xsjl="SELECT * FROM (SELECT * FROM LS_KJH_CHNL_MANAGER  WHERE RPT_DATE = (SELECT MAX(RPT_DATE) FROM LS_KJH_CHNL_MANAGER) ORDER BY TOP) WHERE Rownum<=12";
	// 	if(!empty($conuty_name)){
	// 		$sql_yyy .=" and 县市名称='".$conuty_name."'";
	// 		$sql_khjl .=" and 县市='".$conuty_name."'";
	// 		$sql_xsjl .=" and conuty_name='".$conuty_name."'";
	// 	}
	// 	$yyy=$userInfo->query($sql_yyy);
	// 	$this->assign('yyy',$yyy);
	// 	$khjl=$userInfo->query($sql_khjl);
	// 	$this->assign('khjl',$khjl);
	// 	$xsjl=$userInfo->query($sql_xsjl);
	// 	$this->assign('xsjl',$xsjl);
	// 	// $a=implode(glue,$yyy);
	// 	// $b=implode(glue,$khjl);
	// 	// $c=implode(glue,$xsjl);
	// 	// $json=$a.",".$b.",".$c;
	// 	// $this->ajaxReturn($json);
	// 	$this->display();
	// }
}