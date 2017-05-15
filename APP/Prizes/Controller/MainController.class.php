<?php
namespace Prizes\Controller;
/**
* 
*/
class MainController extends BaseController
{
	
	protected function _initialize(){
		parent::_initialize();
		self::power();
	}

	public function index(){
		$prizesType=M('prizesType');
		$type=$prizesType->select();
		$this->assign('type',$type);
		$this->display();
	}
	//奖品管理
	public function prizes_list(){
		$prizesInfo=M('prizesInfo');
		$sql="SELECT a.*,IFNULL(b.num,0) receive_num,(prizes_num-IFNULL(b.num,0)) prizes_stock from (SELECT a.id,prizes_name,b.type_name,prizes_num,prizes_desc,prizes_img,status FROM rank_prizes_info a,rank_prizes_type b where a.prizes_type=b.id) a left join (SELECT pi_id,count(pi_id) num FROM rank_prizes_record GROUP BY pi_id) b on a.id=b.pi_id where a.status=1";
		$info=parent::listsBySql($sql,10);
		//$info=$prizesInfo->query($sql);
		$this->assign('info',$info);
		$this->display();
	}
	//奖品类型管理
	public function prizes_type(){
		$prizesType=M('prizesType');
		$type=$prizesType->select();
		$this->assign('type',$type);
		$this->display();
	}
	//领奖人员管理
	public function prizes_user(){
		$userInfo=M('userInfo');
		$sql="select * from rank_user_info";
		$user=parent::listsBySql($sql,15);
		$this->assign('users',$user);
		$this->display();
	}
	//奖品类型增改
	public function prizes_type_mod(){
		$j['success']=false;
		$j['msg']='保存失败';
		$prizesType=M('prizesType');
		$id=I('id');
		$data['type_name']=I('type_name');
		if(!empty($id)){
			$results =$prizesType->where("id=".$id)->save($data);
		}else{
			$result =$prizesType->add($data);
		}
		if($result){
			$j['success']=true;
			$j['msg']='新增成功';
		}
		if($results){
			$j['success']=true;
			$j['msg']='修改成功';
		}
		$this->ajaxReturn($j);
	}
	//奖品类型删除
	public function prizes_type_delete($id){
		$j['success']=false;
		$j['msg']='保存失败';
		$prizesType=M('prizesType');
		$delete=$prizesType->delete($id);
		if($delete){
			$j['success']=true;
			$j['msg']='删除成功';
		}
		$this->ajaxReturn($j);
	}
	//奖品删除
	public function delete($id){
		$j['success']=false;
		$j['msg']='保存失败';
		$prizesInfo=M('prizesInfo');
		$data['status']=2;
		$delete=$prizesInfo->where("id=".$id)->save($data);
		if($delete){
			$j['success']=true;
			$j['msg']='删除成功';
		}
		$this->ajaxReturn($j);
	}
	//奖品修改
	public function update(){
		$j['success']=false;
		$j['msg']='保存失败';
		$prizesInfo=M('prizesInfo');
		$id=I('id');
		$id1=I('id1');
		$name=I('prizes_name');
		$type=I('prizes_type');
		$num=I('prizes_num');
		$desc=I('prizes_desc');
		if(!empty($name)){
			$data['prizes_name']=$name;
		}
		if(!empty($type)){
			$data['prizes_type']=$type;
		}
		if(!empty($desc)){
			$data['prizes_desc']=$desc;
		}
		if(!empty($num)){
			$audit1=$prizesInfo->where("id=".$id)->setInc('prizes_num',$num);
		}else{
			$audit=$prizesInfo->where("id=".$id1)->save($data);
		}
		if($audit){
			$j['success']=true;
			$j['msg']='修改成功';
		}
		if($audit1){
			$j['success']=true;
			$j['msg']='入库成功';
		}
		$this->ajaxReturn($j);
	}
	//奖品添加
	public function add(){
		$prizesInfo=M('prizesInfo');
		$tmp_name=$_FILES['prizes_img']['name'];
		if(!empty($tmp_name)){
			$tmp_file=$_FILES['prizes_img']['tmp_name'];
			$file_types=explode('.', $tmp_name);
			$file_type=$file_types[count ( $file_types ) - 1];
			$str=$file_types[count ( $file_types ) - 2];
			if(strpos($str," ")){
				$this->error("图片名称中存在空格，请删除空格后重新上传！");
			}
			if($file_type != "jpg" && $file_type != "JPG" && $file_type != "PNG" && $file_type != "png"){
				$this->error("图片格式只支持jpg、JPG、png、PNG格式");
			}
		    $strs =iconv("utf-8", "GB2312", $str);
		    if($strs==null){
		    	$file_name = $str . "." . $file_type;
		    }else{
		    	$file_name = $strs . "." . $file_type;
		    }
		    /*设置上传路径*/
	    	$savePath = '//10.78.1.85/www/ranking/Public/prizes/images/';

		    /*是否上传成功*/
		    if (! copy ( $tmp_file, $savePath . $file_name )){
		        $this->error ( '上传失败' );
		    }

			$data['prizes_name']=I('prizes_name');
			$data['prizes_type']=I('prizes_type');
			$data['prizes_img']=$str . "." . $file_type;
			$data['prizes_num']=I('prizes_num');
			$data['prizes_desc']=I('prizes_desc');
			$data['status']=1;
		    $result =$prizesInfo->add($data);
		    if(!$result){
		    	$this->error('新增失败');
		    }
		    $this->success('新增成功',"prizes_list");
		}else{
			echo "上传奖品图片不能为空！";
			exit();
		}
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
		for ($row = 2; $row <= $highestRow; $row++) { 
			for ($col = 0; $col < $highestColumnIndex; $col++) { 
                 $excelData[$row][] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
           } 
        } 
        return $excelData; 
	} 

	public function file(){
		if (! empty ( $_FILES ['file_name'] ['name'] )){
		    $tmp_file = $_FILES ['file_name'] ['tmp_name'];
		    $file_types = explode ( ".", $_FILES ['file_name'] ['name'] );
		    $file_type = $file_types [count ( $file_types ) - 1];
		     /*判别是不是.xls文件，判别是不是excel文件*/
		    if (strtolower ( $file_type ) != "xls" && strtolower ( $file_type ) != "xlsx"){
		        $this->error ( '不是Excel文件，重新上传' );
		    }
		    /*设置上传路径*/
		    $savePath = '//10.78.1.85/www/ranking/Public/upfile/Execl/';
		    /*以时间来命名上传的文件*/
		    $str = date ( 'h' ); 
		    $file_name = $str . "." . $file_type;
		     /*是否上传成功*/
		    if (! copy ( $tmp_file, $savePath . $file_name )){
		        $this->error ( '上传失败' );
		    }
		  	$res = $this->read ( $savePath . $file_name,$file_type );
		  	foreach($res as $k => $v){
		    	if($k>1){
		    		$data [$k-2] ['county_name'] = $v[0];
		    		$data [$k-2] ['user_name'] = $v[1];
		    		$data [$k-2] ['bill_id'] = $v[2];
		    		$data [$k-2] ['pos_name'] = $v[3];
		    		$data [$k-2] ['prize_name'] = $v[4];
		    		$data [$k-2] ['status'] = 1;
		    		$data [$k-2] ['create_date'] = date('Y-m-d');
		    	}
		    }
    		$result = M('userInfo')->addAll ( $data );
	        if (! $result){
	            $this->error ( '导入数据库失败' );
	        }else{
		    	$this->success('导入成功');
	        }
		}else{
			$this->error ( '请选择导入的Execl数据！' );
		}
	}
}
?>