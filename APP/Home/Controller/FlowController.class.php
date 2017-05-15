<?php
namespace Home\Controller;

class FlowController extends BaseController {
	public function index(){
		$m=M();
		$empl_oa=$_SESSION['user_auth']['OA'];
		//dump($bill_id);	

		//总情况
		$sql = "select sum(total_num) total_num,sum(finish_num) finish_num , to_char(round((sum(finish_num)/sum(total_num))*100,2),'fm9990.99')||'%' ratio from ls_flow.flow_uniflow_business_process";
		$list3=$m->query($sql);	
		$this->assign('list3',$list3);	
		
		//审批数量、时长
		$sql = "select count_num,to_char(avg_num,'fm9999990.00') avg_num,rank from ls_flow.flow_uniflow_approver where empl_oa = '".$empl_oa."'";
		$list1=$m->query($sql);
		$this->assign('list1',$list1);

		//本人审批情况（最长、最短、平均）
		$sql="select to_char(round(max(approve_length),2),'fm9999990.99') avg_num from ls_flow.flow_UNIFLOW_APPROVE_list where approver = '".$empl_oa."' ";
		$listl=$m->query($sql);
		$this->assign('listl',$listl);

		$sql="select to_char(min(approve_length),'fm9999990.99') avg_num from ls_flow.flow_UNIFLOW_APPROVE_list where approver = '".$empl_oa."' ";
		$lists=$m->query($sql);
		$this->assign('lists',$lists);

		$sql = "select count(empl_oa) num,to_char(round(avg(count_num),1),'fm9990.9') avg_num from ls_flow.flow_uniflow_approver";
		$list4=$m->query($sql);
		$this->assign('list4',$list4);

		//个人
		$sql="select b.dept_no,b.empl_oa,b.empl_name,b.bill_id,count(a.wiid) count_num,round(avg(
				a.approve_length),2) avg_num
				from ls_flow.flow_UNIFLOW_APPROVE_list a,ls_flow.ls_employee_leave_list b
				where a.approver = b.empl_oa
				group by b.dept_no,b.empl_oa,b.empl_name,b.bill_id";
		$listg=$m->query($sql);	
		$this->assign('$listg',$listg);	

		
		//个人审批倒数前十名
		$sql="select * from (select empl_name,avg_num from ls_flow.flow_uniflow_approver order by avg_num desc ) where rownum <= 10 ";
		$list2=$m->query($sql);
		$this->assign('list2',$list2);
		//dump($list1);		


		//单位
		$sql="select b.dept_no,count(a.wiid) as count_num,round(avg(a.approve_length),2) as avg_num
				from ls_flow.flow_UNIFLOW_APPROVE_list a,ls_flow.ls_employee_leave_list b
				where a.approver = b.empl_oa
				group by b.dept_no having dept_no != '公司领导' order by avg(a.approve_length)";
		$listd=$m->query($sql);	
		$this->assign('listd',$listd);

		//流程
		$sql = "select \"subject\" subject,avg_time from  (select * from ls_flow.flow_uniflow_business_process where finish_num >5 order by avg_time ) where rownum <= 10";
		$list5=$m->query($sql);	
		$this->assign('list5',$list5);

		//审批节点
		$sql = "select * from  (select * from ls_flow.flow_uniflow_approve_node order by avg_num desc ) where rownum <=20 ";
		$list6=$m->query($sql);	
		$this->assign('list6',$list6);

		$this->display();

		
		}

}

