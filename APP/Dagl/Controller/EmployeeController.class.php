<?php 
namespace Dagl\Controller;
use Think\Controller;
use Think\Model;

/**
 * 员工信息录入
 * @author chensan
 */
class EmployeeController extends BaseController {
  
    //判断登录
    public function isLogin(){
        $oa = session('user_auth.OA');
        //print_r($a);
        if($oa){
            session('user_auth',NULL);//清除有85账号的session信息，字段太多
            $_SESSION['user_auth']['OA'] = $oa;
            $_SESSION['user_auth']['IS_LOGIN'] = true;
            $sql="select dept_no,empl_no,empl_name,bill_id from ls_flow.ls_employee_leave_list where empl_oa='".$oa."'";
            $oper=M()->query($sql);
            $_SESSION['user_auth']['DEPT'] = $oper[0]['DEPT_NO'];
            $_SESSION['user_auth']['USERID'] = $oper[0]['EMPL_NO'];
            $_SESSION['user_auth']['OPER_NAME'] = $oper[0]['EMPL_NAME'];
            $_SESSION['user_auth']['OPER_LOGIN_CODE'] = $oper[0]['BILL_ID'];
            return session('user_auth');
        }else{
            echo "会话已过期,请关闭浏览器重开OA；当重开OA还是遇到相同的问题，请参考地址：<a href='http://10.78.1.85:9000/ranking/Uploads/Attachment/Employee/OA无法登录员工资料库解决办法.png'>http://10.78.1.85:9000/ranking/Uploads/Attachment/Employee/OA无法登录员工资料库解决办法.png</a> 设置浏览器，在当前窗口打开新选项卡";
        }

    }





    //普通用户查看本人信息，综合管理员查看本部门
    public function index(){
        $user = $this->isLogin();
        if($user['IS_LOGIN']){
            $deal_status=I('deal_status');
            if($deal_status == '') $deal_status = 1;
            $tjGroup = self::tjGroup();
            $this->assign('tjGroup',$tjGroup);
            //办理事项按钮组
            $btnGroup = self::btnGroup($deal_status);//$deal_status='1',已办;
            $this->assign('btnGroup',$btnGroup);//新增、修改、审批、查看等按钮权限
            $employeeList = self::queryString($deal_status);
            $this->assign('employeeList',$employeeList);
            $this->assign('deal_status',$deal_status);
            $this->display('Employee/index');
        }

    }

    public function queryString($deal_status=''){
        $sql = "SELECT id,oa,userid,user_name,bill_id,dept,create_time,id_card,card_unit,bachelor_degree,graduate_time,school,major,
            edu_degree,edu_school,edu_major,decode(tb_status,'1','拟稿','2','各单位管理员','3','定稿') tb_status,political_status FROM tp_employee_info WHERE 1=1";
        $dept = I('dept');
        $user_name = I('user_name');
        $role = $this->role();//本人所含角色
        if($role['1'] || $role['6'] ){
            //市公司人力资源管理员
            if($dept != ''){
                $sql.=" and dept='".$dept."'";
            }
            
            if($user_name !=''){
                $sql = $sql." and user_name like '%".$user_name."%'";
            }
        }else if($role['2'] || $role['3'] || $role['4'] || $role['5']){
            $sql.= " and dept='".session('user_auth.DEPT')."'";
            if($user_name !=''){
                $sql = $sql." and user_name like '%".$user_name."%'";
            }
            //部门综合管理员
            if($role['3'] || $role['5']){
                if($deal_status == '' || $deal_status == '1'){
                    $sql.= " and tb_status ='2'  or bill_id='".session('user_auth.OPER_LOGIN_CODE')."'";
                }else if($deal_status == '2'){
                    $sql.= " and tb_status !='2'";
                }
            }
        }else{
            $sql.=" and oa='".session('user_auth.OA')."'";
        }
        $employeeList = parent::listsSqlByls($sql,$pageSize=20);
        return $employeeList;
    }

