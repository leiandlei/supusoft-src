<?php
/*
合同来源-分子公司-人员模块
*/
class ctfrom extends model{ 

	function add( $args ){
		global $db;
		$default = array(
			//'cerate_uid'	=> current_user('uid'),	//创建人
			//'cerate_date'	=> current_time('mysql')	//创建时间
		);
		$args = parse_args( $args, $default );
		return $db->insert( $this->this_table, $args );
	}

	function get( $code ){
		if( empty( $code ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_settings_ctfrom WHERE code = '$code'");
		return $row;
	}

	function get_next( $code ){
		if( empty( $code ) ) return false;
		global $db;
		$len = strlen( $code );
		$sql = "SELECT COUNT(*) FROM sp_settings_ctfrom WHERE LEFT(code,$len) = '$code'";
		$no = $db->get_var( $sql );
		return sprintf( "%02d", $no );
	}

	function edit( $uid, $args ){
		if( empty( $uid ) ) return false;
		global $db;
		$args = parse_args( $args );
		$db->update( $this->this_table, $args, array( 'id' => $uid ) );
	}

	function del( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$db->update( $this->this_table, array( 'deleted' => 1 ), array( 'id' => $uid ) );
	}

}
?>