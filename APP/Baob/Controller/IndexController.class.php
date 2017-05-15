<?php

namespace Baob\Controller;

class IndexController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
		
  
 	}
  
    //赛马考核导入
    public  function smltmb_dr(){  
        if(IS_GET){
            $m=M();
            $sql="SELECT * FROM (SELECT * FROM t_smltmb_zz_jl  order by  months desc ) 
                  where rownum <=12 order by county_code asc";
            $lists=$m->query($sql);     
            $this->assign('lists',$lists);
            $cur_date=date('d');
            $this->assign('cur_date',$cur_date);
            $this->assign('last_date',$last_date);


            if($_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13967086668' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='15168021899' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13906882009' or
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='18862243446' ){
                $zd_xl='1';
            }
            $this->assign('zd_xl',$zd_xl);


            if($_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13957088637' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='15168021899' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13906882009' or
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='18862243446' ){
                $kd_xl='1';
            }
            $this->assign('kd_xl',$kd_xl);



            if($_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13957088632' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='15168021899' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13906882009' or
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='18862243446' ){
                $fh_xl='1';
            }
            $this->assign('fh_xl',$fh_xl);



            if($_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13906781900' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='15168021899' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13906882009' or
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='18862243446' ){
                $mg_xl='1';
            }
            $this->assign('mg_xl',$mg_xl);


            if($_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13906781900' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='15168021899' or 
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='13906882009' or
                $_SESSION['user_auth']['OPER_LOGIN_CODE'] =='18862243446' ){
                $ll_xl='1';
            }
            $this->assign('ll_xl',$ll_xl);

            if(($_SESSION['user_auth']['OPER_LOGIN_CODE']=='15168021899') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13967086668') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13957088637') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13957088632') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13906781900') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13957040598') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13957078851') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13905788537')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13666553919') or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '18806880123')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '18805882227')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13857046111')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13906787885')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13957060013')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13967055978')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '13906882009')  or 
                ($_SESSION['user_auth']['OPER_LOGIN_CODE'] == '18862243446')   ){
                $code='1';
            }

            $this->assign('code',$code);
            $this->display();
            
        }

        if(IS_POST){
            $yf=I('YF');
            $xs=I('XS');

            $zd_tg=I('ZD_TG');
            $yya=I('YYA');
            $sha=I('SHA');
            $zxa=I('ZXA');

            $kd_tg=I('KD_TG');
            $yyb=I('YYB');
            $shb=I('SHB');
            $zxb=I('ZXB');

            $fh_tg=I('FH_TG');
            $yyc=I('YYC');
            $shc=I('SHC');
            $zxc=I('ZXC');

            $mg_tg=I('MG_TG');
            $yyd=I('YYD');
            $shd=I('SHD');
            $zxd=I('ZXD');

            $ll_tg=I('LL_TG');
            $yye=I('YYE');
            $she=I('SHE');
            $zxe=I('ZXE');  

            $county=I('county_code');      

            $cur_date=date('d');

            $m=M();
            foreach ($xs as $k=>$v) {      
                $data['ZD_TG']=trim($zd_tg[$k]);

                $data['YYA']=trim($yya[$k]);
                $data['SHA']=trim($sha[$k]);
                $data['ZXA']=trim($zxa[$k]);

                $data['KD_TG']=trim($kd_tg[$k]);
                $data['YYB']=trim($yyb[$k]);
                $data['SHB']=trim($shb[$k]);
                $data['ZXB']=trim($zxb[$k]);

                $data['FH_TG']=trim($fh_tg[$k]);
                $data['YYC']=trim($yyc[$k]);
                $data['SHC']=trim($shc[$k]);
                $data['ZXC']=trim($zxc[$k]);

                $data['MG_TG']=trim($mg_tg[$k]);
                $data['YYD']=trim($yyd[$k]);
                $data['SHD']=trim($shd[$k]);
                $data['ZXD']=trim($zxd[$k]);

                $data['LL_TG']=trim($ll_tg[$k]);
                $data['YYE']=trim($yye[$k]);
                $data['SHE']=trim($she[$k]);
                $data['ZXE']=trim($zxe[$k]);


                $county_code=trim($county[$k]);

                

                $w['months']=trim($yf[$k]);
                $w['county_name']=trim($xs[$k]);

                //目标值是不判断
               
                if($cur_date>'23'){
                    $flag=$m->table('t_smltmb_zz_jl')->where($w)->save($data); 
                }
               
                if($cur_date<'05' ){
                    $data['MODIFY_DATE']=date('Y-m-d H:i:s');
                    /**
                    $sql="select * from t_smltmb_zz_jl where months='%s' and county_name='%s' ";
                    $list=$m->query($sql,$w['months'],$w['county_name']);
                    if( $list[0]['YYA']==$data['YYA']&&
                        $list[0]['SHA']==$data['SHA']&&
                        $list[0]['ZXA']==$data['ZXA']&&

                        $list[0]['YYB']==$data['YYB']&&
                        $list[0]['SHB']==$data['SHB']&&
                        $list[0]['ZXB']==$data['ZXB']&&

                        $list[0]['YYC']==$data['YYC']&&
                        $list[0]['SHC']==$data['SHC']&&
                        $list[0]['ZXC']==$data['ZXC']&&

                        $list[0]['YYD']==$data['YYD']&&
                        $list[0]['SHD']==$data['SHD']&&
                        $list[0]['ZXD']==$data['ZXD']&&

                        $list[0]['YYE']==$data['YYE']&&
                        $list[0]['SHE']==$data['SHE']&&
                        $list[0]['ZXE']==$data['ZXE']){
                    }else{
                        $flag=$m->table('t_smltmb_zz_jl')->where($w)->save($data);
                    }
                    **/
                    if($county_code==$_SESSION['user_auth']['COUNTY_CODE']){
                        $flag=$m->table('t_smltmb_zz_jl')->where($w)->save($data);
                    }
                }
            }
            if($flag){
                $this->success('数据保存成功!');
            }else{
                $this->error('数据保存失败!');
            }
        }
    }




    //专题首页
    public function zhuanti(){
        $this->display();
    }
  
    //有价卡专题 
    public function val_card(){
        $m=M();
        //县市疑似外流金额
        $sql="select  sum(card_price) totle_wai,county_name from  ls_chenh.mz_zwx_qgczk_0208_temp  
                where bill_id is  null and card_state1 =12  and county_id='0000' 
                and  to_char(sale_date,'yyyy-mm-dd') >='2017-01-01' group by  county_name 
                order by totle_wai desc ";
        $lists=$m->query($sql);
        $this->assign('lists',$lists);

        //县市外流占比
        $sql="select round((a.totle_wai/b.totle)*100,2) totle_pre,a.county_name 
                from (select  sum(card_price) totle_wai,county_name from  ls_chenh.mz_zwx_qgczk_0208_temp 
                where   bill_id is  null  and card_state1 =12 and county_id='0000' 
                and  to_char(sale_date,'yyyy-mm-dd') >='2017-01-01' group by  county_name) a,
                (select  sum(card_price) totle,county_name from  ls_chenh.mz_zwx_qgczk_0208_temp 
                where to_char(sale_date,'yyyy-mm-dd') >='2017-01-01'
                group by  county_name) b  where a.county_name=b.county_name order by totle_pre desc";
        $lista=$m->query($sql); 
        $this->assign('lista',$lista);

        //外流金额大于三万的渠道
        $sql="select round((a.totle_wai/b.totle)*100,2) qud_pre ,a.org_name1用户信息 from (          
                select  sum(card_price) totle_wai,org_name1用户信息 from  ls_chenh.mz_zwx_qgczk_0208_temp 
                where bill_id is  null  and card_state1 =12 and county_id='0000' 
                and to_char(sale_date,'yyyy-mm-dd') >='2017-01-01'  and org_name1用户信息
                is not null group by org_name1用户信息   having sum(card_price)>=30000 ) a,
                ( select  sum(card_price) totle,org_name1用户信息 from  ls_chenh.mz_zwx_qgczk_0208_temp 
                where to_char(sale_date,'yyyy-mm-dd') >='2017-01-01' group by org_name1用户信息) b
                where a.org_name1用户信息=b.org_name1用户信息  order by qud_pre desc ";
        $listg=$m->query($sql); 
        $this->assign('listg',$listg); 

        //外流金额大于三万的业务 
        $sql="select  sum(card_price) totle_wai,busi_name from  ls_chenh.mz_zwx_qgczk_0208_temp   
                where bill_id is  null  and card_state1 =12 and county_id='0000' and  
                to_char(sale_date,'yyyy-mm-dd') >='2017-01-01' and busi_name is not null group by 
                busi_name    having sum(card_price)>30000 ORDER by sum(card_price) desc ";

        $listy=$m->query($sql); 
        $this->assign('listy',$listy); 

        //外流金额大于三万的业务占比
        $sql=" select round((a.totle_wai/b.totle)*100,2) yew_pre,a.busi_name from (          
            select  sum(card_price) totle_wai,busi_name from  ls_chenh.mz_zwx_qgczk_0208_temp   
            where bill_id is  null  and card_state1 =12 and county_id='0000' and  
            to_char(sale_date,'yyyy-mm-dd') >='2017-01-01' and busi_name is not null group by 
            busi_name   having sum(card_price)>30000 )a,  (select  sum(card_price) totle,busi_name 
            from  ls_chenh.mz_zwx_qgczk_0208_temp   
            where to_char(sale_date,'yyyy-mm-dd') >='2017-01-01'  group by  busi_name ) b          
            where a.busi_name=b.busi_name order by yew_pre desc";  

        $listy2=$m->query($sql); 
        $this->assign('listy2',$listy2);
        $this->display();
    }


    //终端分析
    public function zhongduan(){
        $m=M();
        //渠道类型销量占比
        $sql="select count(imei) num,org_type from mz_crm.ls_wyl_170210_photo_down group by org_type";
        $listq=$m->query($sql); 
        $this->assign('listq',$listq);

        //县市销量统计
        $sql=" select count(imei) num ,decode(销售县市,'5781','莲都','5782','缙云','5783','青田','5784','云和',
            '5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') 销售县市 
            from mz_crm.ls_wyl_170210_photo_down group by 销售县市 order by num desc";
        $listx=$m->query($sql); 
        $this->assign('listx',$listx);

        //渠道销量统计
        $sql="select count(imei) num ,渠道名称 from mz_crm.ls_wyl_170210_photo_down group by 渠道名称 
        having count(imei)>5 order by num desc";
        $listl=$m->query($sql); 
        $this->assign('listl',$listl);    

        //每日销量统计
        $sql="select  count(imei) num ,to_char(销售时间,'yyyy-mm-dd') 销售时间 
            from mz_crm.ls_wyl_170210_photo_down  group by to_char(销售时间,'yyyy-mm-dd') 
            order by  销售时间 asc";
        $listd=$m->query($sql); 
        $this->assign('listd',$listd);  

        //活动销量统计
        $sql=" select count(imei) num ,预缴名称 from mz_crm.ls_wyl_170210_photo_down  group by 预缴名称 
        order by num desc";
        $listh=$m->query($sql); 
        $this->assign('listh',$listh);
        $this->display();    
    }

    //客服投诉
    public function kefu(){
        $m=M();

         $sql="select a.sl_item,b.kf_ldl,c.xy_item,d.gy_item,a.busi_date from  v_ls_kf_slgd a,v_ls_kf_skfldl b, 
             v_ls_kf_xygdl c,v_ls_kf_gytsl d where a.busi_date=b.busi_date and a.busi_date=c.busi_date 
             and  a.busi_date=d.busi_date ";

        $listt=$m->query($sql); 
        $this->assign('listt',$listt);


        $sql="select round(((count_no_b-count_no_a)/count_no_b)*100,2) satis ,
                decode(FEEDBACK_STAFF_NO,'gongcy','龚春燕','gongzhenzhen','龚珍珍','lshuann','胡安娜',
                'jihong','纪虹','ls_jilf','纪丽芬','ls_liuxf','刘小芬','wangli9','王丽','jnwujj','吴晶晶',
                'yingjingyu','应靓煜','zhoucheng1','周铖','moyingzhen1','莫颖震','ls_sunn','孙妮',
                'xieliyan','谢丽燕') FEEDBACK_STAFF_NO    from (
                select nvl(a.count_no_a,0) count_no_a ,b.count_no_b,b.FEEDBACK_STAFF_NO from (
                select count(*) count_no_a,FEEDBACK_STAFF_NO  from  ls_chenh.mz_rpt_sr_20121_daily 
                where FEEDBACK_DEPT=19  and (f_1!=0 or f_2!=0 or f_3!=0 or f_4!=0 or f_5!=0 or f_6!=0 
                or f_7!=0 or f_8!=0 )  and FEEDBACK_STAFF_NO in ('gongcy','gongzhenzhen','lshuann',
                'jihong','ls_jilf','ls_liuxf','wangli9','jnwujj','yingjingyu','zhoucheng1','moyingzhen1',
                'ls_sunn','xieliyan') and   done_date>=trunc(sysdate, 'mm')  group by FEEDBACK_STAFF_NO ) a ,
                (select count(*) count_no_b,FEEDBACK_STAFF_NO  from  ls_chenh.mz_rpt_sr_20121_daily 
                where FEEDBACK_DEPT=19   and FEEDBACK_STAFF_NO in ('gongcy','gongzhenzhen','lshuann',
                'jihong','ls_jilf','ls_liuxf','wangli9','jnwujj','yingjingyu','zhoucheng1','moyingzhen1',
                'ls_sunn','xieliyan')  and   done_date>=trunc(sysdate, 'mm')  group by FEEDBACK_STAFF_NO ) b  
                where a.FEEDBACK_STAFF_NO(+)=b.FEEDBACK_STAFF_NO
                ) order by  satis desc  ";
        $lists=$m->query($sql); 
        $this->assign('lists',$lists);

       

        $sql=" select decode(aaa.op_code,'gongcy','龚春燕','gongzhenzhen','龚珍珍','lshuann','胡安娜',
                'jihong','纪虹','ls_jilf','纪丽芬','ls_liuxf','刘小芬','wangli9','王丽','jnwujj','吴晶晶',
                'yingjingyu','应靓煜','zhoucheng1','周铖','moyingzhen1','莫颖震','ls_sunn','孙妮',
                'xieliyan','谢丽燕') op_code,aaa.f_3,aaa.f_7,aaa.f_14,aaa.f_11,
                nvl(bbb.count_no_a,0) count_no_a , nvl(ccc.count_no,0) count_no , ddd.平均时长 from( 
                select sum(f_3) f_3 ,sum(f_7) f_7,sum(f_14) f_14,sum(f_11) f_11,op_code from ( 
                select f_3,f_7,f_11,f_14,op_code from ls_chenh.mz_rpt_sr_20011_daily 
                where to_date(to_char(busi_date),'yyyy-mm-dd')>=trunc(sysdate, 'mm') and dept_id=19 
                and op_code in('gongcy','gongzhenzhen','lshuann','jihong','ls_jilf','ls_liuxf',
                'wangli9','jnwujj','yingjingyu','zhoucheng1','moyingzhen1','ls_sunn','xieliyan') 
                ) group by op_code ) aaa,(
                select count(*) count_no_a,FEEDBACK_STAFF_NO  from  ls_chenh.mz_rpt_sr_20121_daily 
                where FEEDBACK_DEPT=19  and (f_1!=0 or f_2!=0 or f_3!=0 or f_4!=0 or f_5!=0 or f_6!=0 or 
                f_7!=0 or f_8!=0 ) and FEEDBACK_STAFF_NO in ('gongcy','gongzhenzhen','lshuann','jihong',
                'ls_jilf','ls_liuxf','wangli9','jnwujj','yingjingyu','zhoucheng1','moyingzhen1','ls_sunn',
                'xieliyan')  and   done_date>=trunc(sysdate, 'mm') group by FEEDBACK_STAFF_NO ) bbb,(
                select b.count_no_b-a.count_no_a  count_no , b.FEEDBACK_STAFF_NO from (
                select count(*) count_no_a,FEEDBACK_STAFF_NO  from  ls_chenh.mz_rpt_sr_20121_daily 
                where FEEDBACK_DEPT=19  and (f_1!=0 or f_2!=0 or f_3!=0 or f_4!=0 or f_5!=0 or f_6!=0 or 
                f_7!=0 or f_8!=0 )  and FEEDBACK_STAFF_NO in ('gongcy',
                'gongzhenzhen','lshuann','jihong','ls_jilf','ls_liuxf','wangli9','jnwujj','yingjingyu',
                'zhoucheng1','moyingzhen1','ls_sunn','xieliyan') and   done_date>=trunc(sysdate, 'mm')
                 group by FEEDBACK_STAFF_NO   ) a ,( 
                select count(*) count_no_b,FEEDBACK_STAFF_NO  from  ls_chenh.mz_rpt_sr_20121_daily 
                where FEEDBACK_DEPT=19   and FEEDBACK_STAFF_NO in ('gongcy','gongzhenzhen','lshuann','jihong',
                'ls_jilf','ls_liuxf','wangli9','jnwujj','yingjingyu','zhoucheng1','moyingzhen1','ls_sunn','xieliyan') 
                and  done_date>=trunc(sysdate, 'mm') group by FEEDBACK_STAFF_NO ) b  
                 where a.FEEDBACK_STAFF_NO=b.FEEDBACK_STAFF_NO) ccc,(
                  select count(*) 条数,round(sum(aa)/count(*),1) 平均时长,反馈人工号 from (
                  select 受理时间,反馈时间,反馈人工号,(反馈时间-受理时间)* 86400/3600  aa from  
                  ls_chenh.MZ_QS_KF_SR_PROBLEM_DTL_201703 where  反馈人工号 in('gongcy','gongzhenzhen',
                  'lshuann','jihong','ls_jilf','ls_liuxf','wangli9','jnwujj','yingjingyu','zhoucheng1',
                 'moyingzhen1','ls_sunn','xieliyan') ) group by  反馈人工号 ) ddd
                   where aaa.op_code=bbb.FEEDBACK_STAFF_NO(+) and  aaa.op_code=ccc.FEEDBACK_STAFF_NO(+) 
                   and aaa.op_code =ddd.反馈人工号(+)";
        $listg=$m->query($sql); 
        $this->assign('listg',$listg);
        $this->display();
    }

    public function test(){
        $flag=parent::sms('18862243446','短信测试');  
        if($flag=='true'){
            echo 'yyyy';
        }else{
            echo 'nnnnn';
        }
    }


    public function paperExchange(){
        $this->display();
    }


   


}
?>