<?php
namespace Zxyxsb\Controller;
use Zxyxsb\Controller\BaseController;

class IndexController extends BaseController {
    protected function _initialize(){
        parent::_initialize();
    }

    /**
     * 进入模块的方法，判断是否登录，已登录则读取相关数据
     */
    public function index(){
        $isLogin = parent::isLogin();
        if($isLogin){
            $this->display('index');
        }
    }

    //赛马_直销装备_管理员:分派、报损县公司装备数量
    public function devM(){
        $roles = $this->getRoles();
        if($roles[2]){
            $this->display('devM');
        }else{
            echo '您无此功能权限';
        }
    }

    //赛马_直销装备_管理员:分派、报损县公司装备（新增保存数据）
    public function devmadd(){
        $roles = $this->getRoles();
        if($roles[2]){
            $xh = I('xh');
            $countyCode = I('countyCode');
            $deviceName = I('deviceName');
            $deviceType = I('deviceType');
            $dealType = I('dealType');
            $num = I('num');

            $line = count($countyCode);
            $timeId = timeId1();
            for($i=0;$i < $line;$i++){
                $data['id'] = $timeId;
                $data['xh'] = $i+1;
                $data['county_code'] = $countyCode[$i];
                $data['device_name'] = $deviceName[$i];
                $data['device_type'] = $deviceType[$i];
                if($deviceName[$i] != '身份证识别仪'){
                    $data['device_type'] = '0';//空值作为条件则无法完成数据统计
                }
                $data['deal_type'] = $dealType[$i];
                $data['num'] = $num[$i];
                M('ZxManage')->add($data);
            }
        }else{
            echo '您无此功能权限';
        }
        $this->redirect('index');
    }

    //赛马_直销装备_管理员:分派、报损县公司装备（修改）
    public function updateM(){
        $roles = $this->getRoles();
        if($roles[2]){
            $id = I('id');
            $r = I('r');
            $zm = M('ZxManage');
            $zxManage = $zm->where("id='".$id."' and xh=".$r)->find();
            $this->assign('zxManage', $zxManage);
            $this->display('updateM');
        }else{
            echo '您无此功能权限';
        }
    }

    //赛马_直销装备_管理员:分派、报损县公司装备（修改保存）
    public function devmsave(){
        $roles = $this->getRoles();
        if($roles[2]){
            $zmobj = M('ZxManage');
            $data = $zmobj->create();
            $id = I('id');
            $xh = I('xh');
            if($id != '' && $xh != ''){
                $zmobj->where("id='".$id."' and xh=".$xh)->save();
                $this->redirect('devMl');
            }
        }
    }

    //赛马_直销装备_管理员:删除记录
    public function deleteM(){
        $roles = $this->getRoles();
        if($roles[2]){
            $id = I('id');
            $r = I('r');
            if($id != '' && $r != ''){
                $zm = M('ZxManage');
                $zm->where("id='".$id."' and xh=".$r)->delete();
                $this->redirect('devMl');
            }
        }else{
            echo '您无此功能权限';
        } 
    }

    //赛马_直销装备_管理员:操作清单
    public function devMl(){
        $roles = $this->getRoles();
        if($roles[2]){
            $sql = "select id,xh,decode(county_code,'5781','莲都一部','57812','莲都二部','5782','缙云',".
                "'5783','青田','5784','云和','5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') county_code,".
                "device_name,decode(device_type,'0','',device_type) device_type,decode(deal_type,'1','派发','0','报损') deal_type,num from mz_crm.rank_zx_manage where 1=1";
            $id = I('id');
            $countyCode = I('countyCode');
            $deviceName = I('deviceName');
            $deviceType = I('deviceType');
            $dealType = I('dealType');

            if($id !=''){
                $sql.=" and id='".$id."'";
            }
            if($countyCode !=''){
                $sql.=" and county_code='{$countyCode}'";
            }
            if($deviceName !=''){
                $sql.=" and device_name ='{$deviceName}'";
            }
            if($deviceType !=''){
                $sql.=" and device_type='{$deviceType}'";
            }
            if($dealType !=''){
                $sql.=" and deal_type='{$dealType}'";
            }
            $sql.=" order by id desc,xh asc";
            $devml = parent::listsSqlByls($sql,$pageSize=24);
            $this->assign('devml',$devml);
            $this->display('devMl');
        }  
    }

