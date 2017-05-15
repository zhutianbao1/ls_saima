<?php

namespace Home\Widget;
use Home\Controller\BaseController;

class NewRankWidget extends BaseController {
	protected function _initialize(){
		parent::intiParamsURL();
	}
	// public function ls_index($month){
	// 	$userInfo=M('userInfo');
	// 	$sql="SELECT * FROM (SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc) rn FROM(SELECT * FROM rank_user_info where rpt_month=".$month." order by config_id) a) where rn<=3";
	// 	$user=$userInfo->query($sql);
	// 	$this->assign('user',$user);

	// 	$sql_con="SELECT a.*,rank()over(partition by fst_type,sec_type_id order by des) a FROM rank_config a where month=".$month." and id !=1039";
	// 	$con=$userInfo->query($sql_con);
	// 	$this->assign('con',$con);
	// 	$this->display('NewRank/ls_index');
	// }

	public function ls_index2($month){
		$userInfo=M('userInfo');
		$oa=$_SESSION['user_auth']['OA'];
		parent::rank_config($month,$oa,3);
		parent::user($month);
		parent::khjl(12);//客户经理
		parent::xsjl(12);//销售经理
		parent::yyy(12);//营业员
		parent::qlm(12);//千里马
		parent::jm(12);//骏马
		parent::hm(12);//黑马
		$this->assign('month',$month);
		$gr_sql="SELECT * FROM(select * from ls_mz.t_jfl_notice_bf where notice_type=221 order by no_create_date DESC) WHERE Rownum<=10";
		$gr = $userInfo->db('LS_READ')->query($gr_sql);
		$this->assign('gr',$gr);
		$td_sql="SELECT * FROM(select * from ls_mz.t_jfl_notice_bf where notice_type=222 order by no_create_date DESC) WHERE Rownum<=10";
		$td = $userInfo->db('LS_READ')->query($td_sql);
		$this->assign('td',$td);
		$labour=M('labour')->db('LS_READ')->distinct(true)->where("bill_id='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'")->find();
		$pup=0;
		if($labour!="" && $labour!=null){
			$pup=1;
		}
		$this->assign('pup',$pup);
		$this->display('NewRank/ls_index2');
	}

	public function rank_follow($num,$month){
		$follow=M('follow');
		$oa=$_SESSION['user_auth']['OA'];
		$f=$follow->where("oa='".$oa."'")->find();
		$sql_my="SELECT min(config_id) con FROM rank_year_info where bill_id='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
		$my=$follow->db('LS_READ')->query($sql_my);
		if(empty($f)){
			if(!empty($my[0]['CON'])){
				$sql="SELECT * FROM
  					(SELECT distinct a.*,b.同岗位排名-a.同岗位排名 上升,a.amount-c.amount 相差 FROM
					(SELECT * FROM rank_year_info where rpt_month=".$month." and 同岗位人数<>'团队类无计算') a,
					(SELECT * FROM rank_year_info where rpt_month=to_number(to_char(add_months(to_date(to_char(".$month."),'yyyymm'),-1),'yyyymm'))) b,
					(SELECT * FROM rank_year_info where rpt_month=".$month." and 同岗位排名=1) c 
				where a.bill_id=b.bill_id(+) and a.config_id=b.config_id(+) and a.config_id=c.config_id(+)) where 同岗位排名<=5 and config_id=".$my[0]['CON']." order by 同岗位排名";
			}else{
				$sql="SELECT * FROM
				(SELECT b.* FROM rank_follow a,
					(SELECT distinct a.*,b.同岗位排名-a.同岗位排名 上升,a.amount-c.amount 相差 FROM
					(SELECT * FROM rank_year_info where rpt_month=".$month." and 同岗位人数<>'团队类无计算') a,
					(SELECT * FROM rank_year_info where rpt_month=to_number(to_char(add_months(to_date(to_char(".$month."),'yyyymm'),-1),'yyyymm'))) b,
					(SELECT * FROM rank_year_info where rpt_month=".$month." and 同岗位排名=1) c 
				where a.bill_id=b.bill_id(+) and a.config_id=b.config_id(+) and a.config_id=c.config_id(+)) b 
				where a.oper_code=b.oper_code(+) and a.config_id=b.config_id(+) and a.bill_id is null order by create_date desc)";
			}
		}else{
			$sql="SELECT * FROM
			(SELECT b.* FROM rank_follow a,
				(SELECT distinct a.*,b.同岗位排名-a.同岗位排名 上升,a.amount-c.amount 相差 FROM
				(SELECT * FROM rank_year_info where rpt_month=".$month." and 同岗位人数<>'团队类无计算') a,
				(SELECT * FROM rank_year_info where rpt_month=to_number(to_char(add_months(to_date(to_char(".$month."),'yyyymm'),-1),'yyyymm'))) b,
				(SELECT * FROM rank_year_info where rpt_month=".$month." and 同岗位排名=1) c 
			where a.bill_id=b.bill_id(+) and a.config_id=b.config_id(+) and a.config_id=c.config_id(+)) b 
			where a.oper_code=b.oper_code(+) and a.config_id=b.config_id(+) and a.oa='".$oa."' order by create_date desc,同岗位排名)";
			if($num!=null){
			 $sql .="where Rownum<=".$num."";
			}
		}
		$fol=$follow->db('LS_READ')->query($sql);
		$this->assign('fol',$fol);
		$this->display('NewRank/rank_follow');
	}

