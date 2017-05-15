<?php

namespace Read\Controller;

class TestController extends BaseController {

	public function index(){
		$m=M('test_table');	
		$m->startTrans();

		$m1=M('test_table');		

		$mm=M('test_table2');
		//$mm2=M('test_table2');
		//$m=M();


		$da['oper_city']='11111';
		$da['oper_name']='22222';
		$da['oper_num']='33333';
		$da['oper_phone']='55555';
		$flag1=$m1->table('rank_test_table')->add($da);
		dump($flag1);

	

		$a=$m1->_sql();
		dump($a);
			
		$where['id']=3;
		$data['username']='ff';
		$flag2=$mm->table('rank_test_table2')->where($where)->save($data);
		if($flag1&&$flag2){
			$m->commit();
			echo 'yyyy';
		}else{
			$m->rollback();
			echo 'nnnnn';
		}
	
	
		$aa=$mm->_sql();
		dump($aa);

		dump($flag2);
		
	



	}

}