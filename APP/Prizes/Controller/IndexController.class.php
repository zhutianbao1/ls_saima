<?php
namespace Prizes\Controller;

class IndexController extends BaseController {

	protected function _initialize(){
		parent::_initialize();
		self::power();
	}
	//主页
	public function index(){
		$prizesInfo=M('prizesInfo');
		//$sql="SELECT a.* FROM rank_prizes_info a join (select * from rank_prizes_info GROUP BY prizes_type) b on a.prizes_type=b.prizes_type where b.id+4>=a.id and a.status=1";
		 $sql="SELECT * FROM (SELECT a.*,CASE prizes_type WHEN 1 THEN @1:=@1+1 WHEN 2 THEN @2:=@2+1 WHEN 3 THEN @3:=@3+1 WHEN 4 THEN @4:=@4+1 WHEN 5 THEN @5:=@5+1 WHEN 6 THEN @6:=@6+1 end pm FROM (SELECT * FROM rank_prizes_info where status=1 ORDER BY prizes_type) a,(select @1:=0,@2:=0,@3:=0,@4:=0,@5:=0,@6:=0) b) c where pm<6";
		$commodity=$prizesInfo->query($sql);
		$this->assign('dity',$commodity);
		$sql_con="select * from rank_prizes_type";
		$con=$prizesInfo->query($sql_con);
		$this->assign('con',$con);
		$sql_num="SELECT prizes_type,count(*) num FROM rank_prizes_info group by prizes_type";
		$num=$prizesInfo->query($sql_num);
		$this->assign('num',$num);
		$this->display();
	}

	public function rept_index(){
		$prizesInfo=M('prizesInfo');
		$commodity=$prizesInfo->where("status=1")->limit(10)->select();
		$this->ajaxReturn($commodity);
	}
	//按奖品类别查看更多
	public function prize_borwse(){
		$prizesInfo=M('prizesInfo');
		$type=I('type');
		$sql="SELECT a.*,b.type_name FROM rank_prizes_info a,rank_prizes_type b where a.prizes_type=b.id and prizes_type='".$type."'";
		$commodity=$prizesInfo->query($sql);
		$this->assign('dity',$commodity);
		$this->display();
	}
	//领奖
	public function prize_receive(){
		$User=M('power');
		$User->startTrans();
		//$j['success']=false;
		//$j['msg']='领奖失败';
		$prizesRecord=M('prizesRecord');
		$type=M('prizesInfo')->where("id=".I('id')."")->find();
		$dates=M('userInfo')->where("create_date=(select max(create_date) from rank_user_info) and bill_id='".I('bill_id')."'")->find();
		$data['pi_id']=I('id');
		$data['bill_id']=I('bill_id');
		$data['create_date']=date('Y-m-d');
		$data['status']=1;
		$st['status']=2;
		$sa['prizes_name']=$type['prizes_name'];
		$sa['bill_id']=I('bill_id');
		$sa['create_date']=$dates['create_date'];
		$resurt=$prizesRecord->add($data);
		$res=M('userInfo')->where("bill_id='".I('bill_id')."'")->save($st);
		$rea=M('prizesSa')->add($sa);
		if($resurt && $res && $rea){
			//$j['success']=true;
			$User->commit(); 
			$j['msg']="领奖成功，等待发放！\n温馨提示：奖品所选人数达到10人起采购";
		}else{
			$User->rollback(); 
			$j['msg']='领奖失败';
		}
		$this->ajaxReturn($j);
	}

	public function prize_record(){
		$this->display();
	}
	//查看个人领奖信息
	public function rept_record(){
		$prizesRecord=M('prizesRecord');
		$sql="SELECT a.*,b.prizes_name,b.prizes_img,b.prizes_desc from rank_prizes_record a,rank_prizes_info b where a.pi_id=b.id and bill_id='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
		$rpt=$prizesRecord->query($sql);
		$this->ajaxReturn($rpt);
	}
	//查看全部领奖信息
	public function rept_record1(){
		$prizesRecord=M('prizesRecord');
		$user_name=I('user_name');
		$sql="SELECT a.*,b.user_name,c.prizes_name,prizes_img FROM rank_prizes_record a,rank_user_info b,rank_prizes_info c where a.bill_id=b.bill_id and a.pi_id=c.id";
		if(!empty($user_name)){
			$sql.=" and user_name like '%".$user_name."%'";
		}
		$rpt1=$prizesRecord->query($sql);
		//$rpt1=parent::listsBySql($sql,5);
		$this->ajaxReturn($rpt1);
	}

	public function prize_sa(){
		$prizesSa=M('prizesSa');
		$user_name=I('user_name');
		$phone=I('phone');
		$sql="SELECT a.*,b.prizes_name FROM rank_user_info a LEFT JOIN rank_prizes_sa b on a.bill_id=b.bill_id and a.create_date=b.create_date where a.create_date=(SELECT max(create_date) FROM rank_user_info)";
		//$sa=$prizesSa->query($sql);
		if(!empty($user_name)){
			$sql .= " and a.user_name like '%".$user_name."%'";
		}
		if(!empty($phone)){
			$sql .= " and a.bill_id='".$phone."'";
		}
		$sql .= " order by status desc";
		$sa=parent::listsBySql($sql,20);
		$this->assign('sa',$sa);
		$this->display();
	}
}

?>