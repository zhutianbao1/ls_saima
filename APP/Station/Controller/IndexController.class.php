<?php

namespace Station\Controller;

class IndexController extends BaseController {
	
	/**
	 * 基类控制器初始化
	 */
	protected function _initialize(){	
    //跳转到登录页
    if(!isset($_SESSION['node'])||empty($_SESSION['node'])){
           redirect("/ranking/Station/Login/login",0);
    }
 	}

  //登录
    public function login(){   
        if(IS_GET){
           ob_clean();
        }
        if(IS_POST){    
            $oper_login_code = I('oper_login_code');
            $bill_id = I('bill_id');
            $j['success']=false;
            $j['msg']='登录失败'; 
            $m = M();
            $w['node_login_code']=$oper_login_code;
            $w['bill_id']=$bill_id;
            $w['status']=1;
            $user = $m->table('station_flow_nodes')->where($w)->find();       
            if($user){
                $j['success']=true;
                $j['msg']='登录成功';
                session('node',$user); 
            }else{
                $j['msg']='登录失败，账号或密码错误';
            }
            $this->ajaxReturn($j);      
        }
    }
  

    //登出
    public function logout(){
        session('node',null);
        if(!isset($_SESSION['node'])){
            $msg="1";
        }
        $this->ajaxReturn($msg);
    }

