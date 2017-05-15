<?php

namespace Form\Controller;
use Think\Controller;


class ActionController extends Controller {  

  public function form_design($form_id=0){
    $re = false;
    import('Org.Util.OciUtil');
    $oci=new \Org\Util\OciUtil();
    $oci->table='ls_form_info';
    $data['form_design'] = I('form_design');
    $data['form_objs']   = I('form_objs');
    $data['form_objs_table'] = I('form_objs_table');
    $oci->data=$data;
    $oci->where="id=".$form_id;        
    $re = $oci->update();
    return $re;
  }

	 
}

?>