<?php
/*
*人员管理  类
*
*
*
*/
class customer{

	public $this_table = 'customer';//@zbzytech 这里不需要加前缀 配置文件里面加过了
	//public $customer = 'customer';
    //public $used = 'user';

	function add( $args ){
		global $db;
		$default = array(
			//'code'			=> '',	//人员编号
			//'username'		=> '',	//用户名
			//'password'		=> '',	//密码
			//'cerate_cu_id'	=> current_user('cu_id'),	//创建人
			'cerate_date'	=> current_time('mysql')	//创建时间
		);
		$args = parse_args( $args, $default );

		$cu_id = $db->insert( $this->this_table, $args );
		return $cu_id;
	}

	function get( $cu_id, $meta = true ){
		if( empty( $cu_id ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_customer WHERE cu_id = '$cu_id'");
		// if( $meta ){
		// 	$metas = $this->meta( $cu_id );
		// 	$row = array_merge( $row, $metas );
		// }
		return $row;
	}

	//@zbzytech 判断用户名必须唯一 否则乱登陆了
	function username_exist( $username){
		//if( empty( $cu_id ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_customer WHERE username = '$username'");
		// if( $meta ){
		// 	$metas = $this->meta( $cu_id );
		// 	$row = array_merge( $row, $metas );
		// }
		return $row;
	}

	function edit( $cu_id, $args ){
		if( empty( $cu_id ) ) return false;
		global $db;
		$args = parse_args( $args );
		$row = $db->update( $this->this_table, $args, array( 'cu_id' => $cu_id ) );

		return $row;
	}

	function del( $cu_id ){
		if( empty( $cu_id ) ) return false;
		global $db;
		$db->update( $this->this_table, array( 'deleted' => '1' ), array( 'cu_id' => $cu_id ) );
		//log_add('',$cu_id,"删除人员",NULL,NULL);
	}

	function meta( $cu_id, $meta_name = '', $meta_value = false ){
		if( empty( $cu_id ) ) return false;
		global $db;
		$result = '';
		if( $meta_name && $meta_value !== false ){
			$old_metas = $this->meta( $cu_id );
			if(isset($old_metas[$meta_name])){
			if( $meta_value != $old_metas[$meta_name]){
						$db->update("metas_hr",array("meta_value"=>$meta_value),array("ID"=>$cu_id,"meta_name"=>$meta_name));
					}
				
				}else{
					$db->insert("metas_hr",array("meta_value"=>$meta_value,"ID"=>$cu_id,"meta_name"=>$meta_name,"used"=>"user"));
				
				
				}
			
		} elseif( $meta_name ){
			$result = $db->get_var( "SELECT meta_value FROM sp_metas_hr WHERE ID = '$cu_id' AND meta_name = '$meta_name' AND used = '$this->used'" );
		} else {
			$result = array();
			$query = $db->query("SELECT * FROM sp_metas_hr WHERE ID = '$cu_id' AND used = '$this->used'");
			while( $rt = $db->fetch_array( $query ) ){
				$result[$rt['meta_name']] = $rt['meta_value'];
			}
		}
		return $result;
	}
}
?>