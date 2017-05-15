<?php
namespace Operation\Controller;
use Operation\Controller\BaseController;

class IndexController extends BaseController {
	protected function _initialize(){
        parent::_initialize();
	}
    
    public function index(){
        $isLogin = parent::isLogin();
        if($isLogin){
            $day = I('day');
            $time = '';
            $weekday = 0;
            if($day==''){
                $day = date('Ymd');
                $time = time();
                $weekday = date('w',$time) == 0?7:date('w',$time);
                $startDate = date('Ymd', $time-($weekday-1)*24*3600);
                $endDate = date('Ymd', $time+(7-$weekday)*24*3600);
            }else{
                $time = strtotime($day);
                $day = date('Ymd',$time);
                $weekday = date('w',$time) == 0?7:date('w',$time);
                $startDate = date('Ymd', $time-($weekday-1)*24*3600);
                $endDate = date('Ymd', $time+(7-$weekday)*24*3600);
                $showDay = substr($day, 0, 4).'-'.substr($day, 4, 2).'-'.substr($day, 6, 2);
                $this->assign('day', $showDay);
            }
            
            $month = substr($day,0,6);
            $lastMonth = date('Ym',strtotime("$day - 1 month"));
            /*
            $firstDay = date('Ym01',$time);//当月第一天
            $lastDay = date('Ymt',strtotime("$day"));//当月最后一天
            $firstDay1 = date('Ym01',strtotime("$day - 1 month"));//上月第一天
            $lastDay1 = date('Ymt',strtotime("$day - 1 month"));//上月最后一天
			*/
            //日县市工单量
            $dayCounty = $this->dayCounty($day);
            $this->assign('dayCounty', $dayCounty);
            //日业务分类工单量：
            $dayBusiType = $this->dayBusiType($day);            
            $this->assign('dayBusiType', $dayBusiType);
            //日投诉来源工单量：
            $dayComeFrom = $this->dayComeFrom($day);           
            $this->assign('dayComeFrom', $dayComeFrom);
             //周县市工单量
            $weekCounty = $this->weekCounty($startDate, $endDate);           
            $this->assign('weekCounty', $weekCounty);
            //周业务分类工单量：
            $weekBusiType = $this->weekBusiType($startDate, $endDate);            
            $this->assign('weekBusiType', $weekBusiType);
            //周投诉来源工单量：
            $weekComeFrom = $this->weekComeFrom($startDate, $endDate);
            $this->assign('weekComeFrom', $weekComeFrom);

            //月县市工单量
            $monthCounty = $this->monthCounty($month,$lastMonth);
            $this->assign('monthCounty', $monthCounty);
            //月业务分类工单量：
            $monthBusiType = $this->monthBusiType($month,$lastMonth);
            $this->assign('monthBusiType', $monthBusiType);
           
            //月投诉来源工单量：
            $monthComeFrom = $this->monthComeFrom($month,$lastMonth);
            $this->assign('monthComeFrom', $monthComeFrom);
            
            $day1 = I("day1");
            $day2 = I("day2");
            $queryTj = $this->getTj($day1, $day2);
            $day1 = $queryTj[0];
            $day2 = $queryTj[1];
            $tj = $queryTj[2];

            //阶段县市工单量：
            $dayBtwCounty = $this->dayBtwCounty($day1, $day2, $tj);
            $this->assign('dayBtwCounty', $dayBtwCounty);
             
            //阶段业务类型工单量：
            $dayBtwBusiType = $this->dayBtwBusiType($day1, $day2, $tj);
            $this->assign('dayBtwBusiType', $dayBtwBusiType);
            //dump(M()->getLastSql());

            $dayBtwComeFrom = $this->dayBtwComeFrom($day1, $day2, $tj);
            $this->assign('dayBtwComeFrom', $dayBtwComeFrom);

            $this->display("reporta");
           
        }
    }

    public function getTj($day1='',$day2=''){
        if($day1!='' && $day2==''){
            $day1 = str_replace('-','',$day1);
            $queryTj[2] = " and substr(id,0,8)>='".$day1."'";
            $this->assign('day1', $day1);
        }else if($day1=='' && $day2!=''){
            $day2 = str_replace('-','',$day2);
            $queryTj[2] = " and substr(id,0,8)<='".$day2."'";
            $this->assign('day2', $day2);
        }else{
            if($day1=='' && $day2==''){
                $day1 = date('Ymd');
                $day2 = $day1;
            }else{
                $day1 = str_replace('-','',$day1);
                $day2 = str_replace('-','',$day2);
                $this->assign('day1', $day1);
                $this->assign('day2', $day2);
            }
            $queryTj[2] = " and substr(id,0,8)>='".$day1."' and substr(id,0,8)<='".$day2."'";
        }

        $queryTj[0] = $day1;
        $queryTj[1] = $day2;
        //$queryTj[2] = " and substr(id,0,8)>='".$day1."' and substr(id,0,8)<='".$day2."'";
        return $queryTj;
    }

    //日县市工单量
    public function dayCounty($day=''){
        $sql = "select '".$day."' create_date,a.county_name,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per from (select county_code,mz_crm.get_county_name(county_code) county_name from mz_crm.rank_complain_deal group by county_code) a,(select county_code,substr(id,0,8) create_date,count(id) num from  mz_crm.rank_complain_deal where substr(id,0,8)='".$day."' group by county_code,substr(id,0,8)) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,8)='".$day."') c where a.county_code=b.county_code(+)";
        $dayCounty = M()->query($sql);
        return $dayCounty;
    }