    //赛马_直销装备_内勤：录入直销员领用归还装备
    public function devGr(){
        $roles = $this->getRoles();
        if($roles[0]){
            $sql = "select dept_county_id from mz_user.t_sys_dept where dept_id =(select oper_dept_id from mz_user.t_sys_oper where oper_id='".session('user_auth.OPER_ID')."')";
            $model = M();
            $list = $model->query($sql);
            $countyCode = $list[0]['DEPT_COUNTY_ID'];
            $this->assign('countyCode',$countyCode);
            //获取县公司所有在职直销员
            $sql = "select * from(select a.county_code,a.dept_name,a.employee_name,to_char(a.ru_date,'yyyy-mm-dd') ru_date,"
                ."a.commend,a.position,a.group_id,a.employee_id,a.phone,a.formal,b.have_card " 
                ."from mz_user.t_zx_person_record a,mz_crm.rank_zx_card b "
                ."where a.county_code='{$countyCode}' and a.formal !='离职' and a.employee_id=b.employee_id(+) order by employee_id asc)";
                //." union select '0','其他人员','其他人员','2000-01-01','','正式','','0000','13500000000','专职','' from dual";
            $ulist = $model->query($sql);
            $this->assign('ulist',$ulist);
            $this->display('devGr');
        }else{
            echo '您无此功能权限';
        }
    }

    //赛马_直销装备_内勤：直销员装备借还保存数据
    public function devGrs(){
        $roles = $this->getRoles();
        if($roles[0]){
            $countyCode = I('countyCode');
            $operId = session('user_auth.OPER_ID');
            $oper = M()->query("select oper_name from mz_user.t_sys_oper where oper_id=".$operId);
            $operName = $oper[0]['OPER_NAME'];
            $employeeId = I('employeeId');
            $deviceName = I('deviceName');
            $deviceType = I('deviceType');
            $deviceId = I('deviceId');
            $deposit = I('deposit');
            $num = I('num');
            $gettime = I('getTime');
            $time = I('time');

            //新增或修改直销员工作牌情况
            $haveCard = I('haveCard');
            $backCard = I('backCard');
            $indemnity = I('indemnity');
            $card['county_code'] = $countyCode;
            $card['employee_id'] = $employeeId;
            $card['have_card'] = $haveCard;
            $card['back_card'] = $backCard;
            $card['indemnity'] = $indemnity;

            $zxCard = M('ZxCard');
            $count = $zxCard->where("county_code='".$countyCode."' and employee_id='".$employeeId."'")->find();
            if($count == ''){
                M('ZxCard')->add($card);
            }else{
                $zxCard->where("county_code='".$countyCode."' and employee_id='".$employeeId."'")->save($card);
            }

            $line = 0;
            if(!empty($deviceName)){
                $line = count($deviceName);
            }

            $j = 0;
            $k = 0;
            $timeId = timeId1();
            for($i=0;$i<$line;$i++){
                $data['xh'] = $i+1;
                $data['county_code'] = $countyCode;
                $data['oper_id'] = $operId;
                $data['oper_name'] = $operName;
                $data['employee_id'] = $employeeId;
                $data['device_id'] = $deviceId[$i];
                $data['device_name'] = $deviceName[$i];
                $data['device_type'] = $deviceType[$i];

                if($deviceName[$i] != '身份证识别仪'){
                    $data['device_type'] = '0';
                }

                if($deviceName[$i] == '外场营销资料袋' || $deviceName[$i] == '外场营销文件夹' 
                    || $deviceName[$i] == '外场T恤' || $deviceName[$i] == '外场马甲' 
                    || $deviceName[$i] == '工作牌'){
                    //领用无须归还物品
                    $data['id'] = $timeId;
                    $data['deposit'] = '';
                    $data['num'] = $num[$j];
                    $data['get_time'] = $gettime[$j];
                    $data['return_time'] = '';
                    M('ZxDevice')->add($data);
                    $j++;
                }else{
                    $data['deposit'] = $deposit[$k];
                    //须归还物品
                    $data['num'] = 1;
                    $data['id'] = $timeId;
                    $data['get_time'] = $time[$k];
                    M('ZxDevice')->add($data);
                    $k++;
                }
            }
        }else{
            echo '您无此功能权限';
        }
        $this->redirect('index');
    }

