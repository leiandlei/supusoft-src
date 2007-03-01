<?php
//人员经历类

class experience extends model{


	function add( $args ){
		global $db;
		unset($args['id']);


		$args['add_hr_id'] = current_user('uid');
		$args['add_date'] = current_time('mysql');
 		$id = $db->insert( 'hr_experience', $args );
		return $id;
	}

	function edit( $pid, $args ){
		global $db;
		$args['edit_hr_id']=current_user('uid');
		$args['edit_date']= current_time('mysql');
		$db->update( 'hr_experience', $args, array( 'id' => $pid ) );
	}

	function get( $id ){
		if( empty( $id )  ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_hr_experience WHERE id='$id'");
		return $row;
	}

	function del( $id ){
		if( empty( $id )) return false;
		global $db;
		$db->update( 'hr_experience', array( 'deleted' => 1 ), array( 'id' => $id ) );
	}
	//获取数量
	function get_num($where='',$join=''){
		global $db;
		$sql="SELECT COUNT(*) FROM sp_hr_experience he $join where 1 $where";
		return $db->get_var($sql);
	}
	//获取分页列表
	function get_page($where,$pages='',$join=''){
		global $db;
 		$sql = "SELECT * FROM sp_hr_experience he $join  where 1 $where order by he.id asc $pages[limit]";
		$query = $db->query( $sql );
		$datas = array();
		while( $rt = $db->fetch_array( $query ) ){
			$datas[] = $rt;
		}
		return $datas;
	}



}

?>