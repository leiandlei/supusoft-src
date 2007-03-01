<?php
//合同：合同费用登记

class cost{


	function add( $args ){
 
		global $db;
		$id = $db->insert( 'contract_cost', $args );
		$bf_info = $this->get($id);
		$ct_code = $db->get_var("select * from sp_contract where ct_id='$args[ct_id]' ");
		// 日志
		do {
			//log_add($bf_info['eid'], 0, "[说明:合同费用登记]<合同编号:$ct_code>", NULL, serialize($bf_info));
		}while(false);
		return $id;
	}

	function edit( $id, $args ){
		global $db;
		$af_info = $this->get($id);
		$db->update( 'contract_cost', $args, array( 'id' => $id ) );
		$bf_info = $this->get($id);
		$ct_code = $db->get_var("select * from sp_contract where ct_id='$args[ct_id]' ");
		// 日志
		do {
			//log_add($bf_info['eid'], 0, "[说明:合同费用修改]<合同编号:$ct_code>", serialize($af_info), serialize($bf_info));
		}while(false);
	}

	function get( $id ){
		global $db;
		$row = $db->get_row("SELECT * FROM sp_contract_cost WHERE id = '$id'");
		return $row;
	}

	function del( $id ){
		global $db;
		$af_info = $this->get($id);
		$row = $db->get_row("update sp_contract_cost set deleted='1' WHERE id = '$id'");
		return $row;
	}

	function gets( $ids ){
		if( empty( $ids ) ) return false;
		global $db;
		$result = array();
		$query = $db->query("SELECT * FROM sp_project WHERE id IN (".implode(',',$ids).")");
		while( $rt = $db->fetch_array( $query ) ){
			$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
			$result[$rt['id']] = $rt;
		}

		return $result;
	}


}

?>