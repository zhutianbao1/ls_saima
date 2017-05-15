<?php

namespace Form\Controller;

class ExtController extends BaseController {

  //直销员工资料
  public function ext_zx_person(){ 
    if(IS_POST){
    	$id = $_POST['id'];
    	$d['employee_id']=$_POST['employee_id'];
    	$d['employee_name']=$_POST['employee_name'];
    	$d['sex']=$_POST['sex'];
    	$d['id_card']=$_POST['id_card'];
    	$d['acc_id']=$_POST['acc_id'];
    	$d['diploma']=$_POST['diploma'];
    	$d['formal']=$_POST['formal'];
    	$d['ru_date']=array('exp',"to_date('".$_POST['ru_date']."','yyyy-mm-dd')"); 
    	$d['isback']=intval($_POST['isback']);
    	if(!empty($_POST['li_date'])){
    		$d['li_date']=array('exp',"to_date('".$_POST['li_date']."','yyyy-mm-dd')"); 
    	}
    	$d['phone']=$_POST['phone'];
    	$d['address']=$_POST['address'];
    	$d['commend']=$_POST['commend'];
    	$d['county_code']=$_POST['county_code'];
    	$d['group_id']=$_POST['group_id'];
    	$d['super_name']=$_POST['super_man'];
    	$d['employee_id']=$_POST['employee_id'];
    	$oci->data=$d;
    	$m = M();
    	if(empty($id)){
    		//新增
    		$d['id']=array('exp',"mz_user.my_seq_zx_person.nextval");
    		$re = $m->table('mz_user.t_zx_person_record')->add($d);

    	}else{
    		//编辑
    		$oci->where="id=".$id;    
    		$re = $m->table('mz_user.t_zx_person_record')->save($d);
    	}
    	if($re){
    		$this->success('保存成功');
    	}else{
    		$this->error('保存失败');
    	}
    }else{
    	$this->display();
    }
  }

  //重入司判断
  public function ext_chongrusi($card_id=''){
  	$json['success']= false;
  	// $where['formal']='离职';
  	$where['id_card']=$card_id;
  	$m = M();
  	$re = $m->table('mz_user.t_zx_person_record')->where($where)->find();
  	if($re){
  		$json['success']=true;
  		$json['obj']=json_encode($re);
  	}
  	$this->ajaxReturn($json);
  }
}
?>