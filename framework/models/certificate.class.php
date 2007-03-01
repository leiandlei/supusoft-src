<?php
//证书
class certificate{

	public $this_table = 'certificate';
	public $used = 'certificate';



	function add( $args ){
		global $db;
		//$ep_info=load('enterprise')->get(array('eid'=>$args[eid]));
		$default = array(
			
			'status'	=> '01',	
			'is_check'	=> 'n',	
			
		);
		$args = parse_args( $args, $default );
		$zsid = $db->insert( $this->this_table, $args );
		$bf_info = $this->get($zsid);
		// 日志
		do {
			//log_add($bf_info['eid'], 0, "评定通过-增加证书", NULL, serialize($bf_info));
		}while(false);
		return $zsid;
	}

	function get( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM  sp_certificate WHERE id = '$uid'");
		$metas = $this->meta( $uid );
		$result = array_merge( $row, $metas );
		return $result;
	}

	function edit( $uid, $args ){
		if( empty( $uid ) ) return false;
		global $db;
		//$args = parse_args( $args );
		$af_info = $this->get($uid);
		$db->update( $this->this_table, $args, array( 'id' => $uid ) );
		$bf_info = $this->get($uid);
		if($bf_info['is_check']=='e'){
			$msg = '修改证书-出证前';
		}else if($af_info['is_check']=='e' && $bf_info['is_check']=='y'){
			$msg = '增加证书';
		}else if($af_info['is_check']=='y' && $bf_info['is_check']=='y'){
			$msg = '修改证书';
		}
		// 日志
		do {
			//log_add($bf_info['eid'], 0, $msg, serialize($af_info), serialize($bf_info));
		}while(false);
	}

	function del( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$af_info = $this->get($uid);
		$db->update( $this->this_table, array( 'deleted' => '1' ), array( 'id' => $uid ) );
		$bf_info = $this->get($uid);
		// 日志
		do {
			//log_add($bf_info['eid'], 0, '删除证书', serialize($af_info), serialize($bf_info));
		}while(false);
	}


	function meta( $eid, $meta_name = '', $meta_value = '' ){
		if( empty( $eid ) ) return false;
		global $db;
		$result = '';
		if( $meta_name && $meta_value ){

			$sql = "INSERT INTO sp_metas_ot ( ID, meta_name, meta_value, used )
					VALUES ( '$eid', '$meta_name', '$meta_value', '$this->used' )
					ON DUPLICATE KEY UPDATE meta_value = VALUES( meta_value )";
			$db->query( $sql );
		} elseif( $meta_name ){
			$result = $db->get_var( "SELECT meta_value FROM sp_metas_ot WHERE ID = '$eid' AND meta_name = '$meta_name' AND used = '$this->used'" );
		} else {
			$result = array();
			$query = $db->query("SELECT * FROM sp_metas_ot WHERE ID = '$eid' AND used = '$this->used'");
			while( $rt = $db->fetch_array( $query ) ){
				$result[$rt['meta_name']] = $rt['meta_value'];
			}
		}
		return $result;
	}
}
?>