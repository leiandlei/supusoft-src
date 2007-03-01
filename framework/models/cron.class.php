<?php
//计划任务

class cron extends model{



	function add( $args ){
		global $db;
		$default = array(
			'subject'		=> '',
			'loop_type'		=> '',		//循环类型
			'loop_time'		=> '0-0-0',	//循环时间
			'is_open'		=> 1,		//是否开启
			'run_script'	=> '',		//任务脚本
			'create_date'	=> current_time('mysql')		//创建时间
		);

		$args = parse_args( $args, $default );
		$id = $db->insert( 'cron', $args );
		return $id;
	}

	function edit( $cron_id, $args ){
		global $db;
		$db->update( 'cron', $args, array( 'cron_id' => $cron_id ) );

	}

	function get( $args ){
		if( empty( $args ) || !is_array( $args ) ) return false;
		global $db;
		$args = parse_args( $args );
		$where = $db->sqls( $args, 'AND' );
		$row = $db->get_row("SELECT * FROM sp_cron WHERE $where");
		return $row;
	}


	function del( $args ){
		if( empty( $args ) or !is_array( $args ) ) return false;
		global $db;
		$args = parse_args( $args );
		$db->update( 'cron', array( 'deleted' => 1 ), $args );
	}

}

?>