<?php

namespace Read\Widget;
use Read\Controller\BaseController;

class MeetingWidget extends BaseController  {

	public function info($book_id=0){
		$m = M('meeting');
		$w['book_id']=$book_id;
		$meetings = $m->where($w)->select();
		$this->assign('meeting',$meetings[0]);
		$this->display('index/book_meeting');
	}

	public function index_meeting(){
		//首页阅读会		 
		$m = M('meeting');
		$sql = "select a.*,b.img book_img,b.book_name from rank_meeting as a inner join rank_book as b on a.book_id=b.id where a.meet_date<DATE_FORMAT(NOW(),'%Y-%m-%d') AND a.status=1 and b.status=1 and b.county_code='".$_SESSION['COUNTY_CODE']."' order by a.meet_date desc";
		// $xindes = $m->where($w)->order('oper_date desc')->limit(0,3)->select();
		$meetings = parent::listsBySql($sql,5);
		// $meetings = $m->where($w)->order('create_date desc')->limit(0,5)->select();
		$this->assign('meetings',$meetings);
		$this->display('index/index_meeting');
	}

	//其他县市最近一场阅读会
	public function other_county_meeting(){
		$m=M('meeting');
		$sql="SELECT * FROM(SELECT c.* FROM (
				SELECT a.id,a.county_code,b.id meeting_id,b.book_id,b.title,b.img,b.meet_date,
				b.create_date FROM rank_book a,rank_meeting b WHERE a.id=b.book_id AND 
				a.status=1 AND b.status=1)c  WHERE ( SELECT COUNT(*) FROM (SELECT a.id,a.county_code,
				b.id meeting_id,b.book_id,b.title,b.img,b.meet_date,b.create_date FROM rank_book AS a,
				rank_meeting  AS b WHERE a.id=b.book_id AND a.status=1 AND b.status=1) d WHERE 
				d.county_code = c.county_code 	AND d.create_date > c.create_date )<1  
				 ORDER BY  c.county_code,  c.create_date DESC )e 
				 WHERE county_code !='".$_SESSION['user_auth']['COUNTY_CODE']."'";
		$lists=$m->query($sql);
		$this->assign('lists',$lists);
		$this->display('index/other_county_meeting');		
	}


	public function other_county_meeting1(){
		$m=M('meeting');
		$sql="SELECT * FROM(
				SELECT c.* FROM (
				SELECT a.id,a.county_code,b.id meeting_id,b.book_id,b.title,b.img,b.meet_date,
				b.create_date 
				FROM rank_book a,rank_meeting b WHERE a.id=b.book_id AND a.status=1 AND b.status=1
				)c  WHERE ( SELECT COUNT(*) FROM (SELECT a.id,a.county_code,b.id meeting_id,b.book_id,
				b.title,b.img,b.meet_date,b.create_date FROM rank_book AS a,rank_meeting  AS b 
				WHERE a.id=b.book_id
				AND a.status=1 AND b.status=1) d WHERE   d.county_code = c.county_code 
				AND d.create_date > c.create_date )<1   ORDER BY  c.county_code,  c.create_date DESC
				)e where county_code !='".$_SESSION['user_auth']['COUNTY_CODE']."' ";
		$lists=$m->query($sql);
		$this->assign('lists',$lists);

        $countys=array( '5780'=>array(0=>'5780',1=>'市公司'),
        				'5781'=>array(0=>'5781',1=>'莲都分公司'),
        				'5782'=>array(0=>'5782',1=>'缙云分公司'),
          				'5783'=>array(0=>'5783',1=>'青田分公司'),
          				'5784'=>array(0=>'5784',1=>'云和分公司'),
          				'5785'=>array(0=>'5785',1=>'庆元分公司'),
          				'5786'=>array(0=>'5786',1=>'龙泉分公司'),
          				'5787'=>array(0=>'5787',1=>'遂昌分公司'),
          				'5788'=>array(0=>'5788',1=>'松阳分公司'),
          				'5789'=>array(0=>'5789',1=>'景宁分公司'),
          				'578B'=>array(0=>'578B',1=>'南城分公司'));
        foreach ($countys as $key => $value) {   
	         if(strval($key)==$_SESSION['user_auth']['COUNTY_CODE']){
	         	  unset($countys[$key]);         	
	         }      
   		}   		
   		$this->assign('countys',$countys);
        $sql="select county_code,sum(zan_num) zan_num from rank_county_zan group by county_code";
        $county_zans= $m->query($sql);
        $this->assign('county_zans',$county_zans);

		$this->display('index/other_county_meeting1');  
		

	}


}

?>