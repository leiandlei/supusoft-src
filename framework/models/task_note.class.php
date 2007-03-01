<?php
//审核员：评定问题

class task_note extends model{



	function add( $tid,$args ,$status='0'){
		global $db;
		$default = array(
			'tid'		=> 0,
			'step1'		=> '',
			'step2'		=> '',//是否进现场
			'step3'		=> '',//总人天
			'update_uid'	=> current_user('uid'),	//创建人
			'update_date'	=> current_time('mysql')	//创建时间
		);
		$args = parse_args( $args, $default );
		$args['tid'] = $tid;
		$db->insert( 'task_note', $args );


	}

	function edit( $tid, $args ,$status='0'){
		global $db;
		$af_info = $this->get($tid);
		$db->update( 'task_note', $args, array( 'tid' => $tid ) );
	}

	function get( $tid ){
		global $db;
		$row = $db->get_row("SELECT * FROM sp_task_note WHERE tid = '$tid'");
		return $row;
	}
}

?>