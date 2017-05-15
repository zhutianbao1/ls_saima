<?php

namespace Org\Util;

//oracle操作类，支持CLOB
//调用实例
//========================================
        //vendor('oci.OciUtil');
        //$oci=new OciUtil();
        //$oci=new OciUtil($table,$data);
        
        //insert
        /*$oci->table='t_shopbasicinfo';
        $oci->seqname='SEQ_SHOP_ID';
        $oci->data=array('V_CLOB'=>file_get_contents('http://military.china.com/zh_cn/'),'V_CLOBA'=>file_get_contents('http://www.thinkphp.cn/topic/8026.html'),'v_full_name'=>'PKK1');
        if($oci->insert())  echo 'ok';*/
        
        //select
        /*$oci->where="V_SHOPID='35'";
        $sql="select * from t_shopbasicinfo";
        $ret=$oci->select($sql);
        print_r($ret);*/
        
        //update
        /*$oci->table='t_shopbasicinfo';
        $oci->data=array('V_CLOB'=>file_get_contents('http://military.china.com/zh_cn/'),'V_CLOBA'=>file_get_contents('http://www.thinkphp.cn/topic/8026.html'),'v_full_name'=>'qqq');
        $oci->where="V_SHOPID='35'";
        if($oci->update())  echo 'ok';*/
//==================================================
class OciUtil{
    var $conn;
    var $table;
    var $seqname;
    var $where;//查询条件，字符串
    var $lob;//lob字段名，数组
    var $data;//数据数组
 
    //架构函数
    public function OciUtil($table='',$data=''){
        //取TP框架的数据库配置
        $this->connect(C('DB_USER'),C('DB_PWD'),C('DB_NAME'));
        
        if(!empty($table)) $this->table=$table;
        if(!empty($table)&&!empty($data)){
            $this->checkfield($table,$data);
        }
    }
    
    //检测字段属性
    public function checkfield($table,$data){
        if(!empty($table)&&!empty($data)){
            $fields=$this->getFields($table);
            foreach ($data as $key=>$value){
                //检测lob字段
                if(strtolower($fields[strtolower($key)]['type'])=='clob')  $lob[]=$key;
                //检测PK字段并获取SEQ
                if(strtolower($fields[strtolower($key)]['primary'])==1){
                    $this->seqname=$value;
                    $this->data[$key]=$this->getseq();//根据自动填充主键值
                    $pk=$key;//主键被设置标志
                }
            }
            $this->lob=$lob;
            //如果没有在DATA中的设置主键值，则根据SEQNAME自动填充
            if(!isset($pk)&&!empty($this->seqname)) {
                $this->data[$fields['pk']]=$this->getseq();
            }
            unset($lob);
            unset($pk);
        }
        
    }
    
    /**
    +----------------------------------------------------------
    * 连接ORACLE
    +----------------------------------------------------------
    */
    public function connect($user, $password, $SID){
        $this->conn = OCILogon($user, $password, $SID);   
        if(empty($this->conn)) $this->conn = OCILogon(C('DB_USER'),C('DB_PWD'),C('DB_NAME'));       
    } 
    
    /**
    +----------------------------------------------------------
    * 设置ORACLE字符集
    +----------------------------------------------------------
    */    
    public function charset($code='UTF8'){
        $sql="ALTER DATABASE CHARACTER SET $code";
        $stmt = oci_parse($this->conn, $sql);
        oci_execute($stmt);
        oci_commit($this->conn);
        // Free resources
        oci_free_statement($stmt);
    }
    /**
    +----------------------------------------------------------
    * 添加包含有CLOB字段的记录
    +----------------------------------------------------------
    */    
    public function insert (){
        $this->connect();
        //检测字段属性
        // if(empty($this->lob)) 
        $this->checkfield($this->table,$this->data);
        //字段整理
        $f=strtoupper(join(',',array_keys($this->data)));
        //数据整理
        foreach ($this->data as $key=>$val){
            $f_v_arr[]=!in_array($key,$this->lob)?"'".$val."'":"EMPTY_CLOB()";
        }
        $f_v=join(',',$f_v_arr);
    
        
        //lob字段清理并赋值LOB数据到绑定变量
        for ($i=0;$i<count($this->lob);$i++){
            $lob_str.=":".$this->lob[$i]."_loc,";
        }
        
        if($lob_str) $returning_str.="  RETURNING ".join(',',$this->lob)." INTO ".rtrim($lob_str,',');
        
        //组装SQL
        $sql = "INSERT INTO  $this->table($f) VALUES(".$f_v.")".$returning_str ;
        //echo $sql;
        // die;
        $stmt = oci_parse($this->conn, $sql);
      
        for ($i=0;$i<count($this->lob);$i++){
            // 创建一个“空”的OCI LOB对象绑定到定位器
            $$this->lob[$i] = oci_new_descriptor($this->conn, OCI_D_LOB);
            $lob_str=":".$this->lob[$i]."_loc";
            // 将Oracle LOB定位器绑定到PHP LOB对象
            oci_bind_by_name($stmt, $lob_str, $$this->lob[$i], -1, OCI_B_CLOB);
        }
        // 执行该语句的使用，oci_default -作为一个事务

        oci_execute($stmt, OCI_DEFAULT)    or die ("erro:".$sql."\n");
        // 保存LOB对象数据
        for ($i=0;$i<count($this->lob);$i++){
            if(!$$this->lob[$i]->save($this->data[$this->lob[$i]])){
                $result=false;
                break;
            }
        }        
        if ( isset($result)&&$result==false ) {
            // 如果错误，则回滚事务
            oci_rollback($this->conn);
            $ret=false;
        } else {
            // 如果成功，则提交
            oci_commit($this->conn);
            $ret=true;
        }
        OCIFreeDesc($$this->lob);
        // 释放资源
        oci_free_statement($stmt);
        for ($i=0;$i<count($this->lob);$i++){
            $$this->lob[$i]->free();
        }
        
        return $ret;
    }
    
