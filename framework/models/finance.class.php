<?php
//财务

class finance extends model{

	function add( $args ){
		global $db; 
		$args = parse_args( $args, $default );
		$id = $db->insert( 'contract_cost_detail', $args ); ;
		return $id;
	}

	function edit( $pid, $args ){
		global $db;
		$af_info = $this->get($pid);
		$db->update( 'contract_cost_detail', $args, array( 'id' => $pid ) );
	}

	function get( $pid ){
		global $db;
		$row = $db->get_row("SELECT * FROM sp_contract_cost_detail WHERE id = '$pid'");
		return $row;
	}

	function del( $ccd_id ){
		if( empty( $ccd_id ) ) return false;
		global $db;
		
		$db->update('contract_cost_detail', array( 'deleted' => 1 ), array( 'id' => $ccd_id ) );
		return true;
	}

	function gets( $pids ){
		if( empty( $pids ) ) return false;
		global $db;
		$result = array();
		$query = $db->query("SELECT * FROM sp_project WHERE id IN (".implode(',',$pids).")");
		while( $rt = $db->fetch_array( $query ) ){
			$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
			$result[$rt['id']] = $rt;
		}
		return $result;
	}


}

?>