    //浏览页面
    public function preview(){
        $user = $this->isLogin();
        if($user['IS_LOGIN']){
            $fileConfig = C('FILE_UPLOAD');
            $rootPath = substr($fileConfig['rootPath'],1);
            $path = __ROOT__.$rootPath.$fileConfig['savePath'];
            $oa = I('oa');
            $sql = "SELECT id,oa,userid,user_name,bill_id,dept,create_time,speciality,id_card,card_start,card_end,card_unit,
                bachelor_degree,graduate_time,school,if0211,major,edu_degree,edu_end,edu_school,if1211,edu_major,card_old_name,card_new_name,degree1_old_name,
                degree1_new_name,degree2_old_name,degree2_new_name,political_status,family_address FROM tp_employee_info WHERE oa='{$oa}'";
            $employeeList = M('employeeInfo')->query($sql);

            if($employeeList[0]['CARD_NEW_NAME'] == ''){
                $employeeList[0]['CARD_NEW_NAME1'] = '#';
                $employeeList[0]['CARD_NEW_NAME2'] = '#';
                $employeeList[0]['CARD_OLD_NAME1'] = '';
                $employeeList[0]['CARD_OLD_NAME2'] = '';
            }else{
                $card_old_name = explode('###',$employeeList[0]['CARD_OLD_NAME']);
                $card_new_name = explode('###',$employeeList[0]['CARD_NEW_NAME']);

                $employeeList[0]['CARD_NEW_NAME1'] = $path.$card_new_name[0];
                $employeeList[0]['CARD_NEW_NAME2'] = $path.$card_new_name[1];
                $employeeList[0]['CARD_OLD_NAME1'] = $card_old_name[0];
                $employeeList[0]['CARD_OLD_NAME2'] = $card_old_name[1];
            }

            if($employeeList[0]['DEGREE1_NEW_NAME'] == ''){
                $employeeList[0]['DEGREE1_NEW_NAME'] = '#';
            }else{
                $employeeList[0]['DEGREE1_NEW_NAME'] = $path.$employeeList[0]['DEGREE1_NEW_NAME'];
            }

            if($employeeList[0]['DEGREE2_NEW_NAME'] == ''){
                $employeeList[0]['DEGREE2_NEW_NAME'] = '#';
            }else{
                $employeeList[0]['DEGREE2_NEW_NAME'] = $path.$employeeList[0]['DEGREE2_NEW_NAME'];
            }

            $this->assign("ei",$employeeList[0]);//人员基本信息
            $fami = M('FamilyMember')->where("oa='{$oa}'")->select();
            foreach($fami as $k => $v){
                if($fami[$k]['IMG1'] == ''){
                    $fami[$k]['IMG1_NEW'] = '#';
                    $fami[$k]['IMG1_OLD'] = '';
                }else{
                    $img1 = explode('###',$fami[$k]['IMG1']);
                    $fami[$k]['IMG1_OLD'] = $img1[0];
                    $fami[$k]['IMG1_NEW'] = $path.$img1[1];
                }

                if($fami[$k]['IMG2'] == ''){
                    $fami[$k]['IMG2_NEW'] = '#';
                    $fami[$k]['IMG2_OLD'] = '';
                }else{
                    $img2 = explode('###',$fami[$k]['IMG2']);
                    $fami[$k]['IMG2_OLD'] = $img2[0];
                    $fami[$k]['IMG2_NEW'] = $path.$img2[1];
                }
            }
            $this->assign("fami",$fami);
            $cert = M('LsCertificate')->where("oa='{$oa}'")->select();
            foreach($cert as $k => $v){
                if($cert[$k]['NEW_FILE_NAME'] == ''){
                    $cert[$k]['NEW_FILE_NAME'] = '#';
                }else{
                    $cert[$k]['NEW_FILE_NAME'] = $path.$cert[$k]['NEW_FILE_NAME'];
                }
            }
            $this->assign("cert",$cert);
            $this->display('Employee/preview');
        }
    }