    //赛马_直销装备_内勤:直销员装备借还记录清单（修改）
    public function updateGr(){
        $roles = $this->getRoles();
        if($roles[0]){
            $id = I('id');
            $r = I('r');
            //
            $sql="select a.id,a.xh,a.county_code,a.oper_id,a.oper_name,a.employee_id,a.device_id,"
                ."a.device_name,a.device_type,a.deposit,num,a.get_time,a.return_time,b.employee_name,"
                ."b.phone from mz_crm.rank_zx_device a,mz_user.t_zx_person_record b"
                ." where a.employee_id=b.employee_id(+) and a.id='".$id."' and xh=".$r;
            $result = M()->query($sql);
            $devgr = $result[0];
            $operId = session('user_auth.OPER_ID');
            if($devgr['OPER_ID'] != $operId){
                echo "不允许操作其他内勤人员录入的内容";
                exit;
            }
            $this->assign('devgr', $devgr);

            $sql2 = "select dept_county_id from mz_user.t_sys_dept where dept_id =(select oper_dept_id from mz_user.t_sys_oper where oper_id='".session('user_auth.OPER_ID')."')";
            $model = M();
            $list = $model->query($sql2);
            $countyCode = $list[0]['DEPT_COUNTY_ID'];
            $this->assign('countyCode',$countyCode);
            //获取县公司所有在职直销员
            $sql3 = "select a.county_code,a.dept_name,a.employee_name,to_char(a.ru_date,'yyyy-mm-dd') ru_date,"
                ."a.commend,a.position,a.group_id,a.employee_id,a.phone,a.formal,b.have_card " 
                ."from mz_user.t_zx_person_record a,mz_crm.rank_zx_card b "
                ."where a.county_code='{$countyCode}' and a.formal !='离职' and a.employee_id=b.employee_id(+) ";
            $ulist = $model->query($sql3);
            $this->assign('ulist',$ulist);
            $this->display('updateGr');
        }else{
            echo '您无此功能权限';
        }
    }

    //赛马_直销装备_内勤:直销员装备借还记录清单（修改保存）
    public function grsave(){
        $roles = $this->getRoles();
        if($roles[0]){
            $zxdevice = M('ZxDevice');
            $zxdevice->create();
            //dump($zxdevice);die;
            $id = I('id');
            $xh = I('xh');
            if($id != '' && $xh != ''){
                $zxdevice->where("id='".$id."' and xh=".$xh)->save();
                $this->redirect('devGrl');
            }
        }
    }

