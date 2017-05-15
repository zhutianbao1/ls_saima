<?php
	
namespace Moa\Controller;

class AndroidController extends BaseController
{
	public function index(){
		$this->display();
	}

	public function ad_test(){
		$this->display();
	}

	public function main(){
		$this->display();
	}

	public function ad_manage(){
		$this->display();
	}

	public function ad_speedy(){
		$this->display();
	}

	public function ad_qysm(){
		$userInfo=M('userInfo');
		$jm_pm=I('jm_pm');
		$hm_pm=I('hm_pm');
		$qlm_pm=I('qlm_pm');
		$jm_sql="SELECT * FROM rank_jm where 1=1";
		if(!empty($jm_pm)){
			if(eregi("^[0-9]+$",$jm_pm)){
				$jm_sql.=" and rank=".$jm_pm;
			}else{
				$jm_sql.=" and user_name='".$jm_pm."'";
			}
		}
		$jm_sql.=" ORDER BY config_id,rank";
		$jm = $userInfo->query($jm_sql);
		$this->assign('jm',$jm);

		$hm_sql="SELECT * FROM rank_hm where 1=1";
		if(!empty($hm_pm)){
			if(eregi("^[0-9]+$",$hm_pm)){
				$hm_sql.=" and rank=".$hm_pm;
			}else{
				$hm_sql.=" and user_name='".$hm_pm."'";
			}
		}
		$hm_sql.=" ORDER BY config_id,rank";
		$hm = $userInfo->query($hm_sql);
		$this->assign('hm',$hm);

		$qlm_sql="SELECT * FROM rank_qlm where 1=1";
		if(!empty($qlm_pm)){
			if(eregi("^[0-9]+$",$qlm_pm)){
				$qlm_sql.=" and rank=".$qlm_pm;
			}else{
				$qlm_sql.=" and user_name='".$qlm_pm."'";
			}
		}
		$qlm_sql.=" ORDER BY config_id,rank";
		$qlm = $userInfo->query($qlm_sql);
		$this->assign('qlm',$qlm);
		$this->display();
	}

	public function test(){
		$this->display();
	}

	public function test1(){
		$this->display();
	}

	public function ad_index($month=201607,$id=1001,$mode=1){
		$userInfo=M('userInfo');
		$yearInfo=M('yearInfo');
		if($mode==2){
			$pos_sql='SELECT * FROM rank_config WHERE MONTH=201607 and status=1 order by id';
			$month_sql="SELECT MONTH FROM rank_config GROUP BY MONTH ORDER BY MONTH";
			$sql="select b.cnt zan,a.* from (SELECT a.*,rank()over(partition BY config_id order by amount desc nulls last) pm FROM(SELECT bill_id,user_name,config_id,SUM(全员赛马积分) amount FROM rank_year_info WHERE rpt_month>201602 GROUP BY bill_id,user_name,config_id) a) a,rank_user_zan b where a.bill_id=b.id(+) and a.config_id=".$id." order by a.amount desc";
		}else{
			$pos_sql="SELECT * FROM rank_config WHERE MONTH=".$month." and status=1 order by id";
			$month_sql="SELECT MONTH FROM rank_config GROUP BY MONTH ORDER BY MONTH";
			$sql="SELECT * FROM rank_user_info WHERE rpt_month=".$month." AND config_id=".$id."";
		}
		$pos_name=$userInfo->query($pos_sql);
		$this->assign('pos_name',$pos_name);
		$rpt_month=$userInfo->query($month_sql);
		$this->assign('rpt_month',$rpt_month);
		$users=$userInfo->query($sql);
		$this->assign('users',$users);
		$this->assign('m',$month);
		$this->display();
	}

	public function ad_ing($id=101){
		$jf=M('year');
		$user_name=I('user_name');
		$user_id=I('user_id');
		$pos_sql="SELECT * FROM rank_jf_px";
		$pos_name=$jf->query($pos_sql);
		$this->assign('pos_name',$pos_name);
		if($id<200){
			$sql_gr="select * from (SELECT a.*,rank()over (partition BY 单位 order by 个人总积分 desc) pm FROM(SELECT b.*,a.bill_id cc FROM rank_party_member a,(SELECT 员工编号,单位,姓名,bill_id,nvl(SUM(个人绩效积分),0)+nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0)+nvl(SUM(创新积分),0) 个人总积分 FROM (SELECT 月份,员工编号,config_id,单位,姓名,bill_id,MAX(全员赛马积分) 全员赛马积分,个人绩效积分,竞赛积分,荣誉积分,创新积分 FROM (SELECT b.config_id,a.* FROM Rank_个人积分_Info a, rank_jf_px b WHERE a.单位=b.单位(+)) GROUP BY 月份,个人绩效积分,员工编号,config_id,单位,姓名,bill_id,竞赛积分,荣誉积分,创新积分) a WHERE config_id=".$id." GROUP BY 员工编号,单位,姓名,bill_id) b where b.bill_id=a.bill_id(+)) a) where 1=1";
			  if(!empty($user_name)){
			  	$sql_gr .= " and 姓名 like '%".$user_name."%'";
			  }
			  if(!empty($user_id)){
			  	$sql_gr .= " and 员工编号='".$user_id."'";
			  }
			$gr=$jf->query($sql_gr);
			$this->assign('gr',$gr);
		}else{
			$sql_td="SELECT a.*,rank()over (partition BY 单位 order by 团队总积分 desc) pm FROM(SELECT 单位,县市,团队,bill_id,nvl(SUM(全员赛马积分),0)+nvl(SUM(竞赛积分),0)+nvl(SUM(荣誉积分),0) 团队总积分 FROM (SELECT b.config_id,a.* FROM Rank_团队积分 a, rank_jf_px b WHERE a.单位=b.单位(+)) WHERE config_id=".$id." GROUP BY 县市,单位,团队,bill_id) a";
			$td=$jf->query($sql_td);
			$this->assign('td',$td);
		}
		$this->display();
	}

