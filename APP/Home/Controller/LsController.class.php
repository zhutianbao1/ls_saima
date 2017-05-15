<?php 
namespace Home\Controller;

	class LsController extends BaseController {
		
		public function lishui(){
			// 85数据库维护或者系统故障维护是调用
			// echo '<title>系统通知</title>';
			// echo '系统维护中...,请稍后';
			// echo "<script>location.href='http://10.78.1.85/lishui/pub.jsp';</script>";
			// die;

			//仿登陆制造用户会话方便参数传递
			$billId = I('billId');
			$oa = I('oa');
			parent::loginByOa($billId,$oa);
			$this->display('NewRank/index2');
		}

		public function ls_lishui(){

			$userInfo=M('userInfo');
			$khjl_sql="SELECT * FROM (SELECT DISTINCT 更新时间, 县市, 客户经理, 客户经理电话, 总分值,排名 FROM LS_PANCM.LS_KJH_客户经理汇总_01 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.LS_KJH_客户经理汇总_01) ORDER BY 总分值 DESC) WHERE Rownum<=12";
			$khjl = $userInfo->query($khjl_sql);
			$this->assign('khjl',$khjl);

			$xsjl_sql="SELECT * FROM (SELECT * FROM LS_KJH_CHNL_MANAGER  WHERE RPT_DATE = (SELECT MAX(RPT_DATE) FROM LS_KJH_CHNL_MANAGER) ORDER BY TOP) WHERE Rownum<=12";
			$xsjl = $userInfo->query($xsjl_sql);
			$this->assign('xsjl',$xsjl);

			$yyy_sql="SELECT * FROM (SELECT distinct a.bill_id,c.* 
        			FROM ls_sys_oper_yyy a ,
			    	(SELECT a.* FROM mz_crm.ls_yy_kpi_oper_rpt_szx a WHERE 数据日期=(SELECT MAX(数据日期) md FROM mz_crm.ls_yy_kpi_oper_rpt_szx)) c 
          			WHERE c.操作人编号=a.oper_id
			    	ORDER BY 全市排名  NULLS LAST) WHERE Rownum<=12";
			$yyy = $userInfo->query($yyy_sql);
			$this->assign('yyy',$yyy);

			$jm_sql="SELECT * FROM rank_jm ORDER BY config_id,rank";
			$jm = $userInfo->query($jm_sql);
			$this->assign('jm',$jm);

			$hm_sql="SELECT * FROM rank_hm ORDER BY config_id,rank";
			$hm = $userInfo->query($hm_sql);
			$this->assign('hm',$hm);

			$qlm_sql="SELECT * FROM rank_qlm ORDER BY config_id,rank";
			$qlm = $userInfo->query($qlm_sql);
			$this->assign('qlm',$qlm);


			$gr_sql="SELECT * FROM(select * from ls_mz.t_jfl_notice_bf where notice_type=221 order by no_create_date DESC) WHERE Rownum<=3";
			$gr = $userInfo->query($gr_sql);
			$this->assign('gr',$gr);
			$td_sql="SELECT * FROM(select * from ls_mz.t_jfl_notice_bf where notice_type=222 order by no_create_date DESC) WHERE Rownum<=3";
			$td = $userInfo->query($td_sql);
			$this->assign('td',$td);

			$this->display('t/lishui');
		}
		

		public function ls_jm(){
			$jm_sql="SELECT * FROM rank_jm ORDER BY config_id,rank";
			$jm = parent::listsSqlByls($jm_sql,24);
			$this->assign('jm',$jm);
			$this->display('t/ls_jm');
		}

		public function ls_hm(){
			$rankHm=M('hm');
			$hm_sql="SELECT * FROM rank_hm ORDER BY config_id,rank";
			$hm = $rankHm->query($hm_sql);
			$this->assign('hm',$hm);
			$this->display('t/ls_hm');
		}

		public function ls_qlm(){
			$qlm_sql="SELECT * FROM rank_qlm ORDER BY config_id,rank";
			$qlm =parent::listsSqlByls($qlm_sql,24);
			$this->assign('qlm',$qlm);
			$this->display('t/ls_qlm');
		}

		public function ls_khjl(){
			$userInfo=M('userInfo');
			$user_name=I('user_name');
			$sql="SELECT DISTINCT 更新时间, 县市, 客户经理, 客户经理电话, 总分值,排名 FROM LS_PANCM.LS_KJH_客户经理汇总_01 WHERE 更新时间 =(SELECT MAX(更新时间) FROM LS_PANCM.LS_KJH_客户经理汇总_01)";
			if(!empty($user_name)){
				$sql .= " and 客户经理 LIKE '%".$user_name."%'";
			}
			$sql.=" ORDER BY 总分值 DESC";
			
			$user = parent::listsSqlByls($sql);
			$this->assign('user',$user);
			$this->display('t/ls_khjl');
		}

		public function ls_xsjl(){
			$userInfo=M('userInfo');
			$user_name=I('user_name');
			$sql="SELECT * FROM LS_KJH_CHNL_MANAGER  WHERE RPT_DATE = (SELECT MAX(RPT_DATE) FROM LS_KJH_CHNL_MANAGER)";
			if(!empty($user_name)){
				$sql .= " and manager_name LIKE '%".$user_name."%'";
			}
			$sql.=" ORDER BY TOP";
			//$user = $userInfo->query($sql);
			
			$user = parent::listsSqlByls($sql);
			$this->assign('user',$user);
			$this->display('t/ls_xsjl');
		}

		public function ls_yyy(){
			$userInfo=M('userInfo');
			$user_name=I('user_name');
			$sql="SELECT distinct a.bill_id,c.* 
        			FROM ls_sys_oper_yyy a ,
			    	(SELECT a.* FROM mz_crm.ls_yy_kpi_oper_rpt_szx a WHERE 数据日期=(SELECT MAX(数据日期) md FROM mz_crm.ls_yy_kpi_oper_rpt_szx)) c 
          			WHERE c.操作人编号=a.oper_id";
			if(!empty($user_name)){
				$sql .= " and 营业员 LIKE '%".$user_name."%'";
			}
			$sql.=" ORDER BY 全市排名 NULLS LAST";
			//$user = $userInfo->query($sql);
			//mz_crm.ls_yy_kpi_oper_rpt_szx
			
			$user = parent::listsSqlByls($sql);
			$this->assign('user',$user);


			$sql_county="SELECT 县市名称,县市编号 code,县市人数,综合得分,全市排名 FROM ls_yy_kpi_county_rpt_szx WHERE 数据日期= (SELECT MAX(数据日期) FROM ls_yy_kpi_county_rpt_szx) order by 全市排名";
			$cr=$userInfo->query($sql_county);
			$this->assign('cr',$cr);

			$sql_org="select * from (SELECT 县市名称,渠道名称,渠道人数,综合得分,rownum rid FROM ls_yy_kpi_org_rpt_szx where 数据日期= (SELECT MAX(数据日期) FROM ls_yy_kpi_org_rpt_szx) ORDER BY 综合得分 desc) where rid<=10";
			$org=$userInfo->query($sql_org);
			$this->assign('org',$org);
			$this->display('t/ls_yyy');
		}

		public function lc_table(){
			$m=M();
			$org_id=I('org_id');
			$month=I('month');
			$countyName=I('countyName');
			$userName=I('userName');
			$org_name=I('org_name');
			$sql="SELECT 渠道编号,渠道名称,县市名称,销售经理名称,sett_month 月份,合计/100 总酬金 FROM ls_chenh.mz_shqd_chudian_kpi_list a,RWD_ITF_ORG_578_201604 b WHERE a.渠道编号=b.org_id(+) AND sett_month  IS NOT NULL";
			if(!empty($org_id)){
				$sql .=" and a.渠道编号='".$org_id."'";
			}
			if(!empty($month)){
				$sql .=" and b.sett_month='".$month."'";
			}
			if(!empty($countyName)){
				$sql .=" and a.县市名称='".$countyName."'";
			}
			if(!empty($userName)){
				$sql .=" and a.销售经理名称 like'%".$userName."%'";
			}
			if(!empty($org_name)){
				$sql .=" and a.渠道名称 like '%".$org_name."%'";
			}
			//$t=$m->query($sql);
			$t=parent::listsSql($sql,'','');
			$this->assign('t',$t);
			$this->display('t/lc_table');
		}
		
		public function finance($orgid='',$month=''){
			$userInfo=M('userInfo');
			$tem_sql="SELECT * FROM ls_chenh.mz_shqd_chudian_temp3 WHERE org_id='".$orgid."' and 对应关系 NOT LIKE '%终端%'";
			$tem=$userInfo->query($tem_sql);
			$this->assign('tem',$tem);

			$tem_sql="SELECT * FROM ls_chenh.mz_shqd_chudian_temp3 WHERE org_id='".$orgid."' and 对应关系 LIKE '%终端%'";
			$tem1=$userInfo->query($tem_sql);
			$this->assign('tem1',$tem1);

			$kpi_sql="SELECT * FROM ls_chenh.mz_shqd_chudian_kpi_list WHERE 渠道编号=".$orgid." ";
			$kpi=$userInfo->query($kpi_sql);
			$this->assign('kpi',$kpi);

			$kpi_count="SELECT substr(column_name,3) 本月金额, column_name 上月金额,substr(column_name,5)max FROM all_tab_columns WHERE Table_Name='RWD_ITF_ORG_578_201604' AND column_name LIKE '%酬金%' AND column_name LIKE '%上月%' ORDER by column_name";
			$con=$userInfo->query($kpi_count);
			$this->assign('con',$con);

			$m_sql="SELECT distinct a.*,c.终端分成酬金,c.宽带酬金,c.流量酬金,c.奖金池酬金,c.亲情网酬金,c.虚拟网酬金,c.专营激励酬金,c.改套餐酬金,c.其他酬金,c.合计 同区最高,b.同区同星级最高 FROM
				(SELECT a.片区编号,b.* FROM ls_chenh.mz_shqd_chudian_kpi_list a,RWD_ITF_ORG_578_201604 b WHERE a.渠道编号=b.org_id(+) AND a.渠道编号=".$orgid.") a,        
				(SELECT 片区编号,MAX(to_number(合计)) 同区同星级最高 FROM ls_chenh.mz_shqd_chudian_kpi_list a,RWD_ITF_ORG_578_201604 b WHERE a.渠道编号=b.org_id(+) AND a.片区编号=(SELECT 片区编号 FROM ls_chenh.mz_shqd_chudian_kpi_list WHERE 渠道编号='".$orgid."') AND a.星级=(SELECT 星级 FROM ls_chenh.mz_shqd_chudian_kpi_list WHERE 渠道编号='".$orgid."')GROUP BY 片区编号) b,
				(SELECT * FROM (SELECT a.片区编号,b.合计,b.计算终端分成酬金 终端分成酬金,b.计算宽带酬金 宽带酬金,b.计算流量酬金 流量酬金,b.计算奖金池酬金 奖金池酬金,b.计算亲情网酬金 亲情网酬金,b.计算虚拟网酬金 虚拟网酬金,b.计算专营激励酬金 专营激励酬金,b.计算改套餐酬金 改套餐酬金,b.计算其他酬金 其他酬金,rank()over(partition BY 片区编号 order by to_number(合计) desc nulls last) pm FROM ls_chenh.mz_shqd_chudian_kpi_list a,RWD_ITF_ORG_578_201604 b WHERE a.渠道编号=b.org_id(+) AND 片区编号=(SELECT 片区编号 FROM ls_chenh.mz_shqd_chudian_kpi_list WHERE 渠道编号='".$orgid."')) WHERE pm=1) c
				WHERE a.片区编号=b.片区编号(+) AND a.片区编号=c.片区编号(+)AND sett_month=".$month."";
			$money=$userInfo->query($m_sql);
			$this->assign('money',$money);
			$this->display('t/finance');
		}
		

		public function wid_khjl(){
			$ls_kh=M('LS_PANCM.LS_KJH_客户经理汇总_01');
			$wid_sql="SELECT Column_name FROM all_tab_columns WHERE Table_Name='LS_KJH_客户经理汇总_01'";
			$khjl=$ls_kh->query($wid_sql);
			$this->assign('khjl',$khjl);
			$this->display('t/wid_khjl');
		}

		public function wid_xsjl(){
			$ls_kh=M('LS_KJH_CHNL_MANAGER');
			$wid_sql="SELECT * FROM (SELECT Column_name,rownum rn FROM all_tab_columns WHERE Table_Name='LS_KJH_CHNL_MANAGER') WHERE rn<8 or rn>57";
			$xsjl=$ls_kh->query($wid_sql);
			$this->assign('xsjl',$xsjl);
			$this->display('t/wid_xsjl');

		}

		public function wid_jj(){
			$this->display('t/wid_jj');
		}
	}
?>