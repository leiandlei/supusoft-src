<?php
//oa：公告

class notice extends model{



	function add( $args ){
		global $db;
		$default = array(
			'update_uid'	=> current_user('uid'),	//创建人
			'update_date'	=> current_time('mysql')	//创建时间
		);
		$args = array_merge( $args, $default );
		$id = $db->insert( 'notice', $args );
		return $id;
	}

	function edit( $id, $args ){
		global $db;
		$db->update( 'notice', $args, array( 'id' => $id ) );
	}

	function get( $pid ){
		global $db;
		$row = $db->get_row("SELECT * FROM sp_notice WHERE id = '$pid'");
		return $row;
	}

	function del( $id ){
		global $db;
		$sql = "update sp_notice set status='-1' where id='$id'";
		$db->query($sql);
	}

}

?>