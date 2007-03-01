<?php
//人员：注册资格
class qualification{

	public $this_table = 'hr_qualification';
	public $used = 'qualification';



	function add( $args ){
		global $db;
		$default = array(
			'uid'		=> '',	//用户id
			'iso'		=> '',	//体系
			'qua_type'  => '',  //注册资格
			'qua_no'	=> '',	//注册资格号码
			's_date'	=> '',	//资格开始时间
			'e_date'	=> '',	//资格结束时间
			'note'		=> '',	//备注
			'status'	=> 1,	//状态 -1为删除状态
 
		);
		$args = parse_args( $args, $default );
		
		$id = $db->insert( $this->this_table, $args );

		return $id;
	}

	function get( $id ){
		if( empty( $id ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_hr_qualification WHERE id = '$id'");
		$metas = $this->meta( $id );
		$result = array_merge( $row, $metas );
		return $result;
	}

	function edit( $id, $args ){
		if( empty( $id ) ) return false;
		global $db;
		$args = parse_args( $args );
		$af_info = $this->get($id);
		$db->update( $this->this_table, $args, array( 'id' => $id ) );
	}

	function del( $id ){
		if( empty( $id ) ) return false;
		global $db;
		$db->update( $this->this_table, array( 'status' => '1' ), array( 'id' => $id ) );
		$bf_info = $this->get($id);
		//log_add('',$bf_info['uid'],'删除注册资格','','');
	}


	function meta( $id, $meta_name = '', $meta_value = '' ){
		if( empty( $id ) ) return false;
		global $db;
		$result = '';
		if( $meta_name && $meta_value ){

			$sql = "INSERT INTO sp_metas_ot ( ID, meta_name, meta_value, used )
					VALUES ( '$id', '$meta_name', '$meta_value', '$this->used' )
					ON DUPLICATE KEY UPDATE meta_value = VALUES( meta_value )";
			$db->query( $sql );
		} elseif( $meta_name ){
			$result = $db->get_var( "SELECT meta_value FROM sp_metas_ot WHERE ID = '$id' AND meta_name = '$meta_name' AND used = '$this->used'" );
		} else {
			$result = array();
			$query = $db->query("SELECT * FROM sp_metas_ot WHERE ID = '$id' AND used = '$this->used'");
			while( $rt = $db->fetch_array( $query ) ){
				$result[$rt['meta_name']] = $rt['meta_value'];
			}
		}
		return $result;
	}
}
?>