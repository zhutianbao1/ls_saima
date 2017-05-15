<?php 
namespace Dagl\Controller;
use Think\Controller;
use Think\Model;

/**
 * 档案管理模块
 * @author chensan
 */
class DaglController extends BaseController {
 

    //档案管理 >> 待办清单
    public function daList(){
        if(parent::isLogin()==true){
            $sql = self::queryString($deal_status='0');
            if($sql==''){
                echo '抱歉，您无"档案管理"模块权限';
                return;
            }

            $dalist =parent::listsSqlByls($sql);
            $this->assign('dalist',$dalist);
            $btnGroup=self::btnGroup($deal_status='0');
            $this->assign('btnGroup',$btnGroup);//创建个人档案、修改、审批、查看按钮组的权限控制
            $this->assign('deal_status','0');
            $this->display('dagl/waitDeal');
        }
    }

    //条件查询
    public function query(){
        if(parent::isLogin()==true){
            $deal_status=I('deal_status');
            $dept = I('dept');
            $user_name = I('user_name');
            $year=I('year');
            $sql = self::queryString($deal_status);
            if($sql==''){
                echo '抱歉，您无"档案管理"模块权限';
                return;
            }
            $dalist =parent::listsSqlByls($sql);

            $this->assign('dalist',$dalist);
            $btnGroup=self::btnGroup($deal_status);
            $this->assign('btnGroup',$btnGroup);//创建个人档案、修改、审批、查看按钮组的权限控制
            $this->assign('deal_status',$deal_status);
            $this->assign('dept',$dept);
            $this->assign('user_name',$user_name);
            $this->assign('year',$year);
            $this->display('dagl/previewList');
        }
    }

    //通过角色，判断查询待办清单
    public function queryString($deal_status=''){
        $ur=self::ur();
        if($ur!=''){
            $sql = "select id,year,dept,user_id,user_name,birth,nation,sex,marital,edu_degree,hiredate,
                curr_job,curr_worktime,political,party_time,family_address,
                decode(tb_status,'1','拟稿','2','档案管理员','3','纪检部门经理','4','纪检书记','5','定稿','') tb_status 
                from tp_ls_archives where 1=1";
            //查询条件,审批状态;0:待处理;1:已处理;空值:包含已处理和未处理
            if($deal_status==''){
                //查询自己发起的，有档案管理员、纪检部门经理、纪检书记角色的tb_status>='1'
                $sql = $sql." and (tb_status='0' or (user_id='".session('user_auth.OPER_ID')."' and tb_status>='1')";
                if($ur['2'] || $ur['3'] || $ur['4']){
                    $sql = $sql." or tb_status>='1'";
                }
            }else if($deal_status=='0'){
                //查询待处理，当前节点;
                $sql=$sql." and (tb_status='0'";//tb_status='0'不存在，只是为了与后面的or tb_status='x'进行拼接
                if($ur['1']){
                    $sql=$sql." or (user_id='".session('user_auth.OPER_ID')."' and tb_status='1')";
                }
                if($ur['2']){
                    $sql=$sql." or tb_status='2'";
                }
                if($ur['3']){
                    $sql=$sql." or tb_status='3'";
                }
                if($ur['4']){
                    $sql=$sql." or tb_status='4'";
                }
            }else{
                $sql=$sql." and (tb_status='0'";
                $sql=$sql." or tb_status='".$deal_status."'";
            }
            
            $sql = $sql.')';
            $dept = I('dept');
            if($dept !=''){
                $sql = $sql." and dept like '%".$dept."%'";
            }
            $user_name = I('user_name');
            if($user_name !=''){
                $sql = $sql." and user_name like '%".$user_name."%'";
            }
            $year=I('year');
            if($year !=''){
                $sql=$sql.' and year='.$year;
            }
        }
        return $sql;
    }

    //查询是否可以操作当前数据
    public function cando(){
        $j['cando'] = FALSE;
        $con['id'] = I('id');
        $re = M('lsArchives')->where($con)->find();
        $oper_id = session('user_auth.OPER_ID');
        if($re['USER_ID'] == $oper_id && $re['TB_STATUS']=='1'){
            $j['cando'] = TRUE;
        }
        $this->ajaxReturn($j);
    }

