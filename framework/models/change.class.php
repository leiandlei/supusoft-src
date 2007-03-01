<?php
//证书变更类
class change extends model{
	//接口
	protected $_tb = 'certificate_change';
	protected $_pk='id';
	protected $used = 'cert_change';  
	
	function add( $args ){
		global $db;
	 
		return $db->insert( $this->_tb, $args );
	}

	function get( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_certificate_change WHERE id = '$uid'");
		$metas = $this->meta( $uid );
		$result = array_merge( $row, $metas );
		return $result;
	function edit( $id, $args ){
		if( empty( $id ) ) return false;
		global $db;
		$args = parse_args( $args );
		$db->update( $this->_tb, $args, array( 'id' => $id ) );
	}
	}


	function del( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$db->update( 'certificate_change', array( 'deleted' => 1 ), array( 'id' => $uid ) );
	}
	//获取变更列表
	function gets($where = '', $fields = '*', $joins = '', $pages = '', $order = ''){
		//分解where条件
		$where=$this->db->sqls($where);  
		$sql=" SELECT $fields FROM {$this->_pre}{$this->_tb} WHERE {$where} {$order} {$pages['limit']}";
		$query=$this->db->query($sql);
		while($row=$this->db->fetch_array($query)){
			//变更格式化
			$row=$this->_format_row($row);
 			$rs[]=$row;
		} 
		return $rs;
	}
	//格式化
	function _format_row($row){
		//变更类型
		$row['format_type']=read_cache('certchange',$row['cg_type']); //变更类型
		return $row; 
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