<?php 
namespace Home\Controller;

	class BenefitController extends BaseController {
		
		public function index(){
			$this->user();
			$this->display();
		}

		public function rank_fine($bill_id){
			$benefitInfo=M('benefitInfo');
			$this->user($bill_id);
			$sql_s="SELECT b.* FROM rank_benefit_info a,rank_benefit_fine b where a.user_name=b.user_name and a.user_name=(SELECT 姓名 FROM rank_jifen_bill_id where bill_id='".$bill_id."')";
			$fine=$benefitInfo->query($sql_s);
			$this->assign('fine',$fine);
			$this->display();
		}


		public function user($bill_id){
			$benefitInfo=M('benefitInfo');
			$sql="SELECT * FROM rank_benefit_info a,(SELECT b.* FROM rank_jf_px a,rank_jifen_bill_id b where a.单位=b.部门名称 and a.config_id>110) b where a.user_name=b.姓名(+)";
			if(!empty($bill_id)){
				$sql.=" and b.bill_id='".$bill_id."'";
			}
			$sql.=" order by pm";
			$user=$benefitInfo->query($sql);
			$this->assign('user',$user);
		}

		public function rank_fine_sc(){
			$this->display();
		}
	}
?>