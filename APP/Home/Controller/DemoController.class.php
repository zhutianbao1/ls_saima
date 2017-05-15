<?php

namespace Home\Controller;
use Think\Controller;


class DemoController extends Controller {

	protected function _initialize(){
		$demo = M('Demo');
	}

	public function clob_add(){
        import('Org.Util.OciUtil');
        $oci=new \Org\Util\OciUtil();
        $oci->table='rank_config_msg';
        $oci->seqname='pub_seq';
        $date['id']='';
        $date['config_id']='';
        $date['tag']='';
        $date['title']='';
        $date['msg']='';
        $date['oper_id']='';
        $date['oper_name']='';
        $date['create_date']='';
        $date['modify_date']='';    
        $date['remark']='';
        $date['status']=1;
        $oci ->date = $date;      
        if($oci->insert()){
            echo 'ok';
        }
		
		// import('Org.Util.OciUtil');
  //       $oci=new \Org\Util\OciUtil();
  //       // $oci->connect(C('DB_USER'),C('DB_PWD'),C('DB_NAME'));
  //       // $oci=new OciUtil($table,$data);
		
		// //insert
  //       $oci->table='rank_demo';
  //       $oci->seqname='pub_seq';
  //       $data['name']='demo测试。。';
  //       $data['age'] =24;
  //       $data['address']='城北路488号909室';
  //       $data['info']='昨日，沪指日线级别走势还是比较明朗，只是连续缩量反弹正是大盘弱势的表现。尾盘一波跳水跌破5日均线和20日均线，创下日内新低点，考验3000点的支撑，可见3000点整数关口不可能轻松逾越，必须经过反复震荡夯实整固才能真正开启升势。创业板走势偏弱，在指数强势拉升时，创业板却出现下跌走势。这说明市场资金有限，主要还是存量资金在场内博弈。纵然早盘虽有一波拉升，
		// 	  		   更大的可能性是下跌的中继。午后创出日内新低点，5分钟小级别MACD指标背离构筑没有成功，这就意味着下跌还没有完结，今日不排除继续下探2966点可能。鉴于在G20峰会召开之前更多的维稳意图，同时，深港通的开启预期，静待市场重生涅槃。
		// 			   廊坊发展公告称，鉴于相关股东已履行了信息披露义务，公司股票将于8月12日开市起复牌';
		// $data['info2']='弹正是大盘弱势的表现。尾盘一波跳水跌破5日均线和20日均线';
		// $data['status']='1';
		// $data['timer']=time();
		// $oci->data = $data;

  //       // $oci->data=array( 'name'=>'demo测试',
  //   				// 	  'age'=>23,
  //   				// 	  'address'=>'城北路488号909室',
  //   				// 	  'info'=>'昨日，沪指日线级别走势还是比较明朗，只是连续缩量反弹正是大盘弱势的表现。尾盘一波跳水跌破5日均线和20日均线，创下日内新低点，考验3000点的支撑，可见3000点整数关口不可能轻松逾越，必须经过反复震荡夯实整固才能真正开启升势。创业板走势偏弱，在指数强势拉升时，创业板却出现下跌走势。这说明市场资金有限，主要还是存量资金在场内博弈。纵然早盘虽有一波拉升，
  //   				// 	  		   更大的可能性是下跌的中继。午后创出日内新低点，5分钟小级别MACD指标背离构筑没有成功，这就意味着下跌还没有完结，今日不排除继续下探2966点可能。鉴于在G20峰会召开之前更多的维稳意图，同时，深港通的开启预期，静待市场重生涅槃。
		// 						//    廊坊发展公告称，鉴于相关股东已履行了信息披露义务，公司股票将于8月12日开市起复牌。此前廊坊控股董事会计划自7月21日起6个月内，拟使用自有资金通过交易系统择机增持廊坊发展股份，增持金额在5000万元至5亿元之间，并承诺6个月内不减持。截至目前，廊坊控股增持金额约17582.75万元，尚未达到增持计划金额的上限，廊坊控股不排除继续增持廊坊发展股票的可能性',
		// 				  // 'info2'=>'弹正是大盘弱势的表现。尾盘一波跳水跌破5日均线和20日均线，创下日内新低点，考验3000点的支撑，可见3000点整数关口不可能轻松逾越，必须经过反复震荡夯实整固才能真正开启升势。创业板走势偏弱，在指数强势拉升时，创业板却出现下跌走势。这说明市场资金有限，主要还是存量资金在场内博弈。纵然早盘虽有一波拉',
  //   				// 	  'status'=>'1',
  //   				// 	  'timer'=>time());
      
  //       if($oci->insert()){
  //       	echo 'ok';
  //       }  
	}

 
	public function clob_modify($id){
    echo $id.'......';
		    import('Org.Util.OciUtil');
        $oci=new \Org\Util\OciUtil();
            $oci->table='ls_form_info';
            $data['form_design'] = I('form_design');
            $data['form_objs']   = I('form_objs');
            $data['form_objs_table'] = I('form_objs_table');
            $oci->data=$data;
            $oci->where="id=20161205103511";        
            $re = $oci->update();

        // $oci->table='rank_demo';
        // $data['info']='2sdf水电费水电费水电费';
        // $data['info2']='3是的发送到发送到';

        // $oci->data=$data;
        // $oci->where="id='100685'";
        // if($oci->update())  echo 'ok'; 
	}


  public function form_design($id=0){
    $re = false;
    if($id>0 && IS_POST){
      import('Org.Util.OciUtil');
      $oci=new \Org\Util\OciUtil();
        $oci->table='ls_form_info';
        $data['form_design'] = I('form_design');
        $data['form_objs']   = I('form_objs');
        $data['form_objs_table'] = I('form_objs_table');
        $oci->data=$data;
        $oci->where="id=20161205103511";        
        $re = $oci->update();
    }

    return $re;
  }

	 
}

?>