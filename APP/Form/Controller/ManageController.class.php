<?php

namespace Form\Controller;

class ManageController extends BaseController {

  //主页
  public function index(){
     $this->display('form/form_index');
  }


  //表单设计菜单
  public function form_menu(){
    $this->display('form_design_menu');
  }

  //表单列表
  public function form_list(){
     $sql = "select * from ls_form_info where status=1";
     $forms = parent::listsSqlByls($sql,10);    
     $this->assign('forms',$forms);
     $this->display('form/form_list');
  }


  //删除信息
  public function form_delete($form_id=0){
    $json['success']=false;
    $json['msg']='删除失败';
    $m = M();
    $re = $m->table('ls_form_info')->where('id='.$form_id)->delete();        
    if($re){
      $json['success']=true;
      $json['msg']='删除成功';
    }
    $this->ajaxReturn($json);
  }

    //表单基础信息
    public function form_info($form_id=0){
      
      $m = M();
      if(IS_POST){
        $info['NAME']=$_POST['name'];
        $info['FORM_TYPE']=$_POST['form_type'];
        $info['VALID_TYPE']=$_POST['valid_type'];
        $info['table_name']=$_POST['table_name'];
        $info['STATUS']=$_POST['status'];
        $info['effective_date']=$_POST['effective_date'];
        $info['expire_date']=$_POST['expire_date'];
        $info['back_url']=$_POST['back_url'];
        $info['REQUESTER']=$_POST['requester'];
        $info['ANSWER']=$_POST['answer'];
          $info['theme']=$_POST['theme']; 
          

        if(!empty($form_id)){
          $where['id']=$form_id;   
          $re = $m->table('ls_form_info')->where($where)->save($info);
          $info['id']=$form_id;
        }else{
          $info['id']=date('YmdHis',time());
          $re = $m->table('ls_form_info')->add($info);  
        }
   
        if($re){
          $this->redirect('manage/form_info',array('form_id'=>$info['id']),2, '保存成功...');
        }else{
          $this->error('保存失败');
        }
      }else{
        if(isset($form_id)){
          $info = $this->form_get_info($form_id);
          $this->assign('info',$info);
        }
        $this->display('form/form_info');
      }
    }

    //数据表格设计
    public function form_table_design($form_id=0){
      $m = M();
      if(IS_POST){
          $info['table_name']=$_POST['table_name'];
          $info['table_json']=$_POST['table_json'];
          $info['form_id']=$_POST['form_id'];
            
          $flag = parent::create_table($info['table_name'],json_decode($info['table_json']));          
          if(!($flag===false)){
            if(!empty($id)){
              $where['id']=$form_id;   
              echo '修改';
              $re = $m->table('ls_form_table')->where($where)->save($info);
            }else{        
              echo '新增';   
              $info['id']=date('YmdHis',time());
              $re = $m->table('ls_form_table')->add($info);
            }
          }     

          if($re){
            $this->redirect('manage/form_table_design',array('form_id'=>$form_id),2, '创建成功...');
          }else{
            $this->error('创建失败');
          }
      }else{
        $info = self::form_get_info($form_id);
        $this->assign('info',$info);

        $sql = "select * from ls_form_table where status=1 and form_id=".$form_id." order by modify_date desc";
        $his = $m->query($sql);
        $this->assign('his',$his);

        $this->display('form/form_table_design');
      }
    }

    //数据表存储
    public function form_table($form_id=0,$m=0){
      $m = M();
      $info = $this->form_get_info($form_id);
      $this->assign('info',$info);

      $sql = "select * from ls_form_table where status=1 and form_id=".$form_id." order by modify_date desc";
      $his = $m->query($sql);
      $this->assign('his',$his);

      $this->display('form/form_table'); 
    }

    //表是否已经存在
    public function form_table_exists($table_name=''){
      $re =parent::table_exists($table_name);
      $json['success'] = $re;
      $this->ajaxReturn($json);
    }

    //表单设计
    public function form_design($form_id=0){
      if(IS_POST){        
        if($form_id>0){           
            //先备份下上次的设计
            $sql = " update ls_form_info set form_design_bak=form_design where id=".$form_id;
            $m = M();
            $m->execute($sql);

            //再备份一份到 ls_form_info_his 表
            $d = $m->table('ls_form_info')->where('id='.$form_id)->find();
            $m->table('ls_form_info_his')->add($d);

            //保存设计， 因为存在乱码情况所以才使用这种方式暂时替代
            $re = R('Action/form_design',array('form_id'=>$form_id));
            if($re){
              $this->success('保存成功');
            }else{
              $this->error('保存失败');
            }
        }else{
          $this->error('未找到表单基础信息，先创建基础表单信息');
        }
      }else{
        $info = $this->form_get_info($form_id);
        $this->assign('info',$info);
        $this->display('form/form_design');
      }
    }

