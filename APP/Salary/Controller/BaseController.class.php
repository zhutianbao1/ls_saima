<?php

namespace Salary\Controller;

use Admin\Controller\AdminController;

 
// Home 模块基类、继承于Admin模块基类获取系统共享方法


class BaseController extends AdminController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){
		$CONTROLLER_NAME = $Think.CONTROLLER_NAME;
		$ACTION_NAME  = $Think.ACTION_NAME;	
 	}





	//短信发送	
	
    public function sms($dest_bill,$content){
        $rank = M('book');
        $data['ID']='my_seq_sms_job.nextval';
        $data['BILL_ID']=$dest_bill;
        $data['SMS']=$content;
        $insert = $rank->db(1,'LS_CONFIG')->orcAdd('ls_sms_job',$data);
        if($insert){
            return true;
        }else{
            return false;
        }
    }
    


    

}
?>