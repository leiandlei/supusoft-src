<?php
//证书
class sms extends model{

	public $this_table = 'sms';



	function add( $args ){
		global $db;
		$ep_info=load('enterprise')->get(array('eid'=>$args[eid]));
		$default = array(  
			'is_sms'	=> '0',	
			'sms_name'	=> '01',	
			'sms_person'=> $ep_info[person],	
			'sms_tel'	=> $ep_info[person_tel],
            'sms_addr'	=> $ep_info[cta_addr],	
			'sms_code'	=> $ep_info[cta_addrcode],	
				
		);
		$args = parse_args( $args, $default );
		return $db->insert( $this->this_table, $args );
		
		 
	}

	function get( $args ){
		if( empty( $args ) ) return false;
		global $db;
        
        if(is_array( $args )){
            $args = parse_args( $args );
            $where = $db->sqls( $args, 'AND' );
        }else
            $where=" id='$args'";
		$result = $db->get_row("SELECT * FROM  sp_sms WHERE 1 AND $where AND deleted=0");
		
		return $result;
	}

	function edit( $uid, $args ){
		if( empty( $uid ) ) return false;
		global $db;
		$args = parse_args( $args );
		return $db->update( $this->this_table, $args, array( 'id' => $uid ) );
	
	}

	function del( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$af_info = $this->get($uid);
		$db->update( $this->this_table, array( 'deleted' => '1' ), array( 'id' => $uid ) );
		$bf_info = $this->get($uid);
		// 日志
		do {
			////log_add($bf_info['eid'], 0, '删除邮寄信息', serialize($af_info), serialize($bf_info));
		}while(false);
	}



}
?>