    //查询当年是否可继续录入(每年只允许填报一条记录)
    public function canlr(){
        $con['user_id'] = session('user_auth.OPER_ID');
        $con['year'] = I('year');
        $re=M('lsArchives')->where($con)->find();
        $j['canlr'] = FALSE;
        if($re != NULL){
            $j['canlr'] = TRUE;   
        }
        $this->ajaxReturn($j);
    }

    //新增档案前，进行相应处理
    public function add(){
        if(parent::isLogin()==true){
            $zc=self::zc();
            if($zc){
                $btnTb = self::btnTb();//填报页面,节点按钮组权限(显示、隐藏)
                $this->assign('btnTb',$btnTb);
                $sql="select oper_name from mz_user.t_sys_oper where oper_id=".session('user_auth.OPER_ID');
                $oper=M()->query($sql);
                $da['USER_NAME']=$oper[0]['OPER_NAME'];
                $this->assign('da',$da);

                $this->display('update');
            }else{
                echo '抱歉，您无"中层干部廉政档案"录入权限';
            }
        }
    }
    
    //查看档案页面
    public function preview(){
        if((parent::isLogin())==true){
            self::loadData();
            $this->display('dagl/preview');
        }
    }

    //操作档案
    public function update(){
        if((parent::isLogin())==true){
            self::loadData();
            $this->display('dagl/update');
        }
    }

    //载入当条档案数据
    public function loadData(){
        $model = M('lsArchives');
        $id = I('id');
        $da = $model->where('id='.$id)->find();
        $btnTb=self::btnTb($id=$id,$node=$da['TB_STATUS']);
        $plur = json_decode($da['PLURALITY'],true);//json_decode($plur)默认为object,第二个设置为true就可以转为json
        $fami = json_decode($da['FAMILY'],true);
        $event = json_decode($da['EVENT'],true);
        $subm = json_decode($da['SUBMIT'],true);
        $edu = json_decode($da['EDU'],true);
        $talk = json_decode($da['TALK'],true);
        $peti = json_decode($da['PETITION'],true);
        $deal = json_decode($da['DEAL'],true);

        $this->assign('plur',$plur);
        $this->assign('fami',$fami);
        $this->assign('event',$event);
        $this->assign('subm',$subm);
        $this->assign('edu',$edu);
        $this->assign('talk',$talk);
        $this->assign('peti',$peti);
        $this->assign('deal',$deal);
        $this->assign('da',$da);
        $this->assign('btnTb',$btnTb);

        //查询流程记录
        $frlist = M('lsFlowRecord')->field('last_node,last_oper,opinion,deal_time,curr_node,curr_oper,oper')->where("id='da".$id."'")->order('deal_time asc')->select();
        $this->assign('frlist',$frlist);
    }

