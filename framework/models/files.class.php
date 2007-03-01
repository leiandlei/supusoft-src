<?php
//oa：文档

class files extends model{



	function add( $args ){
		global $db;
		$default = array(
			'fid' 		=> '',
			'note' 		=> '',
			'filename'	=>'',
			'status'	=> '1',
			'update_uid'	=> current_user('uid'),	//创建人
			'update_date'	=> current_time('mysql')	//创建时间
		);
		$args = parse_args( $args, $default );
		$id = $db->insert( 'files', $args );
		return $id;
	}

	function edit( $id, $args ){
		global $db;
		$db->update( 'files', $args, array( 'id' => $id ) );
	}

	function get( $pid ){
		global $db;
		$row = $db->get_row("SELECT * FROM sp_files WHERE id = '$pid'");
		return $row;
	}

	function del( $id ){
		global $db;
		$sql = "update sp_files set status='-1' where id='$id'";
		$db->query($sql);
	}

}

?>