	public function rank_market(){
		if(IS_POST){
			$countyName = I('countyName');
			$mode = I('mode');
			$homeDate = I('homeDate');
			$endDate  = I('endDate');
			if(empty($homeDate) && empty($endDate)){
				$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market group by county_name";
			}

			if(empty($countyName) && empty($mode) && !empty($homeDate)){
				$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."'
					group by county_name";
			}

			if(!empty($countyName) && empty($mode)){
				$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."' 
					  and county_name='".$countyName."'  
					group by county_name";
			}

			if(empty($countyName) && !empty($mode)){
				$sql = "select county_name,服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."' 
					  and 服务类型='".$mode."'
					group by county_name,服务类型";
			}

			if(!empty($countyName) && !empty($mode)){
				$sql = "select county_name,服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
					from rank_market 
					where create_date>='".$homeDate."' and create_date<='".$endDate."' 
					  and county_name='".$countyName."' and 服务类型='".$mode."'
					group by county_name,服务类型";
			}
			$market=M('market');
			$rpt=$market->query($sql);
			$this->assign('rpt',$rpt);
			$this->ajaxReturn($rpt);
		}else{
			$this->display();
		}
	}

	public function push($data,$name="Excel"){
		Vendor('Classes.PHPExcel'); 
		error_reporting(E_ALL);
		date_default_timezone_set('Europe/London');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("转弯的阳光")
       ->setLastModifiedBy("转弯的阳光")
       ->setTitle("数据EXCEL导出")
       ->setSubject("数据EXCEL导出")
       ->setDescription("备份数据")
       ->setKeywords("excel")
      ->setCategory("result file");
      //列宽
      $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6);
      $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
      $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
      //表头加粗
      $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(TRUE);
      $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(TRUE);
      $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(TRUE);
      $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(TRUE);
      $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(TRUE);
      $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(TRUE);
      //表头名称
      $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue('A1', '县市')    
                  ->setCellValue('B1', '服务类型')
                  ->setCellValue('C1', '设摊数量')
                  ->setCellValue('D1', '终端销量')
                  ->setCellValue('E1', '孝道机销量')
                  ->setCellValue('F1', '2G/3G迁移量');
      foreach($data as $k => $v){
     $num=$k+2;
     //居中
      $objPHPExcel->getActiveSheet()->getStyle('A1:F'.$num)
      ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      //导出数据
     $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue('A'.$num, $v['COUNTY_NAME'])    
                  ->setCellValue('B'.$num, $v['服务类型'])
                  ->setCellValue('C'.$num, $v['设摊数量'])
                  ->setCellValue('D'.$num, $v['终端销量'])
                  ->setCellValue('E'.$num, $v['孝道机销量'])
                  ->setCellValue('F'.$num, $v['G2G3迁移量']);
    }

    $objPHPExcel->getActiveSheet()->setTitle('万场营销汇总');
    $objPHPExcel->setActiveSheetIndex(0);
    ob_end_clean();
     header('Content-Type: application/vnd.ms-excel');
     header('Content-Disposition: attachment;filename="'.$name.'.xlsx"');
     header('Cache-Control: max-age=0');
     $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
     $objWriter->save('php://output');
     exit;
	}

	public function file($countyName='',$mode='',$homeDate,$endDate){
		$market=M('market');
		if(empty($homeDate) && empty($endDate)){
			$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
				from rank_market group by county_name";
		}

		if(empty($countyName) && empty($mode) && !empty($homeDate)){
			$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
				from rank_market 
				where create_date>='".$homeDate."' and create_date<='".$endDate."'
				group by county_name";
		}

		if(!empty($countyName) && empty($mode)){
			$sql = "select county_name,'全部' 服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
				from rank_market 
				where create_date>='".$homeDate."' and create_date<='".$endDate."' 
				  and county_name='".$countyName."'  
				group by county_name";
		}

		if(empty($countyName) && !empty($mode)){
			$sql = "select county_name,服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
				from rank_market 
				where create_date>='".$homeDate."' and create_date<='".$endDate."' 
				  and 服务类型='".$mode."'
				group by county_name,服务类型";
		}

		if(!empty($countyName) && !empty($mode)){
			$sql = "select county_name,服务类型,sum(设摊数量) 设摊数量,sum(终端销量) 终端销量,sum(孝道机销量) 孝道机销量,sum(G2G3迁移量) G2G3迁移量
				from rank_market 
				where create_date>='".$homeDate."' and create_date<='".$endDate."' 
				  and county_name='".$countyName."' and 服务类型='".$mode."'
				group by county_name,服务类型";
		}
		$data=$market->query($sql);
		$name='营销汇总-'.date ( 'Ymdhis' );
		$res=$this->push($data,$name);
	}
}
?>