    //保存
    public function save(){
        //header('Content-Type:text/html; charset=utf-8');
        if((parent::isLogin())==true){
            //中层干部人员基本情况
            // $data['id'] = I('id');
            $data['user_id'] = I('user_id');
            $data['user_name'] = I('user_name');
            $data['year'] = I('year');
            $data['dept'] = I('dept');
            $data['birth'] = I('birth');
            $data['nation'] = I('nation');
            $data['sex'] = I('sex');
            $data['marital'] = I('marital');
            $data['edu_degree'] = I('edu_degree');
            $data['hiredate'] = I('hiredate');
            $data['curr_job'] = I('curr_job');
            $data['curr_worktime'] = I('curr_worktime');
            $data['political'] = I('political');
            $data['party_time'] = I('party_time');
            $data['family_address'] = I('family_address');
            $data['busi_scope'] = I('busi_scope');

            //本人兼职情况
            $plur['start_time'] = I('start_time');
            $plur['end_time'] = I('end_time');
            $plur['job'] = I('job');
            $plur['company'] = I('company');
            $plur['nature'] = I('nature');
            $plur['reward'] = I('reward');
            //需要将数组转成json数组，否则无法存入此数组字段
            $data['plurality']=json_encode($plur);

            //配偶、子女及其配偶从业情况
            $fami['family_name'] = I('family_name');
            $fami['relation'] = I('relation');
            $fami['work_com'] = I('work_com');
            $fami['com_pro'] = I('com_pro');
            $fami['position'] = I('position');
            $data['family']=json_encode($fami);

            //党风廉政有关制度和“八项规定”精神执行情况
            $data['insti31'] = I('insti31');
            $data['job_fee'] = I('job_fee');
            $data['insti10'] = I('insti10');
            $data['insti52'] = I('insti52');
            $data['insti864'] = I('insti864');
         
            //年度落实“两个责任”的情况
            $data['edu_rate'] = I('edu_rate');
            $data['duty_state'] = I('duty_state');
            $data['build_progress'] = I('build_progress');
            $data['insti'] = I('insti');

            //操办婚丧嫁娶有关事宜情况
            $event['item'] = I('item');
            $event['bladdress'] = I('bladdress');
            $event['bltime'] = I('bltime');
            $event['join_num'] = I('join_num');
            $event['money'] = I('money');
            $data['event']=json_encode($event);

            //礼金、礼品上交情况
            $subm['submit_time'] = I('submit_time');
            $subm['goods'] = I('goods');
            $subm['sums'] = I('sums');
            $subm['total_value'] = I('total_value');
            $data['submit']=json_encode($subm);

            //参加廉洁教育情况
            $edu['edu_time'] = I('edu_time');
            $edu['edu_address'] = I('edu_address');
            $edu['edu_active'] = I('edu_active');
            $edu['edu_com'] = I('edu_com');
            $data['edu']=json_encode($edu);

            //年度述职述廉情况
            $data['intro_time'] = I('intro_time');
            $data['address'] = I('address');
            $data['main_content'] = I('main_content');
            $data['suggestion'] = I('suggestion');

            //市公司党委、纪委任职、提醒、诫勉等廉政谈话情况
            $talk['talk_time'] = I('talk_time');
            $talk['talk_address'] = I('talk_address');
            $talk['talk_nature'] = I('talk_nature');
            $talk['talker'] = I('talker');
            $talk['talk_content'] = I('talk_content');
            $data['talk']=json_encode($talk);

            //信访举报情况
            $peti['petition_id'] = I('petition_id');
            $peti['peti_time'] = I('peti_time');
            $peti['peti_form'] = I('peti_form');
            $peti['reflect'] = I('reflect');
            $peti['peti_result'] = I('peti_result');
            $data['petition']=json_encode($peti);

            //违法违规违纪处理情况
            $deal['deal_problem'] = I('deal_problem');
            $deal['deal_time'] = I('deal_time');
            $deal['deal_com'] = I('deal_com');
            $deal['deal_situa'] = I('deal_situa');
            $data['deal']=json_encode($deal);
            
            //公司纪委审核意见
            $data['opinion'] = I('opinion');
            $data['lr_time'] = I('lr_time');
            $data['tb_status'] = I('tb_status');//暂存状态不变

            $ar['last_node'] = '';
            $ar['last_oper'] = '';

            if($data['tb_status']=='' || $data['tb_status']=='1'){
                $ar['last_node'] = '拟稿';
                $ar['last_oper'] = I('user_name');
            }else if($data['tb_status']=='2'){
                $ar['last_node'] = '档案管理员';
                $ar['last_oper'] = '谢义连';
            }else if($data['tb_status']=='3'){
                $ar['last_node'] = '纪检部门经理';
                $ar['last_oper'] = '冯敏';
            }else if($data['tb_status']=='4'){
                $ar['last_node'] = '纪检书记';
                $ar['last_oper'] = '陆晔楠';
            }

            $cz = I('cz');
            if($cz=='tj'){//提交
                if($data['tb_status']==''){
                    $data['tb_status'] = 2;//拟稿提交后状态变为2
                }else{
                    $data['tb_status'] = I('tb_status') + 1;
                }
            }
            if($cz=='thng'){
                $data['tb_status'] = 1;
            }
            
            if($cz=='thpre'){
                $data['tb_status'] = I('tb_status') - 1;
            }

            if($cz=='thfsz'){
                //$data['tb_status'] ='xxxx';//暂不做非实质性退回
            }

            $saveState = false;
            $lsArchives = M('lsArchives');
            if(I('id')==''){//新增
                $data['id'] = self::getNextSeq();
                $data['lr_time'] = date('Y-m-d',time());
                if($cz=='zc'){
                    $data['tb_status'] = '1';
                }
                
                import('Org.Util.OciUtil');
                $oci=new \Org\Util\OciUtil();
                $oci->table='tp_ls_archives';
                $oci->seqname='seq_tp_lzda';
                $oci->data = $data;
                $saveState = $oci->insert();
            }else{//修改
                import('Org.Util.OciUtil');
                $oci=new \Org\Util\OciUtil();
                $oci->table='tp_ls_archives';
                $oci->data=$data;
                $oci->where="id='".I('id')."'";
                $saveState = $oci->update();
            }
            
            //流程审批记录(audit_record)
            if($cz != 'zc' && $saveState){
                $model = M('lsArchives')->field('id')->where('user_id='.$data['user_id']." and year=".$data['year'])->find();
                $ar['id'] = 'da'.$model['ID'];

                $ar['flow_name'] = '中层廉政档案';
                
                $ar['opinion'] = I('deal_opin');
                $ar['deal_time'] = date('Y-m-d H:i:s');
                $ar['curr_node'] = '';
                $ar['curr_oper'] = '';
                $ar['oper'] = '';
                $status = $data['tb_status'];
                if($status=='1'){
                    $ar['curr_node'] = '拟稿';
                    $ar['curr_oper'] = I('user_name');
                    $ar['oper'] = '退回拟稿';
                }else if($status=='2'){
                    if($cz == 'tj'){
                        $ar['oper'] = '提交档案管理员';
                    }else if($cz == 'thpre'){
                        $ar['oper'] = '退回档案管理员';
                    }
                    $ar['curr_node'] = '档案管理员';
                    $ar['curr_oper'] = '谢义连';
                }else if($status=='3'){
                    $ar['curr_node'] = '纪检部门经理';
                    $ar['curr_oper'] = '冯敏';
                    if($cz == 'tj'){
                        $ar['oper'] = '提交纪检部门经理';
                    }else if($cz == 'thpre'){
                        $ar['oper'] = '退回纪检部门经理';
                    }
                }else if($status=='4'){
                    $ar['curr_node'] = '纪检书记';
                    $ar['curr_oper'] = '陆晔楠';
                    $ar['oper'] = '提交纪检书记';
                }else if($status=='5'){
                    $ar['oper'] = '定稿';
                }
                M('lsFlowRecord')->data($ar)->add();
                
                $bill_id = '';
                if($ar['curr_node'] == '拟稿'){
                    $mobile = M()->query("select oper_login_code bill_id from mz_user.t_sys_oper where oper_id=".I('user_id'));
                    $bill_id = $mobile[0]['BILL_ID'];
                }else if($ar['curr_node'] == '档案管理员'){
                    $bill_id = '13905787911';
                }else if($ar['curr_node'] == '纪检部门经理'){
                    $bill_id = '13905780037';
                }
                else if($ar['curr_node'] == '纪检书记'){
                    $bill_id = '13906788806';
                }
                
                //对待办人进行短信提醒
                $seqSql = "select mzmms.MMS_SEND_SEQ.nextval@ls85 id from sys.dual";
                $result = M()->query($seqSql);
                $nextId = $result[0]['ID'];
                $dbMsg = "insert into mzmms.t_sms_send@ls85(SMS_ID,SOURCEADDR,DESTADDR,CONTENT,STATUS,SUBMITTIME,SENDTYPE) "
                    ."values(".$nextId.",'106573078155','".$bill_id."','您有廉政档案工单需要处理，请在OA首页下方点击“中层干部廉政档案”登录及时处理。','1',sysdate,'1')";
                M()->execute($dbMsg);
            }

            unset($data);
            unset($ar);
            $this->redirect("daList");
            //需要进入到待办事项,不要直接用调用方法，那样会将提交页面的字段值带到查询页的字段，
            //那样查出的内容限定了条件会出现查询数据过少;用redirect调用方法;
        }
    }