	public function rank_my_info($bill_id,$month){
		$userInfo=M('userInfo');
		$u=$userInfo->where("bill_id='".$bill_id."'")->find();
		//判断是否为赛马人员
		if(!empty($u)){
			//查询人员所在线条编号，多个编号qui最小
			$sql_id="SELECT min(config_id) id FROM rank_year_info where bill_id='".$bill_id."'";
			$id=$userInfo->query($sql_id);
			$this->assign('id',$id);
			//查询人员基本信息、与上月排名对比、与同线条第一相差分数
			$sql="SELECT a.*,b.同岗位排名-a.同岗位排名 上升,a.amount-c.amount 相差 FROM
			(SELECT * FROM rank_year_info where rpt_month=".$month." and bill_id='".$bill_id."' and config_id=".$id[0]['ID'].") a,
			(SELECT * FROM rank_year_info where rpt_month=to_number(to_char(add_months(to_date(to_char(".$month."),'yyyymm'),-1),'yyyymm'))) b,
			(SELECT * FROM rank_year_info where rpt_month=".$month." and 同岗位排名=1) c
			where a.bill_id=b.bill_id(+) 
			and a.config_id=b.config_id(+) 
			and a.config_id=c.config_id(+)";
			//查询人员是否为先锋榜人员
			parent::khjl(12,'',$bill_id);//客户经理
			parent::xsjl(12,'',$bill_id);//销售经理
			parent::yyy(12,'',$bill_id);//营业员
			//查询人员年度赛马积分
			$sql_yjf="SELECT * FROM(SELECT a.*,rank()over(order by amount desc) pm FROM (SELECT bill_id,sum(全员赛马积分) amount FROM rank_year_info where config_id=".$id[0]['ID']." and rpt_month>=201701 group by bill_id) a) where bill_id='".$bill_id."'";
			$year=$userInfo->query($sql_yjf);
			$this->assign('year',$year[0]);
			//查询人员年度成长积分
			$sql_cjf="SELECT a.*,rank()over (partition BY config_id order by 个人总积分 desc) pm FROM(SELECT user_name,oper_code,bill_id,config_id, nvl(SUM(个人绩效积分),0)+nvl(SUM(赛马积分),0)+nvl(SUM(ry积分),0)+nvl(SUM(js积分),0)+nvl(SUM(cx积分),0)+nvl(SUM(xx积分),0)个人总积分 FROM(SELECT a.*,b.ry积分,c.js积分,d.cx积分,e.xx积分 FROM(SELECT rpt_month,config_id,user_name,bill_id,oper_code,max(赛马积分) 赛马积分,个人绩效积分 FROM rank_chengzhangjf_info where rpt_month>=201701 and config_id<200 group by rpt_month,config_id,user_name,bill_id,oper_code,个人绩效积分) a,(SELECT rpt_month,bill_id,sum(ry积分) ry积分 FROM rank_ry_jf where bill_id is not null group by rpt_month,bill_id) b,(SELECT rpt_month,bill_id,sum(js积分) js积分 FROM rank_js_jf where bill_id is not null group by rpt_month,bill_id) c,(SELECT rpt_month,bill_id,sum(cx积分) cx积分 FROM rank_cx_jf where bill_id is not null group by rpt_month,bill_id) d,(SELECT rpt_month,bill_id,sum(xx积分) xx积分 FROM rank_xx_jf where bill_id is not null group by rpt_month,bill_id) e where a.bill_id=b.bill_id(+) and a.bill_id=c.bill_id(+) and a.bill_id=d.bill_id(+) and a.bill_id=e.bill_id(+) and a.rpt_month=b.rpt_month(+) and a.rpt_month=c.rpt_month(+) and a.rpt_month=d.rpt_month(+) and a.rpt_month=e.rpt_month(+)) group by user_name,oper_code,bill_id,config_id) a where bill_id='".$bill_id."'";
			$cjf=$userInfo->query($sql_cjf);
			$this->assign('cjf',$cjf[0]);
			//查询千里马中条线最低分
			$sql_qlm="SELECT * FROM(SELECT config_id,min(score) score FROM rank_qlm group by config_id) where config_id=".$id[0]['ID']."";
			$qlm=$userInfo->query($sql_qlm);
			$this->assign('qlm',$qlm[0]);
		}else{
			$sql="select * from rank_jifen_bill_id where bill_id='".$bill_id."'";
		}
		$my_info=M()->db('LS_READ')->query($sql);
		$this->assign('my_info',$my_info[0]);
		if($my_info[0]['CONFIG_ID']==NULL){
			$sql_line="select rpt_month,amount pm,'".$my_info[0]['姓名']."' user_name from rank_year_info where user_name is null and config_id is null order by rpt_month";
		}else{
			$sql_line="SELECT * FROM(SELECT a.*,row_number()over(order by rpt_month desc nulls last) rn FROM (SELECT a.* FROM(SELECT a.*,rank()over(partition by rpt_month,config_id order by amount desc nulls last) pm FROM rank_user_info a order by rpt_month) a,(SELECT * FROM rank_user_info where bill_id='".$bill_id."' and rpt_month=".$month.") b where a.bill_id=b.bill_id and a.config_id=b.config_id and a.config_id=".$id[0]['ID']." order by a.county_name,a.rpt_month) a order by rpt_month) where rn<=10";
		}
		$lines = M()->db('LS_READ')->query($sql_line);
		$this->assign('lines',$lines);
		$this->display('NewRank/rank_my_info');
	}

	public function widget_kjh_khjl(){
		$this->display('NewRank/widget_kjh_khjl');
	}

	public function widget_kjh_xsjl(){
		$this->display('NewRank/widget_kjh_xsjl');
	}

	public function widget_kjh_yyy(){
		$this->display('NewRank/widget_kjh_yyy');
	}
}