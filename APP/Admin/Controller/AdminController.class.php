<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Controller;
use Think\Model;
/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class AdminController extends Controller {

	/**
	 * 空处理
	 */
	public function _empty(){
		E('访问地址错误,请重试');
	}
	
    /**
     * 后台控制器初始化
     */
    protected function _initialize(){
        self::viewLog();
    }
    
    
    //是否登录
    protected function isLogin($flag=false){
    	if(session('?user_auth')){
    		return session('user_auth');
    	}else{
            if($flag){
                echo '<script>alert("提示：当前登录会话退出或已经超时请重新登录!");</script>';                
            }else{
                return $flag;
            }    		
    	}
    } 
    
    //分页sql
    protected function listsBySql ($sql='',$page_size=10){
    	if(is_string($sql)){
            $REQUEST    =   (array)I('request.');
    		$m = new Model();
    		$total = $m->query("select count(*) as count from (".$sql.") as z");
    		if( isset($REQUEST['r']) ){
    			$listRows = (int)$REQUEST['r'];
    		}else{
    			$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
    		}
            $listRows = $page_size>0?$page_size:$listRows;
             
    		$page = new \Think\Page($total[0]['count'], $listRows, $REQUEST);
    		if($total>$listRows){
    			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
    		}
    		$p =$page->show();
             
    		$this->assign('_page', $p? $p: '');
    		$this->assign('_total',$total);
    		
    		return $m->query("select z.* from (".$sql.") as z limit ".$page->firstRow.",".$page->listRows);
    	}
    }

    
    
    //oracle sql 分页查询
    protected function listsSql($sql,$name,$countyName){
        $REQUEST    =   (array)I('request.');
            $m = new Model();
            $total = $m->query("select count(*) as count from (".$sql.")");
            /*$r  = I('r');
            if( isset($r)){
                $listRows = (int)$r;
            }else{
                $listRows = C('LIST_ROWS') > 10 ? C('LIST_ROWS') : 10;
            }*/

            if( isset($REQUEST['r']) ){
                $listRows = (int)$REQUEST['r'];
            }else{
                $listRows = C('LIST_ROWS') > 10 ? C('LIST_ROWS') : 20;
            }
           
            $page = new \Think\Page($total[0]['COUNT'], $listRows, $REQUEST);
            if($total>$listRows){
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            }
            $p =$page->show();
            $this->assign('_page', $p? $p: '');
            $this->assign('_total',$total[0]['COUNT']);
 
            
            return $m->query("select * from (select a.* , rownum row_num_id from (".$sql.") a) where row_num_id>".$page->firstRow." and row_num_id<=".($page->firstRow+$page->listRows));
    }

    protected function listsSqlByls($sql,$page_size=0){
            $REQUEST    =   (array)I('request.');
            $m = new Model();
            $total = $m->query("select count(*) as count from (".$sql.")");
            
            if($page_size>0){
                $listRows = (int)$page_size;
            }else{
                if( isset($REQUEST['r']) ){
                    $listRows = (int)$REQUEST['r'];
                }else{
                    $listRows = C('LIST_ROWS') > 10 ? C('LIST_ROWS') : 12;
                }
            }
           
            $page = new \Think\Page($total[0]['COUNT'], $listRows, $REQUEST);
            if($total>$listRows){
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            }
            $p =$page->show();
            $this->assign('_page', $p? $p:'');
            $this->assign('_total',$total[0]['COUNT']);
        
            return $m->db('LS_READ')->query("select * from (select a.* , rownum row_num_id from (".$sql.") a) where row_num_id>".$page->firstRow." and row_num_id<=".($page->firstRow+$page->listRows));

    }

    //分页查询
	protected function lists ($model,$table='',$where=array(),$order='',$field=true,$base='1=1'){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   M($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        $options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
            if($val===''||$val===null){
                return false;
            }else{
                return true;
            }
        });
        if( empty($options['where'])){
            unset($options['where']);
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        
        if(is_string($table) && isset($table)){
        	$total  =   $model->table($table)->where($options['where'])->count();
        }else{
        	$total  =   $model->where($options['where'])->count();
        }
        

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);

        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        $this->assign('_num',$page->firstRow);
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;
        
        $model->setProperty('options',$options);
		
        if(is_string($table) && isset($table)){
        	return $model->table($table)->field($field)->select();
        }
        return $model->field($field)->select();
    }

    //查询指定配置
    protected  function config($id=0){
        $type  = M('typeConfig');
        $tc    = $type->where('status=1')->find($id);
        $this->assign('config',$tc);
        session('session_tc',$tc);
        return $tc;
    }

    //网页提交参数设置
    public function intiParams(){
        $arr = I();
        if(is_array($arr)){
            foreach ($arr as $key => $value) {
                $this->assign($key,$value);
            }
        }
    }

    //网页提交参数设置并且拼接结果URL 
    public function intiParamsURL(){
        $url = $this->get_url();
        $arr = I();
        if(is_array($arr)){
            foreach ($arr as $key => $value) {
                $index  =  strpos($url,'?');
                if($index>=0){
                    $url.="&".$key."=".$value;
                }else{
                    $url.="?".$key."=".$value;
                }
            }
        }
        $this->assign('initURL',$url);
    }

    //用户信息
    public function get_info_by_id($id=0){
        $userInfo = M('userInfo');
        if(!empty($id)){
            $user = $userInfo->find(intval($id));
            if($user){
                return $user;
            }
        }
        return false;
    }

    /**
     * 获取当前页面完整URL地址
     */
    function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    public function downLoad(){
        $filePath = I('filePath');
        $showname = I('showname');
        $filename = str_replace('_', '/', $filePath);  
        // 调用类  
        $http = new \Org\Net\Http;  
        
        $http->download($filename, $showname); 
    }   

    //获取表结构
    public function getFields($tableName) {
          $sql="select a.column_name,data_type,decode(nullable,'Y',0,1) notnull,data_default,decode(a.column_name,b.column_name,1,0) pk "
                  ."from user_tab_columns a,(select column_name from user_constraints c,user_cons_columns col "
          ."where c.constraint_name=col.constraint_name and c.constraint_type='P'and c.table_name='".strtoupper($tableName)
          ."') b where table_name='".strtoupper($tableName)."' and a.column_name=b.column_name(+)";
        $m = M();
        $result=  $m->query($sql);
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[strtolower($val['COLUMN_NAME'])] = array(
                    'name'    => strtolower($val['COLUMN_NAME']),
                    'type'    => strtolower($val['DATA_TYPE']),
                    'notnull' => $val['NOTNULL'],
                    'default' => $val['DATA_DEFAULT'],
                    'primary' => $val['PK'],
                    'autoinc' => $val['PK'],
                );
                if($val['PK']==1) $info['pk']=$val['COLUMN_NAME'];
            }
        }
        return $info;
    } 

    //获取表字段时候存在 clob blob等特殊字段
    public function clob_flag($fields=array()){
        $clob_flag = false;
        foreach ($fields as $k => $f) {
          if($f['type']=='clob' || $f['type']=='blob'){
            $clob_flag=true;
            break;
          }
        }
        return $clob_flag;
    }

    public function create_table($table_name,$table_cols){
        if(is_string($table_name) && is_array($table_cols)){
            $sql = "create table ".$table_name." ( ";
            foreach ($table_cols as $key => $col) {
                if(!empty($col->name) && !empty($col->type)){
                    $sql_tmp.= $col->name." ".$col->type.(empty($col->length)?'':'('.$col->length.')')." ".$col->isNull;
                    if(!empty($col->moren)){
                        $sql_tmp.=" default ".$col->moren;
                    }
                    $sql_tmp.=",";
                }
            }
            $sql.=substr($sql_tmp,0,-1);
            $sql.= " )";           
            $m = M();
            $flag = $m->execute($sql);
            return $flag;
        }
        return false;
    }

    public function table_exists($table_name=''){
        $table_name=trim($table_name);
        if(empty($table_name)){
            return false;
        }

        $db_type = C('DB_type');
        if(strtoupper($db_type)=='MYSQL'){
            $rs = M()->query("SHOW TABLES LIKE '".$table_name."'");
            if(!$rs){
                return false;
            }else{
                return true;
            }
        }

        if(strtoupper($db_type)=='ORACLE'){
            $sql = "select * from user_all_tables where table_name='".strtoupper($table_name)."'";
            $m = M();
            $re = $m->query($sql);
            if($re){ return true; }else{ return false; }
        }

    }


    public function seqId(){
        return date('YmdHis',time());
    }

    //访问日志
    public function viewLog(){
        $page = $this->get_url();
        $user = session('user_auth');
        if($page){
            $model = M('viewLog');
            $log['ID']="rank_seq.nextval";
            if(!empty($user)){
                $log['OPER_ID']=$user['OPER_ID'];
                $log['BILL_ID']=$user['OPER_LOGIN_CODE'];   
                $log['OA']=$user['OA']; 
            }
            $log['IP']=$_SERVER['REMOTE_ADDR'] ;
            $log['PAGE']=$page;
            // dump($log);
            $logFlag = $model->orcAdd('rank_view_log',$log);
            // dump($logFlag);
        }
    }

}
