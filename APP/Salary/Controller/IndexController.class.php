<?php

namespace Salary\Controller;

class IndexController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
    protected function _initialize(){
        if($_SESSION['salary']['login_time']&&time()-$_SESSION['salary']['login_time']>300){
            unset($_SESSION['salary']['salary_pwd']);
            unset($_SESSION['salary']['login_time']);           
            redirect("/ranking/Salary/Index/salary_regist", 0);
        }
        //开启session并更新时间
        session_start();
        $_SESSION['salary']['login_time']=time();  

        $search_user_num=I('search_user_num');
        $_SESSION['salary']['user_num']=$search_user_num;   

        if(!empty($_SESSION['salary']['user_num'])){
            $m=M();
            $list=$m->table('mz_user.ls_yd_user')->where("user_id='%s'",$_SESSION['salary']['user_num'])->select();
            $_SESSION['salary']['bill_id']=$list[0]['BILL_ID'];
        }

        if(!isset($_SESSION['salary']['salary_pwd'])||empty($_SESSION['salary']['salary_pwd'])){
            redirect("/ranking/Salary/Login/salary_regist", 0);
        } 

    }
  

    //员工基本信息
    public function salary_jiben(){ 
        $m = M();  
        if(!empty($_SESSION['salary']['bill_id'])){
            $bill_id=$_SESSION['salary']['bill_id'];
        }else {
            $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
        }  
        $sql ="select * from mz_user.ls_yd_user where bill_id='%s'";
        $list = $m->query($sql,$bill_id);   
        $this->assign('list',$list);
        $this->display('Index/salary_jiben'); 
    } 


      //薪酬首页
    public function salary_index(){ 
        $m=M('user_salary_pwd'); 
        $sel_data=I('sel_data');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            //年度平均工资和年度总工资
            $sql1="select nvl(round(avg(yingfa_hj),2),0) yingfa_hj_a,
                    nvl(round(avg(month_sf),2),0) month_sf_a,
                    nvl(round(sum(yingfa_hj),2),0) yingfa_hj_s ,
                    nvl(round(sum(month_sf),2),0) month_sf_s 
                    from ( select avg(yingfa_hj) yingfa_hj, avg(month_sf) month_sf, 
                    fafang_month from  rank_user_salary_mx where fafang_month>= 
                    (select to_char(trunc(sysdate,'yy'),'yyyy-MM') from dual)
                    group by fafang_month order by fafang_month desc )"; 

            //每月应发实发工资       
            if(empty($sel_data)){
                $sql="select rownum,yingfa_hj,month_sf,fafang_month from (                 
                    select round(avg(yingfa_hj),2) yingfa_hj,round(avg(month_sf),2) month_sf,
                    fafang_month  from rank_user_salary_mx 
                    group by fafang_month  order by FAFANG_MONTH desc                 
                    ) where rownum<13  order by fafang_month desc"; 
            }elseif($sel_data=='3'){
                $sql="select rownum,yingfa_hj,month_sf,fafang_month from (                 
                    select round(avg(yingfa_hj),2) yingfa_hj,round(avg(month_sf),2) month_sf,
                    fafang_month  from rank_user_salary_mx 
                    group by fafang_month  order by FAFANG_MONTH desc        
                    ) where rownum<4  order by fafang_month desc";            
            }elseif ($sel_data=='6') {
                $sql="select rownum,yingfa_hj,month_sf,fafang_month from (                 
                    select round(avg(yingfa_hj),2) yingfa_hj,round(avg(month_sf),2) month_sf,
                    fafang_month from rank_user_salary_mx 
                    group by fafang_month  order by FAFANG_MONTH desc                
                    ) where rownum<7  order by fafang_month desc";           
            }elseif ($sel_data=='9') {
                $sql="select rownum,yingfa_hj,month_sf,fafang_month from (                 
                    select round(avg(yingfa_hj),2) yingfa_hj,round(avg(month_sf),2) month_sf,
                    fafang_month from rank_user_salary_mx 
                    group by fafang_month  order by FAFANG_MONTH desc                 
                    ) where rownum<10  order by fafang_month desc";           
            }elseif ($sel_data=='2016') {
                $sql="select rownum ,yingfa_hj ,month_sf,fafang_month from( 
                    select  round(avg(yingfa_hj),2) yingfa_hj ,
                    round(avg(month_sf),2) month_sf , fafang_month 
                    from rank_user_salary_mx  group by fafang_month
                    order by FAFANG_MONTH desc )  where fafang_month  like '2016-%'
                    order by fafang_month desc ";          
            }elseif ($sel_data=='2017') {
                $sql="select rownum ,yingfa_hj ,month_sf,fafang_month from( 
                    select  round(avg(yingfa_hj),2) yingfa_hj ,
                    round(avg(month_sf),2) month_sf , fafang_month 
                    from rank_user_salary_mx  group by fafang_month
                    order by FAFANG_MONTH desc )  where fafang_month  like '2017-%' 
                    order by fafang_month desc"; 
            }
            $arr=$m->query($sql1);  
            $list=$m->query($sql); 
        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }

            //年度平均工资和年度总工资
            $sql1="SELECT sum(yingfa_hj) yingfa_hj_s, sum(month_sf) month_sf_s,
                    round(avg(yingfa_hj),2) yingfa_hj_a,round(avg(month_sf),2) month_sf_a
                    FROM rank_user_salary_mx  where user_id='%s' and fafang_month>=
                    ( select to_char(trunc(sysdate,'yy'),'yyyy-MM') from dual ) ";   


            //每月应发实发工资       
            if(empty($sel_data)){
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx 
                    where USER_ID='%s' order by FAFANG_MONTH desc ) where rownum<13"; 
            }elseif($sel_data=='3'){
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx 
                    where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<4";            
            }elseif ($sel_data=='6') {
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx 
                    where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<7";           
            }elseif ($sel_data=='9') {
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx 
                    where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<10";           
            }elseif ($sel_data=='2016') {
                $sql="select yingfa_hj, month_sf,fafang_month from  rank_user_salary_mx where USER_ID='%s'
                    and fafang_month like '2016-__' order by FAFANG_MONTH desc ";          
            }elseif ($sel_data=='2017') {
                $sql="select yingfa_hj, month_sf,fafang_month from rank_user_salary_mx  where USER_ID='%s'
                    and fafang_month like '2017-__' order by FAFANG_MONTH desc ";          
            }
            $arr=$m->query($sql1,$bill_id);  
            $list=$m->query($sql,$bill_id); 
        }
                  
        $this->assign('arr',$arr);
        $this->assign('list',$list);
        $this->assign('sel_data',$sel_data);
        $this->display('Index/salary_index'); 
    }

    //全部
 	public function salary_quanbu(){
 		$m=M('userInfo');
    if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
        $sql="select round(avg(month_sf),2) month_sf ,fafang_month from rank_user_salary_mx 
                  group by fafang_month order by FAFANG_MONTH desc ";
        $lists=$m->query($sql); 
    }else{  
      if(!empty($_SESSION['salary']['bill_id'])){
            $bill_id=$_SESSION['salary']['bill_id'];
         }else {
            $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
         }
      $sql="select month_sf,fafang_month from rank_user_salary_mx where  USER_ID='%s' order by FAFANG_MONTH desc ";
      $lists=$m->query($sql,$bill_id); 
    } 

       		
    $this->assign('lists',$lists);       	
    $this->display('Index/salary_quanbu');
    
    
    } 


    //工资项
    public function salary_gongzi(){
        $m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select round(avg(fafang_jx),2) fafang_jx,fafang_month from rank_user_salary_mx 
            group by fafang_month order by FAFANG_MONTH desc";
            $lists=$m->query($sql);

        }else{ 
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }
            $sql="select nvl(fafang_jx,0) fafang_jx,fafang_month from rank_user_salary_mx 
            where USER_ID='%s' order by FAFANG_MONTH desc";
            $lists=$m->query($sql,$bill_id);
        }    	   
              		
        $this->assign('lists',$lists);    	
        $this->display('Index/salary_gongzi');
    }

    //奖金项
    public function salary_jiangjin(){
        $m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select  round(avg(nvl(jx_jj,0)+nvl(lianghua_jj,0)+nvl(saima_jj,0)+
                nvl(xianjin_laodong,0)+nvl(jiaban,0)+nvl(qt_yf,0)),2) jiangjin,
                fafang_month from  rank_user_salary_mx    group by fafang_month 
                order by FAFANG_MONTH desc";
            $lists=$m->query($sql); 
        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }
            $sql="select  (nvl(jx_jj,0)+nvl(lianghua_jj,0)+nvl(saima_jj,0)+
                nvl(xianjin_laodong,0)+nvl(jiaban,0)+nvl(qt_yf,0)) jiangjin,
                fafang_month from  rank_user_salary_mx 
                where USER_ID='%s' order by FAFANG_MONTH desc";
            $lists=$m->query($sql,$bill_id); 
        }      		
        $this->assign('lists',$lists);    
        $this->display('Index/salary_jiangjin'); 
    }

    //补贴项
    public function salary_butie(){
    	$m=M();
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
        $sql="select round(avg(nvl(shuidian_bt,0))+ avg(nvl(wucan_bt,0))+avg(nvl(huafei_bt,0))+ 
             avg(nvl(zhufang_bt,0)),2)butie , fafang_month from rank_user_salary_mx  group by 
             fafang_month order by fafang_month desc  ";
             $lists=$m->query($sql); 
        }else{  
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }
            $sql="select nvl(shuidian_bt+wucan_bt+huafei_bt+zhufang_bt,0) butie, fafang_month 
                 from  rank_user_salary_mx where USER_ID='%s' order by FAFANG_MONTH desc "; 
            $lists=$m->query($sql,$bill_id); 
        }    	   
    	   		
    	$this->assign('lists',$lists);
    	$this->display('Index/salary_butie');
    }

    //扣款项
    public function salary_koukuan(){
        $m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select round(              
                  avg(nvl(qt_yk,0))+avg(nvl(kq_kk,0))+avg(nvl(gr_bx_yl,0))+
                  avg(nvl(gr_bx_yiliao,0))+
                  avg(nvl(gr_bx_sy,0))+avg(nvl(gr_bx_gjj,0))+avg(nvl(gr_bx_nj,0))+
                  avg(nvl(geshui,0)),2) kk_hj,         
                  fafang_month from  rank_user_salary_mx 
                  group by fafang_month order by fafang_month desc   ";
            $lists=$m->query($sql); 
        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }

            $sql="select (nvl(qt_yk,0)+nvl(kq_kk,0)+nvl(gr_bx_yl,0)+nvl(gr_bx_yiliao,0)+
                    nvl(gr_bx_sy,0)+nvl(gr_bx_gjj,0)+nvl(gr_bx_nj,0)+nvl(geshui,0)) kk_hj,         
                    fafang_month from  rank_user_salary_mx where USER_ID='%s' order by FAFANG_MONTH desc";
            $lists=$m->query($sql,$bill_id);
        }
        $this->assign('lists',$lists); 
    	$this->display('Index/salary_koukuan');      
    }

    //全部明细
    public function salary_qb_mx(){
        $month=I('month');      
        $m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select round(avg(NVL(yingfa_hj,0)),2) yingfa_hj, 
                    round(avg(NVL(qt_yk,0)+NVL(kq_kk,0)+NVL(gr_bx_yl,0)+NVL(gr_bx_yiliao,0)+
                    NVL(gr_bx_sy,0)+NVL(gr_bx_gjj,0)+NVL(gr_bx_nj,0)+NVL(geshui,0)),2) kk,
                    round(avg(NVL(month_sf,0)),2) month_sf,round(avg(NVL(fafang_jx,0)),2) fafang_jx,
                    round(avg(NVL(shuidian_bt,0)+ NVL(wucan_bt,0)+NVL(huafei_bt,0)+
                    NVL(zhufang_bt,0)),2) bt_hj, round(avg(NVL(shuidian_bt,0)),2) shuidian_bt, 
                    round(avg(NVL(wucan_bt,0)),2) wucan_bt, round(avg(NVL(huafei_bt,0)),2) huafei_bt,
                    round(avg(NVL(zhufang_bt,0)),2) zhufang_bt, round(avg(NVL(jx_jj,0)+NVL(lianghua_jj,0)+
                    NVL(saima_jj,0)+NVL(xianjin_laodong,0)+ NVL(jiaban,0)+ NVL(qt_yf,0)),2) jiangjin_hj, 
                    round(avg(NVL(jx_jj,0)),2) jx_jj, round(avg(NVL(lianghua_jj,0)),2) lianghua_jj,
                    round(avg(NVL(saima_jj,0)),2) saima_jj, round(avg(NVL(xianjin_laodong,0)),2) xianjin_laodong, 
                    round(avg(NVL(jiaban,0)),2) jiaban, round(avg(NVL(qt_yf,0)),2) qt_yf,
                    round(avg(NVL(qt_yk,0)),2) qt_yk,  round(avg(NVL(kq_kk,0)),2) kq_kk,
                    round(avg(NVL(gr_bx_yl,0)),2) gr_bx_yl, round(avg(NVL(gr_bx_yiliao,0)),2) gr_bx_yiliao,
                    round(avg(NVL(gr_bx_sy,0)),2) gr_bx_sy, round(avg(NVL(gr_bx_gjj,0)),2) gr_bx_gjj,
                    round(avg(NVL(gr_bx_nj,0)),2) gr_bx_nj, round(avg(NVL(geshui,0)),2) geshui,
                    round(avg(NVL(month_sf,0)),2) month_sf, fafang_month from  rank_user_salary_mx 
                    where fafang_month='%s'  group by fafang_month"; 
            $list=$m->query($sql,$month); 
        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }
            $sql="select NVL(yingfa_hj,0) yingfa_hj, (NVL(qt_yk,0)+NVL(kq_kk,0)+NVL(gr_bx_yl,0)+
                    NVL(gr_bx_yiliao,0)+NVL(gr_bx_sy,0)+NVL(gr_bx_gjj,0)+NVL(gr_bx_nj,0)+NVL(geshui,0)) kk,
                    NVL(month_sf,0) month_sf, NVL(fafang_jx,0) fafang_jx, (NVL(shuidian_bt,0)+ NVL(wucan_bt,0)+
                    NVL(huafei_bt,0)+NVL(zhufang_bt,0)) bt_hj, NVL(shuidian_bt,0) shuidian_bt, 
                    NVL(wucan_bt,0) wucan_bt,NVL(huafei_bt,0) huafei_bt, NVL(zhufang_bt,0) zhufang_bt,
                    (NVL(jx_jj,0)+NVL(lianghua_jj,0)+NVL(saima_jj,0)+NVL(xianjin_laodong,0)+ NVL(jiaban,0)+
                    NVL(qt_yf,0)) jiangjin_hj, NVL(jx_jj,0) jx_jj, NVL(lianghua_jj,0) lianghua_jj,
                    NVL(saima_jj,0) saima_jj,NVL(xianjin_laodong,0) xianjin_laodong, NVL(jiaban,0) jiaban,
                    NVL(qt_yf,0) qt_yf,  NVL(qt_yk,0) qt_yk, NVL(kq_kk,0) kq_kk, NVL(gr_bx_yl,0) gr_bx_yl,
                    NVL(gr_bx_yiliao,0) gr_bx_yiliao, NVL(gr_bx_sy,0) gr_bx_sy,NVL(gr_bx_gjj,0) gr_bx_gjj,
                    NVL(gr_bx_nj,0) gr_bx_nj, NVL(geshui,0) geshui, NVL(month_sf,0) month_sf,              
                    fafang_month,jx_jj_dept,team_jx_xs,gr_jx,user_name from  rank_user_salary_mx where  
                    USER_ID='%s' and fafang_month= '%s' ";
            $list=$m->query($sql,$bill_id,$month); 
        }
        $this->assign('list',$list);       
        $this->display('Index/salary_qb_mx');
    } 

    //工资明细
    public function salary_gz_mx(){
        $month=I('month');    	
        $m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select round(avg(fafang_jx),2) fafang_jx,fafang_month from 
                        rank_user_salary_mx where fafang_month='%s' group by fafang_month";
            $list=$m->query($sql,$month);  

        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }

            $sql="select fafang_jx,fafang_month from rank_user_salary_mx 
                    where USER_ID='%s'  and fafang_month='%s' "; 
            $list=$m->query($sql,$bill_id,$month);  
            }
        $this->assign('list',$list);   
        $this->display('Index/salary_gz_mx');
    }

    //奖金明细
    public function salary_jj_mx(){
      	$month=I('month'); 
      	$m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select round(avg(nvl(jx_jj,0)),2) jx_jj,
                round(avg(nvl(lianghua_jj,0)),2) lianghua_jj,
                round(avg(nvl(saima_jj,0)),2) saima_jj,
                round(avg(nvl(xianjin_laodong,0)),2) xianjin_laodong ,
                round(avg(nvl(jiaban,0)),2) jiaban ,
                round(avg(nvl(qt_yf,0)),2) qt_yf, 
                round(avg(nvl(jx_jj,0)+nvl(lianghua_jj,0)+nvl(saima_jj,0)+
                nvl(xianjin_laodong,0)+nvl(jiaban,0)+nvl(qt_yf,0)),2) jiangjin,                  
                fafang_month from  rank_user_salary_mx                  
                where fafang_month='%s' group by fafang_month ";
            $list=$m->query($sql,$month); 
        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }

            $sql="select nvl(jx_jj,0) jx_jj, nvl(lianghua_jj,0) lianghua_jj,
                nvl(saima_jj,0) saima_jj,nvl(xianjin_laodong,0) xianjin_laodong ,nvl(jiaban,0) jiaban ,
                nvl(qt_yf,0) qt_yf, (nvl(jx_jj,0)+nvl(lianghua_jj,0)+nvl(saima_jj,0)+
                nvl(xianjin_laodong,0)+nvl(jiaban,0)+nvl(qt_yf,0)) jiangjin,
                fafang_month from  rank_user_salary_mx  where USER_ID='%s' and fafang_month='%s'";
            $list=$m->query($sql,$bill_id,$month);
        }
        $this->assign('list',$list);
    	$this->display('Index/salary_jj_mx');
    }

    //补贴明细
    public function salary_bt_mx(){
        $month=I('month'); 
        $m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select  round(nvl(avg(shuidian_bt+wucan_bt+huafei_bt+zhufang_bt),0),2) butie,              
                    round(avg(nvl(shuidian_bt,0)),2) shuidian_bt , round(avg(nvl(wucan_bt,0)),2) wucan_bt,
                    round(avg(nvl(huafei_bt,0)),2) huafei_bt, round(avg(nvl(zhufang_bt,0)),2)zhufang_bt ,                
                    fafang_month from rank_user_salary_mx  where fafang_month='%s' 
                    group by fafang_month ";
            $list=$m->query($sql,$month);  

        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
            $bill_id=$_SESSION['salary']['bill_id'];
            }else {
            $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }

            $sql="select (shuidian_bt+wucan_bt+huafei_bt+zhufang_bt) butie, shuidian_bt,wucan_bt,
                    huafei_bt,zhufang_bt, fafang_month from rank_user_salary_mx where 
                    USER_ID='%s' and fafang_month='%s' "; 
            $list=$m->query($sql,$bill_id,$month);
        }
        $this->assign('list',$list);  
        $this->display('Index/salary_bt_mx');
    }

    //扣款明细
    public function salary_kk_mx(){
        $month=I('month'); 
        $m=M('userInfo');  
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select round(avg(nvl(qt_yk,0)+nvl(kq_kk,0)+nvl(gr_bx_yl,0)+nvl(gr_bx_yiliao,0)+
                    nvl(gr_bx_sy,0)+nvl(gr_bx_gjj,0)+nvl(gr_bx_nj,0)+nvl(geshui,0)),2) kk_hj,
                    round(avg(nvl(qt_yk,0)),2) qt_yk, round(avg(nvl(kq_kk,0)),2) kq_kk,
                    round(avg(nvl(gr_bx_yl,0)),2) gr_bx_yl,round(avg(nvl(gr_bx_yiliao,0)),2) gr_bx_yiliao,
                    round(avg(nvl(gr_bx_sy,0)),2) gr_bx_sy, round(avg(nvl(gr_bx_gjj,0)),2) gr_bx_gjj,
                    round(avg(nvl(gr_bx_nj,0)),2) gr_bx_nj, round(avg(nvl(geshui,0)),2) geshui,  
                    fafang_month from  rank_user_salary_mx where fafang_month='%s' group by fafang_month";
            $list=$m->query($sql,$month);
        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }
            $sql="select (nvl(qt_yk,0)+nvl(kq_kk,0)+nvl(gr_bx_yl,0)+nvl(gr_bx_yiliao,0)+
                    nvl(gr_bx_sy,0)+nvl(gr_bx_gjj,0)+nvl(gr_bx_nj,0)+nvl(geshui,0)) kk_hj,          
                    nvl(qt_yk,0) qt_yk,nvl(kq_kk,0) kq_kk,nvl(gr_bx_yl,0) gr_bx_yl,
                    nvl(gr_bx_yiliao,0) gr_bx_yiliao,nvl(gr_bx_sy,0) gr_bx_sy,
                    nvl(gr_bx_gjj,0) gr_bx_gjj,nvl(gr_bx_nj,0) gr_bx_nj,
                    nvl(geshui,0) geshui,  fafang_month from  
                    rank_user_salary_mx where USER_ID='%s'  and fafang_month='%s' ";
            $list=$m->query($sql,$bill_id,$month);
        }
        $this->assign('list',$list);         	
        $this->display('Index/salary_kk_mx');
    }


    //全部明细比较
    public function salary_qb_bj(){
        $month1=I('month1');  
        $month2=I('month2');       
        $m=M('userInfo');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            $sql="select round(avg(NVL(yingfa_hj,0)),2) yingfa_hj, 
                    round(avg(NVL(qt_yk,0)+NVL(kq_kk,0)+NVL(gr_bx_yl,0)+NVL(gr_bx_yiliao,0)+
                    NVL(gr_bx_sy,0)+NVL(gr_bx_gjj,0)+NVL(gr_bx_nj,0)+NVL(geshui,0)),2) kk,
                    round(avg(NVL(month_sf,0)),2) month_sf,round(avg(NVL(fafang_jx,0)),2) fafang_jx,
                    round(avg(NVL(shuidian_bt,0)+ NVL(wucan_bt,0)+NVL(huafei_bt,0)+NVL(zhufang_bt,0)),2) bt_hj, 
                    round(avg(NVL(shuidian_bt,0)),2) shuidian_bt, round(avg(NVL(wucan_bt,0)),2) wucan_bt,
                    round(avg(NVL(huafei_bt,0)),2) huafei_bt,round(avg(NVL(zhufang_bt,0)),2) zhufang_bt,
                    round(avg(NVL(jx_jj,0)+NVL(lianghua_jj,0)+NVL(saima_jj,0)+NVL(xianjin_laodong,0)+ 
                    NVL(jiaban,0)+ NVL(qt_yf,0)),2) jiangjin_hj, round(avg(NVL(jx_jj,0)),2) jx_jj,
                    round(avg(NVL(lianghua_jj,0)),2) lianghua_jj,round(avg(NVL(saima_jj,0)),2) saima_jj,
                    round(avg(NVL(xianjin_laodong,0)),2) xianjin_laodong, round(avg(NVL(jiaban,0)),2) jiaban,
                    round(avg(NVL(qt_yf,0)),2) qt_yf, round(avg(NVL(qt_yk,0)),2) qt_yk, 
                    round(avg(NVL(kq_kk,0)),2) kq_kk,round(avg(NVL(gr_bx_yl,0)),2) gr_bx_yl,
                    round(avg(NVL(gr_bx_yiliao,0)),2) gr_bx_yiliao,round(avg(NVL(gr_bx_sy,0)),2) gr_bx_sy,
                    round(avg(NVL(gr_bx_gjj,0)),2) gr_bx_gjj, round(avg(NVL(gr_bx_nj,0)),2) gr_bx_nj,
                    round(avg(NVL(geshui,0)),2) geshui, round(avg(NVL(month_sf,0)),2) month_sf,              
                    fafang_month from  rank_user_salary_mx where fafang_month in ('%s','%s')
                    group by fafang_month order by fafang_month desc";
              $list=$m->query($sql,$month1,$month2);
        }else{ 
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }

            $sql="select  NVL(yingfa_hj,0) yingfa_hj,(NVL(qt_yk,0)+NVL(kq_kk,0)+NVL(gr_bx_yl,0)+
                    NVL(gr_bx_yiliao,0)+NVL(gr_bx_sy,0)+NVL(gr_bx_gjj,0)+NVL(gr_bx_nj,0)+NVL(geshui,0)) kk,
                    NVL(month_sf,0) month_sf,NVL(fafang_jx,0) fafang_jx,NVL(shuidian_bt,0) shuidian_bt, 
                    NVL(wucan_bt,0) wucan_bt,NVL(huafei_bt,0) huafei_bt,NVL(zhufang_bt,0) zhufang_bt,
                    NVL(jx_jj,0) jx_jj,NVL(lianghua_jj,0) lianghua_jj, NVL(saima_jj,0) saima_jj,
                    NVL(xianjin_laodong,0) xianjin_laodong, NVL(jiaban,0) jiaban,NVL(qt_yf,0) qt_yf,
                    NVL(qt_yk,0) qt_yk,NVL(kq_kk,0) kq_kk,NVL(gr_bx_yl,0) gr_bx_yl,NVL(gr_bx_yiliao,0) gr_bx_yiliao,
                    NVL(gr_bx_sy,0) gr_bx_sy, NVL(gr_bx_gjj,0) gr_bx_gjj,NVL(gr_bx_nj,0) gr_bx_nj,
                    NVL(geshui,0) geshui,NVL(month_sf,0) month_sf,fafang_month,jx_jj_dept,team_jx_xs,
                    gr_jx from  rank_user_salary_mx where  USER_ID='%s' and fafang_month in ('%s','%s') 
                    order by fafang_month desc" ;
            $list=$m->query($sql,$bill_id,$month1,$month2);
        }                        
        $this->assign('list',$list);      
        $this->display('Index/salary_qb_bj');
    }
    
    //全部条形图
    public function salary_quanbu_txt(){
        $m=M('userInfo'); 
       //每月实发工资
        $sel_data=I('sel_data');
        if($_SESSION['user_auth']['OPER_LOGIN_CODE']=='13900000000'){
            if(empty($sel_data)){
                $sql="select rownum ,yingfa_hj ,month_sf ,fafang_month from ( 
                        select  round(avg(yingfa_hj),2) yingfa_hj ,
                        round(avg(month_sf),2) month_sf , fafang_month 
                        from rank_user_salary_mx group by fafang_month
                        order by FAFANG_MONTH desc )  where rownum <13";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (                   
                        select rownum, yingfa_hj, month_sf,fafang_month from (                   
                        select round(avg(yingfa_hj),2) yingfa_hj ,round(avg(month_sf),2) month_sf ,
                        fafang_month from rank_user_salary_mx group by fafang_month 
                        order by fafang_month desc )  where rownum<13 
                        order by fafang_month desc )  ";    

            }elseif($sel_data=='3'){
                $sql="select rownum ,yingfa_hj ,month_sf,fafang_month from( 
                        select  round(avg(yingfa_hj),2) yingfa_hj ,
                        round(avg(month_sf),2) month_sf , fafang_month 
                        from rank_user_salary_mx  group by fafang_month
                        order by FAFANG_MONTH desc )  where rownum <4";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from ( 
                        select rownum, yingfa_hj, month_sf,fafang_month from (                   
                        select round(avg(yingfa_hj),2) yingfa_hj ,round(avg(month_sf),2) month_sf,
                        fafang_month from rank_user_salary_mx group by fafang_month order by
                        fafang_month desc) where rownum<4  order by fafang_month desc ) "; 

            }elseif ($sel_data=='6') {
                $sql="select rownum ,yingfa_hj ,month_sf,fafang_month from( 
                        select  round(avg(yingfa_hj),2) yingfa_hj ,
                        round(avg(month_sf),2) month_sf , fafang_month 
                        from rank_user_salary_mx  group by fafang_month
                        order by FAFANG_MONTH desc )  where rownum <7";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (
                        select rownum, yingfa_hj, month_sf,fafang_month from (                   
                        select round(avg(yingfa_hj),2) yingfa_hj ,round(avg(month_sf),2) month_sf,
                        fafang_month from rank_user_salary_mx group by fafang_month order by fafang_month desc ) 
                        where rownum<7 order by fafang_month desc )";

            }elseif ($sel_data=='9') {
                $sql="select rownum ,yingfa_hj ,month_sf,fafang_month from( 
                        select  round(avg(yingfa_hj),2) yingfa_hj ,
                        round(avg(month_sf),2) month_sf , fafang_month 
                        from rank_user_salary_mx   group by fafang_month
                        order by FAFANG_MONTH desc )  where rownum <10";

               $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (
                        select rownum, yingfa_hj, month_sf,fafang_month from (                   
                        select round(avg(yingfa_hj),2) yingfa_hj ,round(avg(month_sf),2) month_sf, 
                        fafang_month from rank_user_salary_mx group by fafang_month order by 
                        fafang_month desc ) where rownum<10  order by fafang_month desc) ";

            }elseif ($sel_data=='2016') {
                $sql="select rownum ,yingfa_hj ,month_sf,fafang_month from( 
                        select  round(avg(yingfa_hj),2) yingfa_hj ,
                        round(avg(month_sf),2) month_sf , fafang_month 
                        from rank_user_salary_mx  group by fafang_month
                        order by FAFANG_MONTH desc )  where fafang_month  like '2016-%'";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (
                        select round(avg(yingfa_hj),2) yingfa_hj,round(avg(month_sf),2) month_sf, 
                        fafang_month from rank_user_salary_mx where fafang_month like '2016-%' 
                        group by fafang_month order by fafang_month desc ) ";

            }elseif ($sel_data=='2017') {
                $sql="select rownum ,yingfa_hj ,month_sf,fafang_month from( 
                        select  round(avg(yingfa_hj),2) yingfa_hj ,
                        round(avg(month_sf),2) month_sf , fafang_month 
                        from rank_user_salary_mx  group by fafang_month
                        order by FAFANG_MONTH desc )  where fafang_month  like '2017-%' "; 

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (
                        select round(avg(yingfa_hj),2) yingfa_hj,round(avg(month_sf),2) month_sf, 
                        fafang_month from rank_user_salary_mx where fafang_month like '2017-%'
                        group by fafang_month order by fafang_month desc ) ";              
            }
            $list=$m->query($sql); 
            $list1=$m->query($sql1);

        }else{
            if(!empty($_SESSION['salary']['bill_id'])){
                $bill_id=$_SESSION['salary']['bill_id'];
            }else {
                $bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
            }


            if(empty($sel_data)){
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx 
                        where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<13";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from ( select * from (select 
                        rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx  where USER_ID='%s' 
                        order by FAFANG_MONTH desc ) where rownum<13)";
            }elseif($sel_data=='3'){
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx
                        where USER_ID='%s' order by FAFANG_MONTH desc ) where rownum<4"; 

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (select * from (
                        select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx 
                        where USER_ID='%s' order by FAFANG_MONTH desc ) where rownum<4)";

            }elseif ($sel_data=='6') {
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from  rank_user_salary_mx 
                        where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<7";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from ( select * from (
                        select rownum,yingfa_hj,month_sf,fafang_month from  rank_user_salary_mx 
                        where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<7)";

            }elseif ($sel_data=='9') {
                $sql="select * from (select rownum,yingfa_hj,month_sf,fafang_month from rank_user_salary_mx   
                        where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<10";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (  select * from ( 
                        select rownum,yingfa_hj,month_sf,fafang_month from  rank_user_salary_mx  
                        where USER_ID='%s'  order by FAFANG_MONTH desc ) where rownum<10)"; 

            }elseif ($sel_data=='2016') {
                $sql="select yingfa_hj,month_sf,fafang_month from rank_user_salary_mx  where USER_ID='%s'   
                        and fafang_month like '2016-__' order by FAFANG_MONTH desc ";

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from ( select yingfa_hj,month_sf,
                        fafang_month from rank_user_salary_mx where USER_ID='%s' and fafang_month like '2016-__' 
                        order by FAFANG_MONTH desc)";                
            }elseif ($sel_data=='2017') {
                $sql="select yingfa_hj,month_sf,fafang_month from rank_user_salary_mx where USER_ID='%s' 
                        and fafang_month like '2017-__' order by FAFANG_MONTH desc "; 

                $sql1="select sum(yingfa_hj) yingfa_hj,sum(month_sf) month_sf from (select yingfa_hj,month_sf,
                        fafang_month from rank_user_salary_mx  where USER_ID='%s' and fafang_month like '2017-__' 
                        order by FAFANG_MONTH desc)";                
            }
            $list=$m->query($sql,$bill_id); 
            $list1=$m->query($sql1,$bill_id);
        }           
       
      $this->assign('list',$list);
      $this->assign('list1',$list1);
      $this->assign('sel_data',$sel_data);
      $this->display('Index/salary_quanbu_txt');
    }


    //薪酬明细数据导入
    public function salary_data_import(){                      
        $this->display('Index/salary_data_import');
    }


    //薪酬明细数据导入
    public function read($filename,$file_type,$encode='utf-8'){
        Vendor('Classes.PHPExcel'); 
        if (strtolower ( $file_type ) == "xls"){
            $objReader = \PHPExcel_IOFactory::createReader('Excel5'); 
        }else if(strtolower ( $file_type ) == "xlsx"){
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


    //薪酬明细数据导入
    public function file(){ 
        if (!empty($_FILES['file_stu']['name'])){
            $tmp_file = $_FILES['file_stu']['tmp_name'];
            $file_types = explode ( ".", $_FILES['file_stu']['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];


            /*判别是不是.xls文件，判别是不是excel文件*/
            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
                $this->error ( '不是Excel文件，重新上传' );
            }

            /*设置上传路径*/
            $savePath = '//10.78.1.85/www/ranking/Public/upfile/Execl2/salary';

            /*以时间来命名上传的文件*/
            $str =date ( 'YmdH' ); //date ( 'Ymdhis' );
            $file_name = $str . "." . $file_type;

            /*是否上传成功*/
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

            if(!empty($res)){ 
                $m=M('user_salary_mx_bak');
                $ffmonth=$res[2][3];
                $where['fafang_month']=$ffmonth;
                $m->where($where)->delete();
               
                
                /**

             G('begin');
                foreach ( $res as $k => $v ){
                    if ($k != 0){ 
                        $data['user_num'] = strval($v[0]);                   
                        $data['user_id'] = strval($v[1]);
                        $data['user_name'] = strval($v[2]);
                        $data['fafang_month']=strval($v[3]);
                        $data['jx_jj_dept'] = $v[4];
                        $data['team_jx_xs'] = intval($v[5]);         
                        $data['gr_jx'] =strval($v[6]) ;
                        $data['fafang_jx']=intval($v[7]);
                        $data['shuidian_bt']=intval($v[8]);
                        $data['wucan_bt'] = intval($v[9]);
                        $data['huafei_bt'] = intval($v[10]);
                        $data['zhufang_bt']=intval($v[11]);
                        $data['jx_jj'] = intval($v[12]);         
                        $data['lianghua_jj'] =intval($v[13]) ;
                        $data['saima_jj']=intval($v[14]);          
                        $data['xianjin_laodong']=intval($v[15]);
                        $data['jiaban'] = intval($v[16]);
                        $data['qt_yf'] = intval($v[17]);
                        $data['qt_yk'] = intval($v[18]);
                        $data['kq_kk'] = intval($v[19]);         
                        $data['yingfa_hj'] =intval($v[20]);
                        $data['gr_bx_yl']=intval($v[21]);
                        $data['gr_bx_yiliao']=intval($v[22]);
                        $data['gr_bx_sy'] = intval($v[23]);
                        $data['gr_bx_gjj'] = intval($v[24]);
                        $data['gr_bx_nj'] = intval($v[25]);         
                        $data['geshui'] =intval($v[26]);
                        $data['month_sf']=intval($v[27]);

                        
                        $flag =$m->add($data); 
                        
                        if(!$flag){
                            $this->error ( '导入数据库失败',U('salary_index') );
                            exit;
                        }           
                    }
                }

                G('end');

                        echo G('begin','end',4).'<br/>';
               **/
               
           

               
                $sq="insert into rank_user_salary_mx_bak select '".strval($res[2][0])."','".strval($res[2][1])."',
                        '".strval($res[2][2])."','".strval($res[2][3])."','".strval($res[2][4])."',
                        '".$res[2][5]."','".strval($res[2][6])."',
                        '".intval($res[2][7])."','".intval($res[2][8])."','".intval($res[2][9])."',
                        '".intval($res[2][10])."','".intval($res[2][11])."',
                        '".intval($res[2][12])."','".intval($res[2][13])."','".intval($res[2][14])."',
                        '".intval($res[2][15])."',
                        '".intval($res[2][16])."','".intval($res[2][17])."','".intval($res[2][18])."',
                        '".intval($res[2][19])."',
                        '".intval($res[2][20])."','".intval($res[2][21])."', '".intval($res[2][22])."',
                        '".intval($res[2][23])."',
                        '".intval($res[2][24])."', '".intval($res[2][25])."', '".intval($res[2][26])."', 
                        '".intval($res[2][27])."' from dual";

             


                foreach($res as $k => $v){
                    //if(!empty($v[1])){
                        if($k>2){
                            $sq .= " union all select '".strval($v[0])."','".strval($v[1])."',
                             '".strval($v[2])."','".strval($v[3])."','".strval($v[4])."',
                             '".intval($v[5])."','".strval($v[6])."',
                             '".intval($v[7])."','".intval($v[8])."','".intval($v[9])."',
                                '".intval($v[10])."','".intval($v[11])."',
                             '".intval($v[12])."','".intval($v[13])."','".intval($v[14])."',
                                '".intval($v[15])."', '".intval($v[16])."',
                             '".intval($v[17])."','".intval($v[18])."','".intval($v[19])."',
                                '".intval($v[20])."','".intval($v[21])."',
                             '".intval($v[22])."','".intval($v[23])."','".intval($v[24])."',
                                '".intval($v[25])."','".intval($v[26])."',
                             '".intval($v[27])."'  from dual";
                        }
                    //}
                }




                $flag=$m->execute($sq); 

                      
                      


                if($flag){
                   $this->success('导入成功!',U('salary_index')); 
                }
            }
        }else{
           $this->error ( '请选择导入的Execl数据!' );
        }
    }

    //查询员工基本信息
    public function salary_list(){  
        $m = M();   
        $sql ="select * from mz_user.ls_yd_user where bill_id='".$_SESSION['user_auth']['OPER_LOGIN_CODE']."'";
        $list = $m->query($sql);   
        $this->assign('list',$list);
        $this->display('Index/salary_list');
    }



    //查询员工基本信息
    public function  salary_search(){
        $search_user_id=I('search_user_id');
        $search_bill_id=I('search_bill_id');
        $m=M();
        if(!empty($search_user_id)&&!empty($search_bill_id)){
            $sql="select * from mz_user.ls_yd_user where user_id='".$search_user_id."' 
            and bill_id='".$search_bill_id."'";     
        }elseif (!empty($search_user_id)&&empty($search_bill_id)) {
            $sql="select * from mz_user.ls_yd_user where user_id='".$search_user_id."' ";      
        }elseif (empty($search_user_id)&&!empty($search_bill_id)) {
            $sql="select * from mz_user.ls_yd_user where bill_id='".$search_bill_id."' ";      
        }
        $list=$m->query($sql);
        if($list){
            $this->ajaxReturn($list);
        }else{
            $msg="1";
        $this->ajaxReturn($msg);
        }    
    }

    //修改基本信息
    public function  salary_modify(){
        $data['user_name']=I('user_name');
        $data['user_id']=I('user_id');
        $data['bill_id']=I('bill_id');    
        $data['position']=I('position');    
        $data['user_lvl']=I('user_lvl');    
        $data['jx']=I('jx');    
        $data['gl']=I('gl'); 
        $data['zc']=I('zc');    
        $data['zong']=I('zong');    
        $data['total']=I('total');
        $data['jx_12']=I('jx_12');
        $data['jx_13']=I('jx_13');
        $data['jx_14']=I('jx_14');    
        $data['jx_15']=I('jx_15');    
        $data['bx_yangl']=I('bx_yangl');    
        $data['bx_yil']=I('bx_yil');    
        $data['bx_shiy']=I('bx_shiy');    
        $data['bx_gongjj']=I('bx_gongjj');    
        $data['bx_nianj']=I('bx_nianj');    
        $data['bx_buc']=I('bx_buc');
        $data['gz_z']=I('gz_z');
        $data['cb_z']=I('cb_z');
        $m=M();
        $where['user_id']=$data['user_id'];
        $flag=$m->table('mz_user.ls_yd_user')->where($where)->save($data);     
        if($flag){
            $msg='修改成功';
        }else{
            $msg='修改失败';
        }
        $this->ajaxReturn($msg);
    }

    //添加员工信息
    public function  salary_user_add(){   
        $data['user_name']=I('user_name');
        $data['user_id']=I('user_id');
        $data['bill_id']=I('bill_id');    
        $data['position']=I('position');    
        $data['user_lvl']=I('user_lvl');    
        $data['jx']=I('jx');    
        $data['gl']=I('gl'); 
        $data['zc']=I('zc');    
        $data['zong']=I('zong');    
        $data['total']=I('total');
        $data['jx_12']=I('jx_12');
        $data['jx_13']=I('jx_13');
        $data['jx_14']=I('jx_14');    
        $data['jx_15']=I('jx_15');    
        $data['bx_yangl']=I('bx_yangl');    
        $data['bx_yil']=I('bx_yil');    
        $data['bx_shiy']=I('bx_shiy');    
        $data['bx_gongjj']=I('bx_gongjj');    
        $data['bx_nianj']=I('bx_nianj');    
        $data['bx_buc']=I('bx_buc');
        $data['gz_z']=I('gz_z');
        $data['cb_z']=I('cb_z');
        $m=M();
        $flag=$m->table('mz_user.ls_yd_user')->add($data);     
        if($flag){
            $msg='新增成功';
        }else{
            $msg='新增失败';
        }
        $this->ajaxReturn($msg);    
    }

    //删除员工信息 
    public function  salary_user_delete(){ 
        $user_id=I('user_id');
        $m=M();
        $flag=$m->table('mz_user.ls_yd_user')->where("user_id='".$user_id."'")->delete();     
        if($flag){
            $msg='删除成功';
        }else{
            $msg='删除失败';
        }
        $this->ajaxReturn($msg);
    }

    //基本信息导入
    public function salary_jiben_import(){
        $this->display('Index/salary_jiben_import');
    }



  
    public function jiben_data(){
        if (!empty($_FILES['file_stu']['name'])){      
            $tmp_file = $_FILES['file_stu']['tmp_name'];
            $file_types = explode ( ".", $_FILES['file_stu']['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];

            //判别是不是.xls文件，判别是不是excel文件
            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
                $this->error ( '不是Excel文件，重新上传' );
            }

            //设置上传路径
            $savePath = '//10.78.1.85/www/ranking/Public/upfile/Execl2/salary';

            //以时间来命名上传的文件
            $str =date ( 'H' ); //date ( 'Ymdhis' );
            $file_name = $str . "." . $file_type;

            //是否上传成功
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

            if(!empty($res)){ 
                $m=M();
                foreach ( $res as $k => $v ){
                    if ($k != 0){ 
                        $data['dept_id'] = strval($v[0]);
                        $data['user_name'] = $v[1];
                        $data['user_id']=strval($v[2]);
                        $data['bill_id'] = strval($v[3]);
                        $data['position'] = $v [4];         
                        $data['lvl'] =intval($v[5]);
                        $data['user_lvl']=intval($v[6]);
                        $data['jx']=intval($v[7]);
                        $data['zc'] =intval($v[8]);
                        $data['gl'] = intval($v[9]);
                        $data['zong']=intval($v[10]);
                        $data['total'] =intval($v[11]);         
                        $data['jx_12'] =$v[12] ;
                        $data['jx_13']=$v[13];          
                        $data['jx_14']=$v[14];
                        $data['jx_15'] = $v[15];
                        $data['bx_yangl'] = intval($v[16]);
                        $data['bx_yil'] = intval($v[17]);
                        $data['bx_shiy'] = intval($v[18]);         
                        $data['bx_gongjj'] =intval($v[19]) ;
                        $data['bx_nianj']=intval($v[20]);
                        $data['bx_buc']=intval($v[21]);
                        $data['gz_z']=$v[21];
                        $data['cb_z']=$v[21];

                        if(!empty($data['user_id'])){
                            $where['user_id']=$data['user_id'];
                            $list=$m->table('mz_user.ls_yd_user')->where($where)->select();         
                            if(empty($list)){
                                $flag=$m->table('mz_user.ls_yd_user')->add($data);
                            }else{
                                $flag=$m->table('mz_user.ls_yd_user')->where($where)->save($data);
                            }
                            if (!$flag){
                                $this->error ('导入数据库失败',U('salary_list') );
                            }
                        }
                    }
                }
                $this->success('导入成功!',U('salary_list'));
            }   
        }else{
            $this->error ('请选择导入的Execl数据!' );
        }
    }






    //查询薪水
    public  function salary_user_search(){  
        if(IS_GET){
            $this->display(); 
        }

        if(IS_POST){
            $search_user_num=I('search_user_num');
            $m=M();
            $where['user_id']=$search_user_num;
            $list=$m->table('mz_user.ls_yd_user')->where($where)->find();
            if(!empty($list)){
                $msg="1";
            }else{
                $msg="未查询到该编号的员工,请检查员工编号是否输入正确!";
            }
            $this->ajaxReturn($msg);
        }

    }
 
    //退出
    public function salary_user_tuichu(){
        unset($_SESSION['salary']['salary_pwd']);
        $_SESSION['salary']['login_time']='1480521600';    
        $msg="3秒后本窗口将关闭!";
        $this->ajaxReturn($msg);
    }


  



}
?>