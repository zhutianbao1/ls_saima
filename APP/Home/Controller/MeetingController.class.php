<?php 
namespace Home\Controller;

	class MeetingController extends BaseController {
		
		public function index(){
			$bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$user = self::feed($bill_id);
			$this->assign('user',$user);
			$btnGroup = self::btnGroup($bill_id);
			$this->assign('btnGroup',$btnGroup);
			$tjGroup = self::tjGroup($bill_id);
			$this->assign('tjGroup',$tjGroup);
			$this->display();
		}

		public function add(){
			$this->display("meeting/add_meeting");
		}

		public function tj(){
			$meeting=M('meeting');
			$data['current_date']=date('Y-m-d');
			$data['county_name']=I('county_name');
			$data['user_name']=I('user_name');
			$data['create_date']=I('create_date');
			$data['主持人']=I('主持人');
			$data['晨操']=I('晨操');
			$data['信息播报']=I('信息播报');
			$data['轻松一刻']=I('轻松一刻');
			$data['典范分享']=I('典范分享');
			$data['专题学习内容']=I('专题学习内容');
			$data['专题主讲人']=I('专题主讲人');
			$data['政令宣导']=I('政令宣导');
			$data['成功欢呼']=I('成功欢呼');
			$data['业务推动']=I('业务推动');
			$data['本周重要事项提醒']=I('本周重要事项提醒');
			$data['其他事项']=I('其他事项');
			$user=$meeting->add($data);
			if($user){
				$this->success('成功','index');
			}else{
				$this->error('失败');
			}
		}

		public function feedback(){
			$bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$j['success']=false;
			$j['msg']='提交失败';
			$meeting=M('meeting');
			$county_name=I('county_name');
			$create_date=I('create_date');
			$data['早会时间']="".I('home_date')."-".I('end_date')."";
			$data['参加人数']=I('参加人数');
			$data['应到人数']=I('应到人数');
			$data['实到人数']=I('实到人数');
			$data['请假人数']=I('请假人数');
			$data['早会内容与行事历是否一致']=I('早会内容和行事历是否一致');
			$data['其他反馈']=I('feed');
			$data['feed_phone']=$bill_id;
			$user=$meeting->where("county_name='".$county_name."' and create_date='".$create_date."'")->save($data);
			if($user){
				$j['success']=true;
				$j['msg']='提交成功';
			}
			$this->ajaxReturn($j);
		}

		public function feedback1(){
			$bill_id=$_SESSION['user_auth']['OPER_LOGIN_CODE'];
			$j['success']=false;
			$j['msg']='提交失败';
			$meeting=M('meetingFeedback');
			$county_names=I('county_name');
			$create_date=I('create_date');
			$data['早会时间']="".I('home_date')."-".I('end_date')."";
			$data['参加人数']=I('参加人数');
			$data['应到人数']=I('应到人数');
			$data['实到人数']=I('实到人数');
			$data['请假人数']=I('请假人数');
			$data['早会内容与行事历是否一致']=I('早会内容和行事历是否一致');
			$data['其他反馈']=I('feed');
			$data['feed_phone']=$bill_id;
			$feed=$meeting->where("cname='".$county_names."' and cdate='".$create_date."'")->find();
			if($feed==null || $feed==""){
				$data['cname']=$county_names;
				$data['cdate']=$create_date;
				$user=$meeting->add($data);
			}else{
				$user=$meeting->where("cname='".$county_names."' and cdate='".$create_date."'")->save($data);
			}
			if($user){
				$j['success']=true;
				$j['msg']='提交成功';
			}
			$this->ajaxReturn($j);
		}

		public function labour(){
			$sql="SELECT * FROM ls_zwx_新春营销激励_daily_pm";
			$sql2="SELECT * FROM ls_zwx_新春营销激励_daily";
			$sql3="SELECT * FROM ls_zwx_新春营销激励_daily_0";
			$sql_kpi="SELECT * FROM ls_zwx_新春营销_KPI指标";
			$sql_key="SELECT * FROM mz_yechen_ydzdgz_daily";
			$sql_cot="SELECT * FROM mz_yechen_触点执行情况_daily";
			$user=M()->query($sql);
			$user2=M()->query($sql2);
			$user3=M()->query($sql3);
			$kpi=M()->query($sql_kpi);
			$key=M()->query($sql_key);
			$cot=M()->query($sql_cot);
			$this->assign('user',$user);
			$this->assign('user2',$user2);
			$this->assign('user3',$user3);
			$this->assign('kpi',$kpi);
			$this->assign('keys',$key);
			$this->assign('cot',$cot);
			$this->display();
		}

		public function data_list(){
			$sql="SELECT * FROM 市场大势";
			$sql2="SELECT * FROM G4发展";
			$sql3="SELECT * FROM 流量经营";
			$sql4="SELECT * FROM 智慧家庭";
			$sql5="SELECT a.*,b.*,c.*,d.*,b.排名9 pm9,c.a2 aa2,c.b2 bb2,c.c2 cc2,c.d2 dd2,c.a3 aa3,c.b3 bb3,c.c3 cc3,c.d3 dd3,b.排名4 排名44,d.b2 bz2,d.c2 cz2,d.d2 dz2,d.e2 ez2 FROM 市场大势 a,G4发展 b,流量经营 c,智慧家庭 d where a.city_name=b.county_name and a.city_name=c.county_name and a.city_name=d.city_name";
			$scds=M()->query($sql);
			$g4fz=M()->query($sql2);
			$lljy=M()->query($sql3);
			$zhjt=M()->query($sql4);
			$hz=M()->query($sql5);
			$this->assign('scds',$scds);
			$this->assign('g4fz',$g4fz);
			$this->assign('lljy',$lljy);
			$this->assign('zhjt',$zhjt);
			$this->assign('hz',$hz);
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

		//PHPExcel导入
		public function file(){
			if (! empty ( $_FILES ['file_stu'] ['name'] )){
			    $tmp_file = $_FILES ['file_stu'] ['tmp_name'];
			    $file_types = explode ( ".", $_FILES ['file_stu'] ['name'] );
			    $file_type = $file_types [count ( $file_types ) - 1];
			    $str = $file_types [count ( $file_types ) - 2];
			     /*判别是不是.xls文件，判别是不是excel文件*/
			    if (strtolower ( $file_type ) != "xls" && strtolower ( $file_type ) != "xlsx"){
			        $this->error ( '不是Excel文件，重新上传' );
			    }
			    /*设置上传路径*/
			    $savePath = '//10.78.1.85/www/ranking/Public/upfile/Execl/';
			    /*以时间来命名上传的文件*/
			    // $str = date ( 'Ymdhis' ); 
			    $file_name = $str . "." . $file_type;
			     /*是否上传成功*/
			    if (! copy ( $tmp_file, $savePath . $file_name )){
			        $this->error ( '上传失败' );
			    }
			  	$res = $this->read ( $savePath . $file_name,$file_type );
			  	foreach ($res as $k => $v) {
			  		if($v[0]==null || trim($v[0])=="" || $v[1]==null || trim($v[1])=="" || $v[2]==null || trim($v[2])=="" || $v[3]==null || trim($v[3])=="" || $v[4]==null || trim($v[4])=="" || $v[5]==null || trim($v[5])=="" || $v[6]==null || trim($v[6])=="" || $v[7]==null || trim($v[7])=="" || $v[8]==null || trim($v[8])=="" || $v[9]==null || trim($v[9])=="" || $v[10]==null || trim($v[10])=="" || $v[11]==null || trim($v[11])=="" || $v[12]==null || trim($v[12])=="" || $v[13]==null || trim($v[13])=="" || $v[14]==null || trim($v[14])==""){
			  			$this->error("导入的数据不能为空");
			  		}else{
			  			if($k>1){
			  				if($this->isDate($v[2])){
				  				$ress=1;
				  			}else{
				  				$this->error("日期格式不正确");
				  			}
			  			}
			  		}
			  	}
			  	if($ress==1){
				    foreach ( $res as $k => $v ){
				       	if ($k > 1){
				           	$data ['county_name'] = $v [0];
				          	$data ['user_name'] = $v [1];
				           	$data ['create_date'] = $v [2];
				           	$data ['主持人'] = $v [3];
				           	$data ['晨操'] = $v [4];
				          	$data ['信息播报'] = $v [5];
				          	$data ['轻松一刻'] = $v [6];
				          	$data ['典范分享'] = $v [7];
				          	$data ['专题学习内容'] = $v [8];
				          	$data ['专题主讲人'] = $v [9];
				          	$data ['政令宣导'] = $v [10];
				          	$data ['成功欢呼'] = $v [11];
				          	$data ['业务推动'] = $v [12];
				          	$data ['本周重要事项提醒'] = $v [13];
				          	$data ['其他事项'] = $v [14];
				          	$data ['current_date'] = date('Y-m-d');
				         	$result = M ('meeting')->add ( $data );
					        if (! $result){
					            $this->error ('导入数据库失败');
					        }
					    }
				    }
				    $this->success('导入成功');
			  	}
			}else{
				$this->error ( '请选择导入的Execl数据！' );
			}
		}

		//PHPExcel导出
	    public function exportxls($county_name,$create_date,$ed_date){
	        if(true){
	            $sql="SELECT a.*,b.早会时间 早会时间1,b.参加人数 参加人数1,b.应到人数 应到人数1,b.实到人数 实到人数1,b.请假人数 请假人数1,b.早会内容与行事历是否一致 早会内容与行事历是否一致1,b.其他反馈 其他反馈1 FROM rank_meeting a,rank_meeting_feedback b where a.county_name=b.cname(+) and a.create_date=b.cdate(+)";
	        	if(!empty($county_name)){
	        		$sql .= " and a.county_name='".$county_name."'";
	        	}
	        	if(!empty($create_date)){
	        		$sql .= " and a.create_date>='".$create_date."'";
	        	}
	        	if(!empty($ed_date)){
	        		$sql .= " and a.create_date<='".$ed_date."'";
	        	}
	        	$sql .=" order by a.current_date desc,a.county_name,a.create_date";
	            $elist = M()->query($sql);

	            $filename="早会行事历.xls";
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
	            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(14);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(14);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(14);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(12);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(18);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(30);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(30);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(15);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(15);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(12);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(18);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(30);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(30);
	            $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(30);

	            // 设置行高度
	            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
	            $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);

	            // 字体和样式
	            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
	            $objPHPExcel->getActiveSheet()->getStyle('A1:AD2')->getFont()->setBold(true);
	            //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
	            $objPHPExcel->getActiveSheet()->getStyle('A1:AD2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
	            $objPHPExcel->getActiveSheet()->getStyle('A1:AD2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);  // 设置垂直居中
	            $objPHPExcel->getActiveSheet()->getStyle('A1:AD2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

	            // 合并
	            $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
	            $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');
	            $objPHPExcel->getActiveSheet()->mergeCells('C1:C2');
	            $objPHPExcel->getActiveSheet()->mergeCells('D1:P1');
	            $objPHPExcel->getActiveSheet()->mergeCells('Q1:W1');
	            $objPHPExcel->getActiveSheet()->mergeCells('X1:AD1');

	            // set table header content  设置表头名称 
	            $objPHPExcel->setActiveSheetIndex(0)
	                ->setCellValue('A1', '部门')
	                ->setCellValue('B1', '提交人')
	                ->setCellValue('C1', '提交时间')
	                ->setCellValue('D1', '早会内容')
	                ->setCellValue('D2', '早会时间')
	                ->setCellValue('E2', '主持人')
	                ->setCellValue('F2', '问好、晨操')
	                ->setCellValue('G2', '信息播报')
	                ->setCellValue('H2', '轻松一刻')
	                ->setCellValue('I2', '典范分享')
	                ->setCellValue('J2', '专题学习内容')
	                ->setCellValue('K2', '专题主讲人')
	                ->setCellValue('L2', '政令宣导')
	                ->setCellValue('M2', '成功欢呼')
	                ->setCellValue('N2', '业务推动')
	                ->setCellValue('O2', '本周重要事项提醒')
	                ->setCellValue('P2', '其他事项')
	                ->setCellValue('Q1', '分部早会反馈')
	                ->setCellValue('Q2', '早会时间')
	                ->setCellValue('R2', '后台参加人数')
	                ->setCellValue('S2', '应到人数')
	                ->setCellValue('T2', '实到人数')
	                ->setCellValue('U2', '请假人数')
	                ->setCellValue('V2', '早会内容和行事历是否一致')
	                ->setCellValue('W2', '其他反馈')
	                ->setCellValue('X1', '市公司抽查反馈')
	                ->setCellValue('X2', '早会时间')
	                ->setCellValue('Y2', '后台参加人数')
	                ->setCellValue('Z2', '应到人数')
	                ->setCellValue('AA2', '实到人数')
	                ->setCellValue('AB2', '请假人数')
	                ->setCellValue('AC2', '早会内容和行事历是否一致')
	                ->setCellValue('AD2', '其他反馈');
	            //将数据写入列
	            if(count($elist) > 0){
	                foreach($elist as $k => $v){
	                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+3), $elist[$k]['COUNTY_NAME']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+3), $elist[$k]['USER_NAME']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+3), $elist[$k]['CURRENT_DATE']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+3), $elist[$k]['CREATE_DATE']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+3), $elist[$k]['主持人']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+3), $elist[$k]['晨操']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+3), $elist[$k]['信息播报']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+3), $elist[$k]['轻松一刻']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+3), $elist[$k]['典范分享']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+3), $elist[$k]['专题学习内容']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+3), $elist[$k]['专题主讲人']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+3), $elist[$k]['政令宣导']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+3), $elist[$k]['成功欢呼']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+3), $elist[$k]['业务推动']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+3), $elist[$k]['本周重要事项提醒']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+3), $elist[$k]['其他事项']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.($k+3), $elist[$k]['早会时间']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('R'.($k+3), $elist[$k]['参加人数']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('S'.($k+3), $elist[$k]['应到人数']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('T'.($k+3), $elist[$k]['实到人数']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('U'.($k+3), $elist[$k]['请假人数']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('V'.($k+3), $elist[$k]['早会内容与行事历是否一致']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('W'.($k+3), $elist[$k]['其他反馈']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('X'.($k+3), $elist[$k]['早会时间1']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.($k+3), $elist[$k]['参加人数1']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.($k+3), $elist[$k]['应到人数1']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.($k+3), $elist[$k]['实到人数1']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.($k+3), $elist[$k]['请假人数1']);
	                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.($k+3), $elist[$k]['早会内容与行事历是否一致1']);  
	                    $objPHPExcel->getActiveSheet()->setCellValue('AD'.($k+3), $elist[$k]['其他反馈1']);
	                }
	            }

	            $objPHPExcel->getActiveSheet()->setTitle('早会行事历');//sheet表名称
	            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
	            $objPHPExcel->setActiveSheetIndex(0);
	            
	            vendor("Classes.PHPExcel\IOFactory");
	            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
	            $objWriter->save('php://output');
	            exit;
	        }else{
	            echo '请确认是否登录和有相应权限';
	        }
	    }




	    

	    protected function btnGroup($bill_id){
			$btnGroup['1'] = '0';
	        $btnGroup['2'] = '0';
	        $btnGroup['3'] = '0';
	        $btnGroup['4'] = '0';

			$meetingRole=M('meetingRole');
			$role=$meetingRole->where("bill_id='".$bill_id."'")->find();
			if($role!=null && $role!=""){
				$btnGroup['1']='1';
				if($role['DEPT']!="" && $role['DEPT']!=null){
					$btnGroup['2']='1';
				}else{
					$btnGroup['3']='1';
					$btnGroup['4']='1';
				}
			}
			return $btnGroup;
		}

		protected function tjGroup($bill_id){
			$tjGroup['0'] = '1';
			$tjGroup['1'] = '1';
			$tjGroup['2'] = '0';

			$meetingRole=M('meetingRole');
			$role=$meetingRole->where("bill_id='".$bill_id."'")->find();
			if($role!=null && $role!=""){
				$tjGroup['2'] = '1';
				if($role['DEPT']!="" && $role['DEPT']!=null){
					$tjGroup['1'] = '0';
				}
			}
			return $tjGroup;
		}

		protected function feed($bill_id){
			$meeting=M('meeting');
			$county_name=I('county_name');
			$create_date=I('create_date');
			$ed_date=I('ed_date');
			$role=M('meetingRole')->where("bill_id=".$bill_id."")->find();
			if($role['DEPT']!="" && $role['DEPT']!=null){
				$sql="SELECT a.*,b.早会时间 早会时间1,b.参加人数 参加人数1,b.应到人数 应到人数1,b.实到人数 实到人数1,b.请假人数 请假人数1,b.早会内容与行事历是否一致 早会内容与行事历是否一致1,b.其他反馈 其他反馈1 FROM rank_meeting a,rank_meeting_feedback b where a.county_name=b.cname(+) and a.create_date=b.cdate(+) and county_name='".$role['COUNTY_NAME']."'";
				if (!empty($create_date)) {
					$sql .= " and create_date>='".$create_date."'";
				}
				if (!empty($ed_date)) {
					$sql .= " and create_date<='".$ed_date."'";
				}
				$sql .=" order by a.current_date desc,a.create_date desc";
				//$user=$meeting->query($sql);
				$user=parent::listsSqlByls($sql,12);
			}else{
				$sql="SELECT a.*,b.早会时间 早会时间1,b.参加人数 参加人数1,b.应到人数 应到人数1,b.实到人数 实到人数1,b.请假人数 请假人数1,b.早会内容与行事历是否一致 早会内容与行事历是否一致1,b.其他反馈 其他反馈1 FROM rank_meeting a,rank_meeting_feedback b where a.county_name=b.cname(+) and a.create_date=b.cdate(+)";
				if (!empty($county_name)) {
					$sql .= " and county_name='".$county_name."'";
				}
				if (!empty($create_date)) {
					$sql .= " and create_date>='".$create_date."'";
				}
				if (!empty($ed_date)) {
					$sql .= " and create_date<='".$ed_date."'";
				}
				$sql .=" order by a.current_date desc,a.county_name,a.create_date desc";

				//$user=$meeting->query($sql);
				$user=parent::listsSqlByls($sql,12);
			}
			return $user;
		}

		public function isDate( $dateString ) {
		    return  date('Y-m-d', strtotime($dateString)) === $dateString ;
		}
	}
?>