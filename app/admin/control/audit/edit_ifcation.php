<?php
//再认证编辑
if( $step ){
	$task = load('task');
	$iid			= (int)getgp( 'iid' );
	$ifcation_date	= getgp( 'ifcation_date' );
	$note			= getgp( 'note' );
	$if_fuping		= getgp( 'if_fuping' );

	$new_ifcation = array(
		'ifcation_date'	=> $ifcation_date,
		'note'			=> $note,
		'if_fuping'			=> $if_fuping,
	);

	$sql = "select * from sp_ifcation where id='$iid' ";
	$af_info = $db->get_row($sql);
	if($if_fuping=='2'){
		$sql = "UPDATE sp_ifcation SET ifcation_date = '$ifcation_date' , note = '$note' ,status = '$if_fuping' WHERE id = '$iid'";
	}else{
		$sql = "UPDATE sp_ifcation SET ifcation_date = '$ifcation_date' , note = '$note' , status = '$if_fuping' WHERE id = '$iid'";
	}
	$db->query($sql);


	// 日志
	do {
		$sql = "select * from sp_ifcation where id='$iid' ";
		$bf_str = $db->get_row($sql);
		log_add($bf_str['eid'], 0, "[说明:客服维护-再认证编辑]", serialize($af_info), serialize($bf_str));
	}while(false);
	showmsg( 'success', 'success', "?c=audit&a=list_ifcation" );
} else {
	$id=getgp('iid');
	$sql = "select * from sp_ifcation where id='$id' ";
	$row = $db->get_row($sql);
	extract( $row, EXTR_SKIP );
	$che1 = $che2 = $che3 = '';
	if($status=='1'){
		$che1 = 'selected';
	}else if($status=='2'){
		$che2 = 'selected';
	}else{
		$che3 = 'selected';
	}
	tpl('audit/edit_ifcation');
}

?>