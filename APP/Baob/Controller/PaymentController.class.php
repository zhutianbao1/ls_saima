<?php

namespace Baob\Controller;

class PaymentController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){

        if(!isset($_SESSION['payment'])||empty($_SESSION['payment'])){
            redirect('/ranking/Baob/Login/payment_index',array('info' => 99), 0);
        }
        
 	}

    //结算清单 模块2
    public function show(){
        $m=M();

        $OA=$_SESSION['payment']['OA']; 
          
        $sql="select org_id from t_pcard_org_info where manager_oa='%s'";
        $orgs=$m->query($sql,$OA);
        $org="";
        for($i=0;$i<count($orgs);$i++){
            if($i==count($orgs)-1){
                $org.="'".$orgs[$i]['ORG_ID']."'";
            }else{
                $org.="'".$orgs[$i]['ORG_ID']."',";
            }
        }

        if($_SESSION['payment']['COUNTY_CODE']=='5780'){
            $sql="select * from mz_user.t_pcard_busi_list where 1=1  ";
        }else{
            $sql="select * from mz_user.t_pcard_busi_list  where  org_id in (".$org.")";
        }
    
        $busi_name=I('busi_name');
        $org_name=I('org_name');  
        $status=I('status'); 
        if(!empty($busi_name)){
            $sql.=" and busi_name like '%".$busi_name."%'";
        }
        if(!empty($org_name)){
            $sql.=" and org_name like '%".$org_name."%' ";
        }
        if(empty($status)){
            $status='1';
        }
        $sql.=" and status ='".$status."' ";

        $lists=parent::listsSqlByls($sql,20);
        $this->assign('lists',$lists);
        $this->assign('busi_name',$busi_name);
        $this->assign('org_name',$org_name);
        $this->assign('status',$status);
        $this->display();
    }



    //结算清单审核  模块2
    public function item_shenhe(){
        $list_ids_tem=I('list_ids');
        $list_ids=substr($list_ids_tem, 0, -1);
        $m=M();
        $where['status']=1;
        $where['list_id']=array('in',$list_ids);
        $data['status']=2;
        $data['review_date']=date('Y-m-d H:i:s');
        $data['review_oper']=$_SESSION['payment']['OPER_NAME'];

        $flag=$m->table('mz_user.t_pcard_busi_list')->where($where)->save($data);
       
        if($flag){
            $j['status']='1';
            $j['msg']='审核成功!';
        }else{
            $j['status']='0';
            $j['msg']='审核失败!';
        }
        $this->ajaxReturn($j);
    }



    //PHPExcel导出  exportxls   模块2
    public function item_busi_exp(){
        $m=M();
        $busi_name=I('busi_name');
        $org_name=I('org_name');  
        $status=I('status');

        $OA=$_SESSION['payment']['OA']; 
       
        $sql="select org_id from t_pcard_org_info where manager_oa='%s'";
        $orgs=$m->query($sql,$OA);
        $org="";
        for($i=0;$i<count($orgs);$i++){
            if($i==count($orgs)-1){
                $org.="'".$orgs[$i]['ORG_ID']."'";
            }else{
                $org.="'".$orgs[$i]['ORG_ID']."',";
            }
        }

        if($OA=='hanlong3'||$OA=='zhangweixing1'){
            $sql="select county_name,org_name,bill_id,imei,busi_name,done_date,amount,
            decode(status,1,'未审核',2,'已审核',3,'已打印') status 
            from mz_user.t_pcard_busi_list where 1=1 ";
        }else{
            $sql="select county_name,org_name,bill_id,imei,busi_name,done_date,amount,
             decode(status,1,'未审核',2,'已审核',3,'已打印') status
              from mz_user.t_pcard_busi_list where 1=1 and org_id in (".$org.")";
        }

        if(!empty($busi_name)){
            $sql.=" and busi_name like '%".$busi_name."%'";
        }
        if(!empty($org_name)){
            $sql.=" and org_name like '%".$org_name."%' ";
        }

        if(!empty($status)){
            $sql.=" and status ='".$status."' ";
        }

        

        $elist = $m->query($sql);

        $filename="缴费卡结算清单.xls";
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
        /**
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);

        **/
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);



        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:Y1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:Y1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:Y1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:Y1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '县市')
            ->setCellValue('B1', '渠道名称')
            ->setCellValue('C1', '手机号码')
            ->setCellValue('D1', '终端串号')
            ->setCellValue('E1', '结算业务')
            ->setCellValue('F1', '受理时间')
            ->setCellValue('G1', '结算金额')
            ->setCellValue('H1', '结算状态');

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['ORG_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['BILL_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), "`".$elist[$k]['IMEI']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['BUSI_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['DONE_DATE']);  
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['AMOUNT']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['STATUS']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('缴费卡结算清单');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //缴费卡结算   模块3
    public function settlement(){
        $m=M();
        $item_id_tep='LS'.date('Ymd');
        $w['item_id'] = array('like',$item_id_tep.'%');
        $item_num=$m->table('t_pcard_payment_record')->where($w)->count();
        $item_num=intval($item_num); 
       
        //生成工单号
        $sql="select  T_PCARD_PAYMENT_RECORD_SEQ.Nextval  item_tem from dual";
        $item_tmp=$m->query($sql);
        $item_id='000000'.$item_tmp[0]['ITEM_TEM'];
        $item_id='LS'.substr($item_id, -10);
        $this->assign('item_id',$item_id);
        $this->display();
    }


    //根据SIM卡查询是否有未打印的工单  模块3
    public function getItemCount(){
        $sim_card=I('sim_card_code');
        $m=M();
        $w['sim_card']=$sim_card;
        $list=$m->table('t_pcard_org_info')->where($w)->find();
        if(!empty($list)){
           $org_id=$list['ORG_ID'];
           $wh['org_id']=$org_id;
           $wh['payment_status']='已结算';
           $count1=$m->table('t_pcard_payment_record')->where($wh)->count();
           if($count1>0){
                $j['status']='1';
                $j['msg']="您有未稽核的工单!请先稽核!";
           }else{
                $j['status']='0';
           }      
           $where['org_id']=$org_id;
           $where['payment_status']='一级稽核';
           $where['first_audit_result']='同意';
           $count2=$m->table('t_pcard_payment_record')->where($where)->count();
           if($count2>0){
                $j['status']='1';
                $j['msg']="您有未领取的工单,请先领取之前的工单!";
           }else{
               $j['status']='0';
           }
        }else{
            $j['status']='2';
            $j['msg']="未查询到对应的渠道信息!";
        }        
        $this->ajaxReturn($j);
    }


    //获取渠道信息    模块3
    public function getInfo(){
        $sim_card_code=I('sim_card_code');
        $w['sim_card']=$sim_card_code;
        $m=M();
        $info=$m->table('t_pcard_org_info')->where($w)->find();
        $org_id=$info['ORG_ID'];

        if(!empty($info)){
            $j['status']='1'; 
            $j['info']=$info;
        }else{
            $j['status']='0';  
        }
        $this->ajaxReturn($j);
    }


    //获得验证码,并发送到渠道老板手机   模块3
    public function getVerfy(){
        $item_id=I('item_id');
        $sim_card_code=I('sim_card_code');
        $org_id=I('org_id');
        $bill_id=I('bill_id');
        $verfy_code=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        $data['item_id']=$item_id;
        $data['sim_card']=$sim_card_code;
        $data['org_id']=$org_id;
        $data['verfy_bill']=$bill_id;
        $data['verfy_code']=$verfy_code;
        $data['verfy_date']=date('Y-m-d H:i:s');
        $data['status']='0';
        $m=M();
        $flag=$m->table('t_pcard_org_verfy_info')->add($data);
        if($flag){
            $where['sim_card']=$sim_card_code;
            $list=$m->table('t_pcard_org_info')->where($where)->find();
            $county_name=$list['COUNTY_NAME'];
            $org_name=$list['ORG_NAME'];
            $org_boss=$list['ORG_BOSS'];
            if(!empty($list)){
                $flag=parent::sms($bill_id,$county_name.'/'.$org_name.'/'.$org_boss.'缴费卡申领验证码是'.$verfy_code);
                if($flag=='true'){
                    $j['status']='1';
                    $j['msg']='验证码发送成功！';
                }else{
                    $j['status']='0';
                    $j['msg']='验证码发送失败!';
                }
            }else{
                $j['status']='0';
                $j['msg']="未查询到对应的渠道信息!";
            }
        }else{
            $j['status']='0';
            $j['msg']='验证码获得失败!'; 
        }
        $this->ajaxReturn($j);
    }


    //验证渠道老板   模块3
    public function  getYanzheng(){
        $m=M();
        $w['item_id']=I('item_id');
        $w['sim_card']=I('sim_card_code'); 
        $w['org_id']=I('org_id'); 
        $w['verfy_bill']=I('bill_id'); 
        $w['verfy_code']=I('verfy_code'); 
        $w['status']='0';
        $da['status']='1';
        $list=$m->table('t_pcard_org_verfy_info')->where($w)->order('verfy_date desc')->find();
        if(!empty($list)){
            $status=$list['STATUS'];
            if($status=='0'){
                $flag=$m->table('t_pcard_org_verfy_info')->where($w)->save($da);
                if($flag){
                    $j['status']='1';
                }else{
                    $j['status']='0';
                }
            }else{
                $j['status']='2'; 
            }
        }else{
            $j['status']='3';
        }
        $this->ajaxReturn($j);
    }

    //获得结算金额   模块3
    public function getAmount(){
        $m=M();
        $org_id=I('org_id');
        $wh['org_id']=$org_id;

        //历史余额
        $list=$m->table('t_pcard_payment_balance')->where($wh)->find();
        if(!empty($list)){
            $j['balance']=$list['BALANCE'];
        }else{
            $data['org_id']=$org_id;
            $data['balance']=0;
            $data['payment_date']=date('Y-m-d H:i:s');
            $m->table('t_pcard_payment_balance')->add($data);
            $j['balance']=0;
        }
        $wh['status']=2;
        $wh['review_date']= array('lt',date("Y-m-d"));
        $amount=$m->table('t_pcard_busi_list')->where($wh)->sum('amount');
        $remain_amount=$m->table('t_pcard_busi_list')->where($wh)->sum('remain_amount');
        if(empty($amount)){
            $amount=0;
        }

        if(empty($remain_amount)){
            $remain_amount=0;
        }

        //本次余额
        $j['small']=$remain_amount;
        //可兑换金额
        $j['totle']=$amount;
        $this->ajaxReturn($j);
    }

    //查看领取人是否法人  模块3
    public function getFaren(){
        $org_id=I('org_id');
        $oper_phone=I('oper_phone');
        $oper_name=I('oper_name');
        $w['org_id']=$org_id;
        $w['org_boss']=$oper_name;
        $w['boss_phone']=$oper_phone;
        $m=M();
        $info=$m->table('t_pcard_org_info')->where($w)->find();        
        if(!empty($info)){
            $j['status']='1';
            $j['result']='是'; 
        }else{
            $j['status']='0';   
            $j['result']='否';  
        }
        $this->ajaxReturn($j);
    }


    //得到分配方案    模块3
    public function  getPriceCase(){
        $sim_card=I('sim_card');
        $org_id=I('org_id');
        $check_arr=I('check_arr');
        $item_id=I('item_id');

        $sql="select sum(amount) amount,bill_id from mz_user.t_pcard_busi_list where status=%s and 
                org_id='%s' and review_date<'%s'  group by bill_id order by   amount desc ";
        $m=M();
        $lists=$m->query($sql,2,$org_id,date('Y-m-d'));

      

        $totle=$this->allocateCase($lists,$check_arr);         

        if(!isset($totle['status'])&&empty($totle['status'])){
            $a1="";$a2="";$a3="";$a4="";$a5="";$a6="";
            $m=M();
            $w['item_id']=$item_id;
            $flag=$m->table('t_pcard_allocate_case')->where($w)->delete();
            if($flag>=0){
                for($k=0;$k<count($totle);$k++){
                    $a1+=$totle[$k][500];
                    $a2+=$totle[$k][300];
                    $a3+=$totle[$k][100];
                    $a4+=$totle[$k][50];
                    $a5+=$totle[$k][30];
                    $a6+=$totle[$k][20];

                    $bill_id=$totle[$k]['bill_id'];
                    $data['item_id']=$item_id;
                    $data['bill_id']=$bill_id;
                    $data['amount']=$totle[$k]['amount'];
                    $data['price500']=$totle[$k][500];
                    $data['price300']=$totle[$k][300];
                    $data['price100']=$totle[$k][100];
                    $data['price50']=$totle[$k][50];
                    $data['price30']=$totle[$k][30];
                    $data['price20']=$totle[$k][20];
                    $data['create_date']=date('Y-m-d H:i:s');
                    $data['status']='1';
                    $flag=$m->table('t_pcard_allocate_case')->add($data);
                    if($flag){
                        $j['status']='1';
                        $j['msg']='方案分配完成!';
                    }else{
                        $j['status']='0';
                        $j['info']='系统请求出错!';
                    }
                }
                $j['500']=$a1;
                $j['300']=$a2;
                $j['100']=$a3;
                $j['50']=$a4;
                $j['30']=$a5;
                $j['20']=$a6;
            }else{ 
                $j['status']='0';
                $j['info']='系统请求出错!';
            }
        }else{
            $j['info']=$totle['msg'];
            $j['status']=$totle['status'];
        }
        $this->ajaxReturn($j);
    }

    //分配方案
    public function allocateCase($lists,$price){
        for($i=0;$i<count($lists);$i++){
            $s_amount=$lists[$i]['AMOUNT'];
            $bill_id=$lists[$i]['BILL_ID'];

            $amount=array(500=>0,300=>0,100=>0,50=>0,30=>0,20=>0,'bill_id'=>'','amount'=>$s_amount);
            $amount['bill_id']=$bill_id;

            $flag=true;

            // 1、先处理零头        
            $temp = $s_amount%100;
            $s_amount = $s_amount - $temp;

            if($temp == 0){
            }elseif($temp == 10){
                //先分配110
                $s_amount = $s_amount-100;
                if($s_amount<0){
                    $flag = false;//金额为10，无法分配
                    //System.out.println("用户金额无法完全分配");
                    $info['msg'] = "有价卡稽核失败！金额:10元无法完成分配。";
                    $info['status']='0';
                }else{
                    //分配50
                   $amount[50]+=1;
                    //分配60
                   $amount[30]+=2;
                }            
            }elseif($temp == 20){
                $amount[20]+=1;
            }elseif($temp == 30){
                $amount[30]+=1;           
            }elseif($temp == 40){
                $amount[20]+=2;            
            }elseif($temp == 50){
                $amount[50]+=1;            
            }elseif($temp == 60){
                $amount[30]+=2;           
            }elseif($temp == 70){
                //50+20
                $amount[50]+=1;
                $amount[20]+=1;
            }elseif($temp == 80){
                $amount[20]+=4;            
            }elseif($temp == 90){
                $amount[30]+=3;            
            }else{
                $flag = false;//金额为10，无法分配
                $info['msg'] = "有价卡稽核失败！用户金额存在个位为".($temp%10)."的数据。";
                $info['status']='0';
            }
            //2、处理整百的数据
            for($j=0;$j<count($price);$j++){          
                //分配结束
                if($s_amount <= 0){
                    break;
                }
                $num = 0;
                $num = floor($s_amount/$price[$j]);
                //判断该额度数量是否满足
                if($num >=1){
                    //优先分配所有数量给用户，剩下的用次高额度
                    //$num = entry.getValue();
                    $amount[$price[$j]]+=$num;
                    $s_amount = $s_amount- ($num * $price[$j]);
                }
            }
            $array1[$i]=$amount;
        }
        if($flag ==true){
            return $array1;
        }else{
            return $info;
        }
    }


    //导入序列号页面   模块3
    public function serial_import(){
        $item_id=I('item_id');
        $this->assign('item_id',$item_id);
        $msg=I('msg');
        if(!empty($msg)){
            $this->assign('msg',$msg);
        }
        $this->display();
    }


    //读取excel文件  模块3
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

    //导入收到数据库  模块3
    public function serial_number(){
        //获得工单号
        $m=M();
        $file_item_id=I('file_item_id'); 
        if(!empty($file_item_id)){
            //删除一次，防止上一次导入失败存在的部分数据
            $where['item_id']=$file_item_id;
            $m->table('t_pcard_print_list')->where($where)->delete();

            $data['item_id']=$file_item_id;
            $data['time_id']=date('YmdHis');
            $data['print_date']=date('Y-m-d H:i:s');
            $data['print_people']=$_SESSION['payment']['OPER_NAME'];
            $data['print_oa']=$_SESSION['payment']['OA'];
            $data['county_code']=$_SESSION['payment']['COUNTY_CODE'];

            /**
            $item_id=$file_item_id;
            $time_id=date('YmdHis');
            $print_date=date('Y-m-d H:i:s');
            
            **/

            $print_people=$_SESSION['payment']['OPER_NAME'];
            $print_oa=$_SESSION['payment']['OA'];
            $county_code=$_SESSION['payment']['COUNTY_CODE'];

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
                
                $pamount=0;           
                if(!empty($res)){ 
                    /**
                    foreach ($res as $k => $v ){
                        if ($k != 0){
                            $data['county_name'] = strval($v[0]);
                            $data['org_name'] = strval($v[1]);
                            $data['org_id'] = strval($v[2]); 
                            $data['oper_id'] =strval($v[3]);
                            $data['oper_name']=strval($v[4]);
                            $data['oper_date']=strval($v[5]);
                            $data['start_card']=strval($v[6]);
                            $data['end_card'] =strval($v[7]);
                            $data['card_price'] =intval($v[8]); 
                            $data['card_type'] =strval($v[9]);
                            $data['allocate_stat']='1';
                            **/ 
                            /**
                            $county_name=strval($v[0]);
                            $org_name=strval($v[1]);                          
                            $org_id=strval($v[2]); 
                            $oper_id=strval($v[3]);
                            $oper_name=strval($v[4]);
                            $oper_date=strval($v[5]);

                            //$start_card=strFilter2(strval($v[6]));
                            //$end_card=strFilter2(strval($v[7]));

                            $start_card=strval($v[6]);
                            $end_card=strval($v[7]);

                            $card_price= intval($v[8]);
                            $card_type=strval($v[9]);
                           

                            $sql="insert into t_pcard_print_list (time_id,print_date,print_people,print_oa,
                                    county_code,county_name,org_name,org_id,oper_id,oper_name,oper_date,
                                    start_card,end_card,card_price,card_type,item_id,allocate_stat ) 
                                    values ('".$time_id."','".$print_date."','".$print_people."','".$print_oa."',
                                    '".$county_code."','".$county_name."','".$org_name."','".$org_id."','".$oper_id."',
                                    '".$oper_name."','".$oper_date."','".$start_card."','".$end_card."',
                                    '".$card_price."','".$card_type."','".$item_id."','1' )";
                            $flag=$m->execute($sql);
                            **/

                           // $flag=$m->table('t_pcard_print_list')->add($data);
                           // $pamount=intval($pamount)+intval($v[8]);
                       // }
                    // }

                   
                    if(!empty($res[2][0]) && !empty($res[2][1]) && !empty($res[2][2]) && !empty($res[2][3]) &&
                       !empty($res[2][4]) && !empty($res[2][5]) && !empty($res[2][6]) && !empty($res[2][8]) &&
                       !empty($res[2][9]) ){
                        $start_card0=substr($res[2][6], 0,17);
                        $end_card0=substr($res[2][7], 0,17);

                        $sql="insert into mz_user.t_pcard_print_list  select 
                               '".date('YmdHis')."','".date('Y-m-d H:i:s')."',
                               '".$print_people."','".$print_oa."','".$county_code."','".strval($res[2][0])."',
                               '".strval($res[2][1])."','".strval($res[2][2])."','".strval($res[2][3])."',
                               '".strval($res[2][4])."','".strval($res[2][5])."','".strval($start_card0)."',
                               '".strval($end_card0)."','".intval($res[2][8])."','".strval($res[2][9])."',
                               '".$file_item_id."','1' from dual";
                        foreach($res as $k => $v){
                                if($k>2){
                                    $start_card=substr($v[6], 0,17);
                                    $end_card=substr($v[7], 0,17);
                                    $sql .= " union all select '".date('YmdHis')."','".date('Y-m-d H:i:s')."',
                                         '".$print_people."', '".$print_oa."',    '".$county_code."', '".strval($v[0])."',
                                         '".strval($v[1])."', '".strval($v[2])."','".strval($v[3])."','".strval($v[4])."',
                                         '".strval($v[5])."', '".strval($start_card)."','".strval($end_card)."',
                                         '".intval($v[8])."', '".strval($v[9])."', '".$file_item_id."','1' from dual";
                                }
                                $pamount=intval($pamount)+intval($v[8]);
                        }
                        
                        $flag=$m->execute($sql);
                        
                        if($flag){
                           $this->success('导入成功!',U('Payment/serial_import',array('msg'=>$pamount))); 
                        }else{
                            $this->error ('导入失败!'); 
                        }

                    }else{
                        $this->error('导入的Execl数据字段不能为空！');
                    }
                }else{
                    $this->error ('导入的Execl数据为空！');
                }
            }else{
                $this->error ('请选择导入的Execl数据！' );
            } 
        }else{
            $this->error('未获得到对应的工单号!,请重新操作!');
        }
    }


    //删除导入的打印清单   模块3
    public function delete_print_list(){
        $m=M();
        $item_id=I('item_id');
        $w['item_id']=$item_id;
        $flag=$m->table('t_pcard_print_list')->where($w)->delete();
        if($flag>=0){
            $j['status']='1';
        }else{
            $j['status']='0';
        }
        $this->ajaxReturn($j);
    }


     //获得有价卡序列号表金额   模块3
    public  function getCardAmount(){
        $item_id=I('item_id');
        $actual_amount=I('actual_amount');
        $exchange_num=I('exchange_num');
        $m=M();    
        $w['item_id']=$item_id;
        $lists=$m->table('t_pcard_print_list')->where($w)->select();
        if(!empty($lists)){
            $totleAmount=$m->table('t_pcard_print_list')->where($w)->sum('card_price');
            $totlePager=$m->table('t_pcard_print_list')->where($w)->count('card_price');
            //从导入的打印清单列表中查询对应的工单各面值数量
            $wh500['card_price']=500;
            $wh500['item_id']=$item_id;
            $pager500=$m->table('t_pcard_print_list')->where($wh500)->count('card_price');
            $wh300['card_price']=300;
            $wh300['item_id']=$item_id;
            $pager300=$m->table('t_pcard_print_list')->where($wh300)->count('card_price');
            $wh100['card_price']=100;
            $wh100['item_id']=$item_id;
            $pager100=$m->table('t_pcard_print_list')->where($wh100)->count('card_price');
            $wh50['card_price']=50;
            $wh50['item_id']=$item_id;
            $pager50=$m->table('t_pcard_print_list')->where($wh50)->count('card_price');
            $wh30['card_price']=30;
            $wh30['item_id']=$item_id;
            $pager30=$m->table('t_pcard_print_list')->where($wh30)->count('card_price');
            $wh20['card_price']=20;
            $wh20['item_id']=$item_id;
            $pager20=$m->table('t_pcard_print_list')->where($wh20)->count('card_price');            
            //从分配方案表中查询工单的各面值分配数量
            $where['item_id']=$item_id;
            $price500=$m->table('t_pcard_allocate_case')->where($where)->sum('price500');
            $price300=$m->table('t_pcard_allocate_case')->where($where)->sum('price300');
            $price100=$m->table('t_pcard_allocate_case')->where($where)->sum('price100');
            $price50=$m->table('t_pcard_allocate_case')->where($where)->sum('price50');
            $price30=$m->table('t_pcard_allocate_case')->where($where)->sum('price30');
            $price20=$m->table('t_pcard_allocate_case')->where($where)->sum('price20');
            //判断导入的记录和分配方案中各面值是否相等
            $flag=true; 
            if($pager500==$price500&&$pager300==$price300&&$pager100==$price100&&
               $pager50==$price50&&$pager30==$price30&&$pager20==$price20){
                $flag=true;
            }else{
                $flag=false;
            }

            if($totleAmount==$actual_amount&&$exchange_num==$totlePager&&$flag==true){
                $j['status']='1';
            }else{
                $j['status']='0';
                $j['info']='您导入的缴费卡金额与实际金额或数量不相同,请删除数据并重新导入';
            }
            $sql="select start_card from  mz_user.t_pcard_print_list where item_id in (
                select item_id from  mz_user.t_pcard_payment_record ) and start_card in (
                select start_card from mz_user.t_pcard_print_list where item_id='%s' )";
            $card_no= $m->query($sql,$item_id);
            if(!empty($card_no)){
                $j['status']='0';
                $j['info']='您导入的缴费卡卡号已经存在!请删除数据并更换卡号重新导入';
            }
        }else{
            $j['status']='00';
        }
        $this->ajaxReturn($j);
    }

    //记录插入   模块3
    public function getRecord(){
        $m=M();
        $item_id=I('item_id');
        $org_id=I('org_id');
        $sim_card_code=I('sim_card_code');
        $payment_amount=I('actual_amount');
        if(!empty($sim_card_code)&&!empty($org_id)){           
            $data['payment_date']=date('Y-m-d H:i:s');
            $data['item_id']=$item_id;
            $data['county_name']=I('county_name');
            $data['org_id']=$org_id;
            $data['org_name']=I('org_name');            
            $data['manager_name']=I('manager_name');
            $data['payment_oper']=I('oper_name');
            $data['payment_bill']=I('oper_phone');
            $data['payment_card']=I('oper_card');
            $data['sf_faren']=I('sf_legal_person');
            $data['payment_amount_sj']=I('actual_amount');
            $data['payment_amount_bc']=I('exchange_amount');
            $data['payment_card_no']=I('exchange_num');
            $data['payment_status']='已结算';
            $data['his_balance']=I('ls_small');
            $data['remain_amount']=I('bc_small');
            $data['js_person']=$_SESSION['payment']['OPER_NAME'];
            $data['js_phone']=$_SESSION['payment']['OPER_LOGIN_CODE'];

            $flag1=$m->table('t_pcard_payment_record')->add($data);

            if($flag1){
                $da['item_id']=$item_id;
                $da['status']=3;
                $da['payment_date']=date('Ymd');
                $where['org_id']=$org_id;
                $where['status']=2;
                $where['review_date']= array('lt',date("Y-m-d"));
                $flag=$m->table('t_pcard_busi_list')->where($where)->save($da);
                if($flag){
                    $wh['item_id']=$item_id;
                    $countlist=$m->table('t_pcard_busi_list')->where($wh)->count('amount');
                    if(!empty($countlist)){
                        $amount=$m->table('t_pcard_busi_list')->where($wh)->sum('amount'); 
                        if($amount==$payment_amount){
                            $this->success('数据保存成功!');
                        }else{
                            $dd['item_id']='';
                            $dd['status']=2;
                            $dd['payment_date']='';
                            $m->table('t_pcard_busi_list')->where($wh)->save($dd);
                            $map['item_id']=$item_id;
                            $flag=$m->table('t_pcard_payment_record')->where($map)->delete();
                            $this->error('数据保存失败!4');
                        }
                    }else{
                        $map['item_id']=$item_id;
                        $flag=$m->table('t_pcard_payment_record')->where($map)->delete();
                        $this->error('数据保存失败!3');
                    }
                }else{
                    $map['item_id']=$item_id;
                    $flag=$m->table('t_pcard_payment_record')->where($map)->delete();
                    $this->error('数据保存失败!2');
                }
            }else{
                $this->error('数据保存失败!1');
            }
        }else{
             $this->error('渠道编号和验证卡号不能为空!');
        }        
    }


    //一级稽核列表    模块4
    public function item_list(){
        $m=M();
        $county_code=$_SESSION['payment']['COUNTY_CODE'];
        if($county_code=='5780'){
            $sql="select * from t_pcard_payment_record where payment_status='已结算'  ";
        }else{
            $sql="select * from t_pcard_payment_record where payment_status='已结算' and 
                                   county_name='".$_SESSION['payment']['COUNTY_NAME']."' ";
        }        
        $lists=parent::listsSqlByls($sql,20);
        $this->assign('lists',$lists);
        $this->display();        
    }

    //一级稽核页面    模块4
    public function first_audit(){
        $m=M();
        $item_id=I('item_id');
        $w['payment_status']='已结算'; 
        $w['item_id']=$item_id;       
        $list=$m->table('t_pcard_payment_record')->where($w)->find();
        $this->assign('list',$list);
        $org_id=$list['ORG_ID'];
       
        $wh['org_id']=$org_id;
        $listq=$m->table('t_pcard_org_info')->where($wh)->find();
        $this->assign('listq',$listq);

        $where['item_id']=$item_id;
        $where['status']='1';
        $listy=$m->table('t_pcard_org_verfy_info')->where($where)->order('verfy_date desc')->find();
        $this->assign('listy',$listy); 
       
        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=500  ";
        $list500=$m->query($sql,$item_id);
        $this->assign('list500',$list500);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=300  ";
        $list300=$m->query($sql,$item_id);
        $this->assign('list300',$list300);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=100  ";
        $list100=$m->query($sql,$item_id);
        $this->assign('list100',$list100);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=50  ";
        $list50=$m->query($sql,$item_id);
        $this->assign('list50',$list50);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=30  ";
        $list30=$m->query($sql,$item_id);
        $this->assign('list30',$list30);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=20  ";
        $list20=$m->query($sql,$item_id);
        $this->assign('list20',$list20);
        $this->display();
    }

    //一级稽核结果    模块4
    public function first_audit_result(){
        $m=M();
        $first_audit_result=I('first_audit_result');
        $item_id=I('item_id');
        $org_id=I('org_id');
        $data['first_audit_oper']=$_SESSION['payment']['OPER_NAME'];
        $data['first_audit_date']=date('Y-m-d H:i:s');
        $data['first_audit_result']=$first_audit_result;
        $data['payment_status']='一级稽核';

        if($first_audit_result=='同意'){
            $data['status']='1';
            $w['item_id']=$item_id;         
            $flag=$m->table('t_pcard_payment_record')->where($w)->save($data);
            if($flag){   
                /**            
                $dd['allocate_stat']='0';
                $where['item_id']=$item_id;
                $flag2=$m->table('t_pcard_print_list')->where($where)->save($dd);
                if($flag2){
                    $this->success('提交成功!','item_list');
                }else{
                    $this->error('提交失败!2');
                }
                **/
                $this->success('提交成功!','item_list');
            }else{
                $this->error('提交失败!');
            }
        }

        if($first_audit_result=='不同意'){
            $data['status']='99';
            $w['item_id']=$item_id;
            $flag=$m->table('t_pcard_payment_record')->where($w)->save($data);
            if($flag){
                $dd['item_id']='';
                $dd['status']='2';
                $dd['payment_date']='';
                $w['item_id']=$item_id;
                $flag=$m->table('t_pcard_busi_list')->where($w)->save($dd);
                if($flag){
                    $this->success('提交成功!','item_list');
                }else{
                    $this->error('提交失败2!');
                }
            }else{
                $this->error('提交失败1!');
            }
        }
    }



     //打印工单    模块5
    public function  item_receive(){
        $m=M();
        $org_name=I('org_name');
        $org_id=I('org_id');

        $county_code=$_SESSION['payment']['COUNTY_CODE'];
        if($county_code=='5780'){
             $sql="select * from mz_user.t_pcard_payment_record 
                where  payment_status='一级稽核' and  first_audit_result='同意' ";
        }else{
            $sql="select * from mz_user.t_pcard_payment_record where county_name='".$_SESSION['payment']['COUNTY_NAME']."' 
              and payment_status='一级稽核' and  first_audit_result='同意' ";
        }
        if(!empty($org_name)){
            $sql.="and org_name like '%".$org_name."%' ";
        }
        if(!empty($org_id)){
            $sql.="and org_id ='".$org_id."' ";
        }

        $lists=parent::listsSqlByls($sql,20); 
        $this->assign('lists',$lists);       
        $this->assign('org_name',$org_name);
        $this->assign('org_id',$org_id);
        $this->display();
       
    }


     //标记记录已打印   模块5
    public function getDayin(){
        $data['dayin']=$_SESSION['payment']['OPER_NAME'];
        $data['dayin_date']=date('Y-m-d H:i:s');
        $data['payment_status']='已打印';
        $item_id=I('item_id');
        $amount=I('amount');
        $org_id=I('org_id');
        $w['item_id']=$item_id;
        $m=M();
        $flag=$m->table('t_pcard_payment_record')->where($w)->save($data);
        if($flag){
            $list=$m->table('t_pcard_payment_record')->where($w)->find();
            $his_amount=$list['HIS_BALANCE'];
            $remain_amount=$list['REMAIN_AMOUNT'];
            $reamount=$his_amount+$remain_amount;

            $where['org_id']=$org_id; 
            $d['balance']=$reamount;
            $d['payment_date']=date('Y-m-d H:i:s');
            $flag=$m->table('t_pcard_payment_balance')->where($where)->save($d);
            if($flag){
                $user=$m->table('t_pcard_org_info')->where($where)->find();
                $county_name=$user['COUNTY_NAME'];
                $org_name=$user['ORG_NAME'];
                $org_phone=$user['BOSS_PHONE'];
                $org_boss=$user['ORG_BOSS'];
                $dates=date('Y-m-d H:i');
                parent::sms($org_phone,$county_name.'/'.$org_name.'/'.$org_boss.'的缴费卡'.$amount.
                           '金额已经于'.$dates.'被领取,请知晓');
                $j['status']='1';

            }else{
                $j['status']='0';
            }
        }else{
           $j['status']='0'; 
        }
        $this->ajaxReturn($j);
    }


   // 缴费卡已打印记录   模块5
    public function make_print(){
        $m=M();
        $org_name=I('org_name');
        $org_id=I('org_id');

        $county_code=$_SESSION['payment']['COUNTY_CODE'];
        if($county_code=='5780'){
             $sql="select item_id,county_name,org_name,org_id,js_person,payment_date,first_audit_oper,
                    first_audit_date from mz_user.t_pcard_payment_record 
                where  payment_status  in('已打印','已分配','二级稽核')  ";
        }else{
            $sql="select item_id,county_name,org_name,org_id,js_person,payment_date,first_audit_oper,
                    first_audit_date from mz_user.t_pcard_payment_record 
            where county_name='".$_SESSION['payment']['COUNTY_NAME']."' 
            and payment_status in('已打印','已分配','二级稽核') ";
        }
        if(!empty($org_name)){
            $sql.="and org_name like '%".$org_name."%' ";
        }
        if(!empty($org_id)){
            $sql.="and org_id ='".$org_id."' ";
        }

        $sql.="order by first_audit_date desc ";

        $lists=parent::listsSqlByls($sql,20); 
        $this->assign('lists',$lists);       
        $this->assign('org_name',$org_name);
        $this->assign('org_id',$org_id);
        $this->display();
    }

    //缴费卡领取登记表   模块5
    public function register_form(){
        $m=M();
        $item_id=I('item_id');
        $where['item_id']=$item_id;
        $list=$m->table('t_pcard_payment_record')->where($where)->find();
        $this->assign('list',$list);

        $org_id=$list['ORG_ID'];
        $wh['org_id']=$org_id;
        $org=$m->table('t_pcard_org_info')->where($wh)->find();
        $this->assign('org',$org);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' 
            and card_price=500  ";
        $list500=$m->query($sql,$item_id);
        $this->assign('list500',$list500);
        if($list500[0]['CARD_NO']>0){
            $sql="select min(start_card) min_card,max(start_card) max_card  from 
                mz_user.t_pcard_print_list where item_id='%s'  and card_price=500 ";
                $list500xs=$m->query($sql,$item_id);
                $card_xlh500=$list500xs[0]['MIN_CARD'].'~'.$list500xs[0]['MAX_CARD'];
            $this->assign('card_xlh500',$card_xlh500);
        }

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' 
            and card_price=300  ";
        $list300=$m->query($sql,$item_id);
        $this->assign('list300',$list300);
        if($list300[0]['CARD_NO']>0){
            $sql="select min(start_card) min_card,max(start_card) max_card  from 
                mz_user.t_pcard_print_list where item_id='%s'  and card_price=300 ";
                $list300xs=$m->query($sql,$item_id);
                $card_xlh300=$list300xs[0]['MIN_CARD'].'~'.$list300xs[0]['MAX_CARD'];
            $this->assign('card_xlh300',$card_xlh300);
        }

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' 
            and card_price=100  ";
        $list100=$m->query($sql,$item_id);
        $this->assign('list100',$list100);
        if($list100[0]['CARD_NO']>0){
            $sql="select min(start_card) min_card,max(start_card) max_card  from 
                mz_user.t_pcard_print_list where item_id='%s'  and card_price=100 ";
                $list100xs=$m->query($sql,$item_id);
                $card_xlh100=$list100xs[0]['MIN_CARD'].'~'.$list100xs[0]['MAX_CARD'];
            $this->assign('card_xlh100',$card_xlh100);
        }

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' 
            and card_price=50  ";
        $list50=$m->query($sql,$item_id);
        $this->assign('list50',$list50);
        if($list50[0]['CARD_NO']>0){
            $sql="select min(start_card) min_card,max(start_card) max_card  from 
                mz_user.t_pcard_print_list where item_id='%s'  and card_price=50 ";
                $list50xs=$m->query($sql,$item_id);
                $card_xlh50=$list50xs[0]['MIN_CARD'].'~'.$list50xs[0]['MAX_CARD'];
            $this->assign('card_xlh50',$card_xlh50);
        }

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' 
            and card_price=30  ";
        $list30=$m->query($sql,$item_id);
        $this->assign('list30',$list30);
        if($list30[0]['CARD_NO']>0){
            $sql="select min(start_card) min_card,max(start_card) max_card  from 
                mz_user.t_pcard_print_list where item_id='%s'  and card_price=30 ";
                $list30xs=$m->query($sql,$item_id);
                $card_xlh30=$list30xs[0]['MIN_CARD'].'~'.$list30xs[0]['MAX_CARD'];
            $this->assign('card_xlh30',$card_xlh30);
        }

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' 
            and card_price=20  ";
        $list20=$m->query($sql,$item_id);
        $this->assign('list20',$list20);
        if($list20[0]['CARD_NO']>0){
           $sql="select min(start_card) min_card,max(start_card) max_card  from 
                        mz_user.t_pcard_print_list where item_id='%s'  and card_price=20 ";
                $list20xs=$m->query($sql,$item_id);
                $card_xlh20=$list20xs[0]['MIN_CARD'].'~'.$list20xs[0]['MAX_CARD'];
            $this->assign('card_xlh20',$card_xlh20);
        }
        $this->display();
    }








    //二级稽核单个稽核列表   模块6
    public function item_list_second(){
        $m=M();
        $sql="select * from t_pcard_payment_record where payment_status='已分配' and 
                        county_name='".$_SESSION['payment']['COUNTY_NAME']."' order by item_id  asc";
        $lists=parent::listsSqlByls($sql,20);
        $this->assign('lists',$lists);
        $tab=I('tab');
        $this->assign('tab',$tab);
        $this->display();
    }


    //二级稽核页面    模块6
    public function second_audit(){
        $m=M();
        $item_id=I('item_id');        
        // $w['county_name']=$_SESSION['payment']['COUNTY_NAME'];
        $w['payment_status']='已分配'; 
        $w['item_id']=$item_id;       
        $list=$m->table('t_pcard_payment_record')->where($w)->find();
        $this->assign('list',$list);
        $org_id=$list['ORG_ID'];
        $wh['org_id']=$org_id;
        $listq=$m->table('t_pcard_org_info')->where($wh)->find();
        $this->assign('listq',$listq);

        $where['item_id']=$item_id;
        $where['status']='1';
        $listy=$m->table('t_pcard_org_verfy_info')->where($where)->find();
        $this->assign('listy',$listy);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=500  ";
        $list500=$m->query($sql,$item_id);
        $this->assign('list500',$list500);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=300  ";
        $list300=$m->query($sql,$item_id);
        $this->assign('list300',$list300);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=100  ";
        $list100=$m->query($sql,$item_id);
        $this->assign('list100',$list100);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=50  ";
        $list50=$m->query($sql,$item_id);
        $this->assign('list50',$list50);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=30  ";
        $list30=$m->query($sql,$item_id);
        $this->assign('list30',$list30);

        $sql="select nvl(count(*),0) card_no  from  mz_user.t_pcard_print_list  where item_id='%s' and card_price=20  ";
        $list20=$m->query($sql,$item_id);
        $this->assign('list20',$list20);
        $this->display();        
    }


    //二级稽核结果    模块6
    public function second_audit_result(){
        $m=M();
        $item_id=I('item_id');

        $da['item_id']=$item_id;        
        $da['county_code']=$_SESSION['payment']['COUNTY_CODE'];
        $da['county_name']=$_SESSION['payment']['COUNTY_NAME'];
        $da['audit_oper']=$_SESSION['payment']['OPER_NAME'];
        $da['audit_bill']=$_SESSION['payment']['OPER_LOGIN_CODE'];
        $da['audit_oa']=$_SESSION['payment']['OA'];
        $da['audit_date']=date('Y-m-d H:i:s');
        $da['record_date_start']=date('Y-m-d');
        $da['recodr_date_end']=date('Y-m-d');
        $da['amount']=I('amount');
        $da['price500']=I('price500');
        $da['price300']=I('price300');
        $da['price100']=I('price100');
        $da['price50']=I('price50');
        $da['price30']=I('price30');
        $da['price20']=I('price20');         
        $da['audit_result']=I('second_audit_result');
        $da['status']='1';

        $flag=$m->table('t_pcard_record_audit')->add($da); 
        if($flag){
            $data['second_audit_result']=I('second_audit_result');
            $data['second_audit_oper']=$_SESSION['payment']['OPER_NAME'];
            $data['second_audit_date']=date('Y-m-d H:i:s');
            $data['payment_status']='二级稽核';
            $where['item_id']=$item_id;
            $flag=$m->table('t_pcard_payment_record')->where($where)->save($data);
            if($flag>0){
                $this->success('提交成功!','item_list_second');
            }else{
                $map['item_id']=$item_id;
                $m->table('t_pcard_record_audit')->where($map)->delete();
                $this->error('提交失败!');
            }
        }else{
            $this->error('提交失败!');
        }
    }


    //记录详情
    public function record_details(){
        $item_id=I('item_id');
        $m=M();
        $w['item_id']=$item_id;        
        $list=$m->table('t_pcard_payment_record')->where($w)->find();
        $this->assign('list',$list);
        $username=I('username');
        if(!empty($username)){
            $this->assign('username',$username);
        }
        $this->display();
    }


    /**
    public function testexcel(){
        $username=I('username');
        if(!empty($username)){
            $this->success('获得成功!',U('Payment/record_details',array('username'=>$username)));
        }else{
            $this->error('获得失败!');
        }
    }
    **/

    //缴费卡结算记录    模块6
    public function item_record(){
        $county_name=I('county_name');
        $start_date=I('start_date');
        $end_date=I('end_date');
        if(empty($start_date)){
            $start_date=date('Ymd', strtotime("-4 day"));
        }

        if(empty($end_date)){
            $end_date=date('Ymd', strtotime("-1 day"));
        }
        
        $lists=self::item_record_sql($county_name,$start_date,$end_date,1);  
        $this->assign('lists',$lists);
        $this->assign('county_name',$county_name);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $tab=I('tab');
        if(empty($tab)){
            $tab=3;
        }
        $this->assign('tab',$tab);
        $this->display(); 
    }


    public function item_record_sql($county_code='',$start_date='',$end_date='',$info=''){
        $m=M();
        $sql="select sum(amount) amount,county_name,case_name,case_id,apply_id,payment_date
                from  t_pcard_busi_list where status='3'  group by county_name,case_name,case_id,
                apply_id,payment_date having 1=1 ";
        if($_SESSION['payment']['COUNTY_CODE']=='5780' ){
            if(!empty($county_name)){
                if($county_name!='全部'){
                    $sql.=" and county_name='".$county_name."' ";
                }
            }
        }else{
            $county_name=$_SESSION['payment']['COUNTY_NAME'];
            $sql.=" and county_name='".$county_name."' ";
        }

        if(!empty($start_date)){
            $sql.="and payment_date>='".$start_date."'";
        }
        /**
        else{
            $start_date=date('Ymd', strtotime("-4 day"));
            $sql.=" and payment_date>='".$start_date."'";

        }
        **/

        if(!empty($end_date)){
            $sql.="and payment_date<='".$end_date."'";
        }
        /**
        else{
            $end_date=date('Ymd', strtotime("-1 day"));
            $sql.=" and payment_date<='".$end_date."'";
        }
        **/
        $sql.="order by payment_date desc ";

        if($info=='1'){
            $lists=parent::listsSqlByls($sql,20);
        }else{
            $lists=$m->query($sql);
        }

        return $lists;    

    }

    //营销案赠卡汇总    模块6
    public function item_record_exp(){
        $county_name=I('county_name');
        $start_date=I('start_date');
        $end_date=I('end_date');
        $elist=self::item_record_sql($county_name,$start_date,$end_date,2); 
        $filename="营销案赠卡汇总表.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);

        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);



        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '结算时间')
            ->setCellValue('B1', '县市')
            ->setCellValue('C1', '营销案名称')
            ->setCellValue('D1', '营销案编号')
            ->setCellValue('E1', '规则编号')
            ->setCellValue('F1', '结算金额');

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['PAYMENT_DATE']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['COUNTY_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['CASE_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), "`".$elist[$k]['CASE_ID']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), "`".$elist[$k]['APPLY_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['AMOUNT']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('营销案赠卡汇总表');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //营销案赠卡清单   模块6
    public function item_pcard(){       
        $county_name=I('county_name');
        $start_date=I('start_date');
        $end_date=I('end_date'); 
        if(empty($start_date)){
            $start_date=date('Ymd', strtotime("-4 day"));
        }
        if(empty($end_date)){
            $end_date=date('Ymd', strtotime("-1 day"));
        }
        $lists=self::item_pcard_sql($county_name,$start_date,$end_date,1);        
        $this->assign('lists',$lists);
        $tab=I('tab');
        if(empty($tab)){
            $tab=4;
        }
        $this->assign('tab',$tab);
        $this->assign('county_name',$county_name);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }



    public function item_pcard_sql($county_code='',$start_date='',$end_date='',$info=''){
        $m=M();
        $sql="select a.payment_date,a.county_name,a.case_name,a.case_id,a.apply_id, b.start_card,
              b.card_price, b.card_type,a.bill_id,a.user_name,  
              decode(c.payment_status,'二级稽核','已稽核','未稽核')  payment_status
              from t_pcard_busi_list a,t_pcard_allocate_list b,t_pcard_payment_record c
              where a.status=3 and a.item_id=b.item_id  and a.bill_id=b.bill_id  and b.item_id=c.item_id";

        if($_SESSION['payment']['COUNTY_CODE']=='5780' ){
            if(!empty($county_name)){
                if($county_name!='全部'){
                    $sql.=" and a.county_name='".$county_name."' ";
                }
            }
        }else{
            $county_name=$_SESSION['payment']['COUNTY_NAME'];
            $sql.=" and a.county_name='".$county_name."' ";
        }


        if(!empty($start_date)){
            $sql.=" and a.payment_date>='".$start_date."'";
        }
        /**
        else{
            $start_date=date('Ymd', strtotime("-4 day"));
            $sql.=" and a.payment_date>='".$start_date."'";

        }
        **/

        if(!empty($end_date)){
            $sql.=" and a.payment_date<='".$end_date."'";
        }

        /**
        else{
            $end_date=date('Ymd', strtotime("-1 day"));
            $sql.=" and a.payment_date<='".$end_date."'";
        }
        **/

        $sql.=" order by payment_date desc";

        if($info=='1'){
            $lists=parent::listsSqlByls($sql,20);

        }else{
            $lists=$m->query($sql);
        }
        return $lists;
    }

    //营销案赠卡清单导出
    public function item_pcard_exp(){
        $start_date=I('start_date');
        $end_date=I('end_date');
        $county_name=I('county_name');
        $elist=self::item_pcard_sql($county_name,$start_date,$end_date,2); 

        if(count($elist)>=5000){
            $elist=array_slice($elist, 0, 5000);
        }

        $filename="营销案赠卡清单.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);

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

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '结算时间')
            ->setCellValue('B1', '县市')
            ->setCellValue('C1', '营销案名称')
            ->setCellValue('D1', '营销案编号')
            ->setCellValue('E1', '规则编号')
            ->setCellValue('F1', '充值卡类型')
            ->setCellValue('G1', '充值卡卡号')
            ->setCellValue('H1', '用户手机')
            ->setCellValue('I1', '用户姓名')
            ->setCellValue('J1', '是否稽核');

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['PAYMENT_DATE']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['COUNTY_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['CASE_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), "`".$elist[$k]['CASE_ID']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), "`".$elist[$k]['APPLY_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['CARD_TYPE']);  
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), "`".$elist[$k]['START_CARD']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['BILL_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['USER_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['PAYMENT_STATUS']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('营销案赠卡清单');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    //按月稽核    模块6
    public function item_list_month(){
        $m=M();
      
        $sql="select to_char(to_date(record_date_end,'yyyy-mm-dd')+1,'yyyy-mm-dd') as record_date_end from  
                mz_user.t_pcard_record_audit  where county_code='%s' and status='%s' and rownum<=1"; 
        $list=$m->query($sql,$_SESSION['payment']['COUNTY_CODE'],1);
        
        $record_date_end=$list[0]['RECORD_DATE_END'];
        if(empty($record_date_end)){
            $record_date_end='2017-04-01';
        }

        //$start_date=I('start_date');
        $start_date=$record_date_end;
        /**
        if(empty($start_date)){
            $start_date=date('Y-m-d',strtotime("-4 day"));
        }
        **/
        $end_date=I('end_date');
        if(empty($end_date)){
            $end_date=date('Y-m-d',strtotime("-1 day"));
        }
        $sql="select nvl(sum(payment_amount_sj),0) amount,nvl(sum(price500),0) price500,
              nvl(sum(price300),0) price300,nvl(sum(price100),0) price100,nvl(sum(price50),0) price50,
              nvl(sum(price30),0) price30, nvl(sum(price20),0) price20 from ( select a.*,b.* from (
              select * from mz_user.t_pcard_payment_record where county_name='%s' and payment_status='已分配' 
              and dayin_date>='%s' and dayin_date<='%s' )a ,(
              select sum(price500) price500,sum(price300) price300 ,sum(price100) price100,sum(price50) price50,
              sum(price30) price30,sum(price20) price20,item_id from  t_pcard_allocate_case  group by item_id
              ) b where a.item_id=b.item_id)";        
        $lists=$m->query($sql,$_SESSION['payment']['COUNTY_NAME'],$start_date,$end_date);
        $this->assign('lists',$lists);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $tab=I('tab');
        $this->assign('tab',$tab);
        $this->display();
    }


    //二级稽核详细页面   模块6
    public function second_audit_details(){
        $m=M();
        $start_date=I('start_date');
        $end_date=I('end_date');
        $sql="select count(card_price) card_no,card_type,card_price from (
              select * from  t_pcard_print_list  where item_id in (
              select item_id from t_pcard_payment_record where payment_status='已分配' and county_name='%s'
              and dayin_date>='%s' and dayin_date<='%s')
              ) group by card_type, card_price ";
        $lists=$m->query($sql,$_SESSION['payment']['COUNTY_NAME'],$start_date,$end_date); 
        $this->assign('lists',$lists);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $listtmp1=array(0=>array('CARD_NO'=>'0','CARD_TYPE'=>'营业厅POS券','CARD_PRICE'=>'500'),
                        1=>array('CARD_NO'=>'0','CARD_TYPE'=>'营业厅POS券','CARD_PRICE'=>'300'),
                        2=>array('CARD_NO'=>'0','CARD_TYPE'=>'营业厅POS券','CARD_PRICE'=>'100'),
                        3=>array('CARD_NO'=>'0','CARD_TYPE'=>'营业厅POS券','CARD_PRICE'=>'50'),
                        4=>array('CARD_NO'=>'0','CARD_TYPE'=>'营业厅POS券','CARD_PRICE'=>'30'),
                        5=>array('CARD_NO'=>'0','CARD_TYPE'=>'营业厅POS券','CARD_PRICE'=>'20'),

                        6=>array('CARD_NO'=>'0','CARD_TYPE'=>'全国卡赠送','CARD_PRICE'=>'500'),
                        7=>array('CARD_NO'=>'0','CARD_TYPE'=>'全国卡赠送','CARD_PRICE'=>'300'),
                        8=>array('CARD_NO'=>'0','CARD_TYPE'=>'全国卡赠送','CARD_PRICE'=>'100'),
                        9=>array('CARD_NO'=>'0','CARD_TYPE'=>'全国卡赠送','CARD_PRICE'=>'50'),
                        10=>array('CARD_NO'=>'0','CARD_TYPE'=>'全国卡赠送','CARD_PRICE'=>'30'),
                        11=>array('CARD_NO'=>'0','CARD_TYPE'=>'全国卡赠送','CARD_PRICE'=>'20')
        );

        for($i=0;$i<count($listtmp1);$i++){
            for($j=0;$j<count($lists);$j++){
                if($listtmp1[$i]['CARD_TYPE']==$lists[$j]['CARD_TYPE']&&
                        $listtmp1[$i]['CARD_PRICE']==$lists[$j]['CARD_PRICE']){
                    $listtmp1[$i]['CARD_NO']=$lists[$j]['CARD_NO'];
                }
            }

        } 

        $this->assign('listtmp1',$listtmp1);
        $this->display();
    }


    //二级稽核按月稽核结果   模块6
    public function second_month_result(){  
        //获得工单号 
        $m=M(); 
        $sql="select  T_PCARD_PAYMENT_RECORD_SEQ.Nextval  item_tem from dual";
        $item_tmp=$m->query($sql);
        $item_id='000000'.$item_tmp[0]['ITEM_TEM'];
        $item_id='LS'.substr($item_id, -10); 

        $result=I('result');
        $start_date=I('start_date');
        $end_date=I('end_date');

        $da['item_id']=$item_id;        
        $da['county_code']=$_SESSION['payment']['COUNTY_CODE'];
        $da['county_name']=$_SESSION['payment']['COUNTY_NAME'];
        $da['audit_oper']=$_SESSION['payment']['OPER_NAME'];
        $da['audit_bill']=$_SESSION['payment']['OPER_LOGIN_CODE'];
        $da['audit_oa']=$_SESSION['payment']['OA'];
        $da['audit_date']=date('Y-m-d H:i:s');
        $da['record_date_start']=$start_date;
        $da['recodr_date_end']=$end_date;

        $da['price500a']=I('price500a');
        $da['price300a']=I('price300a');
        $da['price100a']=I('price100a');
        $da['price50a']=I('price50a');
        $da['price30a']=I('price30a');
        $da['price20a']=I('price20a');

        $da['price500b']=I('price500b');
        $da['price300b']=I('price300b');
        $da['price100b']=I('price100b');
        $da['price50b']=I('price50b');
        $da['price30b']=I('price30b');
        $da['price20b']=I('price20b');

        $da['price500c']=I('price500c');
        $da['price300c']=I('price300c');
        $da['price100c']=I('price100c');
        $da['price50c']=I('price50c');
        $da['price30c']=I('price30c');
        $da['price20c']=I('price20c');


        $da['price500d']=I('price500d');
        $da['price300d']=I('price300d');
        $da['price100d']=I('price100d');
        $da['price50d']=I('price50d');
        $da['price30d']=I('price30d');
        $da['price20d']=I('price20d');
        $da['audit_result']=$result;
        $da['status']='1';

     



        /**
        $sql="select nvl(sum(payment_amount_sj),0) amount,nvl(sum(price500),0) price500,nvl(sum(price300),0) price300,
              nvl(sum(price100),0) price100,nvl(sum(price50),0) price50,nvl(sum(price30),0) price30,
              nvl(sum(price20),0) price20 from ( select a.*,b.* from (select * from mz_user.t_pcard_payment_record
              where county_name='%s' and payment_status='已分配'  and dayin_date>='%s' and dayin_date<='%s' )a ,(
              select sum(price500) price500,sum(price300) price300 ,sum(price100) price100,sum(price50) price50,
              sum(price30) price30,sum(price20) price20,item_id from  t_pcard_allocate_case  group by item_id
              ) b where a.item_id=b.item_id )";        
        $list=$m->query($sql,$_SESSION['payment']['COUNTY_NAME'],$start_date,$end_date);

        $amount=$list[0]['AMOUNT'];
        $price500=$list[0]['PRICE500'];
        $price300=$list[0]['PRICE300'];
        $price100=$list[0]['PRICE100'];
        $price50=$list[0]['PRICE50'];
        $price30=$list[0]['PRICE30'];
        $price20=$list[0]['PRICE20'];
        $da['amount']=$amount;
        **/
       
        
        $flag=$m->table('t_pcard_record_audit')->add($da); 
        if($flag){
            $data['second_audit_result']=$result;
            $data['second_audit_oper']=$_SESSION['payment']['OPER_NAME'];
            $data['second_audit_date']=date('Y-m-d H:i:s');
            $data['payment_status']='二级稽核';
            $where['payment_status']='已分配';
            $where['county_name']=$_SESSION['payment']['COUNTY_NAME'];
            $where['_string']='second_audit_result is null and dayin_date is not null';
            $where['dayin_date']=array(array('egt',$start_date),array('elt',$end_date));
            $flag=$m->table('t_pcard_payment_record')->where($where)->save($data);
            if($flag){
               $this->success('稽核成功!','item_list_month');
            }else{
                $map['item_id']=$item_id;
                $m->table('t_pcard_record_audit')->where($map)->delete();
                $this->error('稽核失败!');
            }
        }else{
            $this->error('稽核失败!');
           
        }
    }
  

    //市场部报表   模块7  营销方案
    public function payment_by_case(){
        $case_name=I('case_name'); 
        $county_code=I('county_code'); 
        $lists=self::payment_by_case_sql($county_code,$case_name,1);
        $this->assign('lists',$lists);
        $this->assign('county_code',$county_code);
        $this->assign('case_name',$case_name);
        $tabbs=I('tabbs');
        $this->assign('tabbs',$tabbs);
        $this->display('payment/payment_by_case');

    }

    //市场部报表   模块7  营销方案
    public function payment_by_case_sql($county_code='',$case_name='',$info){
        $m=M();
        $sql="select  decode(aaaa.county_code,'5781','丽水','5782','缙云','5783','青田','5784','云和',
                '5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁',
                '578B','南城') county_name,   aaaa.case_name,aaaa.办理量2+aaaa.办理量  办理量3,
                aaaa.暂不可结算+aaaa.可结算金额+aaaa.零头金额 发生金额,aaaa.可结算金额,
                aaaa.未审核金额,aaaa.已审核金额,aaaa.已领取金额,aaaa.暂不可结算,aaaa.可领取金额,
                aaaa.零头金额 from ( select aaa.county_code, aaa.case_name,nvl(bbb.暂不可结算,0) 暂不可结算,
                nvl(bbb.办理量2,0) 办理量2,nvl(aaa.办理量,0) 办理量,nvl(aaa.未审核金额,0) 未审核金额,
                nvl(aaa.已审核金额,0) 已审核金额,nvl(aaa.已领取金额,0) 已领取金额,
                nvl(aaa.可领取金额,0) 可领取金额,nvl(aaa.零头金额,0) 零头金额,nvl(aaa.可结算金额,0) 可结算金额
                from mz_user.v_pcard_busi_list aaa, mz_user.v_pcard_busi_down_all bbb 
                where aaa.county_code=bbb.county_code(+) and aaa.case_name=bbb.case_name(+) ) aaaa 
                where 1=1 ";

        if(!empty($case_name)){
            $sql.=" and aaaa.case_name like '%".$case_name."%' ";
        }
    
        if(!empty($county_code)){
            $sql.=" and aaaa.county_code like '".$county_code."' ";
        }
        if($info==1){
            $lists=parent::listsSqlByls($sql,20);
        }else{
            $lists = $m->query($sql);
        }
        
        return $lists;
    }



    //市场部报表   模块7  营销方案
    public function payment_by_case_exp(){
        $case_name=I('case_name'); 
        $county_code=I('county_code'); 
        $elist=self::payment_by_case_sql($county_code,$case_name,2);

        $filename="结算汇总表(分活动名称).xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '县市')
            ->setCellValue('B1', '活动名称')
            ->setCellValue('C1', '办理量')
            ->setCellValue('D1', '发生金额')
            ->setCellValue('E1', '可结算金额')
            ->setCellValue('F1', '其中:未审核金额')
            ->setCellValue('G1', '其中:已审核金额')
            ->setCellValue('H1', '其中:已领取金额')
            ->setCellValue('I1', '暂不能结算金额')
            ->setCellValue('J1', '可领取金额')
            ->setCellValue('K1', '零头金额');
            // intval($elist['可结算金额'])+intval($elist['暂不可结算'])+intval($elist['零头金额'])

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['CASE_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['办理量3']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['发生金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['可结算金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['未审核金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['已审核金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['已领取金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['暂不可结算']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['可领取金额']); 
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['零头金额']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('结算汇总表(分活动名称)');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //市场部报表   模块7  渠道类型
    public function payment_by_org(){
         $m=M();
        $org_type=I('org_type');  
        $county_code=I('county_code');
        $lists=self::payment_by_org_sql($county_code,$org_type,1);
        $this->assign('lists',$lists);
        $this->assign('org_type',$org_type);
        $this->assign('county_code',$county_code);
        $tabbs=I('tabbs');
        $this->assign('tabbs',$tabbs);
        $this->display('payment/payment_by_org');
    }

    //市场部报表   模块7  渠道类型
    public function payment_by_org_sql($county_code='',$org_type='',$info){
        $m=M();
        $sql="select decode(aaaa.county_code,'5781','丽水','5782','缙云','5783','青田',
              '5784','云和','5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁',
            '578B','南城') county_name, aaaa.org_type,aaaa.办理量2+aaaa.办理量  办理量3,
            aaaa.暂不可结算+aaaa.可结算金额+aaaa.零头金额 发生金额,aaaa.可结算金额,aaaa.未审核金额,
            aaaa.已审核金额,aaaa.已领取金额,aaaa.暂不可结算,aaaa.可领取金额,aaaa.零头金额 from ( 
            select aaa.county_code, aaa.org_type,nvl(bbb.暂不可结算,0) 暂不可结算,
            nvl(bbb.办理量2,0) 办理量2,nvl(aaa.办理量,0) 办理量,nvl(aaa.未审核金额,0) 未审核金额,
            nvl(aaa.已审核金额,0) 已审核金额,nvl(aaa.已领取金额,0) 已领取金额,
            nvl(aaa.可领取金额,0) 可领取金额, nvl(aaa.零头金额,0) 零头金额,
            nvl(aaa.可结算金额,0) 可结算金额 from mz_user.v_pcard_busi_list2 aaa, 
            mz_user.v_pcard_busi_down_all2 bbb 
            where aaa.county_code=bbb.county_code(+) and aaa.org_type=bbb.org_type(+) ) aaaa where 1=1  ";
        if(!empty($county_code)){
            $sql.=" and aaaa.county_code = '".$county_code."' ";
        }            
        if(!empty($org_type)){
            $sql.=" and aaaa.org_type like '%".$org_type."%' ";
        }
        if($info==1){
            $lists=parent::listsSqlByls($sql,20);
        }else{
            $lists = $m->query($sql);
        }
        return   $lists;
    }



     //市场部报表   模块7  渠道类型
    public function payment_by_org_exp(){
        $org_type=I('org_type'); 
        $county_code=I('county_code'); 
        $elist=self::payment_by_org_sql($county_code,$org_type,2);

       // dump($elist);
        //exit;

        $filename="结算汇总表(分渠道类型).xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);



        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '县市')
            ->setCellValue('B1', '渠道类型')
            ->setCellValue('C1', '办理量')
            ->setCellValue('D1', '发生金额')
            ->setCellValue('E1', '可结算金额')
            ->setCellValue('F1', '其中:未审核金额')
            ->setCellValue('G1', '其中:已审核金额')
            ->setCellValue('H1', '其中:已领取金额')
            ->setCellValue('I1', '暂不能结算金额')
            ->setCellValue('J1', '可领取金额')
            ->setCellValue('K1', '零头金额');
            // intval($elist['可结算金额'])+intval($elist['暂不可结算'])+intval($elist['零头金额'])

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['ORG_TYPE']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['办理量3']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['发生金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['可结算金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['未审核金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['已审核金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['已领取金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['暂不可结算']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['可领取金额']); 
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['零头金额']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('结算汇总表(分渠道类型)');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

     //市场部报表   模块7  渠道级清单
    public function payment_org_list(){
        $county_code=I('county_code');
        $case_name=I('case_name');
        $org_type=I('org_type');
        $org_name=I('org_name');
        $lists=self::payment_org_list_sql($county_code,$case_name,$org_type,$org_name,1);
        $this->assign('lists',$lists);
        $this->assign('county_code',$county_code);
        $this->assign('case_name',$case_name);
        $this->assign('org_type',$org_type);
        $this->assign('org_name',$org_name);
        $tabbs=I('tabbs');
        $this->assign('tabbs',$tabbs);
        $this->display('payment/payment_org_list');
    }

     //市场部报表   模块7  渠道级清单
    public function payment_org_list_sql($county_code='',$case_name='',$org_type='',$org_name='',$info){
        $m=M();
        $sql="select  decode(aaaa.county_code,'5781','丽水','5782','缙云','5783','青田',
                '5784','云和','5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789',
                '景宁','578B','南城') county_name, aaaa.org_id,aaaa.org_name,aaaa.org_type,
                aaaa.case_name,aaaa.prepay_name, aaaa.办理量2+aaaa.办理量  办理量3,
                aaaa.暂不可结算+aaaa.可结算金额+aaaa.零头金额 发生金额,aaaa.可结算金额,
                aaaa.未审核金额,aaaa.已审核金额,aaaa.已领取金额,aaaa.暂不可结算,aaaa.可领取金额,
                aaaa.零头金额 from ( select aaa.county_code,
                aaa.org_id,aaa.org_name,aaa.org_type,aaa.case_name,aaa.prepay_name,
                nvl(bbb.暂不可结算,0) 暂不可结算,nvl(bbb.办理量2,0) 办理量2,nvl(aaa.办理量,0) 办理量,
                nvl(aaa.未审核金额,0) 未审核金额,nvl(aaa.已审核金额,0) 已审核金额,
                nvl(aaa.已领取金额,0) 已领取金额, nvl(aaa.可领取金额,0) 可领取金额,
                nvl(aaa.零头金额,0) 零头金额, nvl(aaa.可结算金额,0) 可结算金额
                from mz_user.v_pcard_busi_list3 aaa,mz_user.v_pcard_busi_down_all3  bbb
                where aaa.county_code=bbb.county_code(+) and  aaa.org_id=bbb.org_id(+) 
                    and  aaa.org_name=bbb.org_name(+)    and  aaa.org_type=bbb.org_type(+)
                    and   aaa.case_name=bbb.case_name(+) and aaa.prepay_name=bbb.prepay_name(+)       
            ) aaaa  where 1=1";

        if(!empty($county_code)){
            $sql.=" and aaaa.county_code = '".$county_code."' ";
        }

        if(!empty($case_name)){
            $sql.=" and aaaa.case_name like '%".$case_name."%' ";
        }
            
        if(!empty($org_type)){
            $sql.=" and aaaa.org_type like '%".$org_type."%' ";
        }

        if(!empty($org_name)){
            $sql.=" and aaaa.org_name like '%".$org_name."%' ";
        }
    
        
        if($info==1){
            $lists=parent::listsSqlByls($sql,20);
        }else{
            $lists = $m->query($sql);
        }
        return   $lists;
    }


     //市场部报表   模块7  渠道级清单
    public function payment_org_list_exp(){
        $county_code=I('county_code');
        $case_name=I('case_name');
        $org_type=I('org_type');
        $org_name=I('org_name'); 
        $elist=self::payment_org_list_sql($county_code,$case_name,$org_type,$org_name,2);

       

        $filename="渠道级清单.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);

        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);



        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '县市')
            ->setCellValue('B1', '渠道编号')
            ->setCellValue('C1', '渠道名称')
            ->setCellValue('D1', '渠道类型')
            ->setCellValue('E1', '营销方案名称')
            ->setCellValue('F1', '预缴名称')
            ->setCellValue('G1', '办理量')
            ->setCellValue('H1', '发生金额')
            ->setCellValue('I1', '可结算金额')
            ->setCellValue('J1', '其中:未审核金额')
            ->setCellValue('K1', '其中:已审核金额')
            ->setCellValue('L1', '其中:已领取金额')
            ->setCellValue('M1', '暂不能结算金额')
            ->setCellValue('N1', '可领取金额')
            ->setCellValue('O1', '零头金额');
           

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['ORG_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['ORG_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['ORG_TYPE']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['CASE_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['PREPAY_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['办理量3']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['发生金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['可结算金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['未审核金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['已审核金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['已领取金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['暂不可结算']);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['可领取金额']); 
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['零头金额']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('渠道级清单');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

     //市场部报表   模块7  结算全量清单
    public function payment_amount_list(){
        $county_code=I('county_code');
        $case_name=I('case_name');
        $org_type=I('org_type');
        $org_name=I('org_name'); 
        $lists=self::payment_amount_list_sql($county_code,$case_name,$org_type,$org_name,1);
        $this->assign('lists',$lists);
        $this->assign('county_code',$county_code);
        $this->assign('case_name',$case_name);
        $this->assign('org_type',$org_type);
        $this->assign('org_name',$org_name);     
        $tabbs=I('tabbs');
        $this->assign('tabbs',$tabbs); 
        $this->display('payment/payment_amount_list');
        //dump($lists);
        
    }

    //市场部报表   模块7  结算全量清单
    public function  payment_amount_list_sql($county_code='',$case_name='',$org_type='',$org_name='',$info){
        $m=M();
        /**
        $sql="select decode(aaa.county_code,'5781','丽水','5782','缙云','5783','青田','5784',
            '云和','5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') 
            county_name, aaa.org_id,aaa.org_name,aaa.org_type,aaa.bill_id,aaa.case_name,aaa.prepay_name,
            nvl(aaa.已领金额,0)+nvl(aaa.可领金额,0)+nvl(aaa.未领金额,0)+nvl(aaa.暂不结算金额 ,0) 应赠金额,
            aaa.已领金额, aaa.未领金额, aaa.可领金额,aaa.done_date , aaa.effective_date, aaa.imei,
            decode(aaa.是否终止,'1','未终止','0','已终止') 是否终止, bbb.prod_spec_id,bbb.prod_name from ( 
            select aa.*,bb.暂不结算金额 from   mz_user.v_pcard_busi_list4 aa, 
            mz_user.v_pcard_busi_down_all4  bb  where aa.bill_id=bb.bill_id(+) and 1=1 and 2=2
            and 3=3 and 4=4
            ) aaa,mz_crm.ls_pcard_promotion_list_dtl  bbb where aaa.imei=bbb.imei(+) ";
        **/
        $sql="select decode(county_code,'5781','丽水','5782','缙云','5783','青田','5784',
            '云和','5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') 
            county_name, org_id,org_name,org_type,bill_id,case_name,prepay_name,应赠金额,
            已领金额, 未领金额, 可领金额,done_date , effective_date, imei,
            是否终止, prod_spec_id,prod_name  from  t_pcard_busi_list_table4 where 1=1 ";

        if(!empty($county_code)){
            $sql.=" and county_code = '".$county_code."' ";
            //$sql=str_replace('1=1', "aa.county_code ='".$county_code."'", $sql);
        }
        
        if(!empty($case_name)){
            $sql.=" and case_name like '%".$case_name."%' ";
            //$sql=str_replace('2=2', "aa.case_name like '%".$case_name."%' ", $sql);
        }
            
        if(!empty($org_type)){
            $sql.=" and org_type like '%".$org_type."%' ";
            //$sql=str_replace('3=3', "aa.org_type  like '%".$org_type."%' ", $sql);
        }

        if(!empty($org_name)){
            $sql.=" and org_name like '%".$org_name."%' ";
            //$sql=str_replace('4=4', "aa.org_name  like '%".$org_name."%' ", $sql);
        }
        if($info==1){
            $lists=parent::listsSqlByls($sql,20);
        }else{
            $lists = $m->query($sql);
        }
        return  $lists;
    }

    //市场部报表   模块7  结算全量清单
    public function payment_amount_list_exp(){
        $county_code=I('county_code');
        $case_name=I('case_name');
        $org_type=I('org_type');
        $org_name=I('org_name'); 
        $elist=self::payment_amount_list_sql($county_code,$case_name,$org_type,$org_name,2);

        if(count($elist)>=5000){
            $elist=array_slice($elist, 0, 5000);
        }

        $filename="缴费卡全量清单.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);

        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);



        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        
        // 合并
        //$objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '县市')
            ->setCellValue('B1', '渠道编号')
            ->setCellValue('C1', '渠道名称')
            ->setCellValue('D1', '渠道类型')
            ->setCellValue('E1', '受理时间')
            ->setCellValue('F1', '生效时间')
            ->setCellValue('G1', '手机号码')
            ->setCellValue('H1', '营销方案名称')
            ->setCellValue('I1', '预缴名称')
            ->setCellValue('J1', '应赠金额')
            ->setCellValue('K1', '已领金额')
            ->setCellValue('L1', '未领金额')
            ->setCellValue('M1', '可领金额')
            ->setCellValue('N1', '是否终止')
            ->setCellValue('O1', '扣回金额')
            ->setCellValue('P1', '串号')
            ->setCellValue('Q1', '机型')
            ->setCellValue('R1', '机型编号');
           

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['ORG_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['ORG_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['ORG_TYPE']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['DONE_DATE']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['EFFECTIVE_DATE']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['BILL_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['CASE_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['PREPAY_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['应赠金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['已领金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['未领金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['可领金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['是否终止']); 
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['扣回金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+2), $elist[$k]['IMEI']);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.($k+2), $elist[$k]['PROD_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('R'.($k+2), $elist[$k]['PROD_SPEC_ID']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('缴费卡全量清单');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
      
    }








    //市场部报表   模块7  营销资源掌控
    public function payment_statistics_list(){
        $county_code=I('county_code');
        $case_name=I('case_name');
        $prepay_name=I('prepay_name');
        $lists=self::payment_statistics_list_sql($county_code,$case_name,$prepay_name,1);
        $this->assign('lists',$lists);
        $this->assign('county_code',$county_code);
        $this->assign('case_name',$case_name);
        $this->assign('prepay_name',$prepay_name);
        $tabbs=I('tabbs');
        $this->assign('tabbs',$tabbs);
        $this->display('payment/payment_statistics_list');
    }

    //市场部报表   模块7  营销资源掌控
    public function  payment_statistics_list_sql($county_code='',$case_name='',$prepay_name='',$info){
        $m=M();
        $sql=" select  decode(county_code,'5781','丽水','5782','缙云','5783','青田','5784','云和',
                '5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') 
                county_name ,case_id,case_name,prepay_name,offer_id,受理量3,应赠金额,已打印,总金额,
                本省金额,外省金额 from  mz_user.t_pcard_busi_list_table5    where 1=1";
        if(!empty($county_code)){
            $sql.=" and county_code = '".$county_code."' ";
            //$sql=str_replace('1=1', "aaaa.county_code ='".$county_code."'", $sql);
        }
        
        if(!empty($case_name)){
            $sql.=" and case_name like '%".$case_name."%' ";
            //$sql=str_replace('2=2', "aaaa.case_name like '%".$case_name."%' ", $sql);
        }
            
        if(!empty($prepay_name)){
            $sql.=" and org_type like '%".$org_type."%' ";
            //$sql=str_replace('3=3', "aaaa.prepay_name  like '%".$prepay_name."%' ", $sql);
        }
        
        if($info==1){
            $lists=parent::listsSqlByls($sql,20);
        }else{
            $lists = $m->query($sql);
        }
        return  $lists;
    }



    //市场部报表   模块7  营销资源掌控
    public function payment_statistics_list_exp(){
        $county_code=I('county_code');
        $case_name=I('case_name');
        $prepay_name=I('prepay_name');
        $elist=self::payment_statistics_list_sql($county_code,$case_name,$prepay_name,2);
       

        $filename="营销资源掌控.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);

        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '日期')
            ->setCellValue('B1', '县市')
            ->setCellValue('C1', '营销案编号')
            ->setCellValue('D1', '营销案名称')
            ->setCellValue('E1', '预缴编号')
            ->setCellValue('F1', '预缴名称')
            ->setCellValue('G1', '受理量')
            ->setCellValue('H1', '应赠金额')
            ->setCellValue('I1', '已打印金额')
            ->setCellValue('J1', '已充值金额')
            ->setCellValue('K1', '已充值占比')
            ->setCellValue('L1', '充值在浙江金额')
            ->setCellValue('M1', '充值在浙江占比')
            ->setCellValue('N1', '充值在外省金额')
            ->setCellValue('O1', '充值到外省占比');
           

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['COUNTY_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['CASE_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['CASE_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), '`'.$elist[$k]['OFFER_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['PREPAY_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['受理量3']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['应赠金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['已打印']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['总金额']);  
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['总金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['本省金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['本省金额']);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['外省金额']); 
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['外省金额']);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('营销资源掌控');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;

    }






    //模块8 缴费卡打印详细清单
    public function payment_pcard_busi_list(){
        $start_date=I('start_date');
        $end_date=I('end_date');
        $county_name=I('county_name');
        if(empty($start_date)){
            $start_date=date('Ymd', strtotime("-4 day"));
        }
        if(empty($end_date)){
            $end_date=date('Ymd', strtotime("-1 day"));
        }
        $lists=self::payment_pcard_busi_list_sql($start_date,$end_date,$county_name,1);
        $this->assign('lists',$lists);        
        $this->assign('start_date',$start_date); 
        $this->assign('end_date',$end_date);
        $this->assign('county_name',$county_name);         
        $tabbs=I('tabbs');
        $this->assign('tabbs',$tabbs);
        $this->display();
    }





    //模块8 缴费卡打印详细清单详细清单
    public function payment_pcard_busi_list_sql($start_date='',$end_date='',$county_name='',$info=''){
        $m=M();
        $sql="select * from  mz_user.t_pcard_busi_list where status=3 ";
        if(!empty($start_date)){
            $sql.=" and payment_date>'".$start_date."' ";
        }
        if(!empty($end_date)){
            $sql.=" and payment_date<'".$end_date."' ";
        }

        if($_SESSION['payment']['COUNTY_CODE']=='5780' ){
            if(!empty($county_name)){
                if($county_name!='全部'){
                    $sql.=" and county_name='".$county_name."' ";
                }
            }
        }else{
            $county_name=$_SESSION['payment']['COUNTY_NAME'];
            $sql.=" and county_name='".$county_name."' ";
        }


        if($info==1){
            $lists=parent::listsSqlByls($sql,20); 
        }else{
            $lists=$m->query($sql);
        }
        return $lists;
    }




    //模块8 缴费卡打印详细清单详细清单
    public function payment_pcard_busi_exp(){
        $county_name=I('county_name');
        $start_date=I('start_date');
        $end_date=I('end_date');
        $elist =self::payment_pcard_busi_list_sql($start_date,$end_date,$county_name,2);

        if(count($elist)>=5000){
            $elist=array_slice($elist, 0, 5000);
        }


        $filename="缴费卡打印详细清单.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);

        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);

        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '县市')
            ->setCellValue('B1', '渠道编号')
            ->setCellValue('C1', '渠道名称')
            ->setCellValue('D1', '手机号')
            ->setCellValue('E1', '终端串号')
            ->setCellValue('F1', '活动名称')
            ->setCellValue('G1', '活动编号')
            ->setCellValue('H1', '预缴编号')
            ->setCellValue('I1', '预缴名称')
            ->setCellValue('J1', '工单号')
            ->setCellValue('K1', '打印时间')
            ->setCellValue('L1', '审核人')
            ->setCellValue('M1', '审核时间');

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['ORG_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['ORG_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), "`".$elist[$k]['BILL_ID']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), "`".$elist[$k]['IMEI']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['CASE_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['CASE_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), "`".$elist[$k]['OFFER_ID']); 
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['PREPAY_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['ITEM_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['PAYMENT_DATE']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['REVIEW_OPER']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['REVIEW_DATE']);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('缴费卡打印详细清单');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;

    }
   

    //模块8
    public function payment_pcard_print(){
        $start_date=I('start_date');
        $end_date=I('end_date');
        if(empty($start_date)){
            $start_date=date('Y-m-d', strtotime("-4 day"));
        }
        if(empty($end_date)){
            $end_date=date('Y-m-d', strtotime("-1 day"));
        }

        $lists=self::payment_pcard_print_sql($start_date,$end_date,1);
        $this->assign('lists',$lists);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
       
    }

    //模块8
    public function payment_pcard_print_sql($start_date='',$end_date='',$info=''){
        $m=M();
        $sql="select * from (
              select a.*,b.payment_oper from ( 
              select  substr(print_date,0, 10) print_date,print_people,
                org_id,org_name,card_price,start_card,item_id  from mz_user.t_pcard_print_list  
                where item_id in ( select item_id from  mz_user.t_pcard_payment_record  
                where  js_person='".$_SESSION['payment']['OPER_NAME']."' and 1=1 and 2=2  )  ) a,
                (select item_id,payment_oper  from mz_user.t_pcard_payment_record
                where  js_person='".$_SESSION['payment']['OPER_NAME']."' ) b  
                where a.item_id=b.item_id(+) order by a.item_id asc,a.card_price desc )";
        if(!empty($start_date)){
           $sql=str_replace('1=1', "payment_date > '".$start_date."' ", $sql);
        } 

        if(!empty($end_date)){
           $sql=str_replace('2=2', "payment_date < '".$end_date."' ", $sql);
        }

        if($info==1){
            $lists=parent::listsSqlByls($sql,20);
        }else{
            $lists=$m->query($sql);
        }
        return $lists;
    }




     //模块8 缴费卡打印详细清单详细清单
    public function payment_pcard_print_exp(){
        $county_name=I('county_name');
        $start_date=I('start_date');
        $end_date=I('end_date');
        $elist =self::payment_pcard_print_sql($start_date,$end_date,2);

        $filename="缴费卡打印清单.xls";
        $filename=iconv("utf-8", "gb2312",$filename);//文件名会乱码,需要进行转码
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //attachment和inline的方式就是保存时的弹窗不一样
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);

        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        /**
        // 合并
        $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        **/

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '打印时间')
            ->setCellValue('B1', '操作员')
            ->setCellValue('C1', '组织编号')
            ->setCellValue('D1', '组织名称')
            ->setCellValue('E1', '缴费卡面值')
            ->setCellValue('F1', '缴费卡编号')
            ->setCellValue('G1', '工单号')
            ->setCellValue('H1', '领取人');

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['PRINT_DATE']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['PRINT_PEOPLE']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['ORG_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['ORG_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['CARD_PRICE']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), "`".$elist[$k]['START_CARD']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['ITEM_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['PAYMENT_OPER']);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('缴费卡打印清单');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }



    public function paperExchange(){
        $this->display('Payment/paperExchange');
    }






    
}
?>