    //进行填报、新增保存，获取Oracle序列编号
    public function getNextSeq(){ 
        $sql = "select seq_tp_lzda.nextval id from sys.dual";
        $result = M()->query($sql);
        $nextId = $result[0]['ID'];
        return $nextId;
    }

    //控制查询页面按钮组（创建个人档案、修改、审批、查看）
    public function btnGroup($deal_status){
        $role=self::ur();
        $deal_status=$deal_status;
        //中层干部或者档案管理员(字段值为,1:显示;0:隐藏),创建和修改同时只会存在一个;
        $btnPri['create']='0';//本年度未填报（显示创建个人档案按钮）
        $btnPri['modify']='0';//修改按钮
        $btnPri['audit']='0';//审批按钮
        $btnPri['preview']='1';//查看按钮
        
        if($role['1']){
            $btnPri['create']='1';
        }

        if($deal_status==''){
            $btnPri['preview']='0';//处理状态空，无查看按钮
        }else if($deal_status=='0'){
            if($role['2'] || $role['3'] || $role['4']){
                $btnPri['audit']='1';
            }
            if($role['1']){
                $sql="select count(1) count from tp_ls_archives where user_id='".session('user_auth.OPER_ID')."' and tb_status='1'";
                $xg=M('lsArchives')->query($sql);
                if($xg[0]['COUNT']>0){
                    $btnPri['modify']='1';
                }
            }  
        }
        return $btnPri;
    }