    //表单赋权
    public function form_grant($form_id=0){
      if(IS_POST){
        $msg = "";
        $roleIds = I('roleIds');
        $userIds = I('userIds'); 
      
        $m = M();
        import('Org.Util.OciUtil');
        $oci=new \Org\Util\OciUtil();        
         
        //删除表单历史保存的角色
        $sql = "delete from ls_form_role_right where form_id=".$form_id;
        $re = $m->execute($sql);

        //添加角色
        $oci->table='ls_form_role_right';
        $d['id']=parent::seqId();
        $d['form_id']=$form_id;
        $d['role_id']=implode(',',$roleIds);
        $oci->data=$d;
        if($oci->insert()) $msg="角色保存成功";

        //删除表单历史保存的用户
        $sql  = "delete from ls_form_users where form_id=".$form_id;
        $re = $m->execute($sql);
        //添加用户
        $oci->table='ls_form_users';
        $u['id']=parent::seqId();
        $u['form_id']=$form_id;
        $u['oper_id']=implode(',',$userIds);        
        $oci->data=$u;
        if($oci->insert()) $msg.=" , 用户保存成功";

        if(empty($msg)){
          $this->error('权限更改失败');
        }else{
          $this->success($msg);
        }        
      }else{
        //所有角色
        $xtgl  = A('Xtgl/Base');
        $sysRoles = $xtgl->sysRoles();

        $this->assign('sysRoles',$sysRoles);

        //拥有角色
        $formRoles = $xtgl->formRoles($form_id);
        $this->assign('formRoles',$formRoles);

        //拥有用户
        $formUsers = $xtgl->formUsers($form_id);
        $this->assign('formUsers',$formUsers);

        $info = $this->form_get_info($form_id);
        $this->assign('info',$info);
        $this->display('form/form_grant');
      }
    }

    //赋权用户检索
    public function form_grant_query_oper(){
      $json['success']=false;
      $json['msg']='未找到用户信息';
      $value = I('value');
      $ext_value = I('ext_value');      
      if(!empty($value)){
        $where['oper_name']=array('like',"%".$value."%");
      }
      if(!empty($ext_value)){
        $ins = explode(',',$ext_value);
        if(is_array($ins)){
          $where['oper_login_code']=array('in',$ins);
        }
      }
      $m = M();
      $where['_logic'] = 'OR';
      $res = $m->table('mz_user.t_sys_oper')->where($where)->select();
      // echo $m->getLastSql();

      if(count($res)>0){
        $html = '';
        foreach ($res as $key => $re) {
          $html.="<li id='".$re['OPER_ID']."'>".$re['OPER_LOGIN_CODE']." ".$re['OPER_NAME']."</li>";
        }
        $json['msg']=$html;
        $json['success']=true;
      }
      $this->ajaxReturn($json);
    } 

    //表单部署
    public function form_publish(){
      $this->display('form/form_publish');
    }
  

    //表单预览SQL验证
    public function form_proview_valid($sql=null,$val=null){
      $json['success']=true;
      if(is_string($sql) && isset($val)){
        $esql_tmp = str_replace('?',$val,$sql);
        $esql = "select count(1) as cnt from (".$esql_tmp.")";
        $m = M();
        $re = $m->query($esql);
        if($re[0]['CNT']>0){
          $json['success']=false;
        }
      }
      $this->ajaxReturn($json);
    }

    //表单预览
    public function form_priview($form_id=0,$id=0){
       $info = $this->form_get_info($form_id);
    
       if(IS_POST && $info){
          //获取表单对应存储表的表字段结构
          import('Org.Util.OciUtil');
          $oci=new \Org\Util\OciUtil();
          $fields = parent::getFields($info['TABLE_NAME']);
           
          $oci->table = $info['TABLE_NAME'];  
          if(is_array($_POST)){
            foreach ($_POST as $key => $val) {
              if(!empty($fields[$key])){
                $d[$key]=$val;
              }
            }
          }      
          if(empty($d['id'])){
            $d['id']=parent::seqId();
            $oci->data=$d;
            $re = $oci->insert();
          }else{

            $clob_flag = parent::clob_flag($fields);
            unset($d['id']);
            if($clob_flag){   //当表中存在clob blob 等字段时候才使用oci 操作方式更新表
              $oci->data=$d;
              $oci->where='id='.$id;
              $re = $oci->update();
            }else{
              $m = M();
              $re = $m->table($info['TABLE_NAME'])->where('id='.$id)->save($d);
            }
          }

          if($re){
            $this->success('保存成功');
          }else{
            $this->error('保存失败');
          }

       }else{

        //数据信息查询编辑
        if($id>0){
          $m = M();
          $form_data = $m->table($info['TABLE_NAME'])->where('id='.$id)->find();
          $this->assign('form_data',json_encode($form_data));
        }

        $this->assign('info',$info);
        $this->display('form/form_priview');
       }
    }


    //获取表单信息
    public function form_data_list($form_id=0){
       $info = $this->form_get_info($form_id);
       $this->assign('info',$info);
       //获取字段
       $fields = parent::getFields($info['TABLE_NAME']);
 
       $this->assign('fields',$fields);

       //获取数据
       $sql = "select * from ".$info['TABLE_NAME']; 
      
       $datas = parent::listsSqlByls($sql,10);

       $this->assign('datas',$datas);
       $this->display('form/form_data_list');
    }

    //获取表单信息
    public function form_get_info($id=0){
      if(isset($id)){
          $m = M();
          $where['id']=$id;
          $info = $m->where($where)->table('ls_form_info')->find();
          if($info){
            return $info;
          }
      }
      return false;
    }

    //
    public function form_objs_view($rpt_id=0,$form_url=''){
      $this->display('form/form_objs_view');
    }

    //部署到当前菜单下的受理模块
    public function form_objs($menu_id=0){
      $this->display('form/form_objs');
    }

}
?>