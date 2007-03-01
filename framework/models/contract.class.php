<?php 
//合同模型
 class contract{

	private $_tbl='contract'; //表名
	private $_pk='id';

	function add( $args ){
 
		global $db; 
 
		$id = $db->insert( 'contract', $args );
		
		$bf_str = $this->get(array( 'ct_id' => $id ));
		// 日志
		do {
			$status = ($bf_str['status']==1) ? '直接已登记' : '未登记完';
			//log_add($args['eid'], 0, "[说明:合同登记][状态:$status]<合同编号:".$bf_str['ct_code'].">", NULL, serialize($bf_str));
		}while(false);
		return $id;
	}

	function edit( $ct_id, $args ){
		global $db; 
		$db->update( 'contract', $args, array( 'ct_id' => $ct_id ) ); 
	}
//读取单挑合同信息
	function get( $args ){ 
		global $db; 
		$where = $db->sqls( $args, 'AND' );
		$result = $db->get_row("SELECT * FROM sp_contract WHERE  $where ");
	 
		$metas = $this->meta( $result['ct_id'] );
		if($metas)
		$result = array_merge( $result, $metas );
		return $result;
	}


	function last( $eid ){
		global $db;
		$row = $db->get_row("SELECT * FROM sp_contract WHERE eid = '$eid' AND AND deleted=0 ORDER BY create_date DESC LIMIT 1");
		return $row;
	}

	//删除合同
	function del( $args ){ 
		global $db; 
		$db->update( 'contract', array( 'deleted' => 1 ), $args );

		// 日志
		do {
			$deleteds = $this->gets($args);
			foreach($deleteds as $deleted) {
				//log_add($deleted['eid'], 0, "[说明:合同删除]", NULL, serialize($deleted));
			}
		}while(false);
	}

	function gets( $args ){
		if( empty( $args ) || !is_array( $args ) ) return false;
		global $db;
		$result = array();
		$where = $db->sqls( $args, 'AND' );
		$query = $db->query("SELECT * FROM sp_contract WHERE $where AND deleted=0");
		while( $rt = $db->fetch_array( $query ) ){
			$result[$rt['id']] = $rt;
		}
		return $result;
	}

	function meta( $ct_id, $meta_name = '', $meta_value = '' ){
		if( empty( $ct_id ) ) return false;
		global $db;
		$result = '';
		if( $meta_name && $meta_value ){
			$sql = "INSERT INTO sp_metas_ot ( ID, meta_name, meta_value, used )
					VALUES ( '$ct_id', '$meta_name', '$meta_value', 'contract' )
					ON DUPLICATE KEY UPDATE meta_value = VALUES( meta_value )";
			$db->query( $sql );
		} elseif( $meta_name ){
			$result = $db->get_var( "SELECT meta_value FROM sp_metas_ot WHERE ID = '$ct_id' AND meta_name = '$meta_name' AND used = 'contract'" );
		} else {
			$result = array();
			$query = $db->query("SELECT * FROM sp_metas_ot WHERE ID = '$ct_id' AND used = 'contract'");
			while( $rt = $db->fetch_array( $query ) ){
				$result[$rt['meta_name']] = $rt['meta_value'];
			}
		}
		return $result;
	}
}

?>