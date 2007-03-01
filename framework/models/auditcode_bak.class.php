<?php
//人员：小类
class auditcode{

	public $this_table = 'hr_audit_code';
	public $used = 'auditcode';


	function add( $args ){
		global $db;
		$default = array(
			'source'		=> '',	//能力来源
			'pass_date'		=> '',	//合同来源
			'note'			=> '',	//企业名称
		);


		$arr=explode('；',$args[use_code]);

		$args = parse_args( $args, $default );

		foreach($arr as $v){
			$args['use_code']=$v;
			$id=$db->get_var("SELECT id FROM sp_hr_audit_code WHERE use_code='$v' AND uid=$args[uid] AND iso='$args[iso]' AND deleted=0");
			if(!$id){			
 				$id=$db->insert( $this->this_table, $args );
				//日志
				$af=serialize($this->get($id));
				//log_add('',$args['uid'],'增加小类代码','',$af);
			}
			else
				$this->edit($id,$args);
				
		}


	}

	function get( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$row = $db->get_row("SELECT * FROM sp_hr_audit_code WHERE id = '$uid'");
		$metas = $this->meta( $uid );
		$result = array_merge( $row, $metas );
		return $result;
	}

	function edit( $id, $args ){
		if( empty( $id ) ) return false;
		global $db;
		$args = parse_args( $args );
		$af_str = serialize($this->get($id));
		$db->update( $this->this_table, $args, array( 'id' => $id ) );
 		$bf_str = serialize($this->get($id));
	   	$uid = $db->get_var("select uid from sp_hr_qualification where id='$bf_info[qid]' ");

	}

	function del( $uid ){
		if( empty( $uid ) ) return false;
		global $db;
		$db->update( $this->this_table, array( 'deleted' => '1' ), array( 'id' => $uid ) );
		$db->get_var("select uid from sp_hr_qualification where id=$uid");
		$bf_str=serialize($this->get($uid));
		//log_add('',$_GET['uid'],'删除业务代码','',$bf_str);
	}


	function meta( $eid, $meta_name = '', $meta_value = '' ){
		if( empty( $eid ) ) return false;
		global $db;
		$result = array();
		if( $meta_name && $meta_value ){
			$old_metas = $this->meta( $eid );
			if(isset($old_metas[$meta_name])){
				if( $meta_value != $old_metas[$meta_name]){
					$db->update("metas",array("meta_value"=>$meta_value),array("ID"=>$eid,"meta_name"=>$meta_name));
				}
				
				}else{
					$db->insert("metas",array("meta_value"=>$meta_value,"ID"=>$eid,"meta_name"=>$meta_name,"used"=>"$this->used"));
				
				
				}
			
		}  
		return $result;
	}
}
?>