    //针对照片处理部门编号
    public function dealDeptNo(){
    	$dept = session('user_auth.DEPT');
        if($dept=='莲都'){
			$dept = '5781';
		}else if($dept=='缙云'){
			$dept = '5782';
		}else if($dept=='青田'){
			$dept = '5783';
		}else if($dept=='云和'){
			$dept = '5784';
		}else if($dept=='庆元'){
			$dept = '5785';
		}else if($dept=='龙泉'){
			$dept = '5786';
		}else if($dept=='遂昌'){
			$dept = '5787';
		}else if($dept=='松阳'){
			$dept = '5788';
		}else if($dept=='景宁'){
			$dept = '5789';
		}else if($dept=='南城'){
			$dept = '578B';
		}else if($dept=='公司领导'){
			$dept = '5780';
		}else if($dept=='党委办公室（党群工作部）'){
			$dept = '578dqb';
		}else if($dept=='工会'){
			$dept = '578gh';
		}else if($dept=='工程建设部'){
			$dept = '578gc';
		}else if($dept=='网络部'){
			$dept = '578wl';
		}else if($dept=='市场经营部'){
			$dept = '578sc';
		}else if($dept=='纪检监察室'){
			$dept = '578jj';
		}else if($dept=='人力资源部'){
			$dept = '578rl';
		}else if($dept=='财务部'){
			$dept = '578cw';
		}else if($dept=='综合部'){
			$dept = '578zh';
		}else if($dept=='政企客户部'){
			$dept = '578zq';
		}else if($dept=='测试'){
			$dept = '578cs';
		}
        return $dept;
    }

    //进入页面新增
    public function add(){
        $user = $this->isLogin();
        if($user['IS_LOGIN']){
            $ei = M('employeeInfo')->where("oa='".$user['OA']."'")->find();
            if($ei['TB_STATUS'] !='' && $ei['TB_STATUS'] < 3){
                echo '您已有一条单子,请勿重复添加！';
                return;
            }

            $btn['1'] = true;//暂存
            $btn['2'] = true;//提交
            $btn['3'] = false;//退回拟稿
            $btn['4'] = false;//退回上一节点
            $this->assign('btnTb',$btn);
            $da['DEPT_NO'] = $this->dealDeptNo();
            $da['USERID'] = $user['USERID'];
            $da['USER_NAME'] = $user['OPER_NAME'];
            $da['BILL_ID'] = $user['OPER_LOGIN_CODE'];
            $da['OA'] = $user['OA'];
            $this->assign('da',$da);
            $this->display('Employee/main');
        }
    }