    //工单流程跟踪
    public  function flows_track(){
    $m=M();
        $sql="select a.*,b.* from station_application a,( SELECT ROW_NUMBER() OVER(PARTITION BY item_id 
          ORDER BY node_date DESC) rn, station_flow_step.* FROM station_flow_step  ) b where b.rn=1 
           and a.item_id=b.item_id and a.node_bill_id='%s' order by appliy_date desc" ;
        $items=$m->query($sql,$_SESSION['node']['BILL_ID']);
        $this->assign('items',$items);
        $this->display();
    }

    //工单流程跟踪
    public function  flows_track_list(){
        $item_id=I('item_id');
        $m=M();
        $w['item_id']=$item_id;
        $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
        $this->assign('flows',$flows);
        $this->display();
    }

    //节点配置
    public function node_config(){
        $m=M();
        $node_login_code=I('node_login_code');
        $bill_id=I('bill_id');
        $sql="select * from station_flow_nodes where status='1' ";
         
        if(!empty($node_login_code)){
            $sql.="and node_login_code='".$node_login_code."'";
        }

        if(!empty($bill_id)){
            $sql.="and bill_id='".$bill_id."'";
        }

        $nodes=parent::listsSqlByls($sql,20); 
        $this->assign('nodes',$nodes); 
        $this->assign('node_login_code',$node_login_code);
        $this->assign('bill_id',$bill_id);   
        $this->display();
    }



    //节点人员删除
    public function node_user_del(){
        $node_login_code=I('node_login_code');
        $bill_id=I('bill_id');

        $m=M();
        $w['node_login_code']=$node_login_code;
        $w['bill_id']=$bill_id;
        $d['status']='0';
        $flag=$m->table('station_flow_nodes')->where($w)->save($d);
        if($flag){
            $this->success('删除成功!');
        }else{
            $this->error('删除失败!');
        }
    }

    //节点人员信息修改
    public function node_user_mod(){
        if(IS_GET){
            $node_login_code=I('node_login_code');
            $bill_id=I('bill_id');
            $m=M();
            $w['node_login_code']=$node_login_code;
            $w['bill_id']=$bill_id;

            $node=$m->table('station_flow_nodes')->where($w)->find();
            $this->assign('node',$node);
            $this->display();
        }

        if(IS_POST){
            $old_code=I('old_code');
            $old_bill=I('old_bill'); 

            $node_login_code=I('node_login_code');
            $node=I('node_name');
            $tem=explode('|', $node);
            $node_no=$tem[0];
            $node_name=$tem[1];
            $node_username=I('node_username');
            $bill_id=I('bill_id');
            $county_code=I('county_code');
            $net_type=I('net_type');

            $data['node_no']=$node_no;
            $data['node_name']=$node_name;
            $data['node_login_code']=$node_login_code;
            $data['node_username']=$node_username;
            $data['bill_id']=$bill_id;
            $data['node_dept']=$node_name;
            $data['status']='1';

            if(!empty($county_code)){
                $data['county_code']=$county_code;
            }
            if(!empty($net_type)){
                $data['net_type']=$net_type;
            }

            $w['node_login_code']=$old_code;
            $w['bill_id']=$old_bill;
            $flag=$m->table('station_flow_nodes')->where($w)->save($data);

            if($flag){
                $this->success('数据保存成功!');
            }else{
                $this->error('数据保存失败!');
            }
        }
    }


    //工单删除
    public function item_del(){
        if(IS_GET){
            $m=M();
            $w['next_node_no']=$_SESSION['node']['NODE_NO'];
            $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
            $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
            $w['cur_node_result']='0';
            $w['status']='0';
             /**
            $sql=" SELECT * FROM station_flow_step where next_node_no='".$_SESSION['node']['NODE_NO']."' and   
            next_login_code='".$_SESSION['node']['NODE_LOGIN_CODE']."'  and  
            next_bill_id='".$_SESSION['node']['BILL_ID']."'  and cur_node_result='0' and status='0' "; 
            $items=$m->query($sql);
            **/
            $items=$m->table('station_flow_step')->where($w)->select();
            $this->assign('items',$items); 
            $this->display();
        }

        if(IS_POST){
            $item_id=I('item_id');
            $m=M();
            $d['status']='99';
            $d['item_status']='已删除';
            $d['item_status_no']='99';
            $w['item_id']=$item_id;
            $flag=$m->table('station_application')->where($w)->save($d);
            if($flag){
                $da['status']='99';
                $wh['item_id']=$item_id;
                $wh['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                $wh['next_bill_id']=$_SESSION['node']['BILL_ID'];
                $wh['status']='0';
                $flag=$m->table('station_flow_step')->where($wh)->save($da); 
                if($flag){
                    $msg='删除成功!';
                }else{
                    $msg='删除失败!';  
                }
            }else{
                $msg="删除失败!";
            }
            $this->ajaxReturn($msg);
        }
    } 






    //待办首页
    public function index(){
        $m=M();
        $w['next_node_no']=$_SESSION['node']['NODE_NO'];
        $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
        $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
        $w['status']='0';
        /**
        $sql=" SELECT * FROM station_flow_step where next_node_no='".$_SESSION['node']['NODE_NO']."' and   
        next_login_code='".$_SESSION['node']['NODE_LOGIN_CODE']."'  and  
        next_bill_id='".$_SESSION['node']['BILL_ID']."' and status='0' "; 
        $items=$m->query($sql);
        **/
        $items=$m->table('station_flow_step')->where($w)->select();
        $this->assign('items',$items); 
        /**
        if($_SESSION['node']['NODE_NO']=='1'){
            $this->display('index');
        }elseif($_SESSION['node']['NODE_NO']=='2'){
            $this->display('index2');
        }elseif($_SESSION['node']['NODE_NO']=='3'){
            $this->display('index3');
        }elseif($_SESSION['node']['NODE_NO']=='4'){
            $this->display('index');
        }elseif($_SESSION['node']['NODE_NO']=='5'){
            $this->display('index5');
        }elseif($_SESSION['node']['NODE_NO']=='6'){
            $this->display('index6');
        }elseif($_SESSION['node']['NODE_NO']=='7'){
            $this->display('index7');     
        }elseif($_SESSION['node']['NODE_NO']=='8'){
            $this->display('index8');
        }else {
            echo '未获得您的配置信息';
        }
        **/
        $this->display('index');
    }

    //流程申请
    public function station_application(){ 
        $item_id_tep=date('Ymd');
        $m=M();
        /**
        $sql="select count(item_id) num from station_application where item_id like '".$item_id_tep."%' ";
        $list=$m->query($sql);
        $num=$list[0]['NUM'];
        **/
        $w['item_id'] = array('like',$item_id_tep.'%');
        $item_num=$m->table('station_application')->where($w)->count();
        $item_num=intval($item_num); 
       
        //生成工单号
        if($item_num>=0&&$item_num<9){      
            $num1='000'.strval($item_num+1);
        }elseif($item_num>=9&&$item_num<99){
            $num1='00'.strval($item_num+1);
        }elseif($item_num>=99){
            $num1='0'.strval($item_num+1); 
        }
        $item_id=$item_id_tep.$num1;
        $this->assign('item_id',$item_id);
        $m=M();
        //获得节点信息
        $w['node_no']='2';
        $w['county_code']=$_SESSION['node']['COUNTY_CODE'];
        $w['status']='1';
        $nodes=$m->table('station_flow_nodes')->where($w)->select();
        $this->assign('nodes',$nodes);  
        $this->display();
    } 



    //处理申请表单
    public function  application(){
        $m=M();
        $data['item_id']=I('item_id');
        $data['node_login_code']=I('node_login_code');
        $data['appliy_name']=I('appliy_name');
        $data['node_bill_id']=i('bill_id');
        $data['county_name']=I('county_name');
        $data['project_name']=I('project_name');
        $data['station_name']=I('station_name');
        $data['area_name']=I('area_name');
        $data['station_type']=I('station_type');
        $data['network_type']=I('network_type');
        $data['station_con']=I('station_con');
        $data['con_target']=I('con_target');
        $data['jindu']=I('jindu');
        $data['weidu']=I('weidu');
        $data['network_syn']=I('network_syn');
        $data['network_pro']=I('network_pro');
        $data['appliy_date']=date('Y-m-d H:i:s');
        $data['remark']=I('remark');
        $data['status']='1';
        $data['item_status']='待入库';
        $data['item_status_no']='0';
        $flag=$m->table('station_application')->add($data);
        if($flag){ 
            $da['item_id']=I('item_id');
            $da['appliy_type']='1';
            $da['appliy_type_name']='无线网基站';
            $da['cur_node_step']=$_SESSION['node']['NODE_NO'];
            $da['cur_node_name']=$_SESSION['node']['NODE_NAME'];
            $da['cur_node_userid']=$_SESSION['node']['BILL_ID'];
            $da['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
            $da['cur_node_result']='1';
            $da['cur_node_suggest']='提出申请,请审批';      
            $next_node=I('next_node');
            $next_nodes=explode('|',$next_node);
            $da['next_node_no']=$next_nodes[0];
            $da['next_node_name']=$next_nodes[2];
            $da['next_login_code']=$next_nodes[1];
            $da['next_node_username']=$next_nodes[3];
            $da['next_bill_id']=$next_nodes[4];          
            $da['node_date']=date('Y-m-d H:i:s');
            $da['status']='0';
            $da['step_no']='1';
            $flag=$m->table('station_flow_step')->add($da);
            if($flag){
                $this->success('数据保存成功!等待下一步审核!','index');
            }else{
                $this->error('数据保存出错!');  
            }              
        }else{
            $this->error('数据保存出错!');
        } 
    }




    //工单审核节点1
    public function review_item1(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            $county_name=$items[0]['COUNTY_NAME'];
            $station_type=$items[0]['STATION_TYPE'];

            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);
            //获得节点信息
            $wh['node_no']='2';
            $wh['county_code']=$_SESSION['node']['COUNTY_CODE'];
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $tab=I('tab');
            $this->assign('tab',$tab);
            $this->display();
        }

        if(IS_POST){     
            $m=M();
            $item_id=I('item_id');
            $data['item_id']=$item_id;
            $data['project_name']=I('project_name');
            $data['station_name']=I('station_name');
            $data['area_name']=I('area_name');
            $data['station_type']=I('station_type');
            $data['network_type']=I('network_type');
            $data['station_con']=I('station_con');
            $data['con_target']=I('con_target');
            $data['jindu']=I('jindu');
            $data['weidu']=I('weidu');
            $data['network_syn']=I('network_syn');
            $data['network_pro']=I('network_pro');
            $data['appliy_date']=date('Y-m-d H:i:s');
            $data['remark']=I('remark');
            $data['status']='1';

            $wh['item_id']=$item_id;
            $flag=$m->table('station_application')->where($wh)->save($data);

            if($flag){ 
                $da['item_id']=$item_id;
                $da['appliy_type']='1';
                $da['appliy_type_name']='无线网基站';
                $da['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $da['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $da['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $da['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $da['cur_node_result']='1';
                $da['cur_node_suggest']='已修改,请审批';      
                $next_node=I('next_node');
                $next_nodes=explode('|',$next_node);
                $da['next_node_no']=$next_nodes[0];
                $da['next_node_name']=$next_nodes[2];
                $da['next_login_code']=$next_nodes[1];
                $da['next_node_username']=$next_nodes[3];
                $da['next_bill_id']=$next_nodes[4];          
                $da['node_date']=date('Y-m-d H:i:s');
                $da['status']='0';
                $da['step_no']='1';
                $flag=$m->table('station_flow_step')->add($da);

                if($flag){
                    $da2['status']='1';
                    $w['item_id']=$da['item_id'];
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';
                    $flag=$m->table('station_flow_step')->where($w)->save($da2); 
                    if($flag){
                        $this->success('数据保存成功!','index');
                    }else{
                        $this->error('数据保存出错!');  
                    }
                }else{
                    $this->error('数据保存出错!');  
                }              
            }else{
                $this->error('数据保存出错!');
            }
        }
    }



    //添加勘察报告
    public function review_item1_1(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            $county_name=$items[0]['COUNTY_NAME'];
            $station_type=$items[0]['STATION_TYPE'];
            //流程信息
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);


            //获得节点信息
            $wh['node_no']='4';
            $wh['county_code']=$county_name;
            $wh['net_type']=$station_type;
            $wh['status']='1';

            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){     
            $m=M(); 
            $result=fileupload(); 
            if($result['msg']=='0'){
                $this->error('文件已存在!');
            }elseif($result['msg']=='1'){
                $filepath="/ranking/".$result['pathname'];
                $item_id=I('item_id');
                $data['item_id']=$item_id;
                $data['files_path']=$filepath;

                $wh['item_id']=$item_id;
                $flag=$m->table('station_application')->where($wh)->save($data);

                if($flag){ 
                    $da['item_id']=I('item_id');
                    $da['appliy_type']='1';
                    $da['appliy_type_name']='无线网基站';
                    $da['cur_node_step']=$_SESSION['node']['NODE_NO'];
                    $da['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                    $da['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                    $da['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                    $da['cur_node_result']='1';
                    $da['cur_node_suggest']='已修改,请审批';      
                    $next_node=I('next_node');
                    $next_nodes=explode('|',$next_node);
                    $da['next_node_no']=$next_nodes[0];
                    $da['next_node_name']=$next_nodes[2];
                    $da['next_login_code']=$next_nodes[1];
                    $da['next_node_username']=$next_nodes[3];
                    $da['next_bill_id']=$next_nodes[4];          
                    $da['node_date']=date('Y-m-d H:i:s');
                    $da['status']='0';
                    $da['step_no']='1';
                    $flag=$m->table('station_flow_step')->add($da);

                    if($flag){
                        $da2['status']='1';
                        $w['item_id']=$da['item_id'];
                        $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                        $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                        $w['status']='0';
                        $flag=$m->table('station_flow_step')->where($w)->save($da2); 
                        if($flag){
                            $this->success('数据保存成功!','index');
                        }else{
                            $this->error('数据保存出错!');  
                        }
                    }else{
                        $this->error('数据保存出错!');  
                    }              
                }else{
                    $this->error('数据保存出错!');
                }
            }elseif($result['msg']=='2'){
                $this->error('文件格式不允许上传!');
            }elseif($result['msg']=='4'){
                $this->error('您未选择要上传的文件!');
            }else{
                $this->error('文件上传出错!');
            }
        }
    }


    //(3)工单审核片区审核(片区主管)
    public function review_item2(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;

            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);

            //获得流程信息
            /**
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc  ";
            $flows=$m->query($sql);
            **/
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);

            //申请人县市
            $county_name=$items[0]['COUNTY_NAME'];
            //获得节点信息

            /**
            if($flows[0]['STEP_NO']=='1'){
                $sql="select * from station_flow_nodes 
                    where (node_no='1' and county_code='%s' and node_username='%s') or node_no='3' ";
            }
            //$nodes=$m->query($sql,$county_name,$items[0]['APPLIY_NAME']);
            **/
            $wh['node_no']='1';
            $wh['county_code']=$county_name;
            $wh['node_username']=$items[0]['APPLIY_NAME'];
            $map['_complex'] = $wh;
            $map['node_no']='3';
            $map['_logic'] = 'or';
            $nodes=$m->table('station_flow_nodes')->where($map)->select();

            $this->assign('nodes',$nodes);
            $this->display('Index/review_item2');
        }

        if(IS_POST){     
            $m=M();
            $item_id=I('item_id');
            $data['item_id']=$item_id;
            $data['appliy_type']='1';
            $data['appliy_type_name']='无线网基站';
            $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
            $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
            $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
            $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
            $cur_node_result=I('cur_node_result');
            $cur_node_results=explode('|',$cur_node_result);
            $data['cur_node_result']=$cur_node_results[0];
            $data['cur_node_suggest']=I('review_suggest');
            $next_node=I('next_node');
            $next_nodes=explode('|', $next_node);
            $data['next_node_no']=$next_nodes[0];
            $data['next_login_code']=$next_nodes[1]; 
            $data['next_node_name']=$next_nodes[2];
            $data['next_node_username']=$next_nodes[3];
            $data['next_bill_id']=$next_nodes[4];
            $data['node_date']=date('Y-m-d H:i:s');
            $data['status']='0';
            $data['remark']=I('remark2');
            $data['step_no']='2';
            $flag=$m->table('station_flow_step')->add($data);
            if($flag){
                $da['status']='1';
                $w['item_id']=$item_id;
                $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                $w['status']='0';
                $flag2=$m->table('station_flow_step')->where($w)->save($da); 
                if($flag2){
                    $this->success('审核成功!','index');
                }else{
                    $this->error('审核失败!');
                }
            }else{
                $this->error('审核失败!');

            }
        }
    }


    //(4)工单审核节点4规划审核
    public function review_item3(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();

            //获得工单信息
            $items=$m->table('station_application')->where("item_id='".$item_id."'")->select();
            $this->assign('items',$items);
            //获得流程信息
            /**
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc ";
            $flows=$m->query($sql);
            **/
            $w['item_id']=$item_id;
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);
            //获得节点信息
            $county_name=$items[0]['COUNTY_NAME'];
            /**
            $sql="select * from station_flow_nodes where node_no='1' and county_code='".$county_name."' 
            and node_username='".$items[0]['APPLIY_NAME']."' ";
            $nodes=$m->query($sql);
            **/
            $wh['node_no']='1';
            $wh['county_code']=$county_name;
            $wh['node_username']=$items[0]['APPLIY_NAME'];
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){     
            $m=M();
            $item_id=I('item_id');
            $data['item_id']=$item_id;
            $data['appliy_type']='1';
            $data['appliy_type_name']='无线网基站';
            $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
            $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
            $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
            $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
            $cur_node_result=I('cur_node_result');
            $cur_node_results=explode('|',$cur_node_result);
            $data['cur_node_result']=$cur_node_results[0];
            $data['cur_node_suggest']=I('review_suggest');
            $next_node=I('next_node');
            $next_nodes=explode('|', $next_node);
            $data['next_node_no']=$next_nodes[0];
            $data['next_login_code']=$next_nodes[1]; 
            $data['next_node_name']=$next_nodes[2];
            $data['next_node_username']=$next_nodes[3];
            $data['next_bill_id']=$next_nodes[4];
            $data['node_date']=date('Y-m-d H:i:s');
            $data['status']='0';
            $data['remark']=I('remark2');
            $data['step_no']='3';
            $flag=$m->table('station_flow_step')->add($data);
            if($flag){
                $da['status']='1';
                $w['item_id']=$item_id;
                $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                $w['status']='0';
                $flag2=$m->table('station_flow_step')->where($w)->save($da); 
                if($flag2){
                    $this->success('审核成功!','index');
                }else{
                    $this->error('审核失败2!');
                }
            }else{
                $this->error('审核失败1!');
            }
        }
    }


    //设计院县市联系人
    public function review_item4(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);

            //获得报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);

            //获得流程信息
            /**
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc ";
            $flows=$m->query($sql);
            **/
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);


            //获得节点信息
            $station_type=$items[0]['STATION_TYPE'];
            /**
            $sql="select * from station_flow_nodes where node_no='5' and net_type='".$station_type."' ";
            $nodes=$m->query($sql);
            **/
            $wh['node_no']='5';
            $wh['net_type']=$station_type;
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){     
            $m=M();
            //工单编号
            $item_id=I('item_id');
            //基站类型
            $station_type=I('station_type');

            //设计院报告信息      
            $data2['item_id']=$item_id;
            $data2['device_factory']=I('device_factory');  
            $data2['bbb_no']=I('bbb_no');         
            $data2['board_no']=I('board_no');      
            $data2['rru_aisle_no']=I('rru_aisle_no');  
            $data2['rru_no']=I('rru_no');         
            $data2['xq_no']=I('xq_no');          
            $data2['xq_rru']=I('xq_rru'); 
            $data2['xq_no1']=I('xq_no1'); 
            $data2['xq_no2']=I('xq_no2'); 
            $data2['xq_no3']=I('xq_no3'); 
            $data2['tx_type']=I('tx_type'); 
            if($station_type=='宏站'){
                $data2['sgpt']=I('sgpt');            
                $data2['fgpt']=I('fgpt');            
                $data2['pt_totle']=I('pt_totle'); 
                $data2['zsbfy']=I('zsbfy');        
                $data2['zhg']=I('zhg');             
                $data2['power']=I('power');           
                $data2['tkbhh']=I('tkbhh');         
                $data2['hjpj']=I('hjpj');             
                $data2['wxsj']=I('wxsj');           
                $data2['tjsj']=I('tjsj');               
                $data2['jaf']=I('jaf');                
                $data2['sdyr']=I('sdyr');             
                $data2['zdpc']=I('zdpc');           
                $data2['mhf']=I('mhf');            
                $data2['zzxtf']=I('zzxtf');         
                $data2['jff']=I('jff');          
                $data2['twjcf']=I('twjcf');         
                $data2['jcjlf']=I('jcjlf');        
                $data2['twf']=I('twf');
                $data2['twjlf']=I('twjlf');        
                $data2['jzjdf']=I('jzjdf');       
                $data2['jzktf']=I('jzktf');          
                $data2['qtclf']=I('qtclf'); 
            }elseif($station_type=='室分'){
                $data2['fbtz']=I('fbtz');             
                $data2['mpmzj']=I('mpmzj');          
                $data2['fbxtmpmzj']=I('fbxtmpmzj');      
                $data2['rrujd']=I('rrujd');         
                $data2['rruwd']=I('rruwd');          
                $data2['kgpower']=I('kgpower');          
                $data2['tkqj']=I('tkqj');            
                $data2['jcsgf']=I('jcsgf');         
                $data2['sjf']=I('sjf');         
                $data2['jlf']=I('jlf');          
                $data2['tkcsf']=I('tkcsf');         
                $data2['dlf']=I('dlf');          
                $data2['twtjf']=I('twtjf'); 
            }elseif($station_type=='小微站'){
                $data2['zsbbhs']=I('zsbbhs'); 
                $data2['jzpzsgf']=I('jzpzsgf'); 
                $data2['ptfybhs']=I('ptfybhs'); 
                $data2['sjjlf']=I('sjjlf'); 
            }
                $data2['remark']=I('remark2'); 
                $data2['status']='1';

                $w['item_id']=$item_id;
                $lsit=$m->table('station_survey_report')->where($w)->select();

                if(empty($lsit)){
                    $flag2=$m->table('station_survey_report')->add($data2);
                }else{
                    $flag2=$m->table('station_survey_report')->where($w)->save($data2);
                }

                if($flag2){ 
                    $data['item_id']=$item_id;
                    $data['appliy_type']='1';
                    $data['appliy_type_name']='无线网基站';
                    $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                    $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                    $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                    $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                    $cur_node_result=I('cur_node_result');
                    $cur_node_results=explode('|',$cur_node_result);
                    $data['cur_node_result']=$cur_node_results[0];
                    $data['cur_node_suggest']=I('review_suggest');
                    $next_node=I('next_node');
                    $next_nodes=explode('|', $next_node);
                    $data['next_node_no']=$next_nodes[0];
                    $data['next_login_code']=$next_nodes[1]; 
                    $data['next_node_name']=$next_nodes[2];
                    $data['next_node_username']=$next_nodes[3];
                    $data['next_bill_id']=$next_nodes[4];
                    $data['node_date']=date('Y-m-d H:i:s');
                    $data['status']='0';
                    $data['remark']=I('remark');
                    $data['step_no']='4';

                    $flag1=$m->table('station_flow_step')->add($data);
                    if($flag1){
                        $da['status']='1';
                        $wh['item_id']=$item_id;
                        $wh['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                        $wh['next_bill_id']=$_SESSION['node']['BILL_ID'];
                        $wh['status']='0';
                        $flag=$m->table('station_flow_step')->where($wh)->save($da); 
                        if($flag){
                            $this->success('数据保存成功!','index');
                        }else{
                            $this->error('数据保存出错!');
                        }
                    }else{
                        $this->error('数据保存出错!');
                    }
                }else{
                    $this->error('数据保存出错!');
                }
        }
    }


    //设计院主管
    public function review_item5(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);

             //获得流程信息
            /**
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc ";
            $flows=$m->query($sql);
            **/
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);


            //申请单县市
            $county_name=$items[0]['COUNTY_NAME']; 
            //获得节点信息
            $wh['node_no']='2';
            $wh['county_code']=$county_name;
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){     
            $m=M();
            $item_id=I('item_id');
            $data['item_id']=$item_id;
            $data['appliy_type']='1';
            $data['appliy_type_name']='无线网基站';
            $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
            $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
            $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
            $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
            $cur_node_result=I('cur_node_result');
            $cur_node_results=explode('|',$cur_node_result);
            $data['cur_node_result']=$cur_node_results[0];
            $data['cur_node_suggest']=I('review_suggest');
            $next_node=I('next_node');
            $next_nodes=explode('|', $next_node);
            $data['next_node_no']=$next_nodes[0];
            $data['next_login_code']=$next_nodes[1]; 
            $data['next_node_name']=$next_nodes[2];
            $data['next_node_username']=$next_nodes[3];
            $data['next_bill_id']=$next_nodes[4];
            $data['node_date']=date('Y-m-d H:i:s');
            $data['status']='0';
            $data['step_no']='5';
            $data['remark']=I('remark');

            $flag2=$m->table('station_flow_step')->add($data);
            if($flag2){
                $da['status']='1';
                $w['item_id']=$item_id;
                $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                $w['status']='0';
                $flag=$m->table('station_flow_step')->where($w)->save($da); 
                if($flag){
                    $this->success('审核成功!','index');
                }else{
                    $this->error('审核失败!');
                }
            }else{
                $this->error('审核失败!');
            }
        }
    }


    //片区主管审核设计院提交的报告()
    public function review_item2_1(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $wh['item_id']=$item_id;
            $items=$m->table('station_application')->where($wh)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($wh)->select();
            $this->assign('reports',$reports);


            //获得流程信息
            /**
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc ";
            $flows=$m->query($sql);
            **/
            $flows=$m->table('station_flow_step')->where($wh)->order('node_date desc')->select();
            $this->assign('flows',$flows);


            //申请单县市
            $county_name=$items[0]['COUNTY_NAME']; 
            //申请人
            $appliy_name=$items[0]['APPLIY_NAME']; 
            //基站类型
            $station_type=$items[0]['STATION_TYPE'];

            //获得节点信息
            /**
            $sql="select * from station_flow_nodes where (node_no='3')    
            or (node_no='4' and county_code='".$county_name."' and net_type='".$station_type."') 
            or (node_no='5' and net_type='".$station_type."')";
            $nodes=$m->query($sql);
            **/

            $map['node_no&status']=array('3','1','_multi'=>true);
            $map['node_no&county_code&net_type&status'] =array('4',$county_name,$station_type,'1','_multi'=>true);
            $map['node_no&net_type&status'] =array('5',$station_type,'1','_multi'=>true);
            $map['_logic'] = 'or';
            $nodes=$m->table('station_flow_nodes')->where($map)->select();
            //dump($m->getLastSql());
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){     
            $m=M();
            $item_id=I('item_id');
            $data['item_id']=$item_id;
            $data['appliy_type']='1';
            $data['appliy_type_name']='无线网基站';
            $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
            $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
            $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
            $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
            $cur_node_result=I('cur_node_result');
            $cur_node_results=explode('|',$cur_node_result);
            $data['cur_node_result']=$cur_node_results[0];
            $data['cur_node_suggest']=I('review_suggest');
            $next_node=I('next_node');
            $next_nodes=explode('|', $next_node);
            $data['next_node_no']=$next_nodes[0];
            $data['next_login_code']=$next_nodes[1]; 
            $data['next_node_name']=$next_nodes[2];
            $data['next_node_username']=$next_nodes[3];
            $data['next_bill_id']=$next_nodes[4];
            $data['node_date']=date('Y-m-d H:i:s');
            $data['status']='0';
            $data['step_no']='6';
            $data['remark']=I('remark');

            $flag2=$m->table('station_flow_step')->add($data);
            if($flag2){
                $da['status']='1';
                $w['item_id']=$item_id;
                $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                $w['status']='0';
                $flag=$m->table('station_flow_step')->where($w)->save($da); 
                if($flag){
                    $this->success('审核成功!','index');
                }else{
                    $this->error('审核失败!');
                }
            }else{
                $this->error('审核失败!');
            }
        }
    }



    //规划审核设计院提交的报告()
    public function review_item3_1(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);

            //获得流程信息
            /**
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc ";
            $flows=$m->query($sql);
            **/
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);

            //申请单县市
            $county_name=$items[0]['COUNTY_NAME']; 
            //申请人
            $appliy_name=$items[0]['APPLIY_NAME']; 
            //基站类型
            $station_type=$items[0]['STATION_TYPE'];

            //获得节点信息
            /**
            $sql="select * from station_flow_nodes where (node_no='6') or 
            (node_no='1' and  node_username ='".$appliy_name."' and county_code='".$county_name."')
            or (node_no='4'   and county_code='".$county_name."'  and net_type='".$station_type."') 
            or (node_no='5'   and net_type='".$station_type."')";
            $nodes=$m->query($sql);
             **/

            $map['node_no&status']=array('6','1','_multi'=>true);
            $map['node_no&county_code&node_username&status'] =array('1',$county_name,$appliy_name,'1','_multi'=>true);
            $map['node_no&county_code&net_type&status'] =array('4',$county_name,$station_type,'1','_multi'=>true);
            $map['node_no&net_type&status'] =array('5',$station_type,'1','_multi'=>true);
            $map['_logic'] = 'or';
            $nodes=$m->table('station_flow_nodes')->where($map)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){     
            $m=M();
            //工单编号
            $item_id=I('item_id');
            //工单标签
            $item_type=I('item_type');
            if($item_type=='建设摸底库'){ 
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $cur_node_result=I('cur_node_result');
                $cur_node_results=explode('|',$cur_node_result);
                $data['cur_node_result']=$cur_node_results[0];
                $data['cur_node_suggest']=I('review_suggest');
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='2';
                $data['step_no']='7';
                $data['remark']=I('remark');
                $flag=$m->table('station_flow_step')->add($data);
                if($flag){
                    $da['status']='1';
                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';
                    $flag1=$m->table('station_flow_step')->where($w)->save($da);

                    if($flag1){
                        $d['item_type']=$item_type;
                        $wh['item_id']=$item_id;
                        $flag2=$m->table('station_application')->where($wh)->save($d);
                        if($flag2){
                            $this->success('审核成功!','index');
                        }else{
                            $this->error('审核失败!');
                        }
                    }else{
                        $this->error('审核失败!');
                    }
                }else{
                    $this->error('审核失败!');
                }
            }else{
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $cur_node_result=I('cur_node_result');
                $cur_node_results=explode('|',$cur_node_result);
                $data['cur_node_result']=$cur_node_results[0];
                $data['cur_node_suggest']=I('review_suggest');
                $next_node=I('next_node');
                $next_nodes=explode('|', $next_node);
                $data['next_node_no']=$next_nodes[0];
                $data['next_login_code']=$next_nodes[1]; 
                $data['next_node_name']=$next_nodes[2];
                $data['next_node_username']=$next_nodes[3];
                $data['next_bill_id']=$next_nodes[4];
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='0';
                $data['step_no']='7';
                $data['remark']=I('remark');
                $flag2=$m->table('station_flow_step')->add($data);
                if($flag2){
                    $da['status']='1';
                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';
                    $flag=$m->table('station_flow_step')->where($w)->save($da); 
                    if($flag){
                        $d['item_type']=$item_type;
                        $wh['item_id']=$item_id;
                        $flag3=$m->table('station_application')->where($wh)->save($d);
                        if($flag3){
                            $this->success('审核成功!','index');
                        }else{
                            $this->error('审核失败!');
                        }
                    }else{
                    $this->error('审核失败!');
                    }
                }else{
                    $this->error('审核失败!');
                }
            }
        }
    }

    //查看工单标签
    public function item_type(){
        $m=M();   
        $w['item_type']='建设摸底库';
        $items=$m->table('station_application')->where($w)->select();
        $this->assign('items',$items);
        $this->display();
    }


    //修改工单标签
    public function item_type_modify(){
        $m=M();
        if(IS_GET){
            $item_id=I('item_id');
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);
            //获得流程信息
            /**
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc ";
            $flows=$m->query($sql);
            **/
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);
            /**
            //申请单县市
            $county_name=$items[0]['COUNTY_NAME']; 
            //申请人
            $appliy_name=$items[0]['APPLIY_NAME']; 
            //基站类型
            $station_type=$items[0]['STATION_TYPE'];
            **/
            //获得节点信息
            /**
            $sql="select * from station_flow_nodes where node_no='6' ";
            $nodes=$m->query($sql);
            **/
            $wh['node_no']='6';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){
            $item_id=I('item_id');
            $item_type=I('item_type');
            if($item_type=='站址储备库'){
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $cur_node_result=I('cur_node_result');
                $cur_node_results=explode('|',$cur_node_result);
                $data['cur_node_result']=$cur_node_results[0];
                $data['cur_node_suggest']=I('review_suggest');
                $next_node=I('next_node');
                $next_nodes=explode('|', $next_node);
                $data['next_node_no']=$next_nodes[0];
                $data['next_login_code']=$next_nodes[1]; 
                $data['next_node_name']=$next_nodes[2];
                $data['next_node_username']=$next_nodes[3];
                $data['next_bill_id']=$next_nodes[4];
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='0';
                $data['step_no']='7';
                $data['remark']=I('remark');
                $flag2=$m->table('station_flow_step')->add($data);
                if($flag2){
                    $d['item_type']=$item_type;
                    $w['item_id']=$item_id;
                    $flag3=$m->table('station_application')->where($w)->save($d);
                    if($flag3){
                        $this->success('审核成功!','index');
                    }else{
                        $this->error('审核失败!');
                    }                
                }else{
                    $this->error('审核失败!');
                }
            }else {
                $this->error('工单标签修改失败!');
            }
        }
    }








    //规划审核2()
    public function review_item6(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);

            //获得流程信息
            /*
            $sql="select * from station_flow_step where item_id='".$item_id."' order by node_date desc ";
            $flows=$m->query($sql);
            **/
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);

             /**
            //申请单县市
            $county_name=$items[0]['COUNTY_NAME']; 
            //申请人
            $appliy_name=$items[0]['APPLIY_NAME']; 
            **/

            //基站类型
            $station_type=$items[0]['STATION_TYPE'];

            //获得节点信息
            $wh['node_no']='7';
            $wh['net_type']=$station_type;
            $wh['status']='1';

            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){     
            $m=M();
            //工单编号
            $item_id=I('item_id');
            //工单标签
            // $item_type=I('item_type');
            $data['item_id']=$item_id;
            $data['appliy_type']='1';
            $data['appliy_type_name']='无线网基站';
            $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
            $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
            $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
            $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
            $cur_node_result=I('cur_node_result');
            $cur_node_results=explode('|',$cur_node_result);
            $data['cur_node_result']=$cur_node_results[0];
            $data['cur_node_suggest']=I('review_suggest');
            $next_node=I('next_node');
            $next_nodes=explode('|', $next_node);
            $data['next_node_no']=$next_nodes[0];
            $data['next_login_code']=$next_nodes[1]; 
            $data['next_node_name']=$next_nodes[2];
            $data['next_node_username']=$next_nodes[3];
            $data['next_bill_id']=$next_nodes[4];
            $data['node_date']=date('Y-m-d H:i:s');
            $data['status']='0';
            $data['step_no']='8';
            $data['remark']=I('remark');
            $flag2=$m->table('station_flow_step')->add($data);
            if($flag2){
                $da['status']='1';
                $w['item_id']=$item_id;
                $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                $w['status']='0';
                $flag=$m->table('station_flow_step')->where($w)->save($da); 
                if($flag){               
                    $this->success('审核成功!','index');
                }else{
                    $this->error('审核失败!');
                }
            }else{
                $this->error('审核失败!');
            }
        }
    }

    //规划审核2审核站点承建方
    public function review_item6_1(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);
            //获得流程信息
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows); 

            //获得节点信息
            $wh['node_no']='3';
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){ 

            $m=M();
            //修改状态
            $item_id=I('item_id');
            $dec_date=I('dec_date');
            $d['dec_date']=$dec_date;
            $wh['item_id']=$item_id;
            $flag2=$m->table('station_application')->where($wh)->save($d);

            if($flag2){
                //插入流程记录      
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $cur_node_result=I('cur_node_result');
                $cur_node_results=explode('|',$cur_node_result);
                $data['cur_node_result']=$cur_node_results[0];
                $data['cur_node_suggest']=I('review_suggest');
                $next_node=I('next_node');
                $next_nodes=explode('|', $next_node);
                $data['next_node_no']=$next_nodes[0];
                $data['next_login_code']=$next_nodes[1]; 
                $data['next_node_name']=$next_nodes[2];
                $data['next_node_username']=$next_nodes[3];
                $data['next_bill_id']=$next_nodes[4];
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='0';
                $data['step_no']='10';
                $data['remark']=I('remark');
                $flag1=$m->table('station_flow_step')->add($data);

                if($flag1){
                    $da['status']='1'; 
                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';
                    $flag=$m->table('station_flow_step')->where($w)->save($da); 
                    if($flag){
                        $this->success('提交成功!','index');
                    }else{
                        $this->error('提交失败!');
                    }
                }else{
                    $this->error('提交失败!');
                }
            }else{
                $this->error('提交失败!');
            }
        }
    }


    //规划审核1审核站点承建方
    public function review_item3_2(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);
            //获得流程信息
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);  

            //基站类型
            $station_type=$items[0]['STATION_TYPE'];

            //获得节点信息
            $wh['node_no']='7';
            $wh['net_type']=$station_type;
            $wh['status']='1';  
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){ 
            $m=M();
            //修改状态
            $item_id=I('item_id');
            $sf_chaoshi=I('sf_chaoshi');//0 未超时 1已超时
            if($sf_chaoshi=='0'){
                $start_date=I('start_date');
                $d['start_date']=$start_date;
                $wh['item_id']=$item_id;
                $flag2=$m->table('station_application')->where($wh)->save($d);
                if($flag2){
                //插入流程记录      
                    $data['item_id']=$item_id;
                    $data['appliy_type']='1';
                    $data['appliy_type_name']='无线网基站';
                    $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                    $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                    $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                    $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                    $cur_node_result=I('cur_node_result');
                    $cur_node_results=explode('|',$cur_node_result);
                    $data['cur_node_result']=$cur_node_results[0];
                    $data['cur_node_suggest']=I('review_suggest');
                    $next_node=I('next_node');
                    $next_nodes=explode('|', $next_node);
                    $data['next_node_no']=$next_nodes[0];
                    $data['next_login_code']=$next_nodes[1]; 
                    $data['next_node_name']=$next_nodes[2];
                    $data['next_node_username']=$next_nodes[3];
                    $data['next_bill_id']=$next_nodes[4];
                    $data['node_date']=date('Y-m-d H:i:s');
                    $data['status']='0';
                    $data['step_no']='11';
                    $data['remark']=I('remark');
                    $flag1=$m->table('station_flow_step')->add($data);
                    if($flag1){
                        $da['status']='1'; 
                        $w['item_id']=$item_id;
                        $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                        $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                        $w['status']='0';
                        $flag=$m->table('station_flow_step')->where($w)->save($da); 
                        if($flag){
                            $this->success('提交成功!','index');
                        }else{
                            $this->error('提交失败!');
                        }
                    }else{
                    $this->error('提交失败!');
                    }
                }else{
                    $this->error('提交失败!');
                }

            }elseif($sf_chaoshi=='1'){
                //插入流程记录      
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $cur_node_result=I('cur_node_result');
                $cur_node_results=explode('|',$cur_node_result);
                $data['cur_node_result']=$cur_node_results[0];
                $data['cur_node_suggest']=I('review_suggest');
                $next_node=I('next_node');
                $next_nodes=explode('|', $next_node);
                $data['next_node_no']=$next_nodes[0];
                $data['next_login_code']=$next_nodes[1]; 
                $data['next_node_name']=$next_nodes[2];
                $data['next_node_username']=$next_nodes[3];
                $data['next_bill_id']=$next_nodes[4];
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='0';
                $data['step_no']='98';
                $data['remark']=I('remark');
                $flag1=$m->table('station_flow_step')->add($data);
                if($flag1){
                    $da['status']='1'; 
                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';
                    $flag=$m->table('station_flow_step')->where($w)->save($da); 
                    if($flag){
                        $this->success('提交成功!','index');
                    }else{
                        $this->error('提交失败!');
                    }
                }else{
                    $this->error('提交失败!');
                }
            }
        }
    }




    //确定站点承建方
    public function review_item7(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);
            //获得流程信息
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);

            //获得节点信息
            $wh['node_no']='6';
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){ 

            $m=M();
            //修改状态
            $item_id=I('item_id');
            $chengjian=I('chengjian');
            //插入流程记录      
            $data['item_id']=$item_id;
            $data['appliy_type']='1';
            $data['appliy_type_name']='无线网基站';
            $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
            $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
            $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
            $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
            $cur_node_result=I('cur_node_result');
            $cur_node_results=explode('|',$cur_node_result);
            $data['cur_node_result']=$cur_node_results[0];
            $data['cur_node_suggest']=I('review_suggest');
            $next_node=I('next_node');
            $next_nodes=explode('|', $next_node);
            $data['next_node_no']=$next_nodes[0];
            $data['next_login_code']=$next_nodes[1]; 
            $data['next_node_name']=$next_nodes[2];
            $data['next_node_username']=$next_nodes[3];
            $data['next_bill_id']=$next_nodes[4];
            $data['node_date']=date('Y-m-d H:i:s');
            $data['status']='0';
            $data['step_no']='9';
            $data['remark']=I('remark');
            $flag1=$m->table('station_flow_step')->add($data);

            if($flag1){
                $d['chengjian']=$chengjian;
                $d['chengjian_date']=date('Y-m-d H:i:s');

                $wh['item_id']=$item_id;

                $flag2=$m->table('station_application')->where($wh)->save($d);
                if($flag2){
                    $da['status']='1'; 

                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';

                    $flag=$m->table('station_flow_step')->where($w)->save($da); 
                    if($flag){
                        $this->success('提交成功!','index');
                    }else{
                        $this->error('提交失败!3');
                    }
                }else{
                    $this->error('提交失败!2');
                }
            }else{
                $this->error('提交失败!1');
            }
        }
    }



    //市公司工程
    public function review_item7_1(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);

             //获得流程信息
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);
            /**
            //基站类型
            $station_type=$items[0]['STATION_TYPE'];
            **/

            //获得节点信息
            $wh['node_no']='8';
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){ 

            $m=M();
            //修改状态
            $item_id=I('item_id');
            // $chengjian=I('chengjian');
            $end_date=I('end_date');
            $d['end_date']=$end_date;

            $wh['item_id']=$item_id;
            $flag2=$m->table('station_application')->where($wh)->save($d);
            if($flag2){
                //插入流程记录      
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $cur_node_result=I('cur_node_result');
                $cur_node_results=explode('|',$cur_node_result);
                $data['cur_node_result']=$cur_node_results[0];
                $data['cur_node_suggest']=I('review_suggest');
                $next_node=I('next_node');
                $next_nodes=explode('|', $next_node);
                $data['next_node_no']=$next_nodes[0];
                $data['next_login_code']=$next_nodes[1]; 
                $data['next_node_name']=$next_nodes[2];
                $data['next_node_username']=$next_nodes[3];
                $data['next_bill_id']=$next_nodes[4];
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='0';
                $data['step_no']='12';
                $data['remark']=I('remark');
                $flag1=$m->table('station_flow_step')->add($data);

                if($flag1){
                    $da['status']='1';
                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';
                    $flag=$m->table('station_flow_step')->where($w)->save($da);  
                    if($flag){
                        $this->success('提交成功!','index');
                    }else{
                        $this->error('提交失败!');
                    }
                }else{
                    $this->error('提交失败!');
                }
            }else{
                $this->error('提交失败!');
            }
        }
    }


    //市公司工程
    public function review_item7_2(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']=$item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);

            //获得流程信息
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows); 
             /**
            //基站类型
            $station_type=$items[0]['STATION_TYPE'];
            **/
            //获得节点信息
            $wh['node_no']='8';
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){ 
            $m=M();
            //修改状态
            $item_id=I('item_id');
            // $chengjian=I('chengjian');
            $end_date=I('end_date');
            $d['end_date']=$end_date;

            $wh['item_id']=$item_id;

            $flag2=$m->table('station_application')->where($wh)->save($d);
            if($flag2){
                //插入流程记录      
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $cur_node_result=I('cur_node_result');
                $cur_node_results=explode('|',$cur_node_result);
                $data['cur_node_result']=$cur_node_results[0];
                $data['cur_node_suggest']=I('review_suggest');
                $next_node=I('next_node');
                $next_nodes=explode('|', $next_node);
                $data['next_node_no']=$next_nodes[0];
                $data['next_login_code']=$next_nodes[1]; 
                $data['next_node_name']=$next_nodes[2];
                $data['next_node_username']=$next_nodes[3];
                $data['next_bill_id']=$next_nodes[4];
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='0';
                $data['step_no']='12';
                $data['remark']=I('remark');
                $flag1=$m->table('station_flow_step')->add($data);
                if($flag1){
                    $da['status']='1';
                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';

                    $flag=$m->table('station_flow_step')->where($w)->save($da);  
                    if($flag){
                        $this->success('提交成功!','index');
                    }else{
                        $this->error('提交失败!');
                    }
                }else{
                    $this->error('提交失败!');
                }
            }else{
                $this->error('提交失败!');
            }
        }
    }



    //单验
    public function review_item8(){
        if(IS_GET){
            $item_id=I('item_id');
            $m=M();
            //获得工单信息
            $w['item_id']= $item_id;
            $items=$m->table('station_application')->where($w)->select();
            $this->assign('items',$items);
            //获取报告信息
            $reports=$m->table('station_survey_report')->where($w)->select();
            $this->assign('reports',$reports);

            //获得流程信息
            $flows=$m->table('station_flow_step')->where($w)->order('node_date desc')->select();
            $this->assign('flows',$flows);  

            //基站类型
            $station_type=$items[0]['STATION_TYPE'];
            //获得节点信息
            $wh['node_no']='7';
            $wh['net_type']=$station_type;
            $wh['status']='1';
            $nodes=$m->table('station_flow_nodes')->where($wh)->select();
            $this->assign('nodes',$nodes);
            $this->display();
        }

        if(IS_POST){
            $m=M();
            //修改状态
            $item_id=I('item_id');
            //插入流程记录 
            $cur_node_result=I('cur_node_result');
            $cur_node_results=explode('|',$cur_node_result);
            $node_result=$cur_node_results[0];
            if($node_result=='0'){
                $data['item_id']=$item_id;
                $data['appliy_type']='1';
                $data['appliy_type_name']='无线网基站';
                $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                $data['cur_node_result']=$node_result;
                $data['cur_node_suggest']=I('review_suggest');
                $next_node=I('next_node');
                $next_nodes=explode('|', $next_node);
                $data['next_node_no']=$next_nodes[0];
                $data['next_login_code']=$next_nodes[1]; 
                $data['next_node_name']=$next_nodes[2];
                $data['next_node_username']=$next_nodes[3];
                $data['next_bill_id']=$next_nodes[4];
                $data['node_date']=date('Y-m-d H:i:s');
                $data['status']='0';
                $data['step_no']='13';
                $data['remark']=I('remark');
                $flag1=$m->table('station_flow_step')->add($data);
                if($flag1){             
                    $da['status']='1';
                    $w['item_id']=$item_id;
                    $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                    $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                    $w['status']='0';
                    $flag=$m->table('station_flow_step')->where($w)->save($da); 
                    if($flag){
                        $this->success('提交成功!','index');
                    }else{
                        $this->error('提交失败!');
                    }
                }else{
                    $this->error('提交失败!');
                }
            }elseif($node_result=='1'){
                $d['item_status']='已入库';
                $d['item_status_no']='1';
                $wh['item_id']=$item_id;
                $flag2=$m->table('station_application')->where($wh)->save($d);
                if($flag2){
                    $data['item_id']=$item_id;
                    $data['appliy_type']='1';
                    $data['appliy_type_name']='无线网基站';
                    $data['cur_node_step']=$_SESSION['node']['NODE_NO'];
                    $data['cur_node_name']=$_SESSION['node']['NODE_NAME'];
                    $data['cur_node_userid']=$_SESSION['node']['BILL_ID'];
                    $data['cur_node_username']=$_SESSION['node']['NODE_USERNAME'];
                    $data['cur_node_result']=$node_result;
                    $data['cur_node_suggest']=I('review_suggest');
                    /**
                    $next_node=I('next_node');
                    $next_nodes=explode('|', $next_node);
                    $data['next_node_no']=$next_nodes[0];
                    $data['next_login_code']=$next_nodes[1]; 
                    $data['next_node_name']=$next_nodes[2];
                    $data['next_node_username']=$next_nodes[3];
                    $data['next_bill_id']=$next_nodes[4];
                    **/
                    $data['node_date']=date('Y-m-d H:i:s');
                    $data['status']='1';
                    $data['step_no']='14';
                    $data['remark']=I('remark');
                    $flag1=$m->table('station_flow_step')->add($data);
                    if($flag1){
                        $da['status']='1'; 
                        $w['item_id']=$item_id;
                        $w['next_login_code']=$_SESSION['node']['NODE_LOGIN_CODE'];
                        $w['next_bill_id']=$_SESSION['node']['BILL_ID'];
                        $w['status']='0';
                        $flag=$m->table('station_flow_step')->where($w)->save($da);  
                        if($flag){
                            $this->success('提交成功!','index');
                        }else{
                            $this->error('提交失败!');
                        }
                    }else{
                        $this->error('提交失败!');
                    }
                }else{
                    $this->error('提交失败!');
                }
            }
        }
    }

    //添加节点
    public function  node_user_add(){
        if(IS_GET){
            $this->display();
        }
        if(IS_POST){
            $m=M();
            $node_login_code=I('node_login_code');
            $node=I('node_name');
            $tem=explode('|', $node);
            $node_no=$tem[0];
            $node_name=$tem[1];
            $node_username=I('node_username');
            $bill_id=I('bill_id');
            $county_code=I('county_code');
            $net_type=I('net_type');

            $data['node_no']=$node_no;
            $data['node_name']=$node_name;
            $data['node_login_code']=$node_login_code;
            $data['node_username']=$node_username;
            $data['bill_id']=$bill_id;
            $data['node_dept']=$node_name;
            $data['status']='1';

            if(!empty($county_code)){
                $data['county_code']=$county_code;
            }
            if(!empty($net_type)){
                $data['net_type']=$net_type;
            }

            $w['node_login_code']=$node_login_code;
            $w['bill_id']=$bill_id;
            $nodes=$m->table('station_flow_nodes')->where($w)->select();

            if(empty($nodes)){
                $flag=$m->table('station_flow_nodes')->add($data);
            }else{
                $flag=$m->table('station_flow_nodes')->where($w)->save($data);
            }

            if($flag){
                $this->success('数据保存成功');
            }else{
                $this->error('数据保存出错');
            }
        }
    }

    

}
?>