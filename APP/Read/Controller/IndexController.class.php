<?php

namespace Read\Controller;

class IndexController extends BaseController {


	 //浏览量
	public function  index_ydl(){		
		$m=M('borrow');         
        $month=date('Ym',time());                    
       $sql="select month from rank_book_ydl order by month desc " ;
          $arr  = $m->query($sql);        
          if($month==$arr[0]['month']){          	
          	$sql="update rank_book_ydl  set ydl_numb=ydl_numb+1 where month=" .$month. " ";
        	$flag= $m->query($sql);
        }else{           
           $sql="insert into rank_book_ydl (month,ydl_numb) values(".$month.",1) " ;
          $flag=$m->query($sql);
         } 
         redirect("/ranking/Read/index/index?cty=".$_SESSION['COUNTY_CODE']."", 0);
	}

	public function index(){
		$this->display();
	}

	public function login(){
		if(IS_GET){
			ob_clean();
		    $Verify = new \Think\Verify();
			$Verify->entry();
		}
		if(IS_POST){		
			$oper_login_code = I('oper_login_code');
			$oper_login_pass = I('oper_login_pass');
			$img_code        = I('img_code');
			$j['success']=false;
			$j['msg']='登录失败';
			$verify = new \Think\Verify(); 
			if($verify->check($img_code) === false){
	            $j['msg']='验证码错误';
	        }else{
	        	$m = M();
		        $w['oper_login_code']=$oper_login_code;
		        $w['oper_login_pass']=$oper_login_pass;
		        $w['oper_status']=1;
		        $user = $m->db(1,'LS_CONFIG')->table('mz_user.t_sys_oper')->where($w)->find();       
		        if($user){
		        	$j['success']=true;
		        	$j['msg']='登录成功';
		        	session('user_auth',$user);	
		        }else{
		        	$j['msg']='登录失败，账号或密码错误';
		        }
	        }
			$this->ajaxReturn($j);			
		}
	}

