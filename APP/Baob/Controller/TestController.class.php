<?php
namespace Baob\Controller;


class TestController extends BaseController {

	  //读取excel文件
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

    //导入收到数据库
    public function serial_number(){
        //获得工单号
        $file_item_id=I('file_item_id'); 
        /**
        $data['item_id']=$file_item_id;
        $data['time_id']=date('YmdHis');
        $data['print_date']=date('Y-m-d H:i:s');
        $data['print_people']=$_SESSION['payment']['OPER_NAME'];
        $data['print_oa']=$_SESSION['payment']['OA'];
        $data['county_code']=$_SESSION['payment']['COUNTY_CODE'];
        **/
        
        if (!empty($_FILES['file_pcard']['name'])){
            $tmp_file = $_FILES['file_pcard']['tmp_name'];
            $file_types = explode ( ".", $_FILES['file_pcard']['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];

            //*判别是不是.xls文件，判别是不是excel文件
            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
                $this->error ( '不是Excel文件，重新上传' );
            }
            //设置上传路径
            $savePath = '//10.78.1.85/www/ranking/Public/Baob/upfiles/';

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
           // $m=M();

            $m = M();
           // $pamount=0;

           
            if(!empty($res)){  

               /**    
                foreach ( $res as $k => $v ){
                    if ($k != 0){
                        $data['county_name'] = $v[0];
                        $data['org_name'] = $v[1];
                        $data['org_id'] = $v[2];                  
                        $data['oper_id'] =$v[3] ;
                        $data['oper_name']=$v[4] ;
                        $data['oper_date']=$v[5] ;
                        $data['start_card'] =$v[6] ;
                        $data['end_card'] =$v[7] ;   
                        $data['card_price'] =$v[8] ;     
                        $data['card_type'] =$v[9] ;
                        $data['allocate_stat']='1';
                        $flag=$m->table('t_pcard_print_list')->add($data);
                        $pamount=intval($pamount)+intval($v[8]);
                    }
                }
                if($flag){
                    $this->success('导入成功!',U('Payment/serial_import',array('msg'=>$pamount))); 
                }else{
                    $this->error ('导入失败!'); 
                }
                **/
                
              /**
                foreach( $res as $k=> $v ){ //循环excel表               
                    $k=$k-2;//addAll方法要求数组必须有0索引 
                                  
                  
                    //$data['time_id']=date('YmdHis');
                   // $data['print_date']=date('Y-m-d H:i:s');
                   
                   **/

                    $item_id='1';
                    $print_people='2';
                    $print_oa='3';
                    $county_code='4';


                    /**
                    $data['county_name'] = $v[0];
                    $data['org_name'] = $v[1];
                    $data['org_id'] = $v[2];                  
                    $data['oper_id'] =$v[3] ;
                    $data['oper_name']=$v[4] ;
                    $data['oper_date']=$v[5] ;
                    $data['start_card'] =$v[6] ;
                    $data['end_card'] =$v[7] ;   
                    $data['card_price'] =$v[8] ;     
                    $data['card_type'] =$v[9] ;
                    $data['allocate_stat']='1'; 
                    **/

                  /**
                    $sql="insert into t_pcard_print_list_test values('".date('YmdHis')."','".date('Y-m-d H:i:s')."',
                            '".$print_people."','".$print_oa."','".$county_code."','".$v[0]."','".$v[1]."',
                            '".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."','".$v[7]."','".$v[8]."',
                            '".$v[9]."','".$item_id."','1')";


                        //dump($sql);

                    $flag=$m->query($sql); 
                    **/



                        $sq="insert into t_pcard_print_list_test  select '".date('YmdHis')."','".date('Y-m-d H:i:s')."',
                             '".$print_people."','".$print_oa."','".$county_code."','".$res[2][0]."','".$res[2][1]."',
                             '".$res[2][2]."','".$res[2][3]."','".$res[2][4]."','".$res[2][5]."','".$res[2][6]."',
                             '".$res[2][7]."','".$res[2][8]."','".$res[2][9]."','".$item_id."','1' from dual";




                        foreach($res as $k => $v){
                            if($k>2){
                                $sq .= " union all select '".date('YmdHis')."','".date('Y-m-d H:i:s')."',
                                 '".$print_people."','".$print_oa."','".$county_code."','".$v[0]."','".$v[1]."',
                                 '".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."',
                                 '".$v[7]."','".$v[8]."','".$v[9]."','".$item_id."','1' from dual";
                            }
                        }




                        $flag=$m->execute($sq); 



              //  } 

                //$m=M();//M方法  
                //$flag=$m->table('t_pcard_print_list_test')->addAll($data,array(),true);  

                //$flag=$m->table('t_pcard_print_list_test')->add();  

                /// $flag=M()->table('t_pcard_print_list_test')->addAll($data);
             
                if(!$flag){  
                  $this->error('导入数据库失败11');  
                  exit();  
                }else{  
                  $this->success ( '导入成功' );   
                }
               
            }else{
                $this->error ('导入的Execl数据为空！');
            }
        }else{
            $this->error ('请选择导入的Execl数据！' );
        } 
    }


    public function top(){
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


    public function paperExchange(){
        $this->display('Payment/paperExchange');
    }




     //获得结算金额   模块3
    public function testtest(){
        $m=M();
        //$org_id=I('org_id');
        $org_id='4001729';
        $wh['org_id']=$org_id;
       // $list=$m->table('t_pcard_payment_balance')->where($wh)->find();
        $wh['status']=2;
        $wh['review_date']= array('lt',date("Y-m-d"));
        $amount=$m->table('t_pcard_busi_list')->where($wh)->sum('amount');
        //$aa=$m->getLastSql();
       // dump($aa);
        //dump($amount);

        if(empty($amount)){
            echo 'xxxxx';
        }

       // $remain_amount=$m->table('t_pcard_busi_list')->where($wh)->sum('remain_amount');
        
    }




    public function test24(){
        $m=M();
        $sql="select county_name,org_name,org_id,oper_name,case_name,card_price,
                count(card_price) card_no ,count(card_price)*card_price card_amount from (
                select aa.*,bb.org_name,bb.org_id,bb.oper_name from ( select a.* ,b.start_card,
                b.card_price card_price from (select county_name,case_name,bill_id,amount,item_id 
                from mz_user.t_pcard_busi_list where status=3  and county_name='%s'
                )a ,   mz_user.t_pcard_allocate_list b where a.item_id=b.item_id and a.bill_id =b.bill_id
                ) aa,mz_user.t_pcard_print_list bb where aa.start_card=bb.start_card
                ) group by  county_name,org_name,org_id,oper_name,case_name,card_price   
                order  by card_price desc ";
        $lists=$m->query($sql,$_SESSION['payment']['COUNTY_NAME']);
        $this->assign('lists',$lists);
        $this->display();
    }

/**

    public function test25(){

        $rank = M();
        $data['sms_id']='mz_crm.sms_send_seq.nextval';
        $data['destaddr']='18862243446';
        $data['content']='测试测试测试测试';
        //$insert = $rank->db(1,'LS_CONFIG')->orcAdd('ls_sms_job',$data);
        $insert = $rank->orcAdd('mzmms.t_sms_send@ls85',$data);

       // $sql=" select mz_crm.sms_send_seq.nextval from dual";


        //$insert =$rank->table('mzmms.t_sms_send@ls85')->add($data);

        $aa=$rank->getLastSql();
        dump($aa);

        if($insert){
            //return true;
            echo 'yyyy';
        }else{
           // return false;
            echo 'nnnnn';
        }

    }
**/



    public function testUpdate(){
        $m=M('mz_user.t_pcard_print_list_test');
        $m->startTrans();

        $da['code_value']='22';
        $da['code_name']='测试';
        $flag1=$m->table('mz_crm.LS_BS_STATIC_DATA')->add($da);
        dump($flag1);

        $a=$m->_sql();
        dump($a);



        $where['item_id']='hanlong3';
        $where['start_card']='1987';
        $data['end_card']='2004';
        $flag2=$m->table('mz_user.t_pcard_print_list_test')->where($where)->save($data);
        dump($flag2);

        $b=$m->_sql();
        dump($b);


        if($flag2){
            echo 'flag2';
        }else{
             echo 'flag222222';

        }

        echo '<br/>';

        if($flag1&&$flag2){
            
           
            $m->commit();
             echo 'yyy';

        }else{
            
            $m->rollback();
            echo 'nnn';

        }

    }

}
?>
