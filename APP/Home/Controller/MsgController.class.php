<?php

namespace Home\Controller;

class MsgController extends BaseController {

	public function index(){

		parent::isLogin();

		$msg = M('ConfigMsg');
		$month = I('month');
		$sql = "select a.fst_type,a.sec_type,a.name,a.id cid,b.*  from rank_config_curr a, rank_config_msg b where a.id=b.config_id";
		$sql.=' order by a.id,b.id desc';
		if(!empty($month) && $month!='year'){
			$sql=" select * from (".$sql.")  where tag='".$month."'";
		}

		$msgs = parent::listsSqlByls($sql);
		$this->assign('msgs',$msgs);

		$tag_sql="SELECT tag FROM rank_config_msg GROUP BY tag ORDER BY tag DESC";
		$tag=$msg->query($tag_sql);
		$this->assign('tag',$tag);
		$this->display();
	}

	public function msg_list(){
		$this->redirect('index');
	}

	public function msg_delete($id=0){
		$msg = M('ConfigMsg');
		$res = $msg->delete($id);
		$this->redirect('index');
	}

	public function msg_new($id=0){
			 
		parent::intiParams();
		$msg_config = M('ConfigMsg');
		$msg = $msg_config->find($id);
		$config_id=$msg['CONFIG_ID'];
		if(intval($id)>0){
			$this->assign('config_id',$config_id);
		}
		$this->assign('msg',$msg);

		//条线
		$configs = parent::ConfigList();
		foreach ($configs as $key => $val) {
			$rank_configs[$val['FST_TYPE'].'-'.$val['SEC_TYPE'].'-'.$val['NAME']]=$val['ID'];
		}

		$this->assign('rank_configs',$rank_configs);
		$this->display();
	}

	public function add($id=0){
		$msg=M('configMsg');
		import('Org.Util.OciUtil');
        $oci=new \Org\Util\OciUtil();
        //$time=date('Y-m-d h:m:s',time());	

        $oci->table='rank_config_msg';
        $con_save = $msg->find($id);
		if(empty($con_save)){
		 	$data['config_id']=I('config_id');
		 	$data['tag']=I('tag');
		 	$data['title']=I('title');
		 	$data['msg']=$_POST['msg'];
		 	$data['oper_id']='';
		 	$data['oper_name']=I('oper_name');
		 	//$data['remark']='';
		 	$data['status']='1';

		 	$oci->data = $data;	
		 	if($data['tag']==null || $data['tag'] ==''){
		 		$this->error ( '日期不能为空!' );
		 	}else if($data['title']==null || $data['title'] ==''){
		 		$this->error ( '标题不能为空!' );
		 	}else if($data['oper_name']==null || $data['oper_name'] ==''){
		 		$this->error ( '管理人不能为空!' );
		 	}else{
			 	if($oci->insert()){
			 		$sql = "update rank_config_msg set id=rank_seq.nextval,create_date=sysdate,modify_date=sysdate where config_id=".$data['config_id']." and tag=".$data['tag']." and title='".$data['title']."'";
			 		$insert = $msg->execute($sql);
			 		if($insert){
			 			$this->success('保存成功','index');
			 		}else{
			 			$this->error ('保存失败');
			 		}
			 	}
		 	}
		}else{
		 	$data['config_id']=I('config_id');
		 	$data['tag']=I('tag');
		 	$data['title']=I('title');
		 	$data['msg']=$_POST['msg'];
		 	$data['oper_name']=I('oper_name');
		 	$oci->data = $data;	
        	$oci->where="id='".$id."'";
        	if($oci->update()){
		 		$sql = "update rank_config_msg set modify_date=sysdate where config_id=".$data['config_id']." and tag=".$data['tag']." and title='".$data['title']."'";
		 		$insert = $msg->execute($sql);
		 		if($insert){
			 		$this->success('修改成功','index');
		 		}else{
			 		$this->error ('修改失败');
		 		} 
		 	}
		}
	}

	public function msg_priview($id=0){
		$rank_msg = M('configMsg');
		$msg = $rank_msg->find($id);
		$this->assign('msg',$msg);

		parent::viewLog();
		$this->display();
	}

}

?>