    /**
    +----------------------------------------------------------
    * 更新CLOB字段的内容
    +----------------------------------------------------------
    */    
    public function update (){
        $this->connect();
        //检测字段属性
        //if(empty($this->lob)) 
        $this->checkfield($this->table,$this->data);
        //数据整理
        foreach ($this->data as $key=>$val){
            $set_arr[]=!in_array($key,$this->lob)?strtoupper($key)."='".$val."'":$key."=EMPTY_CLOB()";
        }
        $set_str=join(',',$set_arr);
        
        //lob字段清理并赋值LOB数据到绑定变量
        for ($i=0;$i<count($this->lob);$i++){
            $lob_str.=":".$this->lob[$i]."_loc,";
        }
        $returning_str.="  RETURNING ".join(',',$this->lob)." INTO ".rtrim($lob_str,',');        
        $where_str=strtoupper($this->where);
        //组装SQL
        $sql = "UPDATE  $this->table SET   $set_str  WHERE   $where_str  ".$returning_str;
        
        $stmt = OCIParse($this->conn, $sql);
        for ($i=0;$i<count($this->lob);$i++){
            // 创建一个“空”的OCI LOB对象绑定到定位器
            $$this->lob[$i] = OCINewDescriptor($this->conn, OCI_D_LOB);
            $lob_str=":".$this->lob[$i]."_loc";
            // 将Oracle LOB定位器绑定到PHP LOB对象
            OCIBindByName($stmt, $lob_str, $$this->lob[$i], -1, OCI_B_CLOB);
        }        
        // 执行该语句的使用，oci_default -作为一个事务
        OCIExecute($stmt, OCI_DEFAULT) or die ("erro: ".$sql."\n");
        
        // 保存LOB对象数据
        for ($i=0;$i<count($this->lob);$i++){
            if(!$$this->lob[$i]->save($this->data[$this->lob[$i]])){
                $result=false;
                break;
            }
        }        
        if ( isset($result)&&$result==false ) {
            
            OCIRollback($this->conn);
            $ret=false;
            
        }else $ret=true;
        
        // 提交事务
        OCICommit($this->conn);
        //释放资源
        for ($i=0;$i<count($this->lob);$i++){
            $$this->lob[$i]->free();
        }

        OCIFreeDesc($$this->lob);
        OCIFreeStatement($stmt);
        
        return $ret;
    }
    public function getseq(){
        $sql="select $this->seqname.nextval from dual";
        
        $stmt = oci_parse($this->conn, strtoupper($sql));
        oci_execute($stmt);
        
        while ( $row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_LOBS) ) {
            $data[]=$row;
        }
        // 释放资源
        oci_free_statement($stmt);
        
        return $data['0']['NEXTVAL'];
    }
    
    /**
    +----------------------------------------------------------
    * 查询包含有CLOB字段的记录
    +----------------------------------------------------------
    */    
    public function select ($sql=''){
        $sql = empty($sql)?"SELECT * FROM  $this->table  WHERE $this->where ":$sql;
        $stmt = oci_parse($this->conn, strtoupper($sql));
        oci_execute($stmt);
        
        while ( $row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_LOBS) ) {
            $data[]=$row;
        }
        // 释放资源
        oci_free_statement($stmt);
        return $data;
    }
    
    /**
     * 取得数据表的字段信息
     * @access public
     */
     public function getFields($tableName) {
          $sql="select a.column_name,data_type,decode(nullable,'Y',0,1) notnull,data_default,decode(a.column_name,b.column_name,1,0) pk "
                  ."from user_tab_columns a,(select column_name from user_constraints c,user_cons_columns col "
          ."where c.constraint_name=col.constraint_name and c.constraint_type='P'and c.table_name='".strtoupper($tableName)
          ."') b where table_name='".strtoupper($tableName)."' and a.column_name=b.column_name(+)";
        $result=  $this->select ($sql);
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
    
    
    
}    
?>