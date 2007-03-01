<?php
/*
*人员管理  类
*
*
*
*/
class user{

	public $this_table = 'hr';
	public $this_table_meta = 'metas_hr';
	public $used = 'user';

	function add( $args ){
		global $db;
		$default = array(
			//'code'			=> '',	//人员编号
			//'username'		=> '',	//用户名
			//'password'		=> '',	//密码
			'cerate_uid'	=> current_user('uid'),	//创建人
			'cerate_date'	=> current_time('mysql')	//创建时间
		);
		$args = parse_args( $args, $default );

		$hr_id = $db->insert( $this->this_table, $args );


		//处理META
		$metas = getgp( 'meta' );

		$ADDSQL = array();
		foreach( $metas as $meta => $value ){
			$new_value = is_array( $value ) ? implode('|',$value) : $value;
			$ADDSQL[] = "( '$hr_id', '$meta', '$new_value', 'user' )";
			$n_arr[$meta] = $value;
		}
		if( $ADDSQL ){
			$sql = "INSERT INTO sp_metas_hr ( ID, meta_name, meta_value, used ) VALUES " . implode( ',', $ADDSQL );
			$sql .= " ON DUPLICATE KEY UPDATE meta_value = VALUES( meta_value )";
			$db->query( $sql );
		}


		return $hr_id;
	}

	function get( $uid, $meta = true ){
		if( empty( $uid ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_hr WHERE id = '$uid'");
		if( $meta ){
			$metas = $this->meta( $uid );
			$row = array_merge( $row, $metas );
		}
		return $row;
	}

	function edit( $uid, $args ){
		if( empty( $uid ) ) return false;
		global $db;
		$args = parse_args( $args );
		$old_info = $this->get($uid,false);
		$n_arr = array_diff_assoc( $args, $old_info );
		$o_arr = array();
		foreach( array_keys($n_arr) as $key ){
			$o_arr[$key] = $old_info[$key];
		}
		if( $n_arr ){
			//echo "更新主表";
			//print_R( $n_arr );
			echo "<br/>";
			$db->update( $this->this_table, $args, array( 'id' => $uid ) );
		}
		if( isset( $n_arr['ctfrom'] ) ){
			$ctfrom = $n_arr['ctfrom'];
			//echo "更新合同来源 $ctfrom<br/>";
			$db->update( 'hr_qualification', array(
					'ctfrom' => $ctfrom
				), array( 'uid' => $uid ) );

			$db->update( 'hr_audit_code', array(
					'ctfrom' => $ctfrom
				), array( 'uid' => $uid ) );
		} else {
			$ctfrom = $old_info['ctfrom'];
			//echo "更新合同来源 $ctfrom<br/>";
			$db->update( 'hr_qualification', array(
					'ctfrom' => $ctfrom
				), array( 'uid' => $uid ) );

			$db->update( 'hr_audit_code', array(
					'ctfrom' => $ctfrom
				), array( 'uid' => $uid ) );
		}

		// meta非空 处理副表
		if(isset($_REQUEST['meta'])) {
			//处理META
			$metas = getgp( 'meta' );

			$ADDSQL = array();
			foreach( $metas as $meta => $value ){
				
				$this->meta($uid,$meta,$value);
			}
			
		}
	}

	function del( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$db->update( $this->this_table, array( 'deleted' => '1' ), array( 'id' => $uid ) );
		//log_add('',$uid,"删除人员",NULL,NULL);
	}

	function meta( $uid, $meta_name = '', $meta_value = false ){
		if( empty( $uid ) ) return false;
		global $db;
		$result = '';
		if( $meta_name && $meta_value !== false ){
			$old_metas = $this->meta( $uid );
			if(isset($old_metas[$meta_name])){
			if( $meta_value != $old_metas[$meta_name]){
						$db->update("metas_hr",array("meta_value"=>$meta_value),array("ID"=>$uid,"meta_name"=>$meta_name));
					}
				
				}else{
					$db->insert("metas_hr",array("meta_value"=>$meta_value,"ID"=>$uid,"meta_name"=>$meta_name,"used"=>"user"));
				
				
				}
			
		} elseif( $meta_name ){
			$result = $db->get_var( "SELECT meta_value FROM sp_metas_hr WHERE ID = '$uid' AND meta_name = '$meta_name' AND used = '$this->used'" );
		} else {
			$result = array();
			$query = $db->query("SELECT * FROM sp_metas_hr WHERE ID = '$uid' AND used = '$this->used'");
			while( $rt = $db->fetch_array( $query ) ){
				$result[$rt['meta_name']] = $rt['meta_value'];
			}
		}
		return $result;
	}
}
?>