    //借还记录清单，默认显示未归还的
    public function devGrl(){
        $roles = $this->getRoles();
        $sql="select a.id,a.xh,decode(a.county_code,'5781','莲都一部','57812','莲都二部','5782','缙云','5783','青田','5784','云和','5785','庆元',"
            ."'5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') county_code,"
            ."a.oper_id,a.oper_name,a.employee_id,a.device_id,a.device_name,decode(a.device_type,'0','',device_type) device_type,a.deposit,num,a.get_time,"
            ."a.return_time,b.employee_name,b.phone from mz_crm.rank_zx_device a,mz_user.t_zx_person_record b where a.employee_id=b.employee_id(+) ";
        if($roles[2]){
            $countyCode = I('countyCode');
            if($countyCode !=''){
                $sql.=" and a.county_code='{$countyCode}'";
            }
        }else{//内勤和直销经理只能查本县的
            $operId = session('user_auth.OPER_ID');
            $tmpsql = "select dept_county_id county_code from mz_user.t_sys_dept ".
                "where dept_id=(select oper_dept_id from mz_user.t_sys_oper where oper_id='{$operId}')";
            $countyCode = M()->query($tmpsql);
            $sql.=" and a.county_code='{$countyCode[0]['COUNTY_CODE']}'";
        }

        $id = I('id');
        if($id !=''){
            $sql.=" and a.id='".$id."'";
        }
        
        $userName = I('userName');
        if($userName !=''){
            $sql.=" and b.employee_name like '%".$userName."%'";
        }

        $deviceName = I('deviceName');
        if($deviceName !=''){
            $sql.=" and a.device_name='{$deviceName}'";
            if($devicefl == '0' || $devicefl == '4'){
                $devicefl = '2';
            }
        }

        $devicefl = I('devicefl');
        if($devicefl == '0'){//全部
            $flag['devicefl'] = '0';
        }else if($devicefl == '4'){//一次性发放物品
            $sql.=" and a.deposit is null";
            $flag['devicefl'] = '4';
        }else{//须归还物品
            $sql.=" and a.deposit is not null";
            if($devicefl == '' || $devicefl == '2'){//未归还
                $sql.=" and a.return_time is null";
                $flag['devicefl'] = '2';
            }else if($devicefl == '3'){
                $sql.=" and a.return_time is not null";
                $flag['devicefl'] = '3';
            }else{
                $flag['devicefl'] = '1';
            }
        }

        $deviceType = I('deviceType');
        if($deviceType !=''){
            $sql.=" and a.device_type='{$deviceType}'";
        }

        $this->assign('flag',$flag);
        $sql.=" order by id desc,xh asc";
        $devgrl = parent::listsSqlByls($sql,$pageSize=24);
        $this->assign('devgrl',$devgrl);
        $this->display('devGrl');
    }

    //赛马_直销装备_管理员:报损县公司装备（新增保存数据）
    public function devBsAdd(){
        $roles = $this->getRoles();
        if($roles[0]){
            $xh = I('xh');
            $countyCode = I('countyCode');
            $deviceName = I('deviceName');
            $deviceType = I('deviceType');
            $dealType = I('dealType');
            $num = I('num');

            $line = count($countyCode);
            $timeId = timeId1();
            for($i=0;$i < $line;$i++){
                $data['id'] = $timeId;
                $data['xh'] = $i+1;
                $data['county_code'] = $countyCode[$i];
                $data['device_name'] = $deviceName[$i];
                $data['device_type'] = $deviceType[$i];
                if($deviceName[$i] != '身份证识别仪'){
                    $data['device_type'] = '0';//空值作为条件则无法完成数据统计
                }
                $data['deal_type'] = $dealType[$i];
                $data['num'] = $num[$i];
                M('ZxManage')->add($data);
            }
        }else{
            echo '您无此功能权限';
        }
        $this->redirect('index/devBs');
        //$this->success('操作成功！');
    }

    //赛马_直销装备_内勤:分派、报损县公司装备数量
    public function devBs(){
        $roles = $this->getRoles();
        if($roles[0]){
            $model = M();
            $sql = "select id,xh,decode(county_code,'5781','莲都一部','57812','莲都二部','5782','缙云','5783','青田','5784','云和','5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') county_code,device_name,decode(device_type,'0','',device_type) device_type,decode(deal_type,'1','派发','0','报损') deal_type,num from mz_crm.rank_zx_manage where deal_type = 0";
            $operId = session("user_auth.OPER_ID");
            $tmpsql = "select dept_county_id county_code from mz_user.t_sys_dept where dept_id = (select oper_dept_id from mz_user.t_sys_oper where oper_id = '{$operId}')";
            $countyCode = M()->query($tmpsql);
            $sql .= " and county_code = '{$countyCode[0]['COUNTY_CODE']}'";
            $sql .= " order by id desc,xh asc";
            $result = parent::listsSqlByls($sql,$pageSize=22);
            $this->assign("list",$result);
            $this->display('devBs');
        }else{
            echo '您无此功能权限';
        }
    }