    public function dayCountyExcel($day=''){
        $roles = $this->getRoles();
        if($roles[0]){
            $dclist = $this->dayCounty($day);
            $filename="日县市工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);

            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '日期')
                ->setCellValue('B1', '县市')
                ->setCellValue('C1', '日工单总量')
                ->setCellValue('D1', '日县市工单量')
                ->setCellValue('E1', '日县市工单占比');
            //将数据写入列
            if(count($dclist) > 0){
                foreach($dclist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $dclist[$k]['CREATE_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $dclist[$k]['COUNTY_NAME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $dclist[$k]['TOTAL_NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $dclist[$k]['NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $dclist[$k]['PER']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //日业务分类工单量
    public function dayBusiType($day=''){
        $sql = "select '".$day."' create_date,a.busi_type,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per from (select busi_type busi_id,decode(busi_type,0,'业务查询',1,'业务办理',2,'计费类',3,'其他') busi_type from mz_crm.rank_complain_deal group by busi_type) a,(select busi_type,count(id) num from mz_crm.rank_complain_deal where substr(id,0,8)='".$day."' group by busi_type,substr(id,0,8)) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,8)='".$day."') c where a.busi_id=b.busi_type(+) order by a.busi_id";
        $dayBusiType = M()->query($sql);
        return $dayBusiType;
    }

    public function dayBusiExcel($day=''){
        $roles = $this->getRoles();
        if($roles[0]){
            $dblist = $this->dayBusiType($day);
            $filename="日业务分类工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);

            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '日期')
                ->setCellValue('B1', '业务分类')
                ->setCellValue('C1', '日工单总量')
                ->setCellValue('D1', '日县市工单量')
                ->setCellValue('E1', '日县市工单占比');
            //将数据写入列
            if(count($dblist) > 0){
                foreach($dblist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $dblist[$k]['CREATE_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $dblist[$k]['BUSI_TYPE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $dblist[$k]['TOTAL_NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $dblist[$k]['NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $dblist[$k]['PER']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //日投诉来源工单量
    public function dayComeFrom($day=''){
        $sql = "select '".$day."' create_date,a.come_from,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per from (select come_from from_id,decode(come_from,0,'营业飞信群',1,'渠道QQ群',2,'远端投诉处理QQ群') come_from from mz_crm.rank_complain_deal group by come_from) a,(select come_from,count(id) num from mz_crm.rank_complain_deal where substr(id,0,8)='".$day."' group by come_from,substr(id,0,8)) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,8)='".$day."') c where a.from_id=b.come_from(+) order by a.from_id";
        $dayComeFrom = M()->query($sql);
        return $dayComeFrom;
    }

    public function dayComeExcel($day=''){
        $roles = $this->getRoles();
        if($roles[0]){
            $dclist = $this->dayComeFrom($day);
            $filename="日投诉来源工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);

            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '日期')
                ->setCellValue('B1', '投诉来源')
                ->setCellValue('C1', '日工单总量')
                ->setCellValue('D1', '日县市工单量')
                ->setCellValue('E1', '日县市工单占比');
            //将数据写入列
            if(count($dclist) > 0){
                foreach($dclist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $dclist[$k]['CREATE_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $dclist[$k]['COME_FROM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $dclist[$k]['TOTAL_NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $dclist[$k]['NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $dclist[$k]['PER']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //周县市工单量
    public function weekCounty($startDate, $endDate){
        $sql = "select a.county_name,'".$startDate."' start_date,'".$endDate."' end_date,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per from (select county_code,mz_crm.get_county_name(county_code) county_name from mz_crm.rank_complain_deal group by county_code) a,(select county_code,count(id) num from  mz_crm.rank_complain_deal where substr(id,0,8)>='".$startDate."' and substr(id,0,8)<='".$endDate."' group by county_code) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,8)>='".$startDate."' and substr(id,0,8)<='".$endDate."') c where a.county_code=b.county_code(+)";
    	$weekCounty = M()->query($sql);
        return $weekCounty;
    }

    public function weekCountyExcel($startDate, $endDate){
        $roles = $this->getRoles();
        if($roles[0]){
            $wclist = $this->weekCounty($startDate,$endDate);
            $filename="周县市工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);

            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '周一')
                ->setCellValue('B1', '周日')
                ->setCellValue('C1', '县市')
                ->setCellValue('D1', '周工单总量')
                ->setCellValue('E1', '周县市工单量')
                ->setCellValue('F1', '周县市工单占比');
            //将数据写入列
            if(count($wclist) > 0){
                foreach($wclist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $wclist[$k]['START_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $wclist[$k]['END_DATE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $wclist[$k]['COUNTY_NAME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $wclist[$k]['TOTAL_NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $wclist[$k]['NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $wclist[$k]['PER']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //周业务分类工单量
    public function weekBusiType($startDate, $endDate){
        $sql = "select '".$startDate."' start_date,'".$endDate."' end_date,a.busi_type,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per from (select busi_type busi_id,decode(busi_type,0,'业务查询',1,'业务办理',2,'计费类',3,'其他') busi_type from mz_crm.rank_complain_deal group by busi_type) a,(select busi_type,count(id) num from mz_crm.rank_complain_deal where substr(id,0,8)>='".$startDate."' and substr(id,0,8)<='".$endDate."' group by busi_type) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,8)>='".$startDate."' and substr(id,0,8)<='".$endDate."') c where a.busi_id=b.busi_type(+) order by a.busi_id";
        $weekBusiType = M()->query($sql);
        return $weekBusiType;
    }

    public function weekBusiExcel($startDate, $endDate){
        $roles = $this->getRoles();
        if($roles[0]){
            $wblist = $this->weekBusiType($startDate,$endDate);
            $filename="周业务类型工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);

            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '周一')
                ->setCellValue('B1', '周日')
                ->setCellValue('C1', '业务类型')
                ->setCellValue('D1', '周工单总量')
                ->setCellValue('E1', '周业务类型工单量')
                ->setCellValue('F1', '周业务类型工单占比');
            //将数据写入列
            if(count($wblist) > 0){
                foreach($wblist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $wblist[$k]['START_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $wblist[$k]['END_DATE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $wblist[$k]['BUSI_TYPE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $wblist[$k]['TOTAL_NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $wblist[$k]['NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $wblist[$k]['PER']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //周投诉来源工单量
    public function weekComeFrom($startDate, $endDate){
        $sql = "select '".$startDate."' start_date,'".$endDate."' end_date,a.come_from,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per from (select come_from from_id,decode(come_from,0,'营业飞信群',1,'渠道QQ群',2,'远端投诉处理QQ群') come_from from mz_crm.rank_complain_deal group by come_from) a,(select come_from,count(id) num from mz_crm.rank_complain_deal where substr(id,0,8)>='".$startDate."' and substr(id,0,8)<='".$endDate."' group by come_from) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,8)>='".$startDate."' and substr(id,0,8)<='".$endDate."') c where a.from_id=b.come_from(+) order by a.from_id";
        $weekComeFrom = M()->query($sql);
        return $weekComeFrom;
    }

    public function weekComeExcel($startDate, $endDate){
        $roles = $this->getRoles();
        if($roles[0]){
            $wcflist = $this->weekComeFrom($startDate,$endDate);
            $filename="周投诉来源工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);

            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '周一')
                ->setCellValue('B1', '周日')
                ->setCellValue('C1', '投诉来源')
                ->setCellValue('D1', '周工单总量')
                ->setCellValue('E1', '周投诉来源工单量')
                ->setCellValue('F1', '周投诉来源工单占比');
            //将数据写入列
            if(count($wcflist) > 0){
                foreach($wcflist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $wcflist[$k]['START_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $wcflist[$k]['END_DATE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $wcflist[$k]['COME_FROM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $wcflist[$k]['TOTAL_NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $wcflist[$k]['NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $wcflist[$k]['PER']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //月县市工单量
    public function monthCounty($month,$lastMonth){
    	$sql = "select a.county_name,'".$month."' curr_month,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per,e.total_num1,nvl(d.num1,0) num1,rtrim(to_char(round(nvl(d.num1,0)/e.total_num1*100,2), 'FM990D99'),to_char(0, 'D'))||'%' per1,nvl(b.num,0)-nvl(d.num1,0) gap from (select county_code,mz_crm.get_county_name(county_code) county_name from mz_crm.rank_complain_deal group by county_code) a,(select county_code,count(id) num from mz_crm.rank_complain_deal where substr(id,0,6)='".$month."' group by county_code) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,6)='".$month."') c,(select county_code,count(id) num1 from mz_crm.rank_complain_deal where substr(id,0,6)='".$lastMonth."' group by county_code) d,(select count(id) total_num1 from mz_crm.rank_complain_deal where substr(id,0,6)='".$lastMonth."') e where a.county_code=b.county_code(+) and a.county_code=d.county_code(+) order by a.county_code";
    	$monthCounty = M()->query($sql);
        return $monthCounty;
    }

    public function monthCountyExcel(){
    	$roles = $this->getRoles();
        if($roles[0]){
        	$month =  I('month');
        	$lastMonth = date('Ym',strtotime($month.'01')-24*3600);
            $wcflist = $this->monthCounty($month,$lastMonth);
            $filename="月县市工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '年月')
                ->setCellValue('B1', '县市')
                ->setCellValue('C1', '月工单总量')
                ->setCellValue('D1', '月县市工单')
                ->setCellValue('E1', '月县市占比')
                ->setCellValue('F1', '上月工单总量')
                ->setCellValue('G1', '上月县市')
                ->setCellValue('H1', '上月县市占比')
                ->setCellValue('I1', '月差');
            //将数据写入列
            if(count($wcflist) > 0){
                foreach($wcflist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $wcflist[$k]['CURR_MONTH']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $wcflist[$k]['COUNTY_NAME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $wcflist[$k]['TOTAL_NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $wcflist[$k]['NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $wcflist[$k]['PER']);

                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $wcflist[$k]['TOTAL_NUM1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $wcflist[$k]['NUM1']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $wcflist[$k]['PER1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $wcflist[$k]['GAP']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //月业务分类工单量
    public function monthBusiType($month,$lastMonth){
    	$sql = "select '".$month."' curr_month,a.busi_type,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per,e.total_num1,nvl(d.num1,0) num1,rtrim(to_char(round(nvl(d.num1,0)/e.total_num1*100,2), 'FM990D99'),to_char(0, 'D'))||'%' per1,nvl(b.num,0)-nvl(d.num1,0) gap from (select busi_type busi_id,decode(busi_type,0,'业务查询',1,'业务办理',2,'计费类',3,'其他') busi_type from mz_crm.rank_complain_deal group by busi_type) a,(select busi_type,count(id) num from mz_crm.rank_complain_deal where substr(id,0,6)='".$month."' group by busi_type) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,6)='".$month."') c,(select busi_type,count(id) num1 from mz_crm.rank_complain_deal where substr(id,0,6)='".$lastMonth."' group by busi_type) d,(select count(id) total_num1 from mz_crm.rank_complain_deal where substr(id,0,6)='".$lastMonth."') e where a.busi_id=b.busi_type(+) and a.busi_id=d.busi_type(+) order by a.busi_id";
    	$monthBusiType = M()->query($sql);
        return $monthBusiType;
    }

    public function monthBusiExcel(){
    	$roles = $this->getRoles();
        if($roles[0]){
        	$month =  I('month');
        	$lastMonth = date('Ym',strtotime($month.'01')-24*3600);
            $wcflist = $this->monthBusiType($month,$lastMonth);
            $filename="月业务分类工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '年月')
                ->setCellValue('B1', '业务分类')
                ->setCellValue('C1', '月工单总量')
                ->setCellValue('D1', '月业务分类工单')
                ->setCellValue('E1', '月业务分类占比')
                ->setCellValue('F1', '上月工单总量')
                ->setCellValue('G1', '上月业务分类')
                ->setCellValue('H1', '上月业务分类占比')
                ->setCellValue('I1', '月差');
            //将数据写入列
            if(count($wcflist) > 0){
                foreach($wcflist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $wcflist[$k]['CURR_MONTH']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $wcflist[$k]['BUSI_TYPE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $wcflist[$k]['TOTAL_NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $wcflist[$k]['NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $wcflist[$k]['PER']);

                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $wcflist[$k]['TOTAL_NUM1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $wcflist[$k]['NUM1']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $wcflist[$k]['PER1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $wcflist[$k]['GAP']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //月投诉来源工单量
    public function monthComeFrom($month,$lastMonth){
    	$sql = "select '".$month."' curr_month,a.come_from,c.total_num,nvl(b.num,0) num,rtrim(to_char(round(decode(c.total_num,0,0,nvl(b.num,0)/c.total_num*100),2), 'FM990D99'),to_char(0, 'D'))||'%' per,e.total_num1,nvl(d.num1,0) num1,rtrim(to_char(round(nvl(d.num1,0)/e.total_num1*100,2), 'FM990D99'),to_char(0, 'D'))||'%' per1,nvl(b.num,0)-nvl(d.num1,0) gap from (select come_from from_id,decode(come_from,0,'营业飞信群',1,'渠道QQ群',2,'远端投诉处理QQ群') come_from from mz_crm.rank_complain_deal group by come_from) a,(select come_from,count(id) num from mz_crm.rank_complain_deal where substr(id,0,6)='".$month."' group by come_from) b,(select count(id) total_num from mz_crm.rank_complain_deal where substr(id,0,6)='".$month."') c,(select come_from,count(id) num1 from mz_crm.rank_complain_deal where substr(id,0,6)='".$lastMonth."' group by come_from) d,(select count(id) total_num1 from mz_crm.rank_complain_deal where substr(id,0,6)='".$lastMonth."') e where a.from_id=b.come_from(+) and a.from_id=d.come_from(+) order by a.from_id";
    	$monthComeFrom = M()->query($sql);
        return $monthComeFrom;
    }

    public function monthComeExcel(){
    	$roles = $this->getRoles();
        if($roles[0]){
        	$month =  I('month');
        	$lastMonth = date('Ym',strtotime($month.'01')-24*3600);
            $wcflist = $this->monthComeFrom($month,$lastMonth);
            $filename="月投诉来源工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '年月')
                ->setCellValue('B1', '投诉来源')
                ->setCellValue('C1', '月工单总量')
                ->setCellValue('D1', '月投诉来源工单')
                ->setCellValue('E1', '月投诉来源占比')
                ->setCellValue('F1', '上月工单总量')
                ->setCellValue('G1', '上月投诉来源')
                ->setCellValue('H1', '上月投诉来源占比')
                ->setCellValue('I1', '月差');
            //将数据写入列
            if(count($wcflist) > 0){
                foreach($wcflist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $wcflist[$k]['CURR_MONTH']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $wcflist[$k]['COME_FROM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $wcflist[$k]['TOTAL_NUM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $wcflist[$k]['NUM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $wcflist[$k]['PER']);

                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $wcflist[$k]['TOTAL_NUM1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $wcflist[$k]['NUM1']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $wcflist[$k]['PER1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $wcflist[$k]['GAP']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //统计日期范围内县市投诉处理记录
    public function dayBtwCounty($day1='', $day2='', $tj=''){
    	$sql = "select '".$day1."' start_date, '".$day2."' end_date,a.county_name,nvl(b.num,0) num from (select county_code,mz_crm.get_county_name(county_code) county_name from mz_crm.rank_complain_deal group by county_code) a,(select county_code,count(county_code) num from mz_crm.rank_complain_deal where 1=1 ".$tj." group by county_code) b where a.county_code=b.county_code(+) order by a.county_code";
    	$dayBtwCounty = M()->query($sql);
    	return $dayBtwCounty;
    }

	public function btwCountyExcel($day1='', $day2=''){
		$roles = $this->getRoles();
        if($roles[0]){
        	$tj = '';
        	if($day1 !=''){
        		$tj = $tj." and substr(id,0,8)>='".$day1."'";
        	}

        	if($day2 !=''){
        		$tj = $tj." and substr(id,0,8)<='".$day2."'";
        	}

            $btwclist = $this->dayBtwCounty($day1, $day2, $tj);
            $filename="阶段县市工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '开始日期')
                ->setCellValue('B1', '结束日期')
                ->setCellValue('C1', '县市')
                ->setCellValue('D1', '工单量');
            //将数据写入列
            if(count($btwclist) > 0){
                foreach($btwclist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $btwclist[$k]['START_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $btwclist[$k]['END_DATE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $btwclist[$k]['COUNTY_NAME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $btwclist[$k]['NUM']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //统计日期范围内业务类型投诉处理记录
    public function dayBtwBusiType($day1='', $day2='', $tj=''){
    	$sql = "select '".$day1."' start_date,'".$day2."' end_date,a.busi_type,nvl(b.num,0) num from(select busi_type busi_id,decode(busi_type,0,'业务查询',1,'业务办理',2,'计费类',3,'其他') busi_type from mz_crm.rank_complain_deal group by busi_type) a,(select busi_type,count(busi_type) num from mz_crm.rank_complain_deal where 1=1".$tj." group by busi_type) b where a.busi_id=b.busi_type(+) order by a.busi_id";
    	$dayBtwBusiType = M()->query($sql);
    	return $dayBtwBusiType;
    }

    public function btwBusiExcel($day1='', $day2=''){
		$roles = $this->getRoles();
        if($roles[0]){
        	$tj = " and substr(id,0,8)>='".$day1."' and substr(id,0,8)<='".$day2."'";
            $btwblist = $this->dayBtwBusiType($day1, $day2, $tj);
            $filename="阶段业务分类工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '开始日期')
                ->setCellValue('B1', '结束日期')
                ->setCellValue('C1', '业务分类')
                ->setCellValue('D1', '工单量');
            //将数据写入列
            if(count($btwblist) > 0){
                foreach($btwblist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $btwblist[$k]['START_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $btwblist[$k]['END_DATE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $btwblist[$k]['BUSI_TYPE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $btwblist[$k]['NUM']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //统计日期范围内投诉来源投诉处理记录
    public function dayBtwComeFrom($day1='', $day2='', $tj=''){
    	$sql = "select '".$day1."' start_date,'".$day2."' end_date,a.come_from,nvl(b.num,0) num from(select come_from come_id,decode(come_from,0,'营业飞信群',1,'渠道QQ群',2,'远端投诉处理QQ群') come_from from mz_crm.rank_complain_deal group by come_from) a,(select come_from,count(come_from) num from mz_crm.rank_complain_deal where 1=1".$tj." group by come_from order by come_from) b where a.come_id=b.come_from(+) order by a.come_id";
    	$dayBtwComeFrom = M()->query($sql);
    	return $dayBtwComeFrom;
    }

    public function btwComeExcel($day1='', $day2=''){
		$roles = $this->getRoles();
        if($roles[0]){
        	$tj = " and substr(id,0,8)>='".$day1."' and substr(id,0,8)<='".$day2."'";
            $btwflist = $this->dayBtwComeFrom($day1, $day2, $tj);
            $filename="阶段投诉来源工单统计表.xls";
            $filename=iconv("utf-8", "gb2312",$filename);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            ob_end_clean();
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '开始日期')
                ->setCellValue('B1', '结束日期')
                ->setCellValue('C1', '投诉来源')
                ->setCellValue('D1', '工单量');
            //将数据写入列
            if(count($btwflist) > 0){
                foreach($btwflist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $btwflist[$k]['START_DATE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $btwflist[$k]['END_DATE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $btwflist[$k]['COME_FROM']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $btwflist[$k]['NUM']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //投诉处理记录表（首页）
    public function cdeali(){
        $roles = $this->getRoles();
        if($roles[0]){
            $query['come_from'] = I('come_from');
            $query['county_code'] = I('county_code');
            $query['from_site'] = I('from_site');
            $query['busi_type'] = I('busi_type');
            $query['has_post'] = I('has_post');
            $query['result_type'] = I('result_type');
            $query['content'] = I('content');
            $query['deal_way'] = I('deal_way');
            $query['recorder'] = I('recorder');
            $query['start_time'] = I('start_time');
            $query['end_time'] = I('end_time');
            $query['keyword'] = I('keyword');
            $this->assign('query', $query);
            $sql = $this->cdealQueryStr();
            $cdeallist = parent::listsSqlByls($sql,$pageSize=50);
           
            $this->assign('cdeallist', $cdeallist);
            $this->display();
            


        }
    }

    //投诉处理记录表（查询语句）
    public function cdealQueryStr(){
        $sql = "select a.id,substr(a.id,1,8) lr_time,decode(a.come_from,'0','营业飞信群','1','渠道QQ群','2','远端投诉处理QQ群') come_from,"
            ."mz_crm.get_county_name(a.county_code) county_name,a.from_site,"
            ."decode(a.busi_type,'0','业务查询','1','业务办理','2','计费类','3','其他') busi_type,"
            ."a.result_type,decode(a.has_post,'0','否','1','是') has_post,b.oper_name,a.content,a.deal_way,remark "
            ."from mz_crm.rank_complain_deal a,mz_user.t_sys_oper b where a.recorder=b.oper_id(+)";

        $id = I('id');//序号
        if($id != ''){
            $sql.=" and a.id ={$id}";
        }

        $come_from = I('come_from');//投诉来源
        if($come_from != ''){
            $sql.=" and a.come_from ={$come_from}";
        }

        $county_code = I('county_code');
        if($county_code != ''){
            $sql.=" and a.county_code ='{$county_code}'";
        }

        $from_site = I('from_site');
        if($from_site != ''){
            $sql.=" and a.from_site like '%{$from_site}%'";
        }

        $busi_type = I('busi_type');//业务类型
        if($busi_type != ''){
            $sql.=" and a.busi_type ={$busi_type}";
        }

        $has_post = I('has_post');//是否派单
        if($has_post != ''){
            $sql.=" and a.has_post ={$has_post}";
        }

        $result_type = I('result_type');
        if($result_type != ''){
            $sql.=" and a.result_type like '%{$result_type}%'";
        }

        $content = I('content');
        if($content != ''){
            $sql.=" and a.content like '%{$content}%'";
        }

        $deal_way = I('deal_way');
        if($deal_way != ''){
            $sql.=" and a.deal_way like '%{$deal_way}%'";
        }

        $start_time = I('start_time');
        if($start_time != ''){
            $sql.=" and substr(a.id,1,8) >= replace('{$start_time}','-','')";
        }

        $end_time = I('end_time');
        if($end_time != ''){
            $sql.=" and substr(a.id,1,8) <= replace('{$end_time}','-','')";
        }

        $recorder = I('recorder');
        if($recorder != ''){
            $sql.=" and b.oper_name like '%".$recorder."%'";
        }

        $keyword = I('keyword');
        if($keyword != ''){
        	//直接sql拼接会有问题，需要考虑php的字符串拼接方式
        	$tmpsql.="select * from (".$sql.") ".
        		"where come_from like '%".$keyword."%' ".
        		"or county_name like '%".$keyword."%' ".
        		"or from_site like '%".$keyword."%' ".
				"or busi_type like '%".$keyword."%' ".
				"or result_type like '%".$keyword."%' ".
				"or has_post like '%".$keyword."%' ".
				"or oper_name like '%".$keyword."%' ".
				"or content like '%".$keyword."%' ".
				"or deal_way like '%".$keyword."%' ".
				"or remark like '%".$keyword."%'";
			$sql = $tmpsql;
        }
        $sql.=" order by id desc";
        return $sql;
    }



    //投诉处理记录表（新增）
    public function cdeala(){
        $roles = $this->getRoles();
        if($roles[0]){
            $cdeal['RECORDER'] = session('user_auth.OPER_ID');
            $this->assign('cdeal', $cdeal);
            $this->display('cdealr');
        }
    }

    //投诉处理记录表（修改）
    public function cdealu(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $cdealobj = M('ComplainDeal');
            $cdeal = $cdealobj->where('id='.$id)->find();
            $this->assign('cdeal', $cdeal);
            $this->display('cdealr');
        }
    }

    //投诉处理记录表(保存数据)
    public function cdeals(){
        $roles = $this->getRoles();
        if($roles[0]){
            $cdeal = M('ComplainDeal');
            $cdeal->create();
            $id = I('id');
            if($id == ''){
                $cdeal->id = timeId1();
                $cdeal->add();
            }else{
                $cdeal->save();
            }
            
        }
        $this->redirect('cdeali');
    }



    //投诉处理记录表（删除）
    public function cdeald(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $cdealobj = M('ComplainDeal');
            $cdeal = $cdealobj->where('id='.$id)->delete();
            $this->redirect('cdeali');
        }
    }

    //投诉处理记录表（查看）
    public function cdealq(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $sql = $this->cdealQueryStr();
            $cdeallist = M()->query($sql);
            $this->assign('cdeal', $cdeallist[0]);
            $this->display();
        }
    }

    //故障处理记录表(首页)
    public function faulti(){
        $roles = $this->getRoles();
        if($roles[0]){
            $start_time = I('start_time');
            $end_time = I('end_time');


            $faultlist = $this->faultList($start_time, $end_time);
            $this->assign('faultlist', $faultlist);

            $this->assign('start_time',$start_time);
            $this->assign('end_time',$end_time);

            $this->display();
        }
    }

    //故障处理记录表（查询语句）
    public function faultList($id=''){
        $sql = "select a.id,substr(a.id,1,8) lr_time,decode(a.fault_system,'0','CRM','1','渠道','2','其他') fault_system,"
            ."decode(a.notify_way,'0','邮件','1','电话','2','故障单','3','故障群') notify_way,"
            ."decode(a.has_post,'0','否','1','是') has_post,a.happen_time,a.notify_time,a.deal_time,"
            ."a.effect_scope,b.oper_name,a.describ,a.happen_reason,a.solution "
            ."from mz_crm.rank_fault_deal a,mz_user.t_sys_oper b where a.recorder=b.oper_id(+)";

        $id = I('id');//序号
        if($id != ''){
            $sql.=" and a.id ={$id}";
        }

        $fault_system = I('fault_system');//故障系统
        if($fault_system != ''){
            $sql.=" and a.fault_system ={$fault_system}";
        }

        $notify_way = I('notify_way');//报障方式
        if($notify_way != ''){
            $sql.=" and a.notify_way ={$notify_way}";
        }

        $has_post = I('has_post');//是否派单
        if($has_post != ''){
            $sql.=" and a.has_post ={$has_post}";
        }

        $effect_scope = I('effect_scope');
        if($effect_scope != ''){
            $sql.=" and a.effect_scope like '%{$effect_scope}%'";
        }

        $describ = I('describ');
        if($describ != ''){
            $sql.=" and a.describ like '%{$describ}%'";
        }

        $happen_reason = I('happen_reason');
        if($happen_reason != ''){
            $sql.=" and a.happen_reason like '%{$happen_reason}%'";
        }

        $solution = I('solution');
        if($solution != ''){
            $sql.=" and a.solution like '%{$solution}%'";
        }

        $start_time = I('start_time');
        if($start_time != ''){
            $sql.=" and substr(a.id,1,8) >= replace('{$start_time}','-','')";
        }

        $end_time = I('end_time');
        if($end_time != ''){
            $sql.=" and substr(a.id,1,8) <= replace('{$end_time}','-','')";
        }
        $sql.=" order by id desc";
        $faultlist = parent::listsSqlByls($sql);
        return $faultlist;
    }

    //故障处理记录表（新增）
    public function faulta(){
        $roles = $this->getRoles();
        if($roles[0]){
            $fault['RECORDER'] = session('user_auth.OPER_ID');
            $this->assign('fault', $fault);
            $this->display('faultr');
        }
    }

    //故障处理记录表（修改）
    public function faultu(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $faultobj = M('FaultDeal');
            $fault = $faultobj->where('id='.$id)->find();
            $this->assign('fault', $fault);
            $this->display('faultr');
        }
    }

    //故障处理记录表(保存数据)
    public function faults(){
        $roles = $this->getRoles();
        if($roles[0]){
            $fault = M('FaultDeal');
            $fault->create();
            $id = I('id');

            if($id == ''){
                $fault->id = timeId1();
                $fault->add();
            }else{
                $fault->save();
            }
        }
        $this->redirect('faulti');
    }

    //故障处理记录表（删除）
    public function faultd(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $cdealobj = M('FaultDeal');
            $cdeal = $cdealobj->where('id='.$id)->delete();
            $this->redirect('faulti');
        }
    }

    //故障处理记录表（查看）
    public function faultq(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $faultlist = $this->faultList($id);
            $this->assign('fault', $faultlist[0]);
            $this->display();
        }
    }

    //上线类(首页)
    public function onlinei(){
        $roles = $this->getRoles();
        if($roles[0]){
            $start_time = I('start_time');
            $end_time = I('end_time');
            $onlinelist = $this->onlineList($start_time, $end_time);
            $this->assign('onlinelist', $onlinelist);

            $this->display();
        }
    }

    //上线类（查询语句）
    public function onlineList($id = ''){
        $sql = "select a.id,substr(a.id,1,8) lr_time,substr(a.online_time,1,10) online_time,b.oper_name,a.online_name,a.content,a.remark,a.file_old_name,a.file_new_name from mz_crm.rank_zc_online a,mz_user.t_sys_oper b where a.recorder=b.oper_id(+)";

        $id = I('id');//序号
        if($id != ''){
            $sql.=" and a.id ={$id}";
        }

        $onlineName = I('online_name');
        if($onlineName != ''){
            $sql.=" and online_name like '%".$onlineName."%'";
        }

        $content = I('content');
        if($content != ''){
            $sql.=" and content like '%".$content."%'";
        }

        $remark = I('remark');
        if($remark != ''){
            $sql.=" and remark like '%".$remark."%'";
        }

        $start_time = I('start_time');
        if($start_time != ''){
            $sql.=" and substr(a.online_time,1,10) >= '{$start_time}'";
        }

        $end_time = I('end_time');
        if($end_time != ''){
            $sql.=" and substr(a.online_time,1,10) <= '{$end_time}'";
        }
        $sql.=" order by id desc";
        $onlinelist = parent::listsSqlByls($sql);
        return $onlinelist;
    }

    //上线类（新增）
    public function onlinea(){
        $roles = $this->getRoles();
        if($roles[0]){
            $online['RECORDER'] = session('user_auth.OPER_ID');
            $this->assign('online', $online);
            $this->display('onliner');
        }
    }

    //上线类（修改）
    public function onlineu(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $onlineobj = M('ZcOnline');
            $online = $onlineobj->where('id='.$id)->find();
            $this->assign('online', $online);
            $fileConfig = C('FILE_UPLOAD');
	        $rootPath = substr($fileConfig['rootPath'],1);
	        $path = __ROOT__.$rootPath.$fileConfig['savePath'];
	        $this->assign('path', $path);
            $this->display('onliner');
        }
    }

    //上线类(保存数据)
    public function onlines(){
        $roles = $this->getRoles();
        if($roles[0]){
        	$info = array();
        	$online = M('ZcOnline');
            $online->create();
        	$file = $_FILES['file_old_name'];
	        ////没有上传文件直接保存，会提示没有上传文件,并返回页面
	        if($file['name'] !='' && $file['tmp_name'] !=''){
	            $info = $this->upload();
	            $online->file_old_name = $info['file_old_name']['name'];
            	$online->file_new_name = $info['file_old_name']['savename'];
	        }

            $id = I('id');
            if($id == ''){
                $online->id = timeId1();
                $online->add();
            }else{
                $online->save();
            }
        }
        $this->redirect('onlinei');
    }

    //上线类（删除）
    public function onlined(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $onlineobj = M('ZcOnline');
            $online = $onlineobj->where('id='.$id)->delete();
            $this->redirect('onlinei');
        }
    }

    //上线类（查看）
    public function onlineq(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $onlinelist = $this->onlineList($id);
            $this->assign('online', $onlinelist[0]);
            $fileConfig = C('FILE_UPLOAD');
	        $rootPath = substr($fileConfig['rootPath'],1);
	        $path = __ROOT__.$rootPath.$fileConfig['savePath'];
	        $this->assign('path', $path);
            $this->display();
        }
    }

    //知识库(首页)
    public function repositoryi(){
        $roles = $this->getRoles();
        if($roles[0]){
            $start_time = I('start_time');
            $end_time = I('end_time');
            $repositorylist = $this->repositoryList($start_time, $end_time);
            $this->assign('repositorylist', $repositorylist);

            $this->display();
        }
    }   

    //知识库（查询语句）
    public function repositoryList($id = ''){
        $sql = "select a.id,substr(a.id,1,8) lr_time,b.oper_name,a.busi_name,a.describ "
            ."from mz_crm.rank_repository a,mz_user.t_sys_oper b where a.recorder=b.oper_id(+)";

        $id = I('id');//序号
        if($id != ''){
            $sql.=" and a.id ={$id}";
        }

        $busiName = I('busi_name');
        if($busiName != ''){
            $sql.=" and busi_name like '%".$busiName."%'";
        }

        $describ = I('describ');
        if($describ != ''){
            $sql.=" and describ like '%".$describ."%'";
        }

        $start_time = I('start_time');
        if($start_time != ''){
            $sql.=" and substr(a.id,1,8) >= replace('{$start_time}','-','')";
        }

        $end_time = I('end_time');
        if($end_time != ''){
            $sql.=" and substr(a.id,1,8) <= replace('{$end_time}','-','')";
        }
        $sql.=" order by id desc";
        $repositorylist = parent::listsSqlByls($sql);
        return $repositorylist;
    }

    //知识库（新增）
    public function repositorya(){
        $roles = $this->getRoles();
        if($roles[0]){
            $repository['RECORDER'] = session('user_auth.OPER_ID');
            $this->assign('repository', $repository);
            $this->display('repositoryr');
        }
    }

    //知识库（修改）
    public function repositoryu(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $repositoryobj = M('Repository');
            $repository = $repositoryobj->where('id='.$id)->find();
            $this->assign('repository', $repository);
            $this->display('repositoryr');
        }
    }

    //知识库(保存数据)
    public function repositorys(){
        $roles = $this->getRoles();
        if($roles[0]){
            $repository = M('Repository');
            $repository->create();
            $id = I('id');
            if($id == ''){
                $repository->id = timeId1();
                $repository->add();
            }else{
                $repository->save();
            }
        }
        $this->redirect('repositoryi');
    }

    //知识库（删除）
    public function repositoryd(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $repositoryobj = M('Repository');
            $repositoryobj->where('id='.$id)->delete();
            $this->redirect('repositoryi');
        }
    }

    //知识库（查看）
    public function repositoryq(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $repositorylist = $this->repositoryList($id);
            $this->assign('repository', $repositorylist[0]);
            $this->display();
        }
    }

    //常规流程（首页）
    public function ruleflowi(){
        $roles = $this->getRoles();
        if($roles[0]){
            $start_time = I('start_time');
            $end_time = I('end_time');
            $ruleflowlist = $this->ruleflowlist($start_time, $end_time);
            $this->assign('ruleflowlist', $ruleflowlist);

            $this->display();
        }
    }   

    //常规流程（查询语句）
    public function ruleflowList($id = ''){
        $sql = "select a.id,substr(a.id,1,8) lr_time,b.oper_name,a.complain_type,a.deal_way,lime_light,remark "
            ."from mz_crm.rank_rule_flow a,mz_user.t_sys_oper b where a.recorder=b.oper_id(+)";
        $id = I('id');//序号
        if($id != ''){
            $sql.=" and a.id ={$id}";
        }

        $complainType = I('complain_type');
        if($complainType != ''){
            $sql.=" and complain_type like '%".$complainType."%'";
        }

        $deal_way = I('deal_way');
        if($deal_way != ''){
            $sql.=" and deal_way like '%".$deal_way."%'";
        }

        $lime_light = I('lime_light');
        if($lime_light != ''){
            $sql.=" and lime_light like '%".$lime_light."%'";
        }

        $remark = I('remark');
        if($remark != ''){
            $sql.=" and remark like '%".$remark."%'";
        }

        $start_time = I('start_time');
        if($start_time != ''){
            $sql.=" and substr(a.id,1,8) >= replace('{$start_time}','-','')";
        }

        $end_time = I('end_time');
        if($end_time != ''){
            $sql.=" and substr(a.id,1,8) <= replace('{$end_time}','-','')";
        }
        $sql.=" order by id desc";
        $ruleflowlist = parent::listsSqlByls($sql);
        return $ruleflowlist;
    }

    //常规流程（新增）
    public function ruleflowa(){
        $roles = $this->getRoles();
        if($roles[0]){
            $ruleflow['RECORDER'] = session('user_auth.OPER_ID');
            $this->assign('ruleflow', $ruleflow);
            $this->display('ruleflowr');
        }
    }

    //常规流程（修改）
    public function ruleflowu(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $ruleflowobj = M('RuleFlow');
            $ruleflow = $ruleflowobj->where('id='.$id)->find();
            $this->assign('ruleflow', $ruleflow);
            $this->display('ruleflowr');
        }
    }

    //常规流程(保存数据)
    public function ruleflows(){
        $roles = $this->getRoles();
        if($roles[0]){
            $ruleflow = M('RuleFlow');
            $ruleflow->create();
            $id = I('id');
            if($id == ''){
                $ruleflow->id = timeId1();
                $ruleflow->add();
            }else{
                $ruleflow->save();
            }
        }
        $this->redirect('ruleflowi');
    }

    //常规流程（删除）
    public function ruleflowd(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $ruleflowobj = M('RuleFlow');
            $ruleflowobj->where('id='.$id)->delete();
            $this->redirect('ruleflowi');
        }
    }

    //常规流程（查看）
    public function ruleflowq(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $ruleflowlist = $this->ruleflowList($id);
            $this->assign('ruleflow', $ruleflowlist[0]);
            $this->display();
        }
    }

    //排班表（首页）
    public function schedulei(){
        $roles = $this->getRoles();
        if($roles[0]){
            $schedulelist = $this->schedulelist();

            $this->assign('schedulelist', $schedulelist);
            $holidayArrange = $this->holidayArrange();
            $haLength = count($holidayArrange);
            $this->assign('admin', $roles[1]);
            $this->assign('holidayArrange', $holidayArrange);

            $this->display();
        }
    }

    //排班表（查询语句）
    public function schedulelist(){
        $sql = "select id,to_char(day,'yyyy-MM-dd') day,weekday，p1,p2,p3,p4,p5,p6,holiday,pointer from mz_crm.rank_work_schedule where 1=1";
        //月份始终以两位显示，小于10月的会补全前导0
        //PHP对Oracle日期的显示为：01-DEC-16,故查询时将日期显示为字符串格式
        $ym = I('ym');
        $month = '';
        if($ym == ''){
            $ym = date('Y/m');
        }else{
            $ym = str_replace('-', '/', substr($ym,0,7));
        }

        $sql.=" and to_char(day,'yyyy/MM')='{$ym}' order by day asc";
        //echo $sql;die;
        $count = M('WorkSchedule')->where("to_char(day,'yyyy/MM')='{$ym}'")->count();
        $schedulelist = parent::listsSqlByls($sql,$count);
        return $schedulelist;
    }

    //排班表（修改）
    public function scheduleu(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $scheduleobj = M('WorkSchedule');
            $schedule = $scheduleobj->where("id=".$id)->find();
            $this->assign('schedule', $schedule);
            $this->display('scheduler');
        }
    }

    //排班表(保存数据)
    public function schedules(){
        $roles = $this->getRoles();
        if($roles[0]){
            $schedule = M('WorkSchedule');
            $schedule->create();
            $schedule->save();
        }
        $this->redirect('schedulei');
    }

    //调休安排
    public function holidayArrange(){
        //避免0.5输出.5的情况
        $halist = M('HolidaySchedule')->query("select id,user_name,10*has_day has_day,10*not_day not_day,10*surplus_day surplus_day from mz_crm.rank_holiday_schedule");
        return $halist;
    }

    //调休安排(修改)
    public function holidayu(){
        $ha = M('HolidaySchedule')->query("select id,user_name,10*has_day has_day,10*not_day not_day,10*surplus_day surplus_day from mz_crm.rank_holiday_schedule where id=".I('id'));

        //->where('id='.I('id'))->find();
        $this->assign('ha', $ha[0]);
        $this->display('holidayr');
    }

    //调休安排(保存)
    public function holidays(){
        $roles = $this->getRoles();
        if($roles[0]){
            $hs = M('HolidaySchedule');
            $hs->create();
            $hs->save();
        }
        $this->redirect('schedulei');
    }

    //PHPExcel导出投诉处理记录表
    public function cdealXlsExport(){
        $roles = $this->getRoles();
        if($roles[0]){
            $query['come_from'] = I('come_from');
            $query['county_code'] = I('county_code');
            $query['from_site'] = I('from_site');
            $query['busi_type'] = I('busi_type');
            $query['has_post'] = I('has_post');
            $query['result_type'] = I('result_type');
            $query['content'] = I('content');
            $query['deal_way'] = I('deal_way');
            $query['recorder'] = I('recorder');
            $query['start_time'] = I('start_time');
            $query['end_time'] = I('end_time');
            $query['keyword'] = I('keyword');
            $this->assign('query', $query);

            $sql = $this->cdealQueryStr();
            $elist = M()->query($sql);

            $filename="投诉处理记录表.xls";
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

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(18);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            // set table header content  设置表头名称 
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '编号')
                ->setCellValue('B1', '录入时间')
                ->setCellValue('C1', '投诉来源')
                ->setCellValue('D1', '县市')
                ->setCellValue('E1', '投诉网点')
                ->setCellValue('F1', '业务分类')
                ->setCellValue('G1', '业务现象')
                ->setCellValue('H1', '是否派单')
                ->setCellValue('I1', '投诉内容')
                ->setCellValue('J1', '问题原因及解决办法')
                ->setCellValue('K1', '备注')
                ->setCellValue('L1', '记录人');
            //将数据写入列
            if(count($elist) > 0){
                foreach($elist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), ' '.$elist[$k]['ID']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['LR_TIME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['COME_FROM']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['COUNTY_NAME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['FROM_SITE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['BUSI_TYPE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['RESULT_TYPE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), ' '.$elist[$k]['HAS_POST']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['CONTENT']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['DEAL_WAY']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['REMARK']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['OPER_NAME']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle(' ');//sheet表名称
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    public function upload(){
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = C("FILE_UPLOAD.maxSize"); // 设置附件上传大小
        $upload->exts = C("FILE_UPLOAD.exts"); // 设置附件上传类型
        $upload->rootPath = C("FILE_UPLOAD.rootPath"); // 设置附件上传根目录
        $upload->savePath = C("FILE_UPLOAD.savePath");//date("Y-m-d",time()) ."/";// 设置附件上传（子）目录
        $upload->autoSub = false;
        $upload->saveName = 'timeId';//C("FILE_UPLOAD.saveName");
        $info = $upload->upload();

        if(!$info){
            // 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            foreach($key as $file){
                $info[$key] = $file['savepath'].$file['savename'];
            }
        }
        return $info;
    }

    //含当前模块所须角色(传数组)
    public function getRoles(){
        //赛马_支撑运维_录入人员：5020001622
        $arr = array('5020001622');
        $role[0] = parent::hasRoles($arr);
        $role[1] = FALSE;
        if(session('user_auth.OA')=="lsjinxin"){
        	$role[1] = TRUE;
        }
        return $role;
    }
}