    /* 
     * 填报页面控制按钮组
     * 暂存、提交、退回上一节点、退回拟稿、非实质性退回
     * 状态0：隐藏;状态1：显示
     */
    public function btnTb($id='',$node=''){
        //$btnTb['1']='1';//暂存按钮
        //$btnTb['2']='1';//提交
        $btnTb['3']='1';//退回拟稿
        $btnTb['4']='1';//退回上一节点
        $btnTb['5']='1';//非实质性退回
        if($id==''){
            //$id为空且操作为空，新增
            $btnTb['3']='0';
            $btnTb['4']='0';
            $btnTb['5']='0';
        }else{
            //状态：审批中
            if($node=='1'){//拟稿节点
                $btnTb['3']='0';
                $btnTb['4']='0';
                $btnTb['5']='0';
            }else if($node=='2'){//档案管理员节点
                $btnTb['4']='0';
                $btnTb['5']='0';
            }else{
                $btnTb['5']='0';//(非实质性退回暂时不做;)纪检部门经理和纪检书记显示所有操作按钮
            }
            
        }

        return $btnTb;        
    }

    //判断当年是否已经录入
    public function hasTb(){
        $model = M('lsArchives');
        $count=$model->where("user_id=".session('user_auth.OPER_ID')." AND substr(lr_time,0,4)='".date('Y')."'")->count();
        return $count;
    }

    //判断用户是否中层干部
    public function zc($flag=false){
        $zc_level = C('ZC_LEVEL');
        $sql="select oper_type_id from mz_user.t_sys_oper where oper_id='".session('user_auth.OPER_ID')."'";
        $ul = M()->query($sql);
        if($ul[0]['OPER_TYPE_ID'] >= $zc_level){
            $flag=true;
        }else{
            $flag=false;
        }
        return $flag;
    }

    //用户角色user_role
    //1：拟稿(中层);2：档案管理员;3：纪检部门经理;4：纪检书记
    public function ur(){
        $ur = array();
        $zc = $this->zc();
        if($zc){
           $ur['1']='1'; 
        }

        $admin=parent::hasRole(5020001482);//档案管理员
        if($admin){
            $ur['2']='1';
        }

        $disManager=parent::hasRole(5020001483);//纪检部门经理
        if($disManager){
            $ur['3']='1';
        }
        $disSj=parent::hasRole(5020001484);//纪检书记
        if($disSj){
            $ur['4']='1';
        }
        if($ur['1']=='' && $ur['2']=='' && $ur['3']=='' && $ur['4']==''){
            return;
        }
        return $ur;
    }

}