    public function deleteGr(){
        $roles = $this->getRoles();
        if($roles[0]){
            $operId = session('user_auth.OPER_ID');
            $zm = M('ZxDevice');
            $id = I('id');
            $r = I('r');
            $device = $zm->where("id='".$id."' and xh=".$r)->find();
            if($device['OPER_ID'] != $operId){
                echo "不允许操作其他内勤人员录入的内容";
                exit;
            }
           
            if($id != '' && $r != ''){
                $zm->where("id='".$id."' and xh=".$r)->delete();
                $this->redirect('devGrl');
            }
        }else{
            echo '您无此功能权限';
        } 
    }

    //统计市公司分派清单
    public function devTotal(){
        $roles = $this->getRoles();
        if($roles[0] || $roles[1] || $roles[2]){
            //$sql = "select * from ls_mz.ls_chenhf_2016092006";
            $sql = "select * from ls_mz.ls_zxy_2017050204";
            $list = M()->query($sql);
            $this->assign('list',$list);
            $this->display('devTotal');
        }else{
            echo '您无此功能权限';
        }
    }

    public function exportGr(){
        $sql="select a.id,a.xh,decode(a.county_code,'5781','莲都一部','57812','莲都二部','5782','缙云','5783','青田','5784','云和','5785','庆元',"
            ."'5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') county_code,"
            ."a.oper_name,a.employee_id,b.employee_name,b.phone,a.device_id,a.device_name,decode(a.device_type,'0','',device_type) device_type,a.deposit,num,a.get_time,"
            ."a.return_time from mz_crm.rank_zx_device a,mz_user.t_zx_person_record b where a.employee_id=b.employee_id(+)";
        $roles = $this->getRoles();
        if($roles[2]){
            $sql.=" order by id desc,xh asc";
        }else if($roles[0] || $roles[1]){
            $operId = session('user_auth.OPER_ID');
            $tmpsql = "select dept_county_id county_code from mz_user.t_sys_dept ".
                "where dept_id=(select oper_dept_id from mz_user.t_sys_oper where oper_id='{$operId}')";
            $countyCode = M()->query($tmpsql);
            $sql.=" and a.county_code='{$countyCode[0]['COUNTY_CODE']}' order by id desc,xh asc";
        }else{
            $sql="";
        }

        $elist = M()->query($sql);
        $filename="直销员装备借还记录清单 .xls";
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(12);
        // 设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
        //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        // set table header content  设置表头名称 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '批次编号')
            ->setCellValue('B1', '行')
            ->setCellValue('C1', '县市')
            ->setCellValue('D1', '内勤')
            ->setCellValue('E1', '直销员工号')
            ->setCellValue('F1', '直销员')
            ->setCellValue('G1', '直销员号码')
            ->setCellValue('H1', '设备编号')
            ->setCellValue('I1', '设备名称')
            ->setCellValue('J1', '设备类型')
            ->setCellValue('K1', '押金')
            ->setCellValue('L1', '数量')
            ->setCellValue('M1', '领取时间')
            ->setCellValue('N1', '归还时间');
        //将数据写入列
        if(count($elist) > 0){
            foreach($elist as $k => $v){//z第一行为列标题，所以内容从A2开始写入
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), ' '.$elist[$k]['ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['XH']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['COUNTY_CODE']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['OPER_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['EMPLOYEE_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['EMPLOYEE_NAME']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['PHONE']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['DEVICE_ID']);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['DEVICE_NAME']);  
                $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['DEVICE_TYPE']);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['DEPOSIT']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['NUM']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['GET_TIME']);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['RETURN_TIME']);   
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('员工信息清单');//sheet表名称
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        vendor("PHPExcel.PHPExcel\IOFactory");
        $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function exportM(){
        $roles = $this->getRoles();
        if($roles[0] || $roles[1] || $roles[2]){
            $sql = "select id,xh,decode(county_code,'5781','莲都一部','57812','莲都二部','5782','缙云',".
                "'5783','青田','5784','云和','5785','庆元','5786','龙泉','5787','遂昌','5788','松阳','5789','景宁','578B','南城') county_code,".
                "device_name,decode(device_type,'0','',device_type) device_type,decode(deal_type,'1','派发','0','报损') deal_type,num from mz_crm.rank_zx_manage where 1=1";
            $elist = M()->query($sql);
            $filename="县市直销装备派发、报损清单.xls";
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
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            // set table header content  设置表头名称 
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '批次编号')
                ->setCellValue('B1', '行')
                ->setCellValue('C1', '县市编号')
                ->setCellValue('D1', '设备名称')
                ->setCellValue('E1', '设备类型')
                ->setCellValue('F1', '处理类型')
                ->setCellValue('G1', '操作数量');
            //将数据写入列
            if(count($elist) > 0){
                foreach($elist as $k => $v){//z第一行为列标题，所以内容从A2开始写入
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), ' '.$elist[$k]['ID']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['XH']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['COUNTY_CODE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['DEVICE_NAME']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['DEVICE_TYPE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['DEAL_TYPE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['NUM']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle('员工信息清单');//sheet表名称
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }
    }

    public function exportTotal(){
        $roles = $this->getRoles();
        if($roles[0] || $roles[1] || $roles[2]){
            //$sql = "select * from ls_mz.ls_chenhf_2016092006";
            $sql = "select * from ls_mz.ls_zxy_2017050204";
            $elist = M()->query($sql);

            $filename="直销物资统计表.xls";
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
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            // 设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(80);

            // 字体和样式
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getStyle('A1:Aj1')->getFont()->setBold(true);
            //$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:Aj1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);// 设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle('A1:Aj1')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:Aj1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            // set table header content  设置表头名称 
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '县市编号')
                ->setCellValue('B1', '县市名称')
                ->setCellValue('C1', '分离式身份证识别仪部门领取量')
                ->setCellValue('D1', '分离式身份证识别仪直销员领取量')
                ->setCellValue('E1', '分离式身份证识别仪部门报损量')
                ->setCellValue('F1', '分离式身份证识别仪库存量')
                ->setCellValue('G1', '蓝牙式身份证识别仪部门领取量')
                ->setCellValue('H1', '蓝牙式身份证识别仪直销员领取量')
                ->setCellValue('I1', '蓝牙式身份证识别仪部门报损量')
                ->setCellValue('J1', '蓝牙式身份证识别仪库存量')
                ->setCellValue('K1', '外场订制帐篷部门领取量')
                ->setCellValue('L1', '外场订制帐篷直销员领取量')
                ->setCellValue('M1', '外场订制帐篷部门报损量')
                ->setCellValue('N1', '外场订制帐篷库存量')
                ->setCellValue('O1', '外场订制桌子部门领取量')
                ->setCellValue('P1', '外场订制桌子直销员领取量')
                ->setCellValue('Q1', '外场订制桌子部门报损量')
                ->setCellValue('R1', '外场订制桌子库存量')
                ->setCellValue('S1', '外场订制椅子部门领取量')
                ->setCellValue('T1', '外场订制椅子直销员领取量')
                ->setCellValue('U1', '外场订制椅子部门报损量')
                ->setCellValue('V1', '外场订制椅子库存量')
                ->setCellValue('W1', '外场营销资料袋部门领取量')
                ->setCellValue('X1', '外场营销资料袋直销员领取量')
                ->setCellValue('Y1', '外场营销资料袋库存量')
                ->setCellValue('Z1', '外场营销文件夹部门领取量')
                ->setCellValue('AA1', '外场营销文件夹直销员领取量')
                ->setCellValue('AB1', '外场营销文件夹库存量')
                ->setCellValue('AC1', '外场T恤部门领取量')
                ->setCellValue('AD1', '外场T恤直销员领取量')
                ->setCellValue('AE1', '外场T恤库存量')
                ->setCellValue('AF1', '外场马甲部门领取量')
                ->setCellValue('AG1', '外场马甲直销员领取量')
                ->setCellValue('AH1', '外场马甲库存量')
                ->setCellValue('AI1', '有工作牌人数')
                ->setCellValue('AJ1', '无工作牌人数');
            //将数据写入列
            if(count($elist) > 0){
                foreach($elist as $k => $v){//z第一行为列标题，所以内容从A2开始写入
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $elist[$k]['COUNTY_CODE']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $elist[$k]['COUNTY_NAME']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $elist[$k]['分离式身份证识别仪部门领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $elist[$k]['分离式身份证识别仪直销员领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), $elist[$k]['分离式身份证识别仪部门报损量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($k+2), $elist[$k]['分离式身份证识别仪库存量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($k+2), $elist[$k]['蓝牙式身份证识别仪部门领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($k+2), $elist[$k]['蓝牙式身份证识别仪直销员领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($k+2), $elist[$k]['蓝牙式身份证识别仪部门报损量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($k+2), $elist[$k]['蓝牙式身份证识别仪库存量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.($k+2), $elist[$k]['外场订制帐篷部门领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.($k+2), $elist[$k]['外场订制帐篷直销员领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.($k+2), $elist[$k]['外场订制帐篷部门报损量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.($k+2), $elist[$k]['外场订制帐篷库存量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.($k+2), $elist[$k]['外场订制桌子部门领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.($k+2), $elist[$k]['外场订制桌子直销员领取量']); 
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.($k+2), $elist[$k]['外场订制桌子部门报损量']); 
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.($k+2), $elist[$k]['外场订制桌子库存量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.($k+2), $elist[$k]['外场订制椅子部门领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.($k+2), $elist[$k]['外场订制椅子直销员领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.($k+2), $elist[$k]['外场订制椅子部门报损量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.($k+2), $elist[$k]['外场订制椅子库存量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.($k+2), $elist[$k]['外场营销资料袋部门领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.($k+2), $elist[$k]['外场营销资料袋直销员领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.($k+2), $elist[$k]['外场营销资料袋库存量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.($k+2), $elist[$k]['外场营销文件夹部门领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.($k+2), $elist[$k]['外场营销文件夹直销员领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.($k+2), $elist[$k]['外场营销文件夹库存量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.($k+2), $elist[$k]['外场T恤部门领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('AD'.($k+2), $elist[$k]['外场T恤直销员领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('AE'.($k+2), $elist[$k]['外场T恤库存量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('AF'.($k+2), $elist[$k]['外场马甲部门领取量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('AG'.($k+2), $elist[$k]['外场马甲直销员领取量']);  
                    $objPHPExcel->getActiveSheet()->setCellValue('AH'.($k+2), $elist[$k]['外场马甲库存量']);
                    $objPHPExcel->getActiveSheet()->setCellValue('AI'.($k+2), $elist[$k]['有工作牌人数']);
                    $objPHPExcel->getActiveSheet()->setCellValue('AJ'.($k+2), $elist[$k]['无工作牌人数']);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle('员工信息清单');//sheet表名称
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            
            vendor("PHPExcel.PHPExcel\IOFactory");
            $objWriter = \IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            exit;
        }
    }

    //内勤录入时,填写直销员姓名异步获取直销员信息
    public function getEmployee(){
        $countyCode = I('countyCode');
        $userName = I('userName');
        $sql = "select county_code,dept_name,employee_name,to_char(ru_date,'yyyy-mm-dd') ru_date,commend,position,group_id,".
            "employee_id,phone,formal from mz_user.t_zx_person_record where county_code='{$countyCode}'".
            " and employee_name='".$userName."'";
        $user = M()->query($sql);
        $this->ajaxReturn($user[0]);
    }

    //含当前模块所须角色(传数组)
    public function getRoles(){
        $isLogin = parent::isLogin();
        //返回的数组根据索引来判断值,请勿调整角色编号间的位置
        if($isLogin){
            $arr = array('5020001582','5020001583','5020001584');
            return parent::hasRoles($arr);
        }
    }
}