	public function people(){	
		 //我的积分
		 $m = M('manager'); 
		 //$sql="select count(*) from  rank_manager where status=1 and 
		 // bill_id='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";		
         //$bill_ids= $m->query($sql); 

         //管理员看到的读书会信息         
		$m = M('manager');
        $w['bill_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $list=$m->where("bill_id='".$w['bill_id']."'")->find();

         //判断是不是管理员  
         if($list!=null){
                 $type="manager";
                //共参与人数
                $sql="select COUNT(oper_phone) people_totle FROM (select a.meeting_id,a.oper_phone,
                        b.book_id,c.county_code FROM rank_meeting_result a,rank_meeting b,rank_book c 
                     WHERE a.meeting_id=b.id  AND b.book_id=c.id AND 
                     c.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' 
                     AND FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d ";
                 $people_totle= $m->query($sql);
                 $this->assign('people_totle',$people_totle);

                 //读书会场次
                $sql="select COUNT(DISTINCT(meeting_id)) book_meeting_no  FROM (
                      select a.meeting_id,a.oper_phone,b.book_id,c.county_code FROM rank_meeting_result a,
                        rank_meeting b,rank_book c  WHERE a.meeting_id=b.id  AND b.book_id=c.id  AND 
                     c.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' AND 
                       FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d" ;
                $book_meeting_no= $m->query($sql);
                $this->assign('book_meeting_no',$book_meeting_no); 

                //推荐的图书数目
                $sql="select COUNT(id) book_no FROM rank_book  WHERE  STATUS=1 AND 
                county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' ";
                $book_no= $m->query($sql);
                $this->assign('book_no',$book_no);
         }else{	        
	        //普通员工
	        $type="gen_user";
	             		
			$m = M('user_score'); 
	         //获取本月第一天
	        $sql="select DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
	        $first= $m->query($sql);
	         //获取本月最后一天
	        $sql="select LAST_DAY(CURDATE()) last_date";
	        $last= $m->query($sql); 
            //本月读书会积分
	        $sql="select IFNULL(sum(score),0) total from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr1= $m->query($sql);
           

	         //本月参与积分
	        $sql="select IFNULL(sum(score),0) canyu from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason=".'"参加读书会"'." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr2= $m->query($sql);
			 
             //本月心得发表积分
			$sql="select IFNULL(sum(score),0) xinde from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason like".'"分享心得%"'." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr3= $m->query($sql);
	        $arr4=array();
	        $arr4[0]=$arr1[0];
	        $arr4[1]=$arr2[0];
	        $arr4[2]=$arr3[0]; 
	        $this->assign('arr4',$arr4);
         } 
         $this->assign('type',$type);


		//我的报名
		$m = M('borrow');
		$sql = "select * from rank_meeting_bao where bao_oper='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."' ";
		$baos = parent::listsBySql($sql,2);
		$this->assign('baos',$baos);

		//我的借阅
		$m = M('borrow');
		$sql = "select * from rank_borrow where borrow_oper=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." order by borrow_date desc ";
		// $where['borrow_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		// $borrows = $m->where($where)->select();
		$borrows = parent::listsBySql($sql,5);
		$this->assign('borrows',$borrows);

		//我的心得
		$sql = "select a.*,b.book_name FROM rank_article a LEFT JOIN rank_book b ON a.book_id=b.id WHERE a.status=1 and a.oper_id=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." ORDER BY a.oper_date DESC ";
		$xindes = parent::listsBySql($sql,5);
		$this->assign('xindes',$xindes);
         
         //我推荐的书籍			
        $sql="select * from rank_book_tuijian where book_oper_phone='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."' order by book_date desc ";
        $tuijs = parent::listsBySql($sql,5);		
		$this->assign('tuijs',$tuijs);
		
		//我的请假
        $m=M('user');
        $oper=$m->where("oper_phone='".$oper_phone."'")->find(); 
        $oper_num=$oper['oper_num'];
        $m=M('meeting_qingjia');       
        $sql="select b.title,a.item_date,a.step,a.shenpi_result from 
              rank_meeting_qingjia a, rank_meeting b where a.meeting_id=b.id 
            and a.status=1 and oper_num='".$oper_num."'";
        $qingjia=$m->query($sql);
        $this->assign('qingjia',$qingjia);

		$this->display('Vip/index');
	}




	public function book(){
 
		$m = M('book');
		// $w['status']=1;
		// $books = $m->where($w)->order('create_date desc')->select();
		$sql = "select * from rank_book where 1=1";
		$cat = I('cat');
		if(!empty($cat) && $cat!='all'){
			$sql .=" and book_type = '".$cat."' ";
		}
		$kw = I('kw');
		if(!empty($kw)){
			$sql .=" and book_name like '%".$kw."%' ";
		}
		$sql .=" and county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' and status=1 order by create_date desc";
		$books = parent::listsBySql($sql,5);
		$this->assign('books',$books);

		$this->display();		
	}


	//月份阅读会信息
	public function info_month($month=0){
		$this->display();
	}

	//书籍选择 
	public function book_select(){
		//$oper_county_code=$_SESSION['user_auth']['COUNTY_CODE'];

		//$sql = "select * from rank_book where status=1 and COUNTY_CODE='".$oper_county_code."'";

		$sql = "select * from rank_book where status=1 ";
		$book_name = I('title');
		if(!empty($book_name)){
			$sql.=" and book_name like '%".$book_name."%'";
		}
 
		$sql.="  order by create_date desc";
		$books = parent::listsBySql($sql,$page_size);
		$this->assign('books',$books);
		$this->display('index/book_select');
		
	}


	//书籍信息
	public function book_info($id=0){
		$m = M('book');
		$book = $m->find($id);

		$this->assign('book',$book);

		//阅读会信息
		$m2 = M('meeting');
		$w['book_id']=$id;
		$meetings = $m2->where($w)->select();
		$this->assign('meeting',$meetings[0]);

		//心得列表
		$xindes = parent::query_xinde_book($id);
		$this->assign('xindes',$xindes);

		$this->display();
	}

	public function xinde(){
		$xindes = parent::query_xinde(5);
		$this->assign('xindes',$xindes);
		$this->display();
	}
	

	public function xinde_del($id=0){
		$json['success'] = false;
		$json['msg'] = '操作失败';

		$m = M('article');
		$re = $m->delete($id);
		if($re){
			$json['success']=true;
		}
		$this->ajaxReturn($json);
	}

	public function xinde_edit($id=0){		
		$book_id = I('book_id');
		if(intval($book_id)>0){
			$b = M('book');
			$book = $b->find($book_id);
			$this->assign('book',$book);
		}

		$m = M('article');
		if(IS_GET){
			if(intval($id)>0){
				$article = $m->find($id);
				$this->assign('xinde',$article);
			}
		}else{
			$d['book_id']=I('book_id');
			$d['title']=I('title');
			$d['text']=I('text');

			$bill_id=I('bill_id');
			$county_code=I('county_code');
			$art_act=I('art_act');

			if(intval($id>0)){
				$w['id']=$id;
				$re = $m->where($w)->save($d);
			}else{
				$d['oper_date']=time();
				//$d['oper_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
				//$d['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];

				if(!empty($bill_id)){
					$d['oper_id']=$bill_id;
				}else{
					$d['oper_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
				}

				if(!empty($county_code)){
					$d['county_code']=$county_code;
				}else{
					$d['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
				}
				if(!empty($art_act)){
					$d['art_act']=$art_act;
				}
				$re = $m->add($d);
			}
			if($re){                   
				$this->success('数据保存成功,等待审核!',U('xinde')); die;
			}else{
				$this->error('数据添加失败'); die;
			}
		}
		$this->display();
	}

	//阅读心得
	public function book_xinde($id=0){
		$m2 = M('article');
		$xinde = $m2->find($id);
		$this->assign('xinde',$xinde);

		$m = M('book');
		$book = $m->find($xinde['book_id']);
		$this->assign('book',$book);

		//心得评论
		$sql = "select a.* from rank_article_pin a where art_id=".$id;
		$pins = parent::listsBySql($sql,20);
		$this->assign('pins',$pins);

		$this->display();
	}

	//心得评论
	public function book_xinde_pin(){
		$m = M('articlePin');
		$data['art_id']=I('xinde_id');
		$data['oper_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		$data['oper_date']=time();
		$data['info']=I('info');
		$data['status']=1;
		$data['article_score']=I('article_score');
		$re = $m->add($data);
		redirect(U('index/book_xinde?id='.$data['art_id']));
	}

	//阅读会
	public function meeting(){
		//首页阅读会
		$m = M('meeting');
        $county_code=I('county_code');

        if(!empty($county_code)){
        	$sql = "select a.*,b.img book_img,b.book_name,b.county_code from rank_meeting as a
        	  inner join rank_book as b on a.book_id=b.id where a.status=1 and 
        		b.county_code='".$county_code."' order by a.meet_date desc";
        }else{
        	$sql = "select a.*,b.img book_img,b.book_name,b.county_code from rank_meeting as a 
        	inner join rank_book as b on a.book_id=b.id where  a.status=1 and 
        	b.county_code='".$_SESSION['COUNTY_CODE']."' order by a.meet_date desc";
        }

		// $xindes = $m->where($w)->order('oper_date desc')->limit(0,3)->select();		
		$meetings = parent::listsBySql($sql,12);
		//当前时间
        $current=date('Y-m-d');
		$this->assign('meetings',$meetings);
		$this->assign('current',$current);
		$this->display();
	}



	//读书会报名撤销
	public function meeting_bao_del($id=0){
		$json['success'] = false;
		$json['msg'] = '操作失败';

		$m = M('meetingBao');
		$re = $m->delete($id);
		if($re){
			$json['success']=true;
		}
		$this->ajaxReturn($json);
	}




	//读书会报名情况
	public function meeting_bao($book_id=0){
		$m = M('book');
		/**
		$sql = "select a.*,b.title as meet_title,b.id as meet_id,b.meet_date from rank_book a,rank_meeting b where a.status=1 and a.id=b.book_id and a.id=".$book_id;
		**/

		$sql = "select a.*,b.title as meet_title,b.id as meet_id,b.meet_date 
		        from rank_book a,rank_meeting b where a.id=b.book_id and a.id=".$book_id;
		//dump($sql);
		$books = $m->query($sql);
		$book = $books[0];
		$this->assign('book',$book);

		//报名情况
		$bms = parent::query_meeting_bao($book['meet_id']);
		$this->assign('bms',$bms);

		$oper_phone= $_SESSION['user_auth']["OPER_LOGIN_CODE"];
		$m=M('manager');
		$w['bill_id']=$oper_phone;
		$list = $m->where($w)->select();
		$num=count($list);
		$this->assign('num',$num);
		$this->assign('oper_phone',$oper_phone);
		$this->display('meeting_bao');

	}


	//阅读会信息
	public function book_meeting($id=0){
		//首页阅读会
		$m = M('meeting');
		$meeting = $m->find($id);
		
		$this->assign('meeting',$meeting);
		$m = M('book');
		$sql = "select a.*,b.title as meet_title,b.id as meet_id,b.meet_date from rank_book a,rank_meeting b where a.status=1 and a.id=b.book_id and a.id=".$meeting['book_id'];
		$books = $m->query($sql);
		$book = $books[0];
		$this->assign('book',$book);

		//报名情况
		$bms = parent::query_meeting_bao($book['meet_id']);
		$this->assign('bms',$bms);
		

		//心得列表
		$xindes = parent::query_xinde_book($book['id']);
		$this->assign('xindes',$xindes);

		$this->display();
	}

	//活动
	public function ploy(){
		$m = M('ploy');
		// $w['status']=1;
		// $ploys = $m->where($w)->order('oper_date desc')->select();
		$sql = "select * from rank_ploy where status=1 and county_code='".$_SESSION['COUNTY_CODE']."' order by oper_date desc";
		$ploys = parent::listsBySql($sql,10);

		$this->assign('ploys',$ploys);
		$this->display();
	}

	//活动信息
	public function ploy_info($id=0){
		$m = M('ploy');
		$ploy = $m->find($id);
		$this->assign('ploy',$ploy);
		$this->display();
	}

	//图书馆
	public function library(){
		$m = M('book');
		// $w['status']=1;
		// $books = $m->where($w)->order('create_date desc')->select();
		$sql = "select * from rank_book where 1=1";
		$cat = I('cat');
		if(!empty($cat) && $cat!='all'){
			$sql .=" and book_type = '".$cat."' ";
		}
		$kw = I('kw');
		if(!empty($kw)){
			$sql .=" and book_name like '%".$kw."%' ";
		}
		$sql .=" and status=1  and county_code='".$_SESSION['COUNTY_CODE']."' order by create_date desc";
        
		$books = parent::listsBySql($sql,12);
		$this->assign('books',$books);
		$this->display();
		
	}

	//电子书
	public function ebook(){
		$sql = "select * from rank_book where ebook is not null and ebook<>''";
		$books = parent::listsBySql($sql,10);
		$this->assign('books',$books);
		$this->display();
	}

	public function ebook_down($book_id=0){
        $m = M('book');
        $book = $m->find($book_id);
        $filePath = $book['ebook'];
        $ext      = substr($filePath, strpos($filePath,'.')+1);
        $showname = $book['id'].'.'.$ext;
        //echo 'filePath : '.$filePath.' showname : '.$showname;
        // 调用类  
        $http = new \Org\Net\Http;  
        $http->download($filePath, $showname); 
    }

    

    //得到当前月份的浏览量
    public function getMonth(){
    	$m=M('borrow');         
        $month=date('Ym',time());
        $sql="select ydl_numb from rank_book_ydl  where  month=".$month." " ;
        $arr= $m->query($sql); 
        $this->ajaxReturn($arr);	
    }

     //得到总的浏览量
    public function getTotal(){
    	$m=M('borrow');        
        $sql="select sum(ydl_numb) toatal_numb from rank_book_ydl  " ;
        $arr= $m->query($sql); 
        $this->ajaxReturn($arr);	
    }



    //读书活动与会者名单导入
    /**
	public function meeting_yh_import(){		
	    $this->display('index/meeting_yh_import');	  
    }
    **/


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
		for ($row = 1; $row <= $highestRow; $row++) { 
			for ($col = 0; $col < $highestColumnIndex; $col++) { 
                 $excelData[$row][] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
           } 
        } 
        return $excelData;
	}

    public function file(){
	    if (!empty($_FILES['file_stu']['name'])){
		    $tmp_file = $_FILES['file_stu']['tmp_name'];
		    $file_types = explode ( ".", $_FILES['file_stu']['name'] );
		    $file_type = $file_types [count ( $file_types ) - 1];

		     //*判别是不是.xls文件，判别是不是excel文件
		    if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
		        $this->error ( '不是Excel文件，重新上传' );
		    }

		    //设置上传路径
		    $savePath = '//10.78.1.85/www/ranking/Public/upfile/Execl2/';


		    //*以时间来命名上传的文件
		    $str =date ( 'YmdH' ); //date ( 'Ymdhis' );
		    $file_name = $str . "." . $file_type;

		     //*是否上传成功
		    if (! copy ( $tmp_file, $savePath . $file_name )){
		        $this->error ( '上传失败' );
		    }
		  
		    $ress = $this->read($savePath.$file_name,$file_type); 

		    $res=array();
		    for($i=2;$i<=count($ress);$i++){
		    	for($j=0;$j<count($ress[1]);$j++){
		    		$temp=explode('.', $ress[$i][$j]);
		    		if(is_numeric($ress[$i][$j]) && $temp[1]>99){
		    			 $num=sprintf('%.2f',$ress[$i][$j]);
		    		}else{
		    			$num=$ress[$i][$j];
		    		}
		    			 $res[$i][$j]=$num;
		    	}
		    }

	        //获取本月第一天
	        $m=M();
	        $sql="select DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
	        $first= $m->query($sql);           
	         //获取本月最后一天
	        $sql="select LAST_DAY(CURDATE()) last_date";
	        $last= $m->query($sql);

	        //获取下个月第一天
	        $sql="select DATE_ADD(CURDATE()-DAY(CURDATE())+1,INTERVAL 1 MONTH) next_first";
	        $next=$m->query($sql);
	        $next_first=$next[0]['next_first'];

	        $curmonth=date('Ym');

			if(!empty($res)){			
				foreach ( $res as $k => $v ){
					if ($k != 0){
						$data['meeting_id'] = $v[0];
						$data['meeting_name'] = $v[1];
						$data['meeting_city'] = $v[2];					
						$data['oper_phone'] =strval($v[5]) ;
						$data['oper_date']=time();
						$data['status']='1';	           
						//判断记录是否存在
						$m = M('meeting_result');

						$where['meeting_id']=$v[0];
						$where['oper_phone']=strval($v[5]);
						
						$user=$m->where($where)->select(); 

						//不存在添加
						if(empty($user)){                   	
							$flag=$m->add($data);   

							if($flag){
							    $m=M('user_score');						    
							    $da['oper_city']=$v[2];
							    $da['oper_name']=$v[3];
							    $da['oper_num']=$v[4];
							    $da['oper_phone']=strval($v[5]);
							    $da['meeting_id'] = $v[0];
							    $da['sc_name']=$v[1];
							    $da['sc_reason']="参加读书会";
                                /**
						        $sql="select IFNULL(sum(score),0) m_totle from rank_user_score
				               			where oper_phone=".$da['oper_phone']." and 
				               		(select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' 
				               		and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<'".$next_first."'"; 
				               		**/
				               	$sql="select IFNULL(sum(score),0) m_totle from rank_user_score 
				               			where oper_phone=".$da['oper_phone']." and smonth='".$curmonth."' "; 	
				                $month_jf= $m->query($sql);
				                $score=intval($month_jf[0]['m_totle']);
				                if($score<=5){
				                	$score1=5;
				                }else{
				                	$score1=10-$score;
				                }
				                $da['score']=$score1;
							    $da['oper_date']=time(); 
							    $da['status']='1';  
							    $da['smonth']=date('Ym');                 
							    $flag2 = $m->add($da);

							    if(!$flag2){
	                              	$this->error('导入失败!');
							    }
							}else{
								$this->error('导入失败!');
							}						

						}else{ 
						   //存在更新              	
							$flag=$m->where($where)->save($data);
							if(!$flag){
								$this->error('导入失败!');
							}			          
						}
					}
				}
				$this->success('导入成功!');
			}else{
			   $this->error ( '导入的Execl数据为空！' );
		    }
		}else{
			$this->error ( '请选择导入的Execl数据！' );
		}
	}




	//读书会会员申请
    public function rank_user_add(){    	    	
    	if(IS_GET){ 
    		$oper_city=$_SESSION['user_auth']['COUNTY_CODE'];
    		$this->assign('oper_city',$oper_city);    	
    		$this->display();
    	}

    	if(IS_POST){ 
    		$m=M('user');
	        $data['oper_name']=I('post.oper_name');
	        $data['oper_city']=I('post.oper_city');
	        $data['oper_gender']=I('post.oper_gender');
	        $data['oper_birth']=I('post.oper_birth');
	        $data['oper_num']=I('post.oper_num');
	        $data['oper_phone']=I('post.oper_phone'); 
	        $data['oper_school']=I('post.oper_school');
	        $data['oper_prof']=I('post.oper_prof');
	        $data['oper_text1']=I('post.oper_text1');
	        $data['oper_text2']=I('post.oper_text2'); 
	        $data['oper_hobby']=I('post.oper_hobby'); 
	        $data['oper_text4']=I('post.oper_text4');
	        $data['oper_xuzhi']=I('post.check1');	
	        $data['oper_date']=time();    	
	    	$data['status']='0';	    	
	    	
	    	$flag=$m->add($data); 
	    	if($flag){
	    		$this->success("申请成功,等待审核!");
	    	}else{
	    		$this->eregi("数据出错!请检查,并重新提交!");
	    	} 
    	} 
    		
    }

    //判断编号是否已被申请
    public function rank_user_add_check(){
    	 $oper_num=I('post.oper_num2');
         $m=M('user');
         $sql="select count(*) num from rank_user where oper_num='".$oper_num."'";
         $flag=$m->query($sql);    	 
    	 $this->ajaxReturn($flag);
     }



     //请求已报名的数据
    public function rank_meeting_bao_yb(){
    	$m=M('borrow'); 
            $meeting_id=I('post.meeting_id');
            $sql="select bao_oper,FROM_UNIXTIME(bao_date,'%Y-%m-%d ') AS  bao_date FROM rank_meeting_bao  WHERE meeting_id=".$meeting_id." ORDER BY  id DESC LIMIT 1";
            $arr= $m->query($sql);      

            $arr2 = array();
		foreach ($arr as $i => $b) {
			$model = M();
			$where['oper_login_code']=$b['bao_oper'];
			$oper = $model->db(1,'LS_CONFIG')->table('mz_user.t_sys_oper')->where($where)->find();
			if($oper['OPER_NAME']){
			   $b['bao_oper_name']=$oper['OPER_NAME'];
			 }
			$arr2[] = $b;
		}         
         $this->ajaxReturn($arr2);	
    }
     
     //书籍推荐
    public function book_tuijian(){    	
    	if(IS_GET){    		
    		$this->display();      
    	} 
    	if(IS_POST){ 
	    	$m=M('book_tuijian');
            $data['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
	    	$data['book_name']=I('post.book_name'); 
	    	$data['book_auth']=I('post.book_auth');
	    	$data['book_jianjie']=I('post.book_jianjie');
	    	$data['book_reason']=I('post.book_reason');
	    	$data['book_oper']=$_SESSION['user_auth']['OPER_NAME'];
	    	$data['book_oper_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
	    	$data['book_date']=time();
	    	$data['status']='0';
	    	$arr=$m->add($data);	    	
	    	if($arr){
	    		$this->success('提交成功,等待审核!');
	    	}else{
	    		$this->error('提交失败!');
	    	}        	
    	}    	
    } 

        //阅读会请假
    public function meeting_qingjia(){
        $m=M('user');
        $w['oper_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $w['status']='1';
        $user=$m->where($w)->find();

        $data['oper_name']=$user['oper_name'];
    	$data['oper_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
    	$data['oper_city']=$user['oper_city'];
    	$data['meeting_id']=I('meet_id');
    	$data['qingjia_reason']=I('qj_reason');
    	$data['item_date']=time();
    	$data['status']=1;

    	$m=M('meeting_qingjia');
		$flag=$m->add($data);    		
		if($flag){
			$msg='1';
		}else{
			$msg='0';
		}
		$this->ajaxReturn($msg);
    }



     //查询是否请假
    public function meeting_qingjia_cx(){   
    /**  	    
    		$m=M('user'); 
            $wh['oper_phone']=  
            $wh['status']='1';
    		$user=$m->where($wh)->find(); 
    		$oper_num=$user['oper_num'];
    		
**/         $meeting_id=I('meet_id');
    		$m=M('meeting_qingjia');
    		$w['meeting_id']=$meeting_id;
    		$w['oper_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
    		$flag=$m->where($w)->find();
    		if($flag){
    			$msg="1";
    		}else{
    			$msg="0";
    		}
    	 $this->ajaxReturn($msg);
    }

    /*
   public function meeting_qingjia_cx(){     	    
    		$m=M('user');  
    		$oper_phone=$_SESSION['user_auth']['OPER_LOGIN_CODE'];    		
    		$arr=$m->where("oper_phone=".$oper_phone."")->select();    		
    		$oper_num=$arr[0]['oper_num'];
    		$meeting_id=I('get.meet_id');
    		$m=M('meeting_qingjia');
    		$w['meeting_id']=$meeting_id;
    		$w['oper_num']=$oper_num;
    		$flag=$m->where($w)->select();
    		 $msg="";
    		if(!$flag){
    			$msg="0";
    		}else{
    			$msg="1";
    		}
    	 $this->ajaxReturn($msg);
    }

    */


   //图书点赞
    public function index_book_zan(){
       $m=M('book');
       //$w['id']=I('book_id');
       $book_id=I('book_id');
       //$zan = $m->where("id='".$book_id."'")->getField('zan');
       $list = $m->where("id='".$book_id."'")->select();
       if($list!=null){
       	$zan=intval($list[0]['zan'])+1;           
       	$data['zan']=$zan;
       	   $list1 = $m->where("id='".$book_id."'")->save($data);
       	   if($list1){
	       	  $json['msg']='点赞成功';
	       	  $json['zan']=$zan;
	       }else {
	       	  $json['msg']='点赞失败';
	       }	     
       }else {
       	$json['msg']='点赞失败';
       } 
       $this->ajaxReturn($json);
    }

    //更新借阅次数
    public function index_zong_borrow(){
    	//统计借阅次数
			$m=M('book');
			$book_id=I('book_id');						
			//$sql="select zong_borrow from rank_book where id=".$book_id."";
			//$list=$m->query($sql);
			$list=$m->where("id=".$book_id."")->select();
			    if($list!=null){
                    $data['zong_borrow']=intval($list[0]['zong_borrow'])+1;
                	$list1 = $m->where("id='".$book_id."'")->save($data);
                	if($list1){
                		$zong_borrow=$data['zong_borrow'];
                	}						
			    } 
        $this->ajaxReturn($zong_borrow);
    }



    public function user_xinxi(){
    	//我的积分
		 $m = M('manager');
         //管理员看到的读书会信息         
		$m = M('manager');
        $w['bill_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $w['status']=1;
        $list=$m->where($w)->find();

         //判断是不是管理员  
         if(empty($list)){
                 $type="manager";
                //共参与人数
                $sql="select COUNT(oper_phone) people_totle FROM (select a.meeting_id,a.oper_phone,
                        b.book_id,c.county_code FROM rank_meeting_result a,rank_meeting b,rank_book c 
                     WHERE a.meeting_id=b.id  AND b.book_id=c.id AND 
                     c.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' 
                     AND FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d ";
                 $people_totle= $m->query($sql);
                 $this->assign('people_totle',$people_totle);

                 //读书会场次
                $sql="select COUNT(DISTINCT(meeting_id)) book_meeting_no  FROM (
                      select a.meeting_id,a.oper_phone,b.book_id,c.county_code FROM rank_meeting_result a,
                        rank_meeting b,rank_book c  WHERE a.meeting_id=b.id  AND b.book_id=c.id  AND 
                     c.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' AND 
                       FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d" ;
                $book_meeting_no= $m->query($sql);
                $this->assign('book_meeting_no',$book_meeting_no); 

                //推荐的图书数目
                $sql="select COUNT(id) book_no FROM rank_book  WHERE  STATUS=1 AND 
                county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' ";
                $book_no= $m->query($sql);
                $this->assign('book_no',$book_no);
         }else{	        
	        //普通员工
	        $type="gen_user";
	             		
			$m = M('user_score'); 
	         //获取本月第一天
	        $sql="select DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
	        $first= $m->query($sql);
	         //获取本月最后一天
	        $sql="select LAST_DAY(CURDATE()) last_date";
	        $last= $m->query($sql); 
            //本月读书会积分
	        $sql="select IFNULL(sum(score),0) total from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr1= $m->query($sql);
           

	         //本月参与积分
	        $sql="select IFNULL(sum(score),0) canyu from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason=".'"参加读书会"'." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr2= $m->query($sql);
			 
             //本月心得发表积分
			$sql="select IFNULL(sum(score),0) xinde from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason like".'"分享心得%"'." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr3= $m->query($sql);
	        $arr4=array();
	        $arr4[0]=$arr1[0];
	        $arr4[1]=$arr2[0];
	        $arr4[2]=$arr3[0]; 
	        $this->assign('arr4',$arr4);
         } 
         $this->assign('type',$type);
    	$m = M('user_score');
		$sql = "select * from rank_user_score 
		  where oper_phone='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'
		      order by oper_date desc ";
		$myscores = parent::listsBySql($sql,10);
		$this->assign('myscores',$myscores);

       //dump(strtotime('2017-03-23')+17*3600);
    	$this->display('Vip/user_xinxi');


    	
    }

    
    //我的积分
    public function user_read_score(){   
        //我的积分
		 $m = M('manager');
         //管理员看到的读书会信息         
		$m = M('manager');
        $w['bill_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        $list=$m->where("bill_id='".$w['bill_id']."'")->find();

         //判断是不是管理员  
         if($list!=null){
                 $type="manager";
                //共参与人数
                $sql="select COUNT(oper_phone) people_totle FROM (select a.meeting_id,a.oper_phone,
                        b.book_id,c.county_code FROM rank_meeting_result a,rank_meeting b,rank_book c 
                     WHERE a.meeting_id=b.id  AND b.book_id=c.id AND 
                     c.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' 
                     AND FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d ";
                 $people_totle= $m->query($sql);
                 $this->assign('people_totle',$people_totle);

                 //读书会场次
                $sql="select COUNT(DISTINCT(meeting_id)) book_meeting_no  FROM (
                      select a.meeting_id,a.oper_phone,b.book_id,c.county_code FROM rank_meeting_result a,
                        rank_meeting b,rank_book c  WHERE a.meeting_id=b.id  AND b.book_id=c.id  AND 
                     c.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' AND 
                       FROM_UNIXTIME(a.oper_date,'%Y-%m-%d') >='2017-01-01' ) d" ;
                $book_meeting_no= $m->query($sql);
                $this->assign('book_meeting_no',$book_meeting_no); 

                //推荐的图书数目
                $sql="select COUNT(id) book_no FROM rank_book  WHERE  STATUS=1 AND 
                county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' ";
                $book_no= $m->query($sql);
                $this->assign('book_no',$book_no);
         }else{	        
	        //普通员工
	        $type="gen_user";
	             		
			$m = M('user_score'); 
	         //获取本月第一天
	        $sql="select DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
	        $first= $m->query($sql);
	         //获取本月最后一天
	        $sql="select LAST_DAY(CURDATE()) last_date";
	        $last= $m->query($sql); 
            //本月读书会积分
	        $sql="select IFNULL(sum(score),0) total from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr1= $m->query($sql);
           

	         //本月参与积分
	        $sql="select IFNULL(sum(score),0) canyu from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason=".'"参加读书会"'." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr2= $m->query($sql);
			 
             //本月心得发表积分
			$sql="select IFNULL(sum(score),0) xinde from rank_user_score where oper_phone=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." and sc_reason like".'"分享心得%"'." and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' and (select FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'";
	        $arr3= $m->query($sql);
	        $arr4=array();
	        $arr4[0]=$arr1[0];
	        $arr4[1]=$arr2[0];
	        $arr4[2]=$arr3[0]; 
	        $this->assign('arr4',$arr4);
         } 
         $this->assign('type',$type);

		$m = M('user_score');
		$sql = "select * from rank_user_score where oper_phone='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'
		order by oper_date desc ";
		$myscores = parent::listsBySql($sql,10);
		$this->assign('myscores',$myscores);
		$this->display('Vip/user_read_score');
    }

    //我的报名
    public function user_meeting_bao(){    	
		$m = M('meeting_bao');
		$sql = "select * from rank_meeting_bao where bao_oper='".$_SESSION['user_auth']
		      ['OPER_LOGIN_CODE']."' order by bao_date desc";
		$mybaos = parent::listsBySql($sql,10);
		$this->assign('mybaos',$mybaos);
		
		$this->assign('current_time',date('Y-m-d'));
		$this->display('Vip/user_meeting_bao');
		
    }

    //我的借阅
    public function user_book_borrow(){		
		$m = M('borrow');
		$sql = "select * from rank_borrow where borrow_oper=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." order by borrow_date desc ";
		// $where['borrow_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		// $borrows = $m->where($where)->select();
		$myborrows = parent::listsBySql($sql,10);
		$this->assign('myborrows',$myborrows);
		$this->display('Vip/user_book_borrow');
		//dump($sql);
		
    }

      //我的心得
    public function user_book_xinde(){		
		$m = M('borrow');
		$sql = "select a.*,b.book_name FROM rank_article a LEFT JOIN rank_book b ON a.book_id=b.id WHERE a.status=1 and a.oper_id=".$_SESSION['user_auth']['OPER_LOGIN_CODE']." ORDER BY a.oper_date DESC ";
		$myxindes = parent::listsBySql($sql,5);
		$this->assign('myxindes',$myxindes);
		$this->display('Vip/user_book_xinde');		
    }


    //我推荐的书籍	
    public function user_book_tuijian(){
    	$m = M('book_tuijian');
    	$sql="select * from rank_book_tuijian where book_oper_phone='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."' order by book_date desc ";
        $mytuijs = parent::listsBySql($sql,10);		
		$this->assign('mytuijs',$mytuijs);
		$this->display('Vip/user_book_tuijian');
    }



    //我的请假
    public function user_meeting_qingjia(){
    	$m=M('user');
        $oper=$m->where("oper_phone='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'")->find(); 
        $oper_num=$oper['oper_num'];
        $m=M('meeting_qingjia');       
        $sql="select b.title,a.item_date,a.step,a.shenpi_result from 
              rank_meeting_qingjia a, rank_meeting b where a.meeting_id=b.id 
            and a.status=1 and oper_num='".$oper_num."'";
        $myqingjia=$m->query($sql);
        $this->assign('myqingjia',$myqingjia);
        $this->display('Vip/user_meeting_qingjia');

       
    }


    //查询是否点赞 
    public function county_zan_chaxun(){    	
    	$m=M('county_zan');
    	$w['county_code']=strval(I('county_code'));
    	$w['zan_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
    	$w['zan_date']=date('Y-m-d');

    	$flag=$m->where($w)->select();
			if(!empty($flag)){
			  $json['status']='1';
			  $json['msg']='您今天已经对此分公司点赞了,请明天再点!';
			}else {				
			  $json['status']='0';
			}
    	$this->ajaxReturn($json);
    }
    
    //为各公司点赞
    public function county_zan_add(){
    	$m=M('county_zan');
    	$data['county_code']=strval(I('county_code'));
    	$data['zan_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
    	$data['zan_date']=date('Y-m-d');
    	$data['zan_num']=1;
    	$data['status']='1';
    	$flag=$m->add($data);
    	if($flag){
    		 $json['status']='1';
             $json['msg']='点赞成功!';
             $sql="select SUM(zan_num) zan_num,county_code FROM rank_county_zan 
				where county_code='".$data['county_code']."' AND STATUS='1' GROUP BY county_code ";
			$list=$m->query($sql);
			$json['zan_num']=$list[0]['zan_num'];
			$json['county_code']=$list[0]['county_code'];	           
    	}else {
    		$json['status']='2';
    		$json['msg']='点赞失败!';
    	}
    	$this->ajaxReturn($json);
    }


	public function xinde_zan(){
		$m=M();
		$cat=I('cat');
		if($cat=='1'){
            $cat1="中层班";
		}elseif($cat=='2'){
            $cat1="综合班";
		}elseif($cat=='3'){
           $cat1="市场班";
		}elseif($cat=='4'){
           $cat1="政企班";
		}elseif($cat=='5'){
           $cat1="网络班";
		}
		
		$sql="select a.*,b.img,b.book_name FROM rank_article AS a LEFT JOIN rank_book AS b ON a.book_id=b.id 
			WHERE a.status=1 AND a.shenhe=1  and a.art_act='".$cat1."' ";

		$sql.="ORDER BY a.oper_date desc   LIMIT  5";
		$xindes=$m->query($sql);
		$this->assign('xindes',$xindes);
         //dump($sql);
		

		$sql="select art_id ,MAX(article_score) max_score, MIN(article_score) min_score 
		      FROM  rank_article_pin   GROUP BY art_id ";
		$art_scores=$m->query($sql); 
		$this->assign('art_scores',$art_scores);
		$this->display();

	}


   //心得点赞
    public function index_xinde_zan(){
    	$bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
    	if(!empty($bill_id)){
    		$wh['oper_phone']=$bill_id;
    		$wh['sf_yy']='1';
    		$wh['status']='1';
    		$user=M('user')->where($wh)->find();
    	   if(!empty($user)){
    	   	   $xinde_id=I('xinde_id');
		       $w['num_id']=$xinde_id;
		       $w['zan_type']='2';
		       $w['zan_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		       $m=M('county_zan');
		       $list2=$m->where($w)->find();
		       if(empty($list2)){
		       	$d['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
		       	$d['zan_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		       	$d['zan_date']=date('Y-m-d');
		       	$d['zan_num']='1';
		       	$d['zan_type']='2';
		       	$d['num_id']=$xinde_id;
		       	$d['status']='1';
		        $flag=$m->add($d);
			        if($flag){
		               $m=M('article');             
				       $list = $m->where("id='".$xinde_id."'")->find();
				       if($list){
				       	$zan=intval($list['zan'])+1;           
				       	$data['zan']=$zan;
				       	   $list1 = $m->where("id='".$xinde_id."'")->save($data);
				       	   if($list1){
					       	  $json['msg']='点赞成功';
					       	  $json['zan']=$zan;
					       }else {
					       	  $json['msg']='点赞失败';
					       }	     
				       }else {
				       	$json['msg']='点赞失败';
				       } 
			       	}else{
			        	 $json['msg']='点赞失败';
			        }
		       }else{
		       	  $json['msg']='您已点过赞了!';
		       }
    	   }else{
              $json['msg']='您没有点赞的权限!';
    	   }
    	}else{
           $json['msg']='未获得您的OA信息,请先登录OA!';
    	}
	    $this->ajaxReturn($json);
    }


    
}

?>