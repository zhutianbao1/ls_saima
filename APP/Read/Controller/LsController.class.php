<?php

namespace Read\Controller;
use Admin\Controller\AdminController;

class LsController extends AdminController {


	public function index(){
		$this->display();
	}

	//根据手机号码获取账号信息
	public function get_oper_info($bill_id='13867060919'){
		$m = M();
		$w['oper_status']=1;
		$w['oper_login_code']=$bill_id;
		$info = $m->db(1,'LS_CONFIG')->table('mz_user.t_sys_oper')->where($w)->find();
		return $info;
	}
    

}

?>