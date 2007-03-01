<?php
//人员：小类申请
class auditcodeapp{

	public $this_table = 'hr_audit_code_app';


	function add( $args ){
		global $db;
		$default = array(
			'uid'			=> '',	//
			'note'			=> '',	//
			'note2'			=> '',	//
			'status'		=> 1,	//企业状态 -1为删除状态
		);
		$args = parse_args( $args, $default );
		$id = $db->insert( $this->this_table, $args );
		$bf_info = $this->get($id);
		$bf_str =serialize($bf_info);
		//log_add($bf_info['uid'],'增加小类申请','',$bf_str);
		return $id;
	}

	function get( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_hr_audit_code_app WHERE id = '$uid'");
		return $row;
	}

	function edit( $uid, $args ){
		if( empty( $uid ) ) return false;
		global $db;
		$args = parse_args( $args );
		$af_info = $this->get($uid);
		$db->update( $this->this_table, $args, array( 'id' => $uid ) );
		$bf_info = $this->get($uid);
		$af_str = serialize($af_info);
		$bf_str = serialize($bf_info);
		//log_add('',$bf_info['uid'],'编辑小类申请',$af_str,$bf_str);
	}

	function del( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$db->update( $this->this_table, array( 'status' => -1 ), array( 'id' => $uid ) );
		 $info=$this->get($uid);
	 	//log_add('',$info['uid'],'删除小类申请','',serialize($info));

	}


}
?>