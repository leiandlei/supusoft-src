<?php
//合同：合同项目
if( !class_exists( 'contract' ) )
require_once MODEL_DIR . 'contract.class.php';

class contract_item extends contract{
	public $_tb='contract_item';
	public $_pk='cti_id';
 	
	function add( $args ){
		global $db;
		$default = array(
			
			'create_uid'	=> current_user('uid'),
			'create_date'	=> current_time('mysql'),
			
		);
		$args = parse_args( $args, $default );
		$id = $db->insert( 'contract_item', $args );
		$bf_info = $this->get(array( 'cti_id' => $id ));
		// 日志
		do {
			//log_add($bf_info['eid'], 0, "[说明:合同项目-增加]<合同编号:".$bf_info['ct_code'].">", NULL, serialize($bf_info));
		}while(false);
		return $id;
	}

	function get( $args ){
		if( empty( $args ) || !is_array( $args ) ) return false;
		global $db;
		$args = parse_args( $args );
		$where = $db->sqls( $args, 'AND' );
		$row = $db->get_row( "SELECT * FROM sp_contract_item WHERE $where AND deleted=0" );
		
		return $row;
	}

	function edit( $cti_id, $args ,$status=NULL){
		global $db;
		$af_info = $this->get(array( 'cti_id' => $cti_id ));
		$res=$db->update( 'contract_item', $args, array( 'cti_id' => $cti_id ) );
		$bf_info = $this->get(array( 'cti_id' => $cti_id ));
		// 日志
		do {
			//log_add($bf_info['eid'], 0, "合同项目-修改", serialize($af_info), serialize($bf_info));
		}while(false);
		return $res;
	}

	function del( $args ){
		if( empty( $args ) or !is_array( $args ) ) return false;
		global $db;
		$cti_id = $args['cti_id'];
		$af_info = $this->get(array( 'cti_id' => $cti_id ));
		$args = parse_args( $args );
		$db->update( 'contract_item', array( 'deleted' => 1 ), $args );
	}

	

}

?>