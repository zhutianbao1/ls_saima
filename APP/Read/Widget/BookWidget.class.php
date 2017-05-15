<?php

namespace Read\Widget;
use Read\Controller\BaseController;

class BookWidget extends BaseController  {


	public function index_book(){
		//首页心得
		$m = M('book');
        /**
		$sql = "select a.*,b.title as meet_title,b.id as meet_id,b.meet_date,b.meeting_planner 
        from rank_book a,rank_meeting b where b.meet_date>=DATE_FORMAT(NOW(),'%Y-%m-%d') and 
        a.county_code='".$_SESSION['COUNTY_CODE']."' and b.status=1 and a.status=1 and 
        a.id=b.book_id order by b.meet_date desc";
        **/

        $sql = "select a.*,b.title as meet_title,b.id as meet_id,b.meet_date,b.meeting_planner 
                from rank_book a,rank_meeting b where a.county_code='".$_SESSION['COUNTY_CODE']."' 
                and b.status=1 and a.status=1  and a.id=b.book_id order by b.meet_date desc ";     
        
		$books = $m->query($sql);
		$book = $books[0]; 
		$this->assign('book',$book);     

		$bms = parent::query_meeting_bao($book['meet_id']);
       
		$this->assign('bms',$bms);

        $cur_date=date('Y-m-d');

        $this->assign('cur_date',$cur_date);

		 //首页读书积分信息
        $OPER_NAME= $_SESSION['user_auth']['OPER_NAME'];         
        $this->assign('OPER_NAME',$OPER_NAME);

        //我的积分信息			
		$m = M('user_score'); 
         //获取本月第一天
        $sql="SELECT DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
        $first= $m->query($sql);           
         //获取本月最后一天
        $sql="SELECT LAST_DAY(CURDATE()) last_date";
        $last= $m->query($sql); 
        //本月积分
        $sql="select IFNULL(sum(score),0) m_total from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
        $month_jf= $m->query($sql); 
        //dump($sql);
      
        //读书会累计积分和全市排名
		$sql="SELECT a.s_score,a.rownum FROM (    
               SELECT obj_new.s_score, obj_new.oper_phone, obj_new.rownum FROM (
                 SELECT obj.s_score,obj.oper_phone, @rownum := @rownum + 1 AS num_tmp,
                     @incrnum := CASE   WHEN @rowtotal = obj.s_score THEN @incrnum
                                         WHEN @rowtotal := obj.s_score THEN @rownum
                      END AS rownum  FROM ( 
                          SELECT  SUM(score) s_score, oper_phone FROM (
                            SELECT  e.*  FROM rank_user_score e 
                            WHERE FROM_UNIXTIME(oper_date,'%Y-%m-%d') >='2017-01-01' ) f 
                          GROUP BY oper_phone     ORDER BY s_score DESC
                      ) AS obj, (
                        SELECT  @rownum := 0 ,@rowtotal := NULL ,@incrnum := 0 ) r 
                        ) AS obj_new   ) a  WHERE a.oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $zong_jf= $m->query($sql);                 
        $this->assign('month_jf',$month_jf);
        $this->assign('zong_jf',$zong_jf);
       
            

        //管理员看到的读书会信息         
		$m = M('manager');
        $w['bill_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $w['status']=1;
        $list=$m->where($w)->find();

         //判断是不是管理员  
         if(!empty($list)){
                 $type="manager";
                //共参与人数
                $sql="SELECT COUNT(oper_phone) people_totle FROM (SELECT a.meeting_id,a.oper_phone,
                        b.book_id,c.county_code FROM rank_meeting_result a,rank_meeting b,rank_book c 
                     WHERE a.meeting_id=b.id  AND b.book_id=c.id AND 
                     c.county_code='".$_SESSION['COUNTY_CODE']."' 
                     AND FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d ";
                 $people_totle= $m->query($sql);
                 $this->assign('people_totle',$people_totle);

                 //读书会场次
                $sql="SELECT COUNT(DISTINCT(meeting_id)) book_meeting_no  FROM (
                      SELECT a.meeting_id,a.oper_phone,b.book_id,c.county_code FROM rank_meeting_result a,
                        rank_meeting b,rank_book c  WHERE a.meeting_id=b.id  AND b.book_id=c.id  AND 
                     c.county_code='".$_SESSION['COUNTY_CODE']."' AND 
                       FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d" ;
                $book_meeting_no= $m->query($sql);
                $this->assign('book_meeting_no',$book_meeting_no); 

                //推荐的图书数目
                $sql="SELECT COUNT(id) book_no FROM rank_book  WHERE  STATUS=1 AND county_code='".$_SESSION['COUNTY_CODE']."' ";
                $book_no= $m->query($sql);
                $this->assign('book_no',$book_no);
                $cty=I('cty');
                switch ($cty) {
                    case '5780':
                        $county_name='市公司'; 
                        break;
                    case '5781':
                        $county_name='莲都'; 
                        break;
                    case '5782':
                        $county_name='缙云'; 
                        break;
                    case '5783':
                        $county_name='青田'; 
                        break;
                    case '5784':
                        $county_name='云和'; 
                        break;
                    case '5785':
                        $county_name='庆元'; 
                        break;
                    case '5786':
                        $county_name='龙泉'; 
                        break;
                    case '5787':
                        $county_name='遂昌'; 
                        break;
                    case '5788':
                        $county_name='松阳'; 
                        break;
                    case '5789':
                        $county_name='景宁'; 
                        break; 
                    case '5789':
                        $county_name='南城'; 
                        break;
                }
                $this->assign('county_name',$county_name);
         }else{	        
	        //普通员工
	        $type="gen_user";
	             		
			$m = M('user_score'); 
	         //获取本月第一天
	        $sql="SELECT DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
	        $first= $m->query($sql);
	         //获取本月最后一天
	        $sql="SELECT LAST_DAY(CURDATE()) last_date";
	        $last= $m->query($sql); 
            
            //本月读书会积分
	        $sql="select IFNULL(sum(score),0) total from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr1= $m->query($sql);
           

	         //本月参与积分
	        $sql="select IFNULL(sum(score),0) canyu from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason=".'"参加读书会"'." and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr2= $m->query($sql);
			 
             //本月心得发表积分
			$sql="select IFNULL(sum(score),0) xinde from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason like".'"分享心得%"'." and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr3= $m->query($sql);
	        $arr4=array();
	        $arr4[0]=$arr1[0];
	        $arr4[1]=$arr2[0];
	        $arr4[2]=$arr3[0]; 
	        $this->assign('arr4',$arr4);
         } 
         $this->assign('type',$type);

		$this->display('index/index_book'); 

	}
    

   

}

?>