    public function upload(){
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = C("FILE_UPLOAD.maxSize"); // 设置附件上传大小
        $upload->exts = C("FILE_UPLOAD.exts"); // 设置附件上传类型
        $upload->rootPath = C("FILE_UPLOAD.rootPath"); // 设置附件上传根目录
        $upload->savePath = C("FILE_UPLOAD.savePath");//date("Y-m-d",time()) ."/";// 设置附件上传（子）目录
        $upload->autoSub = false;
        $upload->saveName = 'timeId';//C("FILE_UPLOAD.saveName");
        $info = $upload->upload();

        if(!$info){
            // 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            foreach($key as $file){
                $info[$key] = $file['savepath'].$file['savename'];
            }
        }
        return $info;
    }

    //保存的数据
    public function save(){
        $data['oa'] = I('oa');
        if($data['oa'] == ''){
            echo '无法获取人员OA';
            exit;
        }

        $info = array();
        //没有上传文件直接保存，会提示没有上传文件,并返回页面
        /*
        if($_FILES['card_name1']['tmp_name'] !='' || $_FILES['card_name2']['tmp_name'] !='' || $_FILES['degree1_old_name']['tmp_name'] !=''
            || $_FILES['degree2_old_name']['tmp_name'] !='' || ($_FILES['cert_name'] !=NULL && $_FILES['cert_name']['tmp_name'][0] !='')){
            $info = $this->upload();
        }
        */

        /**
         * 动态表数据操作某张数据表
         * 页面数据行多于数据表条数，前部更新，后部插入
         * 页面数据行小于数据表条数，前部更新，后部删除
         */
        $count = M('FamilyMember')->where("oa='{$data['oa']}'")->count();//家属信息数据库条数
        $line = count(I('fami_name'));//家属信息表单数据行
        //不插入动态表行，获取的动态表为空字符串，长度为1;有数据行则是数组
        if($line == 1){
            if(I('fami_name') == ''){
                //当页面0行数据时，I('fami_name')为空字符串，统计条数为1;
                $line = 0;
            }
        }

        //表单中无数据
        if($line == 0){
            //表单中无数据行，数据表中有数据行，删除数据库记录
            if($count > 0){
                M('FamilyMember')->where("oa='{$data['oa']}'")->delete();
            }
            //表单和数据库表中均无数据行，不做数据操作
        }else{
            //表单中有数据
            $fami['xh1'] = I('xh1');
            $fami['oa'] = I('oa');
            $fami['fami_name'] = I('fami_name');
            $fami['birth'] = I('birth');
            $fami['card_id'] = I('card_id');

            $fami['img1'] = I('img1');
            $fami['img1_1'] = I('img1_1');
            $fami['img2'] = I('img2');
            $fami['img2_1'] = I('img2_1');

            $fami['relation'] = I('relation');
            $fami['link_no'] = I('link_no');
            $fami['com'] = I('com');
            $fami['position'] = I('position');

            for($i=0;$i<$line;$i++){
                $fm['oa'] = I('oa');
                $fm['xh1'] = $fami['xh1'][$i];
                $fm['fami_name'] = $fami['fami_name'][$i];
                $fm['birth'] = $fami['birth'][$i];
                $fm['card_id'] = $fami['card_id'][$i];

                $fm['img1'] = $fami['img1'][$i].'###'.$fami['img1_1'][$i];
                //dump($fm['img1']);die;
                if($fm['img1'] == '###'){
                    $fm['img1'] = '';
                }
                $fm['img2'] = $fami['img2'][$i].'###'.$fami['img2_1'][$i];
                if($fm['img2'] == '###'){
                    $fm['img2'] = '';
                }

                $fm['relation'] = $fami['relation'][$i];
                $fm['link_no'] = $fami['link_no'][$i];
                $fm['com'] = $fami['com'][$i];
                $fm['position'] = $fami['position'][$i];
                
                if($count == 0){
                    M('FamilyMember')->add($fm);
                }else{
                    if($i < $count){
                        M('FamilyMember')->where("oa='{$data['oa']}' AND xh1={$fm['xh1']}")->save($fm);
                    }else{
                        M('FamilyMember')->add($fm);
                    }
                }
            }
            //表单中行数少于数据库条数时（数据库表多出的部分应删除），
            //**那么此刻的排序尤为重要（查询表单数据时的排序和删除表单行数据时的排序方式应保持一致）
            if($line < $count){
                M('FamilyMember')->where("oa='{$data['oa']}' AND xh1>".$line)->delete();
            }
        }

        //认证信息行数据保存
        $count2 = M('LsCertificate')->where("oa='{$data['oa']}'")->count();
        $line2 = count(I('certify_name'));
        if($line2 == 1){
            if(I('certify_name') == ''){
                //当页面0行数据时，I('certify_name')为空字符串，统计条数为1;
                $line2 = 0;
            }
        }

        if($line2 == 0){
            //表单中无数据行，数据表中有数据行，删除数据库记录
            if($count2 > 0){
                $lcert = M('LsCertificate');
                $lcert->where("oa='{$data['oa']}'")->delete();
            }
            //表单和数据库表中均无数据行，不做数据操作
        }else{
            //表单中有数据
            $xh = I('xh2');
            $oa = I('oa');
            $certify_name = I('certify_name');
            $sign_date = I('sign_date');
            $sign_unit = I('sign_unit');
            $start_date = I('start_date');
            $end_date = I('end_date');
            $old_name = I('cert_name');
            $new_name = I('cert_name_1');
            dump($old_name);
            dump($new_name);
            $j = 0;
            $k = 0;

            for($i=0;$i<$line2;$i++){
                $lc['oa'] = I('oa');
                $lc['xh2'] = $xh[$i];
                $lc['certify_name'] = $certify_name[$i];
                $lc['sign_date'] = $sign_date[$i];
                $lc['sign_unit'] = $sign_unit[$i];
                $lc['start_date'] = $start_date[$i];
                $lc['end_date'] = $end_date[$i];
                $lc['old_file_name'] = $old_name[$i];
                $lc['new_file_name'] = $new_name[$i];

                if($count2 == 0){
                    M('LsCertificate')->add($lc);
                }else{
                    if($i < $count2){
                        $cert = M('LsCertificate');
                        $cert->where("oa='{$data['oa']}' AND xh2=".$lc['xh2'])->save($lc);
                    }else{
                        $cert = M('LsCertificate');
                        $cert->add($lc);
                    }
                }
            }

            //表单中行数少于数据库条数时（数据库表多出的部分应删除），
            //**那么此刻的排序尤为重要（查询表单数据时的排序和删除表单行数据时的排序方式应保持一致）
            if($line2 < $count2){
                M('LsCertificate')->where("oa='{$data['oa']}' AND xh2>".$line2)->delete();
            }
        }

        $data['user_name'] = I('user_name');
        $data['bill_id'] = I('bill_id');
        $data['create_time'] = I('create_time');
        $data['dept'] = I('dept');
        $data['userid'] = I('userid');
        $data['id_card'] = I('id_card');
        $data['card_unit'] = I('card_unit');
        $data['card_start'] = I('card_start');
        $data['card_end'] = I('card_end');
        $data['bachelor_degree'] = I('bachelor_degree');
        $data['major'] = I('major');
        $data['graduate_time'] = I('graduate_time');
        $data['school'] = I('school');
        $data['if0211'] = I('if0211');
        $data['edu_degree'] = I('edu_degree');
        $data['edu_major'] = I('edu_major');
        $data['edu_end'] = I('edu_end');
        $data['edu_school'] = I('edu_school');
        $data['if1211'] = I('if1211');
        $data['speciality'] = I('speciality');
        $data['tb_status'] = I('tb_status');
        $data['political_status'] = I('political_status');
        $data['family_address'] = I('family_address');
        $data['audit_view'] = I('audit_view');
        //两身份证附件共用一个附件原名字段，共用一个服务器存放名字段，用###分割

        $data['card_old_name'] = I('card_name1').'###'.I('card_name2');
        if($data['card_old_name'] == '###'){
            $data['card_old_name'] = '';
        }
        $data['card_new_name'] = I('card_name1_1').'###'.I('card_name2_1');
        if($data['card_new_name'] == '###'){
            $data['card_new_name'] = '';
        }

        $data['degree1_old_name'] = I('degree1_name');
        $data['degree1_new_name'] = I('degree1_name_1');

        $data['degree2_old_name'] = I('degree2_name');
        $data['degree2_new_name'] = I('degree2_name_1');

        if(I('cz') == 'zc'){
            if($data['tb_status']=='3'){
                $data['tb_status']== 1;
            }
        }else if(I('cz') == 'tj'){
            if($data['tb_status']=='3'){
                $data['tb_status'] = 2;
            }else{
                $data['tb_status'] += 1;
            }
        }else if(I('cz') == 'thng'){
            $data['tb_status'] = 1;
        }else if(I('cz') == 'thpre'){
            $data['tb_status'] -= 1;
        }

        $data['id'] = I('id');
        $ei = M('employeeInfo');
        $saveStatus = "";
        if($data['id'] == ''){
            $sql = "select seq_tp_employee.nextval id from sys.dual";
            $result = M()->query($sql);
            $data['id'] = $result[0]['ID'];
            $saveStatus = $ei->add($data);
        }else{
            $saveStatus = $ei->save($data);
        }
        if($saveStatus = "1"){
            $this->redirect('index');
        }else{
            echo "数据保存失败";
            exit;
        }
    }

    //查询是否可以操作当前数据
    public function cando(){
        $j['cando'] = FALSE;
        $con['id'] = I('id');
        $re = M('employeeInfo')->where($con)->find();
        $oper_id = session('user_auth.OPER_ID');
        if($re['USER_ID'] == $oper_id && $re['TB_STATUS']=='1'){
            $j['cando'] = TRUE;
        }
        $this->ajaxReturn($j);
    }

    //读取要更新记录的数据
    public function update(){
        $user = $this->isLogin();
        if($user['IS_LOGIN']){
            $roles = $this->role();
            $oa = I('oa');
            $empl = M('employeeInfo');
            $da = $empl->where("oa='{$oa}'")->find();//基本信息
            $status = $da['TB_STATUS'];
            //只有部门综合管理员可以修改其他人的状态为2的单子
            //其他人只能修改自己状态为1和3的单子
            if($roles[3] || $roles[5]){
                if($oa != $user['OA'] && $status !='2'){
                    echo '单子不在单位管理员节点';
                    exit;
                }
            }else{
                if($oa != $user['OA']){
                    echo '不能修改他人的单子';
                    exit;
                }
            }
            
            $card_old_name = explode('###',$da['CARD_OLD_NAME']);
            $da['CARD_NAME1'] = $card_old_name[0];
            $da['CARD_NAME2'] = $card_old_name[1];
            $card_new_name = explode('###',$da['CARD_NEW_NAME']);
            $da['CARD_NAME1_1'] = $card_new_name[0];
            $da['CARD_NAME2_1'] = $card_new_name[1];
            $da['DEGREE1_NAME'] = $da['DEGREE1_OLD_NAME'];
            $da['DEGREE1_NAME_1'] = $da['DEGREE1_NEW_NAME'];
            $da['DEGREE2_NAME'] = $da['DEGREE2_OLD_NAME'];
            $da['DEGREE2_NAME_1'] = $da['DEGREE2_NEW_NAME'];
            $da['DEPT_NO'] = $this->dealDeptNo();
            $this->assign('da',$da);
            $fm = M('FamilyMember')->where("oa='{$oa}'")->order('xh1 asc')->select();
            $count1 = M('FamilyMember')->where("oa='{$oa}'")->count();
            for($i=0;$i<$count1;$i++){
                $img1 = explode('###',$fm[$i]['IMG1']);
                $fm[$i]['IMG1'] = $img1[0];
                $fm[$i]['IMG1_1'] = $img1[1];
                $img2 = explode('###',$fm[$i]['IMG2']);
                $fm[$i]['IMG2'] = $img2[0];
                $fm[$i]['IMG2_1'] = $img2[1];
            }
            $this->assign('fami',$fm);
            unset($fm);
            $lc = M('LsCertificate')->where("oa='{$oa}'")->order('xh2 asc')->select();
            $this->assign('cert',$lc);
            $fileConfig = C('FILE_UPLOAD');
            $rootPath = substr($fileConfig['rootPath'],1);
            $path = __ROOT__.$rootPath.$fileConfig['savePath'];
            unset($fileConfig);
            $filePath = '';

            for($i=0;$i<count($lc);$i++){
                $filePath[$i] = $path.$lc[$i]['NEW_FILE_NAME'];
            }

            $this->assign('path',$path);
            $this->assign('filePath',$filePath);

            $btnTb['3'] = FALSE;
            $btnTb['4'] = FALSE;
            $btnTb['5'] = FALSE;
            if($status == 2){
                $btnTb['1'] = FALSE;
                $btnTb['3'] = TRUE;
            }else{//拟稿和定稿状态
                $btnTb['1'] = TRUE;
            }

            $this->assign('btnTb',$btnTb);

            //部门综合管理员有退回意见框（角色3、角色5）
            $role = $this->role();
            if($role[3] || $role[5]){
                $role = "zhadmin";
            }else if($role[2] || $role[4]){
                $role = "deptManager";
            }else{
                $role = "";
            }

            $this->assign('role',$role);
            $this->display('Employee/main');
        }
    }

    //查询是否为211院校
    public function if211(){
    	$sql = "select school,if211 from ls_mz.county_hight_school where school like '%".I('school')."%'";
    	$school = M()->query($sql);
    	$this->ajaxReturn($school[0]);
    }

    //PHPExcel导出
    public function exportxls(){
        $roles = $this->role();
        $user = $this->isLogin();
        if($user['IS_LOGIN'] && $roles[1] == '1'){
            $sql = "select id,userid,user_name,bill_id,dept,create_time,speciality,id_card,"
                ."card_start,card_end,card_unit,bachelor_degree,school,major,graduate_time,"
                ."edu_degree,edu_school,edu_major,edu_end,decode(tb_status,'1','拟稿','2',".
                "'各单位管理员','3','定稿') tb_status,political_status,family_address from mz_crm.tp_employee_info order by id asc";
            $elist = M()->query($sql);

            $filename="员工信息清单.xls";
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
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(24);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(30);

            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getFont()->setBold(true);
            //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            // 合并
            //$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');

            // set table header content  设置表头名称 
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '编号')
                ->setCellValue('B1', '工号')
                ->setCellValue('C1', '用户名')
                ->setCellValue('D1', '手机号')
                ->setCellValue('E1', '部门')
                ->setCellValue('F1', '录入时间')
                ->setCellValue('G1', '特长')
                ->setCellValue('H1', '身份证号')
                ->setCellValue('I1', '身份证发证日期')
                ->setCellValue('J1', '身份证到期日期')
                ->setCellValue('K1', '身份证签发单位')
                ->setCellValue('L1', '全日制最高学历')
                ->setCellValue('M1', '毕业院校')
                ->setCellValue('N1', '专业')
                ->setCellValue('O1', '毕业时间')
                ->setCellValue('P1', '非全日制最高学历')
                ->setCellValue('Q1', '毕业院校')
                ->setCellValue('R1', '专业')
                ->setCellValue('S1', '毕业时间')
                ->setCellValue('T1', '工单状态')
                ->setCellValue('U1', '政治面貌')
                ->setCellValue('V1', '家庭住址');
            //将数据写入列
            if(count($elist) > 0){
                foreach($elist as $k => $v){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['ID']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['USERID']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['USER_NAME']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['BILL_ID']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['DEPT']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['CREATE_TIME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['SPECIALITY']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), ' '.$elist[$k]['ID_CARD']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['CARD_START']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['CARD_END']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['CARD_UNIT']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['BACHELOR_DEGREE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['SCHOOL']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['MAJOR']);
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['GRADUATE_TIME']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+2), $elist[$k]['EDU_DEGREE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.($k+2), $elist[$k]['EDU_SCHOOL']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.($k+2), $elist[$k]['EDU_MAJOR']);
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.($k+2), $elist[$k]['EDU_END']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.($k+2), $elist[$k]['TB_STATUS']);
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.($k+2), $elist[$k]['POLITICAL_STATUS']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.($k+2), $elist[$k]['FAMILY_ADDRESS']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle('员工信息清单');//sheet表名称
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            echo '请确认是否登录和有相应权限';
        }
    }

    //为搜索form条件赋值
    protected function tjGroup(){
        $tjGroup['0'] == 0;//查询form是否显示
        $tjGroup['1'] = 0;//部门是否显示
        $tjGroup['2'] = 0;//状态是否显示
        $role = self::role();
        if($role['1'] || $role['2'] || $role['3'] || $role['4'] || $role['5'] || $role['6']){
            $tjGroup['0'] = 1;
            if($role['1'] || $role['6'] ){
                $tjGroup['1'] = 1;
            }

            if($role['3'] || $role['5']){
                $tjGroup['2'] = 1;
            }
        }
        return $tjGroup;
    }

    //查询页面按钮组
    protected function btnGroup($deal_status=''){
        /** 
         * 1.新增数据（未填报，状态为空和状态为0）
         * 2.修改数据（当前流程状态为拟稿节点1时显示，新增和修改不能共存）
         * 3.审批（只在待办时显示）
         * 4.查看（已办和空状态显示，待办时不显示）
         * 5.导出excel
         * 1：显示;0：隐藏;
         */
        $btnGroup['1'] = '0';
        $btnGroup['2'] = '0';
        $btnGroup['3'] = '0';
        $btnGroup['4'] = '1';
        $btnGroup['5'] = '0';

        $role = self::role();
        $ep = M('employeeInfo');
        //本人录入的基本信息
        $employee = $ep->field('tb_status')->where("oa='".session('user_auth.OA')."'")->find();
        
        if($employee==NULL){//用户未曾录入过，就有录入按钮
            $btnGroup['1'] = '1';
        }else{
            if($employee['TB_STATUS'] == '1'){//本人记录状态为1,待修改
                $btnGroup['2'] = '1';
            }else if($employee['TB_STATUS'] == '3'){
                $btnGroup['2'] = '1';
            }
        }

        if($role['1']){
            $btnGroup['5'] = '1';
        }else if(($role['3'] || $role['5']) && $deal_status == '1'){
            //$deal_status == '1'为待办
            $btnGroup['3'] = '1';
        }else{
            $btnGroup['3'] = '0';
        }

        return $btnGroup;
    }

    public function hasRole($roleId=''){
        $flag = false;
        $sql = "select count(or_id) count from mz_user.t_sys_oper_role where or_oper_id="
            ."(select oper_id from mz_user.t_sys_oper where oa='".session('user_auth.OA').
            "') and or_role_id={$roleId}";
        $num = M()->query($sql);
        if($num[0][COUNT] > 0){
            $flag = true;
        }
        return $flag;
    }

    protected function role(){
        $role['1'] = $this->hasRole(5020001522);//市公司人力资源管理员
        $role['2'] = $this->hasRole(5020001523);//市公司部门经理
        $role['3'] = $this->hasRole(5020001502);//市公司部门综合管理员
        $role['4'] = $this->hasRole(5020001524);//县公司总经理
        $role['5'] = $this->hasRole(5020001503);//县公司综合部经理
        $role['6'] = $this->hasRole(5020000382);//地市管理层
        return $role;
    }


    
}