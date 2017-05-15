<?php

namespace Read\Controller;

class ManagerController extends BaseController {
 
	public function index(){
		$this->display();
	}

	//阅读书籍
	public function manager_book(){
		if(!is_array($_SESSION['user_auth'])){
			$this->error('未登录或登录会话超时，请重新刷新OA主页');
			die;
		}
		$m = M('book');
		$w['status']=1;
		$w['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
		$books = $m->where($w)->order('create_date desc')->select();
		$this->assign('books',$books);
		$this->display();
	}

	//借阅管理
	public function manager_borrow(){
		$m = M('borrow');
		$sql = "select a.* from rank_borrow a inner join rank_book b on a.book_id=b.id where b.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' order by borrow_date desc";
		$list= parent::listsBySql($sql,10);
		$this->assign('borrows',$list);
		$this->display();
	}


	public function manager_book_del($id=0){
		$m = M('book');
		$w['id']=$id;
		$d['status']=0;
		$re = $m->where($w)->save($d);
		
		if($re){
			$this->success('书籍已删除',U('manager_book')); die;
		}else{
			$this->error('书籍删除失败'); die;
		}
	}

	public function manager_book_edit($id=0){
		$web_attrs = parent::base_attr();
		$book_info = $web_attrs['BOOK_TYPE'];
		$book_types = json_decode($book_info);
		$this->assign('book_types',$book_types);
		$m = M('book');
		if(IS_GET){
			if(intval($id)>0){
				$book = $m->find($id);
				$this->assign('book',$book);
			}
		}else{
			$d['book_type']=I('book_type');
			$d['book_name']=I('book_name');
			$d['author']=I('author');
			$d['ebook']=I('ebook');
			$d['img']=I('img');
			$d['star']=I('star');
			$d['description']=I('description');
			$d['sub_content']=I('sub_content');
			$d['status']=I('status');
			$d['store']=I('store');
			$d['lend']=I('lend');
			$d['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
			$d['remark']=I('remark');
			if(intval($id>0)){
				$d['modify_date']=time();
				$d['modify_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
				$w['id']=$id;
				$re = $m->where($w)->save($d);
			}else{
				$d['create_date']=time();
				$d['create_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
				$re = $m->add($d);
			}
			if($re){
				$this->success('数据保存成功',U('manager_book')); die;
			}else{
				$this->error('数据添加失败'); die;
			}
		}
		$this->display();
	}

	//发起借阅
	public function manager_book_borrow($book_id=0){
		$json['success']=false;
		$json['msg']='借阅发起失败，请联系书籍管理员';
		if(intval($book_id)>0){
			$book_model = M('book');
			$book_info = $book_model->find($book_id);
			if($book_info['store']){
				//已经借阅
				$m = M('borrow');
				$where['borrow_oper'] = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
				$where['status']      = array('gt',0);
				$where['book_id']     = $book_info['id'];
				$his = $m->where($where)->select();
				if($his){
					$json['msg']='已经借阅未归还，不允许重复借阅';
				}else{
					$data['borrow_oper'] = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
					$data['borrow_date'] = time();
					$data['book_id']     = $book_info['id'];
					$data['book_name']   = $book_info['book_name'];
					$data['status']      = 1;
					$re = $m->add($data);
					if($re){
						parent::book_store_modify($book_info['id'],true);
						$json['success'] = true;
						$json['msg']     = '发起借阅成功,请联系书籍管理员';
						parent::sms($_SESSION['user_auth']['OPER_LOGIN_CODE'],'您发起的借阅'.$book_info['book_name'].'书籍成功,请联系县市管理员取书');

						//发短信到管理员
						$county_name=$_SESSION['user_auth']['COUNTY_NAME'];      
						$m=M('manager');
						$w['county_code']=$county_name;
						if($county_name=='市公司'){
				           $w['manager_op']=2;
						}else{
						   $w['manager_op']=1;
						}						
						$manager=$m->where($w)->find();
						$oper_phone=$manager['bill_id'];		
						parent::sms($oper_phone,$_SESSION['user_auth']['OPER_NAME'].'发起借阅,借阅的书籍是'.$book_info['book_name']);						
					}
				}
			}else{
				$json['msg'] ='借阅失败，库存不足或书籍已下架';
			}
		}
		$this->ajaxReturn($json);
	}

	//书籍借出
	public function manager_book_lend($borrow_id=0){
		$json['success']=false;
		$json['msg']='书籍借出失败，请联系书籍管理员';
		if(intval($borrow_id)>0){
			$br_model = M('borrow');
			$where['id']=$borrow_id;
			$data['oper_lend']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$data['oper_date']=time();
			$data['status']=2;
			$re = $br_model->where($where)->save($data);
			if($re){
				$json['success']=true;
				$json['msg']='书本已经标记借出';
			}
		}
		$this->ajaxReturn($json);
	}


	//书籍归还
	public function manager_book_comp($book_id=0,$borrow_id=0){
		$json['success']=false;
		$json['msg']='书籍归还失败，请联系书籍管理员';
		if(intval($borrow_id)>0){
			$br_model = M('borrow');
			$where['id']=$borrow_id;
			$data['comp_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$data['comp_date']=time();
			$data['status']=0;
			$re = $br_model->where($where)->save($data);
			if($re){
				parent::book_store_modify($book_id,false);
				$json['success']=true;
				$json['msg']='书本已经标记归还';
			}
		}
		$this->ajaxReturn($json);
	}


	//心得体会
	public function manager_xinde(){
		$m = M('article');
		$w['status']=1;
		$sql = "select a.*,b.img,b.book_name from rank_article as a inner join rank_book as b on a.book_id=b.id 
		where 1=1 ";
		$book_id = I('book_id');
		if(!empty($book_id)){
			$sql.=" and a.book_id={$book_id}";
		}

		//$sql.=" and a.status=1  and b.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."' order by a.oper_date desc";
    
         $sql.=" and a.status=1 and a.county_code='".$_SESSION['user_auth']['COUNTY_CODE']."'  order by a.oper_date desc";


		// $xindes = $m->where($w)->order('oper_date desc')->limit(0,3)->select();
		$xindes = parent::listsBySql($sql,$page_size);
		
		$this->assign('xindes',$xindes);
		//dump($sql);
		$this->display();
	}

	public function manager_xinde_edit($id=0){
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
			if(intval($id>0)){
				$w['id']=$id;
				$re = $m->where($w)->save($d);
			}else{
				$d['oper_date']=time();
				$d['oper_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
				$d['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
				$re = $m->add($d);
			}
			if($re){
				$this->success('数据保存成功',U('manager_xinde')); die;
			}else{
				$this->error('数据添加失败'); die;
			}
		}
		$this->display();
	}

	//阅读会
	public function manager_meeting(){
		$m = M('meeting');
		$w['status']=1;
		$xindes = $m->where($w)->order('create_date desc')->select();
		$this->assign('meetings',$xindes);
		$this->display();
	}

	//阅读会报名
	public function manager_meeting_bao($meeting_id=0){
		$json['success']=false;
		$json['msg']='报名发起失败，请联系书籍管理员';
		if(intval($meeting_id)>0){
			$meeting_model = M('meeting');
			$meeting_info = $meeting_model->find($meeting_id);

			 //查询登录人是不是读书会会员
            $oper_phone= $_SESSION['user_auth']["OPER_LOGIN_CODE"];
            $m=M('user');           
            $oper = $m->where("oper_phone='".$oper_phone."'")->select();
			if($oper==null){
				$json['msg']='您非读书会会员,不予报名!';
			}else{	

			//已经报名   请到人力资源部领取书籍吧
			$m = M('meetingBao');
			$where['bao_oper'] = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$where['status']      = array('gt',0);
			$where['meeting_id']     = $meeting_info['id'];
			$his = $m->where($where)->select();			
			if($his){
				$json['msg']='已经报名，不允许重复报名';
			}else{
				$data['bao_oper'] = $_SESSION['user_auth']['OPER_LOGIN_CODE'];
				$data['bao_date'] = time();
				$data['meeting_id']     = $meeting_info['id'];
				$data['meeting_name']   = $meeting_info['title'];
				$data['status']      = 1;
				$re = $m->add($data);
				if($re){
					$json['success'] = true;
					$json['msg']  = '发起报名成功,请联系读书会管理员';
					$re = parent::sms($_SESSION['user_auth']['OPER_LOGIN_CODE'],'您发起的'.$meeting_info['title'].'报名成功!');
				}	
			}
		}
		}
		$this->ajaxReturn($json); 
	}

	public function manager_meeting_edit($id=0){

		$book_id = I('book_id');
		if(intval($book_id)>0){
			$b = M('book');
			$book = $b->find($book_id);
			$this->assign('book',$book);
		}

		$m = M('meeting');
		if(IS_GET){
			if(intval($book_id)>0){
				$w['book_id']=$book_id;
				$meeting = $m->where($w)->select();
				$this->assign('meeting',$meeting[0]);
			}
		}else{
			$d['book_id']=I('book_id');
			$d['title']=I('title');
			$d['description']=I('description');
			$d['content']=I('content');
			//$d['ying']=I('ying');
			//$d['shi']=I('shi');
			$d['img']=I('img');
			$d['meet_date']=I('meet_date');
			$d['meeting_planner']=I('meeting_planner');

			if(intval($id>0)){
				$w['id']=$id;
				$re = $m->where($w)->save($d);
			}else{
				$d['create_date']=time();
				$d['create_oper']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
				$re = $m->add($d);
			}
			if($re){
				$this->success('数据保存成功',U('manager_book')); die;
			}else{
				$this->error('数据添加失败'); die;
			}
		}
		$this->display();
	}
	
	//会员
	public function manager_vip(){
		$oper_city=$_SESSION['user_auth']['COUNTY_NAME'];
		$m=M('user');
		if($oper_city=='市公司'){
            $sql="select * from rank_user where status='0' and oper_city  NOT IN 
             ('景宁','青田','缙云','庆元','遂昌','龙泉','松阳','云和','南城','莲都') "; 
		}else{
			$sql="select * from rank_user where status='0' and oper_city="
								.$_SESSION['user_auth']['COUNTY_NAME'] ;
		} 
        $arrs = parent::listsBySql($sql,$page_size);      
        $this->assign('arrs',$arrs);
		$this->display();
	}


	//会员申请审核
	public function manager_shenhe(){
		$oper_city=$_SESSION['user_auth']['COUNTY_NAME'];
		$m=M('user');
		if($oper_city=='市公司'){
            $sql="select * from rank_user where status='0' and oper_city  NOT IN 
             ('景宁','青田','缙云','庆元','遂昌','龙泉','松阳','云和','南城','莲都') "; 
		}else{
			$sql="select * from rank_user where status='0' and oper_city="
								.$_SESSION['user_auth']['COUNTY_NAME'] ;
		} 
        $arrs = parent::listsBySql($sql,$page_size);      
        $this->assign('arrs',$arrs);
		$this->display();
	}



    //管理员审核员工信息
    public function manager_shenhe_xinxi(){
    	$oper_num=I('oper_num');    	
    	$m=M('user');
    	$oper=$m->where("oper_num='".$oper_num."'")->find();    	
    	$this->assign('oper',$oper);
    	$this->display();
    	//dump($oper);
    }

    //修改状态
	public function manager_user_tianjia(){
		$oper_num=I('oper_num');		
		$m=M('user');
		$data['status'] =I('shenhe_result') ;		
		$flag=$m->where('oper_num='.$oper_num.'')->save($data);        
		if($flag){			
			$this->success('审核成功!','manager_shenhe');			
	    }else{
	    	$this->error('审核失败!','manager_shenhe');
	    }
	}

	//会员列表
	public function manager_user_list(){
		$m=M('user');
       	$county_name=I('county_name');
        $sql="select * from rank_user where status='1' ";
       	if(!empty($county_name)){
       		if($county_name=='市公司'){
                $sql.= " and oper_city in ('市场经营部','政企客户部','网络部','工程建设部','综合部',
                '财务部','人力资源部','党委办公室（党群工作部）','纪检监察室','工会') ";
       		}else{
       			$sql.="  and oper_city='".$county_name."'";
       		}
       	}
       	$arrs = parent::listsBySql($sql,10);
        $this->assign('arrs',$arrs);
        $this->assign('county_name',$county_name);
		$this->display();		
	}


	public function manager_user_modify(){
		$m=M();
		if(IS_GET){
			$user_id=I('id');
			$where['id']=$user_id;
			$list=$m->table('rank_user')->where($where)->find();
			$this->assign('list',$list);
			$this->display();
		}

		if(IS_POST){
			$id=I('id');
			$data['oper_city']=I('oper_city');
			$data['oper_name']=I('oper_name');
			$data['oper_num']=I('oper_num');
			$data['oper_phone']=I('oper_phone');
			$m=M('user');
			$where['id']=$id;
			$flag=$m->where($where)->save($data);
			if($flag){
				$this->success('保存成功','manager_user_list');

			}else{
				$this->error('保存失败');
			}
		}


		

	}
	

	//网站
	public function manager_site(){
		$m = M('attr');
		$attrs = $m->where('status=1')->cache(true,1800)->select();
		$this->assign('attrs',$attrs);
		$this->display();
	}

	//资源
	public function manager_resource(){
		$this->display();
	}

	//活动
	public function manager_ploy(){
		$m = M('ploy');
		$w['status']=1;
		$w['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
		$ploys = $m->where($w)->order('oper_date desc')->select();
		$this->assign('ploys',$ploys);
		$this->display();
	}

	//活动编辑
	public function manager_ploy_edit($id=0){
		$m = M('ploy');
		if(IS_GET){
			if(intval($id)>0){
				$ploy = $m->find($id);
				$this->assign('ploy',$ploy);
			}
		}else{
			$d['title']=I('title');
			$d['content']=I('content');
			$d['status']=I('status');
			$d['county_code']=$_SESSION['user_auth']['COUNTY_CODE'];
			if(intval($id>0)){
				$w['id']=$id;
				$re = $m->where($w)->save($d);
			}else{
				$d['oper_date']=time();
				$d['oper_id']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
				$re = $m->add($d);
			}
			if($re){
				$this->success('数据保存成功',U('manager_ploy')); die;
			}else{
				$this->error('数据添加失败'); die;
			}
		}
		$this->display();
	}

 
    //报名人员列表
    public function manager_meeting_yh_list(){
     	$meeting_id=I('meet_id');        
     	$m=M('meeting_bao');     	
        $sql="select a.id,a.meeting_id,a.meeting_name,b.oper_city,b.oper_name,
            b.oper_num,b.oper_phone from rank_meeting_bao a,rank_user b where a.status='1' and
            a.bao_oper=b.oper_phone  and a.meeting_id='".$meeting_id."'and a.bao_oper 
            not in (select oper_phone from rank_meeting_result c where c.meeting_id='".$meeting_id."')";                           
       //$list=$m->query($sql);
       $list= parent::listsBySql($sql,15);	      
       $this->assign('list',$list);
       $this->display();

    }


       //确认到会人员
     public function manager_meeting_yh_queren(){
     	$meeting_id=I('meeting_id');
     	$chlist=I('ids');
        $bao_opers=explode(',',$chlist);
     	   $bao_oper='';
        for($i=0;$i<count($bao_opers);$i++){
        	if($i<count($bao_opers)-1){        		 
        		$bao_oper.="'".$bao_opers[$i]."'".",";        		      		 
        	}else{        		
        		$bao_oper.="'".$bao_opers[$i]."'";       		 
        	} 
     	} 
        
        $m=M('meeting_result'); 
        $sql="select a.meeting_id,a.meeting_name,a.bao_oper,b.oper_city from rank_meeting_bao a,
                 rank_user b  where a.meeting_id='".$meeting_id."' and a.bao_oper in 
                 (".$bao_oper.") and a.bao_oper=b.oper_phone";        
        $list=$m->query($sql);
        //dump($list);

          //获取本月第一天
        $sql="SELECT DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
        $first= $m->query($sql);           
         //获取本月最后一天
        $sql="SELECT LAST_DAY(CURDATE()) last_date";
        $last= $m->query($sql); 
      
     // $oper_date=time();       	 
      foreach ($list as $key => $value) {      		
      	$data['meeting_id']=$value['meeting_id'];
      	$data['meeting_city']=$value['oper_city'];
      	$data['meeting_name']=$value['meeting_name'];
      	$data['oper_phone']=$value['bao_oper'];
      	$data['oper_date']=time(); 
      	$data['status']='1';
      	$m=M('meeting_result'); 
      	$flag=$m->add($data);

       if($flag){
       	    $m=M('user');       	  
       	    $oper= $m->where("oper_phone='".$value['bao_oper']."'")->find();
       	 	if(!empty($oper)){        	 	 	      	 	      	 		
	       	 	$m=M('user_score');
	       	 	//查询本月积分
	       	 	$sql="select IFNULL(sum(score),0) m_totle from rank_user_score
               			where oper_phone=".$oper['oper_phone']." and 
               			(SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' 
               			and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'"; 
                $month_jf= $m->query($sql);
                $score=intval($month_jf[0]['m_totle']);
                if($score<=5){
                	$score1=5;
                }else{
                	$score1=10-$score;
                }
                
	       	 	$da['oper_city']=$oper['oper_city'];
	       	 	$da['oper_name']=$oper['oper_name'];
	       	 	$da['oper_num']=$oper['oper_num'];
	       	 	$da['oper_phone']=$oper['oper_phone'];
	       	 	$da['meeting_id']=$data['meeting_id'];
	       	 	$da['sc_name']=$data['meeting_name'];
				$da['sc_reason']="参加读书会";
				$da['score']=$score1;			
				$da['oper_date']=time();
				$da['status']='1';
				$da['smonth']=date('Ym');
				$flag=$m->add($da);
					if(empty($flag)){
						$this->error ('导入失败!');						
					}							
       	 	}       	  
       }
       $this->success('导入成功'); 
       }
    }
   

    //确认策划人
    public function manager_meeting_planner(){
    	if(IS_GET){
    		$meet_id=I('meet_id');
            $m=M('meeting');
            $list=$m->where("id='".$meet_id."'")->find();
            $meeting_planner= $list['meeting_planner'];
            $meeting_planner1=explode(',',$meeting_planner);
            $planner_no=count($meeting_planner1);
            $this->assign('list',$list);
            $this->assign('meeting_planner1',$meeting_planner1);
            $this->assign('planner_no',$planner_no);
          

            $sql="select count(*) num from rank_user_score  where meeting_id='".$meet_id."' and 
                      sc_reason='读书会策划' ";
            $list1=$m->query($sql);
            $this->assign('list1',$list1);           
            $this->display();
          
          

    	}
    	if(IS_POST){
    		$m=M('user_score');
    		$meeting_id=I('meeting_id');
    		$meeting_title=I('meeting_title');
    		$meeting_planner=I('meeting_planner');
    		$meeting_planner_dept=I('meeting_planner_dept');
    		$meeting_planner_num=I('meeting_planner_num');
    		$meeting_planner_phone=I('meeting_planner_phone');

    		  //获取本月第一天
	        $sql="SELECT DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
	        $first= $m->query($sql);           
	         //获取本月最后一天
	        $sql="SELECT LAST_DAY(CURDATE()) last_date";
	        $last= $m->query($sql); 
          
            foreach ($meeting_id as $k=>$v) { 
            	$data['meeting_id']=trim($meeting_id[$k]); 
                $data['oper_city']=trim($meeting_planner_dept[$k]); 
                $data['oper_name']=trim($meeting_planner[$k]); 
                $data['oper_num']=trim($meeting_planner_num[$k]); 
                $data['oper_phone']=trim($meeting_planner_phone[$k]);
                $data['sc_name']=trim($meeting_title[$k]); 
                $data['sc_reason']="读书会策划";

                $sql="select IFNULL(sum(score),0) m_totle from rank_user_score
               			where oper_phone=".$data['oper_phone']." and 
               			(SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' 
               			and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'"; 
                $month_jf= $m->query($sql);
                $score=intval($month_jf[0]['m_totle']);
                if($score<=5){
                	$score1=5;
                }else{
                	$score1=10-$score;
                }
                $data['score']=$score1; 
                $data['oper_date']=time(); 
                $data['status']=1;
                $data['smonth']=date('Ym');
                if(!empty($data['oper_name'])){
                  $flag=$m->add($data);
                }
            } 
            if($flag){
        		$this->success('数据保存成功!');
      		}else{
        		$this->error('数据保存失败!');
            } 
    	}
    }



    //管理员审核心得
    public function manager_xinde_shenhe($id=0){
    	$book_id = I('book_id');
    	$id = I('id');    	
		if(intval($book_id)>0){
			$b = M('book');
			$book = $b->find($book_id);
			$this->assign('book',$book);
		}
		//
		if(intval($id)>0){
			$b = M('article');
			$xinde = $b->find($id);
			$this->assign('xinde',$xinde);
		}     	
     	$this->display();
     }


    //心得审核结果
    public function manager_xinde_shenhe_result($id=0){
      	$id = I('id');
      	$book_id = I('book_id');
      	$shenhe=I('shenhe'); 
      	$oper_id=I('oper_id'); 
      	$title=I('title');        
        //审核通过
        if($shenhe=='1'){ 
           	$m=M("article");
        	$data['shenhe']=$shenhe;
        	$w['id']=$id;
			$flag=$m->where($w)->save($data); // 根据条件更新记录			
			if($flag){	        	 
	        	 $book_id=intval($book_id);	        	
	        	 $m=M('meeting');
	        	 //$list=$m->where("book_id=".$book_id."")->select();
	        	 //获得个人信息	        	
	        	// $w['oper_phone']=$oper_id;	        	
	        	 $m=M('user');	        	
	        	 $oper=$m->where("oper_phone='".$oper_id."'")->find();
	        	 $data['oper_city']=$oper['oper_city'];
	        	 $data['oper_name']=$oper['oper_name'];
				 $data['oper_num']=$oper['oper_num'];
				 $data['oper_phone']=$oper['oper_phone'];
				 $data['sc_name']=$title; 
        	 
	        	 //判断是不是必读书
	        	 $m=M('user_score');

        	//if(!empty($list)){
                $data['sc_reason']="分享心得";

                  //获取本月第一天
		        $sql="SELECT DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
		        $first= $m->query($sql);           
		         //获取本月最后一天
		        $sql="SELECT LAST_DAY(CURDATE()) last_date";
		        $last= $m->query($sql);
		        $sql="select IFNULL(sum(score),0) m_totle from rank_user_score
               			where oper_phone=".$data['oper_phone']." and 
               			(SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' 
               			and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'"; 
                $month_jf= $m->query($sql);
                $score=intval($month_jf[0]['m_totle']);
                if($score<=7){
                	$score1=3;
                }else{
                	$score1=10-$score;
                }
                $data['score']=$score1;
                $data['oper_date']=time();
                $data['status']=1;
                $data['smonth']=date('Ym');

                $flag=$m->add($data);

                if($flag){
                	$this->success('审核成功!','manager_xinde');
                }else{
                    $this->error('审核出错!','manager_xinde');	
                }
            }
        }else{
        	$m=M("article");
        	$data['shenhe']=$shenhe;
        	$w['id']=$id;
			$flag=$m->where($w)->save($data); // 根据条件更新记录 			
			if($flag){
				$this->success('审核成功!','manager_xinde');
			}      	
        }
     }

    
     
     //管理员删除阅读会报名人员
    public function bao_shanchu(){
         $id=I('meeting_bao_id');
         $m=M('meeting_bao');                
         $flag=$m->where("id=".$id."")->delete();
         if($flag){           
            $msg='删除成功!';          
         }else{            
            $msg='删除失败!';
         }
         $this->ajaxReturn($msg);
     }


     
    //书籍推荐
	public function manager_book_tuijian(){
		$m = M('book_tuijian');
		$county_code=$_SESSION['user_auth']['COUNTY_CODE'];
	    $sql="select * from rank_book_tuijian where county_code='".$county_code."' 
	                     order by  book_date desc ";	   
	    $list= parent::listsBySql($sql,10);	   
	    $this->assign('list',$list);
		$this->display();
		//dump($_SESSION);
	}

	//管理员审核推荐书籍
	public function manager_book_tuijian_shenhe(){			
		if(IS_GET){
			$id=I('get.id');
			$m = M('book_tuijian');
			$list=$m->where('id='.$id)->find();
			$this->assign('list',$list);
			$this->display();		
		}
		if(IS_POST){
			$id=intval(I('id'));			
			$status=intval(I('status'));
			$book_name=I('book_name');
			$m = M('book_tuijian');
			$data['status']=$status;
			$data['shenhe_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$data['shenhe_name']=$_SESSION['user_auth']['OPER_NAME'];
			$flag=$m->where('id='.$id)->save($data);
			if($flag){
				if($status==1){
	                 $oper_phone=I('book_oper_phone');
	                 $m=M('user');
	                 $oper=$m->where("oper_phone='".$oper_phone."'")->find();                
	                 if(!empty($arr)){
	                 	$data['oper_city']=$oper['oper_city'];
	                 	$data['oper_name']=$oper['oper_name'];
	                 	$data['oper_num']=$oper['oper_num'];
	                 	$data['oper_phone']=$oper_phone;
	                 	$data['sc_name']=$book_name;
	                 	$data['sc_reason']='推荐书籍';

	                 	    //获取本月第一天
				        $sql="SELECT DATE_ADD(CURDATE(), INTERVAL - DAY(CURDATE()) + 1 DAY) first_date";
				        $first= $m->query($sql);           
				         //获取本月最后一天
				        $sql="SELECT LAST_DAY(CURDATE()) last_date";
				        $last= $m->query($sql);
				        $sql="select IFNULL(sum(score),0) m_totle from rank_user_score
		               			where oper_phone=".$data['oper_phone']." and 
		               			(SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))>='".$first[0]['first_date']."' 
		               			and (SELECT FROM_UNIXTIME(oper_date,'%Y-%m-%d'))<='".$last[0]['last_date']."'"; 
		                $month_jf= $m->query($sql);
		                $score=intval($month_jf[0]['m_totle']);
		                if($score<=8){
		                	$score1=2;
		                }else{
		                	$score1=10-$score;
		                }

		                $data['score']=$score1;

	                 	$data['oper_date']=time();  
	                 	$data['status']=1;
	                 	$m=M('user_score');	
	                 	$flag=$m->add($data);	                 	
	                 	if(!$flag){	                 		
	                 		$this->error('保存失败!');
	                 	}	                 	
	                }
			   }
               $this->success('保存成功!');
			}else{
				$this->error('保存失败!');
			}			
		}		
	}

	//管理员列表
    public function manager_list(){
    	$oper_city=$_SESSION['user_auth']['COUNTY_NAME'];
    	$m=M('manager'); 
    	$sql="select * from rank_manager where status='1' "; 
       /**   
       if($oper_city=='市公司'){
    		$sql="select * from rank_manager where status=1 and county_code not in(
    		'景宁','青田','缙云','庆元','遂昌','龙泉','松阳','云和','南城','莲都')";
    	}else{
    		$sql="select * from rank_manager where status=1 and county_code='" 
    		  .$oper_city."'";
    	} 
    	**/
    	$arr= parent::listsBySql($sql,10);
    	//$arr= $m->query($sql);
    	$this->assign('arr',$arr);
    	$this->display();
    }
    

    //修改管理员信息
    public function manager_modify(){
    	if(IS_GET){
    		$m=M('manager');
    		$id=I('get.id');
            $arr = $m->where('id='.$id)->select();        
            $this->assign('arr',$arr);
            $oper_city=intval($_SESSION['user_auth']['COUNTY_CODE']);
    		$this->assign('oper_city',$oper_city);     		         
            $this->display(); 
    	}
    	if(IS_POST){
    		$id=I('post.id');
    		$data['bill_id']=I('post.bill_id');
    		$data['county_code']=I('post.county_code');
    		$data['manager_name']=I('post.manager_name');
    		$data['manager_op']=I('post.manager_op');
    		$m=M('manager');    		    		
    		$flag=$m->where('id='.$id)->save($data);
    		if($flag){
    			$this->success('提交成功!');
    		}else{
    			$this->error('提交失败!');
    		} 
    	}     	   	
    }

    //删除管理员
    
    public function manager_del(){
    	$m=M('manager');
    	$bill_id=I('bill_id');
    	$data['status']=0;    	
    	$flag=$m->where("bill_id='".$bill_id."'")->save($data); // 根据条件更新记录
    	if($flag){
    		//$this->success('删除成功!');
    		$res['status']='1';
    		$res['msg']="删除成功!";
    	}else{
    		//$this->error('删除失败');
    		$res['msg']="删除失败!";
    		$res['status']='0';
    	} 
    	$this->ajaxReturn($res);   	
    }
    
	 
    //添加管理员
    public function manager_add(){
    	if(IS_GET){
    		$oper_city=intval($_SESSION['user_auth']['COUNTY_CODE']);
    		$this->assign('oper_city',$oper_city);
    		$this->display();
    	}
    	if(IS_POST){
    		$bill_id=I('bill_id');
    		$data['bill_id']=$bill_id;

    		$data['county_code']=I('county_code');
    		$data['manager_name']=I('manager_name');
    		$data['manager_op']=I('manager_op');
    		$data['status']='1';
    		$m=M('manager');

    		$user=$m->where("bill_id='".$bill_id."'")->select();
    		if(!empty($user)){
    			$flag=$m->where("bill_id='".$bill_id."'")->save($data);
    		}else{
    			$flag=$m->add($data);
    		}
    		if($flag){
    			$this->success('提交成功!');
    		}else{
    			$this->error('提交失败!');
    		}
    	}    		
    }
    
      //管理员待办  
    public  function  manager_qingjia_gl(){
    	$oper_phone=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
    	$m=M('manager');
    	$w['bill_id']=$oper_phone;
    	$list=$m->where($w)->find();
    	$this->assign('list',$list);    	
    	$this->display();
    }

    //一级管理员请假审核列表
    public function manager_qingjia_list1(){
    	$m=M('manager');
    	$oper_phone=$_SESSION['user_auth']['OPER_LOGIN_CODE'];    	
    	$list=$m->where("bill_id='".$oper_phone."'")->find();      
        $oper_city=$list['county_code'];
        $manager_op=$list['manager_op'];
        $this->assign('manager_op',$manager_op); 


    	$m=M('meeting_qingjia');
    	$sql="select * from rank_meeting_qingjia where status=1 and  step=0 
    	      and shenpi_result=0  and  oper_city='".$oper_city."' ";
    	$arrs=$m->query($sql);    	
    	$this->assign('arrs',$arrs);    	
    	$this->display('manager/manager_qingjia_list');
    }

    //请假审核页面
    public function manager_qingjia_shenhe(){
    	if(IS_GET){
    		$manager_op=I('manager_op');    		
    		$m=M('meeting_qingjia');
	    	$qingjia_id=I('id');
	    	$w['id']=$qingjia_id;
	    	$arr=$m->where($w)->select();
	        $this->assign('arr',$arr);
	        $m=M('meeting');
	        $meeting_id=intval($arr[0]['meeting_id']);	         	      
	        $meeting=$m->where('id='.$meeting_id)->getField('title');
	        $this->assign('meeting',$meeting);
	        $this->assign('manager_op',$manager_op);       
	    	$this->display();
    	}    	
     } 

      //一级管理员请假审核结果
	public function manager_qingjia_sh_result(){
		  $qingjia_id=intval(I('qingjia_id'));
		  $shenhe_result=I('shenhe_result');
		  $data['qingjia_id']=$qingjia_id;		  
		  $data['shenhe_result']=$shenhe_result;
		  $data['shenhe_oper']=$_SESSION['user_auth']['OPER_NAME'];
		  $data['shenhe_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		  if($shenhe_result==0){
		  		$data['next_step']=0;		  		
		  	}else{
		  		$data['next_step']=2;		  		
		  }
		  $m=M('qingjia_item');
		  $flag=$m->add($data);
		  if($flag){
		  	if($shenhe_result==0){
		  		$da['shenpi_result']=2;		  		
		  	}else{
		  		$da['shenpi_result']=1;		  		
		  	}
		  	$da['step']=1;
		  	$m=M('meeting_qingjia');		  	
		  	$flag=$m->where('id='.$qingjia_id)->save($da);
		  	if($flag){
               $this->success('审核成功!','manager_qingjia_list1');		  		
		  	}else{
		  		$this->error('审核失败!');
		  	}
		  }else{
		  	$this->error('审核失败!');
		  } 	 
	}

	 //二级管理员请假审核列表
    public function manager_qingjia_list2(){
    	$m=M('manager');
    	$oper_phone=$_SESSION['user_auth']['OPER_LOGIN_CODE'];    	
    	$list=$m->where("bill_id='".$oper_phone."'")->select();      
        $oper_city=$list[0]['county_code'];      
        $manager_op=$list[0]['manager_op'];
        $this->assign('manager_op',$manager_op);

    	$m=M('meeting_qingjia');       
    	if($oper_city=='市公司'){    		
    		$sql=" select a.id,a.oper_name,a.oper_city,a.meeting_id,b.shenhe_oper,
    		     b.shenhe_result FROM rank_meeting_qingjia a, rank_qingjia_item b  
				 WHERE b.shenhe_result=1 AND    a.id =b.qingjia_id  AND a.step=1 AND 
				 b.next_step=2    AND a.oper_city IN ('市场经营部','政企客户部','网络部',
				 '工程建设部','综合部','财务部', '人力资源部','党委办公室(党群工作部)',
				 '纪检监察室','工会')";
    	}else{
    		$sql="select a.id,a.oper_name,a.oper_city,a.meeting_id,b.shenhe_oper,
    		     b.shenhe_result FROM rank_meeting_qingjia a, rank_qingjia_item b  
				 WHERE b.shenhe_result=1 AND a.id =b.qingjia_id  AND a.step=1 
				 AND b.next_step=2  and a.oper_city='".$oper_city."' ";
    	}
    	$arrs=$m->query($sql);    	
    	$this->assign('arrs',$arrs);    	 	
    	$this->display('manager/manager_qingjia_list');
    } 
     //二级管理员请假审核结果   
    public function manager_qingjia_sh_result2(){
    	  $qingjia_id=intval(I('post.qingjia_id'));
		  $shenhe_result=I('post.shenhe_result');
		  $data['qingjia_id']=$qingjia_id;		  
		  $data['shenhe_result']=$shenhe_result;
		  $data['shenhe_oper']=$_SESSION['user_auth']['OPER_NAME'];
		  $data['shenhe_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		  if($shenhe_result==0){
		  		$data['next_step']=0;		  		
		  	}else{
		  		$data['next_step']=3;		  		
		  }
		  $m=M('qingjia_item');
		  $flag=$m->add($data);
		  if($flag){
		  	if($shenhe_result==0){
		  		$da['shenpi_result']=2;		  		
		  	}else{
		  		$da['shenpi_result']=1;		  		
		  	}
		  	$da['step']=2;
		  	$m=M('meeting_qingjia');		  	
		  	$flag=$m->where('id='.$qingjia_id)->save($da);
		  	if($flag){
               $this->success('审核成功!','manager_qingjia_list2');		  		
		  	}else{
		  		$this->error('审核失败!');
		  	}
		  }else{
		  	$this->error('审核失败!');
		  }     	
    }

     //三级管理员请假审核列表
    public function manager_qingjia_list3(){
    	$m=M('manager');
    	$oper_phone=$_SESSION['user_auth']['OPER_LOGIN_CODE'];    	
    	$list=$m->where("bill_id='".$oper_phone."'")->select();      
        $oper_city=$list[0]['county_code'];      
        $manager_op=$list[0]['manager_op'];
        $this->assign('manager_op',$manager_op);

    	$m=M('meeting_qingjia');       
    	if($oper_city=='市公司'){    		
    		$sql=" select a.id,a.oper_name,a.oper_city,a.meeting_id,b.shenhe_oper,
    		     b.shenhe_result FROM rank_meeting_qingjia a, rank_qingjia_item b  
				 WHERE b.shenhe_result=1 and a.id =b.qingjia_id  and a.step=2 and 
				 b.next_step=3  and  a.oper_city IN ('市场经营部','政企客户部','网络部',
				 '工程建设部','综合部','财务部', '人力资源部','党委办公室(党群工作部)',
				 '纪检监察室','工会')";
    	}else{
    		$sql="select a.id,a.oper_name,a.oper_city,a.meeting_id,b.shenhe_oper,
    		     b.shenhe_result FROM rank_meeting_qingjia a, rank_qingjia_item b  
				 WHERE b.shenhe_result=1 and a.id =b.qingjia_id  and a.step=2 
				 and b.next_step=3  and a.oper_city='".$oper_city."' ";
    	} 
    	$arrs=$m->query($sql);    	
    	$this->assign('arrs',$arrs);    	 	
    	$this->display('manager/manager_qingjia_list');
    }   

    //三级管理员请假审核结果   
    public function manager_qingjia_sh_result3(){
    	  $qingjia_id=intval(I('post.qingjia_id'));
		  $shenhe_result=I('post.shenhe_result');
		  $data['qingjia_id']=$qingjia_id;		  
		  $data['shenhe_result']=$shenhe_result;
		  $data['shenhe_oper']=$_SESSION['user_auth']['OPER_NAME'];
		  $data['shenhe_phone']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
		  if($shenhe_result==0){
		  		$data['next_step']=0;		  		
		  	}else{
		  		$data['next_step']=4;		  		
		  }
		  $m=M('qingjia_item');
		  $flag=$m->add($data);
		  if($flag){
		  	if($shenhe_result==0){
		  		$da['shenpi_result']=2;		  		
		  	}else{
		  		$da['shenpi_result']=1;		  		
		  	}
		  	$da['step']=3;
		  	$m=M('meeting_qingjia');		  	
		  	$flag=$m->where('id='.$qingjia_id)->save($da);
		  	if($flag){
               $this->success('审核成功!','manager_qingjia_list3');		  		
		  	}else{
		  		$this->error('审核失败!');
		  	}
		  }else{
		  	$this->error('审核失败!');
		  }     	
    } 

     //我的待办
    public function manager_need_todo(){
	    $smonth=I('smonth');
	    $county_name=I('county_name');
	    $bill_id=I('bill_id');


	    $m=M('user_score');
	    $sql="select id,oper_city,oper_name,oper_num,oper_phone,sc_name,sc_reason,
	         score, FROM_UNIXTIME(oper_date, '%Y-%m-%d') im_date,smonth
	         from rank_user_score where status=1 ";

	    if(!empty($smonth)){
	     $sql.="and smonth='".$smonth."'";
	    }
	    if(!empty($county_name)){
	     $sql.="and oper_city='".$county_name."'";
	    }

	    if(!empty($bill_id)){
	     $sql.="and oper_phone='".$bill_id."'";
	    }

	    $sql.="order by oper_date desc";
	    $scores=parent::listsBySql($sql,10);
	    $this->assign('scores',$scores);
	    $this->assign('smonth',$smonth);
	    $this->assign('county_name',$county_name);
	    $this->assign('bill_id',$bill_id);
		$this->display();
    }


  public function manager_score(){
    $smonth=I('smonth');
    $county_name=I('county_name');
    $bill_id=I('bill_id');


    $m=M('user_score');
    $sql="select id,oper_city,oper_name,oper_num,oper_phone,sc_name,sc_reason,
         score, FROM_UNIXTIME(oper_date, '%Y-%m-%d') im_date,smonth
         from rank_user_score where status=1 ";

    if(!empty($smonth)){
    	$sql.="and smonth='".$smonth."'";
    }
    if(!empty($county_name)){
     	$sql.="and oper_city='".$county_name."'";
    }

    if(!empty($bill_id)){
     	$sql.="and oper_phone='".$bill_id."'";
    }
    $sql.=" order by oper_date desc";

    $scores=parent::listsBySql($sql,10);
    $this->assign('scores',$scores);

    $this->assign('smonth',$smonth);
    $this->assign('county_name',$county_name);
    $this->assign('bill_id',$bill_id);


  	$this->display();
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
		for ($row = 1; $row <= $highestRow; $row++) { 
			for ($col = 0; $col < $highestColumnIndex; $col++) { 
                 $excelData[$row][] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
           } 
        } 
        return $excelData;
	}


    //积分导入
	public function manager_score_import(){

		if(IS_GET){
			$m=M('meeting');
			$meeting=$m->field('id,title')->order('id desc')->select();
			$this->assign('meeting',$meeting);
			$this->display();
		}


		if(IS_POST){
			$meeting=I('meeting');
			$meeting_tmp=explode('|',$meeting);
			$meeting_id=intval($meeting_tmp[0]);
			$meeting_title=$meeting_tmp[1];
			$meeting_date=I('meeting_date');
            $oper_date=strtotime($meeting_date);
            $smonth=date('Ym',$oper_date);
          
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
		        $curmonth=date('Ym');

				if(!empty($res)){			
					foreach ( $res as $k => $v ){
						if ($k != 0){
							$data['meeting_id'] = $meeting_id;
							$data['meeting_name'] = $meeting_title;
							$data['meeting_city'] = $v[2];					
							$data['oper_phone'] =strval($v[5]) ;
							$data['oper_date']=$oper_date;
							$data['status']='1';	           
							//判断记录是否存在
							$m = M('meeting_result');

							$where['meeting_id']=$meeting_id;
							$where['oper_phone']=strval($v[5]);
							
							$user=$m->where($where)->find(); 

							
							//不存在添加
							if(empty($user)){                   	
								$flag=$m->add($data);   

								if($flag){
								    $m=M('user_score');						    
								    $da['oper_city']=$v[2];
								    $da['oper_name']=$v[3];
								    $da['oper_num']=$v[4];
								    $da['oper_phone']=strval($v[5]);
								    $da['meeting_id'] = $meeting_id;
								    $da['sc_name']=$meeting_title;
								    $da['sc_reason']="参加读书会";
								    
					                $map['oper_phone']=$da['oper_phone'];
					                $map['smonth']=$curmonth;

					                $score_count=$m->where($map)->sum('score');
					                $score=intval($score_count);


					                if($score<=5){
					                	$score1=5;
					                }else{
					                	$score1=10-$score;
					                }
					                $da['score']=$score1;
								    $da['oper_date']=$oper_date; 
								    $da['status']='1';  
								    $da['smonth']=$smonth;                 
								    $flag2 = $m->add($da);

								    if(!$flag2){
		                              	$this->error('导入失败!3');
		                              	exit;
								    }
								}else{
									$this->error('导入失败!2');
									exit;
								}
							}else{ 
							   //存在更新              	
								$flag=$m->where($where)->save($data);
								if(!$flag){
									$this->error('导入失败!1');
									exit;
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
	}

    //积分删除
	public function score_del(){
		$score_id=I('score_id');
		$m=M('user_score');
		$data['status']='0';
		$w['id']=$score_id;
		$flag=$m->where($w)->save($data);
		if($flag){
	    $msg='1';
		}
		$this->ajaxReturn($msg);
	}


	//积分修改
  public function manager_score_modify(){
  	if(IS_GET){
  		$score_id=I('score_id');
  		$m=M('user_score');
  		$sql="select id,oper_city,oper_name,oper_num,oper_phone,sc_name,sc_reason,
         score, FROM_UNIXTIME(oper_date, '%Y-%m-%d') im_date,smonth
         from rank_user_score where status=1 and id='".$score_id."'";
  		//$w['id']=$score_id;

  		//$list=$m->where($w)->find();
         $list=$m->query($sql);
  		$this->assign('list',$list);
  		$this->display();

  	}

  	if(IS_POST){
  		$score_id=I('score_id');

  		$data['oper_name']=I('oper_name');
  		$data['oper_city']=I('oper_city');
  		$data['oper_num']=I('oper_num');
  		$data['oper_phone']=I('oper_phone');
  		$data['sc_name']=I('sc_name');
  		$data['sc_reason']=I('sc_reason');
  		$data['score']=I('score');
  		$data['smonth']=I('smonth');
  		$im_date=I('im_date');
  		$data['oper_date']=strtotime($im_date);
  		$m=M('user_score');
  		$w['id']=$score_id;
      
  		$flag=$m->where($w)->save($data);

  		if($flag){
          $this->success('数据保存成功!','manager_score');
  		}else{
  			$this->error('数据保存失败!');
  		}
  		
  	}

  }

    //阅读会积分导出
    public function user_score_exp(){
        $smonth=I('smonth');
        $county_name=I('county_name');
        $bill_id=I('bill_id');
        //$qu_name=I('qu_name');
    
        $m=M('user_score');
        $sql="select id,oper_city,oper_name,oper_num,oper_phone,sc_name,sc_reason,
         score, FROM_UNIXTIME(oper_date, '%Y-%m-%d') im_date,smonth
         from rank_user_score where status=1 ";

	    if(!empty($smonth)){
	     $sql.="and smonth='".$smonth."'";
	    }
	    if(!empty($county_name)){
	     $sql.="and oper_city='".$county_name."'";
	    }

	    if(!empty($bill_id)){
	     $sql.="and oper_phone='".$bill_id."'";
	    }
	    $sql.=" order by oper_date desc";

       
        $elist = $m->query($sql);

        //dump($elist);

       
     
          $filename="阅读会积分.xls";
          $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
          header("Content-Type: application/force-download");
          header("Content-Type: application/octet-stream");
          header("Content-Type: application/download");
          header('Content-Type: application/vnd.ms-excel');
          header("Content-Transfer-Encoding: binary");
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Pragma: no-cache");
          header('Content-Disposition:inline;filename="'.$filename.'"');//attachment和inline的方式就是保存时的弹窗不一样
          ob_end_clean();//清除缓冲区,避免乱码（不清除缓冲区,下载的数据就会乱码）

          //创建一个excel对象
          vendor("PHPExcel.PHPExcel");//导入PHPExcel类库
          $objPHPExcel = new \PHPExcel();
          // Set properties  设置文件属性（右键文件属性看到的内容）
          $objPHPExcel->getProperties()->setCreator("ctos")
              ->setLastModifiedBy("ctos")
              ->setTitle("Office 2007 XLSX Test Document")
              ->setSubject("Office 2007 XLSX Test Document")
              ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
              ->setKeywords("office 2007 openxml php")
              ->setCategory("Test result file");

              // set width
              
              //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
              //$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
         
              
              // 设置行高度
              $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
             // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);
             

            
              // 字体和样式
              $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
              $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);
              //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
              $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
              $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
              $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

             
              // 合并
              //$objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
             

              // set table header content  设置表头名称 
              $objPHPExcel->setActiveSheetIndex(0)

                  ->setCellValue('A1', '编号')
                  ->setCellValue('B1', '月份')
                  ->setCellValue('C1', '部门')
                  ->setCellValue('D1', '姓名')
                  ->setCellValue('E1', '员工编号')
                  ->setCellValue('F1', '手机号码')
                  ->setCellValue('G1', '阅读会名称')
                  ->setCellValue('H1', '积分类型')
                  ->setCellValue('I1', '积分')
                  ->setCellValue('J1', '导入时间');

              //将数据写入列
              if(count($elist) > 0){
                  foreach($elist as $k => $v){
                      $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['id']);  
                      $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['smonth']);
                      $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['oper_city']);
                      $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['oper_name']);  
                      $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['oper_num']);
                      $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['oper_phone']);  
                      $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['sc_name']);
                      $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['sc_reason']);  
                      $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['score']);
                      $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['im_date']);
                  }
              }

              $objPHPExcel->getActiveSheet()->setTitle('阅读会积分');//sheet表名称
              // Set active sheet index to the first sheet, so Excel opens this as the first sheet
              $objPHPExcel->setActiveSheetIndex(0);
              
              vendor("PHPExcel.PHPExcel\IOFactory");
              $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');

              $objWriter->save('php://output');
              exit;
        }



}

?>