<?php
//项目模型-检查+检验+审定
class audit extends model{ 
	public $_tb='project'; 
	

	//新增项目
	function add( $default ){
		global $db; 
		$args[ct_code]= $db->get_var("select ct_code from sp_contract where ct_id='$default[ct_id]'");
		$cti_info=$this->db->find_one('contract_item',array('cti_id'=>$default['cti_id']));
		if($cti_info){
			$args['eid']=$cti_info['eid'];
			$args['iso']=$cti_info['iso'];
			$args['total']=$cti_info['total'];
			$args['audit_ver']=$cti_info['audit_ver'];
			$args['audit_code']=$cti_info['audit_code'];
			$args['use_code']=$cti_info['use_code'];
			$args['scope']=$cti_info['scope']; 
			
		}
		$default = parse_args( $args, $default );	
		$id = $db->insert( 'project', $default );  
		return $id;
	}
	//修改项目
	function edit($where, $args ){
		//默认为主键
		if(!is_array($where)){ 
			if(!$where)debug('pid传参数错误');
			$where=array( 'id' => $where );
		}  
		return $this->db->update( 'project', $args,$where); 
	}

	function get( $args ){
		if( empty( $args ) || !is_array( $args ) ) return false;
		global $db;
		$args = parse_args( $args );
		$where = $db->sqls( $args, 'AND' );
		$row = $db->get_row("SELECT * FROM sp_project WHERE $where AND deleted=0 order by id asc");
		return $row;
	}

	function del( $args,$from = false ){
		if( empty( $args ) or !is_array( $args ) ) return false;
		global $db;
		$args = parse_args( $args );
		$db->update( 'project', array( 'deleted' => 1 ), $args ); 
	}

	function gets( $args ){
		if( empty( $args ) || !is_array( $args ) ) return false;
		global $db;
		$result = array();
		$where = $db->sqls( $args, 'AND' );
		$query = $db->query("SELECT * FROM sp_project WHERE $where AND deleted=0");
		while( $rt = $db->fetch_array( $query ) ){
			$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
			$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] );
			$rt['iso_V'] = f_iso( $rt['iso'] );  
			$result[$rt['id']] = $rt;
		} 
		return $result;
	}
	//格式化行
	function _format_row($row){
		
		
		
		
	}


}

?>