<?php


namespace Baob\Controller;

class ChannelController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
        if(!isset($_SESSION['channel'])||empty($_SESSION['channel'])){
              redirect('/ranking/Baob/Login/channel_index', 0);
        }
	}

    //PHPExcel导出  exportxls
    public function channel_jy_exp(){
        $xiaoshou=I('xiaoshou');
        $county_name=I('county_name');
        $qu_id=I('qu_id');
        $qu_name=I('qu_name');

        $sql="select 渠道编号,渠道名称, 二级类型名称,县市名称, 片区名称, 星级,运营商,
            销售经理名称,销售经理电话, 所属商圈,乡镇街道,社区行政村,法人代表,渠道联系人,
            联系电话,门头间数,  to_char(创建时间,'yyyy-mm-dd') 创建时间,渠道地址, 
            商圈类型,商圈等级,店面月租金*12  店面年租金,店面面积,
            to_char(修改时间,'yyyy-mm-dd') 禁用时间  from  mz_crm.ls_wyl_channel_info 
            where 渠道状态编号_1正常0删除2禁用 ='1' and 二级类型名称='签约渠道' ";

        if(!empty($xiaoshou)){
            $sql.=" and  销售经理名称='".$xiaoshou."'";
        }

        if(!empty($county_name)){
            $sql.=" and  县市名称='".$county_name."'";
        }

        if(!empty($qu_id)){
            $sql.=" and  渠道编号='".$qu_id."'";
        }
        if(!empty($qu_name)){
            $sql.=" and  渠道名称 like '%".$qu_name."%'";
        }
        $elist = M()->query($sql);

        $filename="渠道经营者信息.xls";
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
            ->setCellValue('A1', '渠道编号')
            ->setCellValue('B1', '渠道名称')
            ->setCellValue('C1', '二级类型名称')
            ->setCellValue('D1', '县市名称')
            ->setCellValue('E1', '片区名称')
            ->setCellValue('F1', '星级')
            ->setCellValue('G1', '运营商')
            ->setCellValue('H1', '销售经理名称')
            ->setCellValue('I1', '销售经理电话')
            ->setCellValue('J1', '所属商圈')
            ->setCellValue('K1', '乡镇街道')
            ->setCellValue('L1', '社区行政村')
            ->setCellValue('M1', '法人代表')
            ->setCellValue('N1', '法人联系电话')                  
            ->setCellValue('O1', '渠道联系人')
            ->setCellValue('P1', '联系电话')
            ->setCellValue('Q1', '门头间数')
            ->setCellValue('R1', '创建时间')
            ->setCellValue('S1', '渠道地址')
            ->setCellValue('T1', '商圈类型')
            ->setCellValue('U1', '商圈等级')
            ->setCellValue('V1', '店面月租金')
            ->setCellValue('W1', '店面面积')
            ->setCellValue('X1', '禁用时间');

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['渠道编号']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['渠道名称']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['二级类型名称']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['县市名称']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['片区名称']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['星级']);  
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['运营商']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['销售经理名称']);  
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['销售经理电话']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['所属商圈']);  
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['乡镇街道']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['社区行政村']);  
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['法人代表']);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['法人联系电话']);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['渠道联系人']);  
                $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+2), $elist[$k]['联系电话']);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.($k+2), $elist[$k]['门头间数']);  
                $objPHPExcel->getActiveSheet()->setCellValue('R'.($k+2), $elist[$k]['创建时间']);
                $objPHPExcel->getActiveSheet()->setCellValue('S'.($k+3), $elist[$k]['渠道地址']);  
                $objPHPExcel->getActiveSheet()->setCellValue('T'.($k+3), $elist[$k]['商圈类型']);
                $objPHPExcel->getActiveSheet()->setCellValue('U'.($k+3), $elist[$k]['商圈等级']);  
                $objPHPExcel->getActiveSheet()->setCellValue('V'.($k+3), $elist[$k]['店面年租金']);  
                $objPHPExcel->getActiveSheet()->setCellValue('W'.($k+3), $elist[$k]['店面面积']);
                $objPHPExcel->getActiveSheet()->setCellValue('X'.($k+3), $elist[$k]['禁用时间']); 
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('渠道经营者信息');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //渠道经营者信息移动
    public function channel_jingying(){
        $m=M();
        $xiaoshou=I('xiaoshou');
        $county_name=I('county_name');
        $qu_id=I('qu_id');
        $qu_name=I('qu_name');

        if($_SESSION['channel']['USER_QX_NO']=='1' ){
            $sql="select 渠道编号,渠道名称, 二级类型名称,县市名称, 片区名称, 星级,运营商,
            销售经理名称,销售经理电话, 所属商圈,乡镇街道,社区行政村,法人代表,法人联系电话,渠道联系人,
            联系电话,门头间数,  to_char(创建时间,'yyyy-mm-dd') 创建时间 , 渠道地址,商圈类型,商圈等级,
            店面月租金*12 店面年租金,店面面积,  to_char(修改时间,'yyyy-mm-dd')  禁用时间
            from  mz_crm.ls_wyl_channel_info 
            where 渠道状态编号_1正常0删除2禁用 ='1' and 二级类型名称='签约渠道' and 
            销售经理电话='".$_SESSION['channel']['BILL_ID']."'";
        }elseif($_SESSION['channel']['USER_QX_NO']=='2' ){
            $sql="select 渠道编号,渠道名称, 二级类型名称,县市名称, 片区名称, 星级,运营商,
            销售经理名称,销售经理电话, 所属商圈,乡镇街道,社区行政村,法人代表,法人联系电话,渠道联系人,
            联系电话,门头间数,  to_char(创建时间,'yyyy-mm-dd') 创建时间 , 渠道地址,商圈类型,商圈等级,
            店面月租金*12 店面年租金,店面面积,  to_char(修改时间,'yyyy-mm-dd')   禁用时间
            from  mz_crm.ls_wyl_channel_info 
            where 渠道状态编号_1正常0删除2禁用 ='1' and 二级类型名称='签约渠道' and 
            县市名称='".$_SESSION['channel']['COUNTY_NAME']."'";
        }elseif($_SESSION['channel']['USER_QX_NO']=='3'){
            $sql="select 渠道编号,渠道名称, 二级类型名称,县市名称, 片区名称, 星级,运营商,
            销售经理名称,销售经理电话, 所属商圈,乡镇街道,社区行政村,法人代表,法人联系电话,渠道联系人,
            联系电话,门头间数,  to_char(创建时间,'yyyy-mm-dd') 创建时间 , 渠道地址,商圈类型,商圈等级,
            店面月租金*12 店面年租金,店面面积,  to_char(修改时间,'yyyy-mm-dd')  禁用时间
            from  mz_crm.ls_wyl_channel_info 
            where 渠道状态编号_1正常0删除2禁用 ='1' and 二级类型名称='签约渠道'";
        }

        if(!empty($xiaoshou)){
            $sql.=" and  销售经理名称='".$xiaoshou."'";
        }

        if(!empty($county_name)){
            $sql.=" and  县市名称='".$county_name."'";
        }

        if(!empty($qu_id)){
            $sql.=" and  渠道编号='".$qu_id."'";
        }
        if(!empty($qu_name)){
            $sql.=" and  渠道名称 like '%".$qu_name."%'";
        }

        $lists=parent::listsSqlByls($sql,25); 
        $this->assign('lists',$lists);

        $this->assign('xiaoshou',$xiaoshou);
        $this->assign('county_name',$county_name);
        $this->assign('qu_id',$qu_id);
        $this->assign('qu_name',$qu_name);

        $sql1="select distinct 销售经理名称 from    mz_crm.ls_wyl_channel_info 
                where 渠道状态编号_1正常0删除2禁用 ='1' and 二级类型名称='签约渠道' ";
        $nodes=$m->query($sql1);     
        $this->assign('nodes',$nodes);
        $sql2="select distinct 渠道名称 from    mz_crm.ls_wyl_channel_info 
                where 渠道状态编号_1正常0删除2禁用 ='1' and 二级类型名称='签约渠道' ";
        $nodes2=$m->query($sql2);     
        $this->assign('nodes2',$nodes2);
        $this->display();
        
    }


    public function channel_modify(){
        if(IS_GET){
            $qd_id=I('qd_id');
            $m=M();
            $sql="select 渠道编号,渠道名称,  二级类型名称,县市名称, 片区名称, 星级,运营商,销售经理名称,
                        销售经理电话, 所属商圈,乡镇街道,社区行政村,法人代表,渠道联系人,联系电话,门头间数,
                        to_char(创建时间,'yyyy-mm-dd') 创建时间 , 渠道地址,商圈类型,商圈等级,
                        店面月租金*12 店面年租金,店面面积,  to_char(修改时间,'yyyy-mm-dd')   禁用时间
                        from  mz_crm.ls_wyl_channel_info  where 渠道编号='%s'";
            $list=$m->query($sql,$qd_id);
            // $list=$m->table('mz_crm.ls_wyl_channel_info')->where("渠道编号='".$qd_id."'")->find();
            $this->assign('list',$list);

            //录入信息 
            $sql="select org_id, subsidy_model,subsidy_assess,subsidy_amount,lease_term,subsidy_condit,
                    channel_operation, task_require,task_end_time ,task_oper,task_bill,task_start_time,
                    channel_date,sh_result, nvl(status,'0') status,task_result,nvl(task_no,'0') task_no 
                    from  t_channel_yd where  org_id='%s'";

            $content=$m->query($sql,$qd_id);
            $this->assign('content',$content);
            if(empty($content)){
                $this->assign('num','0');
            }else{
                $this->assign('num','1');
            }
            if($_SESSION['channel']['USER_QX_NO']=='1'){     
                $this->display('channel_modify');
            }

            if($_SESSION['channel']['USER_QX_NO']=='2' or  $_SESSION['channel']['USER_QX_NO']=='3' ){
                $this->display('channel_modify_sh');
            }
        }

        if(IS_POST){
            $qd_id=I('qd_id');
            $mp_rw=I('mp_rw');
            $rw_bill=I('rw_bill');
            $rw_xdz=I('rw_xdz');
            $m=M();
            $list=$m->table('t_channel_yd')->where("org_id='".$qd_id."'")->select();
            //$data['渠道编号']=$qd_id;
            $data['subsidy_model']=I('bt_ms');
            $data['subsidy_assess']=I('bt_kh');
            $data['subsidy_amount']=I('bt_je');
            $data['lease_term']=I('zl_qx');
            $data['subsidy_condit']=I('bt_tj');
            $data['channel_operation']=I('jy_qk');
            $data['channel_date']=date('Y-m-d H:i:s');
            if(empty($list)){  
                $data['status']='1';      
                $flag=$m->table('t_channel_yd')->add($data);
                if($flag){
                    $this->success('数据保存成功!','channel_jingying');
                }else {
                    $this->error('数据保存失败!');
                }
            }else{
                if($mp_rw=='0'){
                    $data['status']='1';
                }
                if($mp_rw=='1'){
                    $data['task_result']=I('mp_result');
                    $data['status']='3';
                    $data['sh_result']='0';
                    $flag=$m->table('t_channel_yd')->where("org_id='".$qd_id."'")->save($data);
                    if($flag){
                        if($mp_rw=='1'){
                            parent::sms('18862243446',$rw_xdz.'您好,您下达的渠道编号为'
                            .$qd_id.'的摸排任务,销售经理已经提交了摸排结果,请审核!');
                        }
                        $this->success('数据保存成功!','channel_jingying');
                    }else {
                        $this->error('数据保存失败!');
                    }
                }
            }
        }
    }


    //摸排审核
    public function  channel_modify_sh(){
        if(IS_POST){
            $qd_id=I('qd_id');
            $jl_name=I('jl_name');
            $m=M();
            $list=$m->table('t_channel_yd')->where("org_id='".$qd_id."'")->select();
            if(empty($list)){      
                $data['org_id']=$qd_id;
                $data['task_require']=I('rw_yq');
                $data['task_end_time']=I('wc_sj');
                $data['task_oper']=$_SESSION['user_auth']['OPER_NAME'];
                $data['task_bill']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
                $data['task_start_time']=date('Y-m-d H:i:s');
                $data['status']='2';
                $data['task_no']='1';
                $flag=$m->table('t_channel_yd')->add($data);
                if($flag){
                    parent::sms('18862243446','销售经理'.$jl_name.'您好,您有渠道编号为'.$qd_id.
                    '的摸排任务需要在'.$data['task_end_time'].'前完成!');
                    $this->success('数据保存成功!','channel_jingying');
                }else{
                    $this->error('数据保存失败!');  
                }
            }else{
                $pre_status=I('pre_status');
                if($pre_status=='1'){
                    $data['task_require']=I('rw_yq');
                    $data['task_end_time']=I('wc_sj');
                    $data['task_oper']=$_SESSION['user_auth']['OPER_NAME'];
                    $data['task_bill']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
                    $data['task_start_time']=date('Y-m-d H:i:s');
                    $data['status']='2';
                    $data['task_no']='1';
                    $flag=$m->table('t_channel_yd')->where("org_id='".$qd_id."'")->save($data);
                    if($flag){
                    parent::sms('18862243446','销售经理'.$jl_name.'您好,您有渠道编号为'
                    .$qd_id.'的摸排任务需要在'.$data['task_end_time'].'前完成!');
                    $this->success('数据保存成功!','channel_jingying');
                    }else {
                    $this->error('数据保存失败!');
                    }
                }
                if($pre_status=='3'){
                    $sh_result=I('sh_result');
                    if($sh_result=='1'){
                        $data['task_require']="";
                        $data['task_end_time']="";
                        $data['task_oper']="";
                        $data['task_bill']="";
                        $data['task_start_time']="";
                        $data['sh_result']=$sh_result;
                        $data['task_no']='0';
                        $data['status']='1';
                    }
                    if($sh_result=='0'){
                        $data['sh_result']=$sh_result;
                    }
                    $flag=$m->table('t_channel_yd')->where("org_id='".$qd_id."'")->save($data);
                    if($flag){
                        if($sh_result=='0'){
                            parent::sms('18862243446','销售经理'.$jl_name.'您好,您提交的渠道编号为'
                            .$qd_id.'的摸排任务未通过审核,请重新提交摸排结果');
                        }
                        if($sh_result=='1'){
                            parent::sms('18862243446','销售经理'.$jl_name.'您好,您提交的渠道编号为'
                            .$qd_id.'的摸排任务已通过审核!'); 
                        }
                        $this->success('数据保存成功!','channel_jingying');
                    }else {
                        $this->error('数据报损失败!');
                    }
                } 
            }
        }
    }


    //渠道经营者信息对手
    public function channel_jingying_ds(){
        $m=M();
        $xiaoshou=I('xiaoshou');
        $county_name=I('county_name');
        $yunys=I('yunys');
        $qu_id=I('qu_id');
        $qu_name=I('qu_name');

        if($_SESSION['channel']['USER_QX_NO']=='1' ){
            $sql="select 渠道编号,渠道中文名称,三级类型,县市名称,销售经理名称,销售经理电话,片区名称,运营商,渠道地址,
            商圈名称,乡镇街道名称, 法人代表,法人联系电话,联系人,联系电话,经营面积,门头间数,  
            to_char(开业时间,'yyyy-mm-dd') 创建时间, to_char(修改时间,'yyyy-mm-dd') 禁用时间
            from mz_crm.ls_compchannel_info where 销售经理电话='".$_SESSION['channel']['BILL_ID']."'";
        }elseif($_SESSION['channel']['USER_QX_NO']=='2' ){
            $sql="select 渠道编号,渠道中文名称,三级类型,县市名称,销售经理名称,销售经理电话,片区名称,运营商,渠道地址,
            商圈名称,乡镇街道名称, 法人代表,法人联系电话,联系人,联系电话,经营面积,门头间数,  
            to_char(开业时间,'yyyy-mm-dd') 创建时间, to_char(修改时间,'yyyy-mm-dd') 禁用时间
            from mz_crm.ls_compchannel_info where 县市名称='".$_SESSION['channel']['COUNTY_NAME']."'";
        }elseif($_SESSION['channel']['USER_QX_NO']=='3'){
            $sql="select 渠道编号,渠道中文名称,三级类型,县市名称,销售经理名称,销售经理电话,片区名称,运营商,渠道地址,
            商圈名称,乡镇街道名称, 法人代表,法人联系电话,联系人,联系电话,经营面积,门头间数,  
            to_char(开业时间,'yyyy-mm-dd') 创建时间, to_char(修改时间,'yyyy-mm-dd') 禁用时间
            from mz_crm.ls_compchannel_info where 1=1";
        }

        if(!empty($qu_id)){
            $sql.=" and  渠道编号='".$qu_id."'";
        }
        if(!empty($qu_name)){
            $sql.=" and  渠道中文名称 like '%".$qu_name."%'";
        }

        if(!empty($xiaoshou)){
            $sql.=" and  销售经理名称='".$xiaoshou."'";
        }

        if(!empty($county_name)){
            $sql.=" and  县市名称='".$county_name."'";
        }
        if(!empty($yunys)){
            $sql.=" and  运营商='".$yunys."'";
        }

        $lists=parent::listsSqlByls($sql,25); 
        $this->assign('lists',$lists);

        $this->assign('xiaoshou',$xiaoshou);
        $this->assign('county_name',$county_name);
        $this->assign('yunys',$yunys);
        $this->assign('qu_id',$qu_id);
        $this->assign('qu_name',$qu_name);

        $sql1="select distinct 销售经理名称 from  mz_crm.ls_compchannel_info ";
        $nodes=$m->query($sql1);     
        $this->assign('nodes',$nodes);
        $sql2="select distinct 渠道中文名称 from  mz_crm.ls_compchannel_info  ";
        $nodes2=$m->query($sql2);     
        $this->assign('nodes2',$nodes2);
        $this->display();
    }

    //录入信息
    public function  channel_modify_ds(){
        if(IS_GET){
            $qd_id=I('qd_id');
            $m=M();
            $sql="select 渠道编号,渠道中文名称,三级类型,县市名称,销售经理名称,销售经理电话,片区名称,运营商,渠道地址,
                商圈名称,乡镇街道名称, 法人代表,法人联系电话,联系人,联系电话,经营面积,门头间数,  
                to_char(开业时间,'yyyy-mm-dd') 创建时间, to_char(修改时间,'yyyy-mm-dd') 禁用时间
                from mz_crm.ls_compchannel_info where 渠道编号='%s'";
            $list=$m->query($sql,$qd_id);
            $this->assign('list',$list);

            //录入信息 
            $sql="select org_id, lease_model,subsidy_model,lease_term,subsidy_condit,channel_operation,
                task_require,task_end_time ,task_oper,task_bill,channel_date,sh_result,
                nvl(status,'0') status,task_result,nvl(task_no,'0')  task_no from  t_channel_yd_ds where  org_id='%s'";
            $content=$m->query($sql,$qd_id);
            $this->assign('content',$content);
            if(empty($content)){
                $this->assign('num','0');
            }else{
                $this->assign('num','1');
            }
            if($_SESSION['channel']['USER_QX_NO']=='1'){     
                $this->display('channel_modify_ds');
            }
            if($_SESSION['channel']['USER_QX_NO']=='2' or  $_SESSION['channel']['USER_QX_NO']=='3' ){
                $this->display('channel_modify_ds_sh');
            }
        }

        if(IS_POST){
            $qd_id=I('qd_id');
            $mp_rw=I('mp_rw');
            $rw_bill=I('rw_bill');
            $rw_xdz=I('rw_xdz');

            $m=M();
            $list=$m->table('t_channel_yd_ds')->where("org_id='".$qd_id."'")->select();
            $data['org_id']=$qd_id;
            $data['lease_model']=I('zl_ms');
            $data['subsidy_model']=I('bt_ms');
            $data['lease_term']=I('zl_qx');
            $data['subsidy_condit']=I('bt_tj');
            $data['channel_operation']=I('jy_qk');
            $data['channel_date']=date('Y-m-d H:i:s');
            if(empty($list)){  
                $data['status']='1';      
                $flag=$m->table('t_channel_yd_ds')->add($data);
                if($flag){
                    $this->success('数据保存成功!','channel_jingying_ds');
                }else {
                    $this->error('数据保存失败!');
                }
            }else{
                if($mp_rw=='0'){
                    $data['status']='1';
                }
                if($mp_rw=='1'){
                    $data['task_result']=I('mp_result');
                    $data['status']='3';
                    $data['sh_result']='0';
                }
                $flag=$m->table('t_channel_yd_ds')->where("org_id='".$qd_id."'")->save($data);
                if($flag){
                    if($mp_rw=='1'){
                        parent::sms('18862243446',$rw_xdz.'您好,您下达的渠道编号为'
                        .$qd_id.'的摸排任务,销售经理已经提交了摸排结果,请审核!');
                    }
                    $this->success('数据保存成功!','channel_jingying_ds');
                }else {
                    $this->error('数据保存失败!');
                }
            }
        }
    }


    //摸排审核
    public function  channel_modify_ds_sh(){
        if(IS_POST){
            $qd_id=I('qd_id');
            $jl_name=I('jl_name');
            $m=M();
            $list=$m->table('t_channel_yd_ds')->where("org_id='".$qd_id."'")->select();
            if(empty($list)){      
                $data['org_id']=$qd_id;
                $data['task_require']=I('rw_yq');
                $data['task_end_time']=I('wc_sj');
                $data['task_oper']=$_SESSION['user_auth']['OPER_NAME'];
                $data['task_bill']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
                $data['task_start_time']=date('Y-m-d H:i:s');
                $data['status']='2';
                $data['task_no']='1';
                $flag=$m->table('t_channel_yd_ds')->add($data);
                if($flag){
                    parent::sms('18862243446','销售经理'.$jl_name.'您好,您有渠道编号为'.$qd_id.'的摸排任务需要在'
                    .$data['task_end_time'].'前完成!');

                    $this->success('数据保存成功!','channel_jingying_ds');
                }else {
                    $this->error('数据报损失败!');
                }
            }else{
                $pre_status=I('pre_status');
                if($pre_status=='1'){
                    $data['task_require']=I('rw_yq');
                    $data['task_end_time']=I('wc_sj');
                    $data['task_oper']=$_SESSION['user_auth']['OPER_NAME'];
                    $data['task_bill']=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
                    $data['task_start_time']=date('Y-m-d H:i:s');
                    $data['status']='2';
                    $data['task_no']='1';
                    $flag=$m->table('t_channel_yd_ds')->where("渠道编号='".$qd_id."'")->save($data);
                    if($flag){
                    parent::sms('18862243446','销售经理'.$jl_name.'您好,您有渠道编号为'.$qd_id.'的摸排任务需要在'
                    .$data['task_end_time'].'前完成!');

                    $this->success('数据保存成功!','channel_jingying_ds');
                    }else {
                    $this->error('数据报损失败!');
                    }
                }

                if($pre_status=='3'){
                    $sh_result=I('sh_result');
                    if($sh_result=='1'){
                        $data['task_require']="";
                        $data['task_end_time']="";
                        $data['task_oper']="";
                        $data['task_bill']="";
                        $data['task_start_time']="";
                        $data['sh_result']=$sh_result;
                        $data['task_no']='0';
                        $data['status']='1';
                    }
                    if($sh_result=='0'){
                        $data['sh_result']=$sh_result;
                    }
                    $flag=$m->table('t_channel_yd_ds')->where("渠道编号='".$qd_id."'")->save($data);
                    if($flag){
                        if($sh_result=='0'){
                            parent::sms('18862243446','销售经理'.$jl_name.'您好,您提交的渠道编号为'
                            .$qd_id.'的摸排任务未通过审核,请重新提交摸排结果');
                        }
                        if($sh_result=='1'){
                            parent::sms('18862243446','销售经理'.$jl_name.'您好,您提交的渠道编号为'
                            .$qd_id.'的摸排任务已通过审核!'); 
                        }
                        $this->success('数据保存成功!','channel_jingying_ds');
                    }else {
                        $this->error('数据报损失败!');
                    }
                } 
            }
        }
    }




    //PHPExcel导出  导出竞争对手信息
    // public function daochu($county_name,$create_date,$ed_date){
    public function channel_jy_exp_ds(){
        $xiaoshou=I('xiaoshou');
        $county_name=I('county_name');
        $qu_id=I('qu_id');
        $qu_name=I('qu_name');
        $yunys=I('yunys');

        $sql="select 渠道编号,渠道中文名称,三级类型,县市名称,销售经理名称,销售经理电话,片区名称,运营商,
                渠道地址,商圈名称,乡镇街道名称, 法人代表,法人联系电话,联系人,联系电话,经营面积,门头间数,  
                to_char(开业时间,'yyyy-mm-dd') 创建时间, to_char(修改时间,'yyyy-mm-dd') 禁用时间
                from mz_crm.ls_compchannel_info ";

        if(!empty($xiaoshou)){
            $sql.=" and  销售经理名称='".$xiaoshou."'";
        }

        if(!empty($county_name)){
            $sql.=" and  县市名称='".$county_name."'";
        }

        if(!empty($qu_id)){
            $sql.=" and  渠道编号='".$qu_id."'";
        }
        if(!empty($qu_name)){
            $sql.=" and  渠道名称 like '%".$qu_name."%'";
        }
        if(!empty($yunys)){
            $sql.=" and  运营商='".$yunys."'";
        }

        $elist = M()->query($sql);


        $filename="渠道经营竞争者信息.xls";
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);


        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '渠道编号')
        ->setCellValue('B1', '渠道中文名称')
        ->setCellValue('C1', '三级类型')
        ->setCellValue('D1', '县市名称')
        ->setCellValue('E1', '销售经理名称')
        ->setCellValue('F1', '销售经理电话')
        ->setCellValue('G1', '片区名称')
        ->setCellValue('H1', '运营商')
        ->setCellValue('I1', '渠道地址')
        ->setCellValue('J1', '商圈名称')
        ->setCellValue('K1', '乡镇街道名称')
        ->setCellValue('L1', '法人代表')
        ->setCellValue('M1', '法人联系电话')
        ->setCellValue('N1', '联系人')                  
        ->setCellValue('O1', '联系电话')
        ->setCellValue('P1', '经营面积')
        ->setCellValue('Q1', '门头间数')
        ->setCellValue('R1', '创建时间')
        ->setCellValue('S1', '禁用时间');

        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['渠道编号']);  
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['渠道中文名称']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['三级类型']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['县市名称']);  
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['销售经理名称']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['销售经理电话']);  
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['片区名称']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['运营商']);  
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['渠道地址']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['商圈名称']);  
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['乡镇街道名称']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['法人代表']);  
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['法人联系电话']);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['联系人']);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['联系电话']);  
                $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+2), $elist[$k]['经营面积']);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.($k+2), $elist[$k]['门头间数']);  
                $objPHPExcel->getActiveSheet()->setCellValue('R'.($k+2), $elist[$k]['创建时间']);
                $objPHPExcel->getActiveSheet()->setCellValue('S'.($k+3), $elist[$k]['禁用时间']);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('渠道经营竞争者信息');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    






}