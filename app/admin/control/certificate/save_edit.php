<?php

$tid = (int)getgp( 'tid' );

if($_POST){

	$sql_eid    = "select eid from sp_task where id='$tid' and save_status=0";
	$arr_eid    = $db->getAll($sql_eid);
	foreach ($arr_eid as  $value) {
	    $arrEid[0] = $value['eid'];
	}
    
    $auditType = $db->getOne("select audit_type from sp_project where tid='$tid' and deleted=0");
    $arr_auditType = array("1002","1003");
    // echo '<pre />';
    // print_r($auditType);exit;
    if (in_array($auditType['audit_type'], $arr_auditType)) {
    	$sql_tid = "select tid from sp_project where eid='$arrEid[0]' and audit_type in ('1002','1003') and deleted=0";
	    $arr_tid    = $db->getAll($sql_tid);
		foreach ($arr_tid as $value) {
		    $tids[] = $value['tid'];
		}
		$tids = array_unique($tids);

		foreach ($tids as $tid) {
		    $db->update("task",array("save_date"=>date("Y-m-d"),"save_note"=>$_POST['save_note'],"save_status"=>'1'),array("id"=>$tid));
		}
    }else{
    	$db->update("task",array("save_date"=>date("Y-m-d"),"save_note"=>$_POST['save_note'],"save_status"=>'1'),array("id"=>$tid));
    }
	showmsg("success","success","?c=certificate&a=save_file");
}else{

	//取任务信息
	// $sql = "SELECT t.*,e.ep_name FROM sp_task t INNER JOIN sp_enterprises e ON e.eid = t.eid WHERE t.id = '$tid'";
	// $task = $db->get_row( $sql );
	$eid=$db->get_var("SELECT eid FROM `sp_task` WHERE `id` = '$tid'");
	$task_archives=$db->get_results("SELECT * FROM `sp_attachments`  where tid='$tid'");

	$ct_ids=$db->get_col("SELECT ct_id FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
	$ct_ids=array_unique($ct_ids);
	$ct_id=join("|",$ct_ids);
	tpl();
}



 


?>