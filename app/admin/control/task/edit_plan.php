<?php
/*
 * 审核安排
 */
$tid = (int)getgp('tid');

if ($_POST) {
	$up=$_POST;
	unset($up[tid]);
	if($_POST[jh_sp_status]=='2')
		$up['upload_plan_date']='';
	$up[jh_sp_date]=date("Y-m-d");
	$db->update("task",$up,array("id"=>$tid));
    showmsg('success', 'success', "?c=task&a=edit_plan&tid=$tid");
} else {
	$archive=$db->get_row("SELECT * FROM `sp_attachments` WHERE tid='$tid' AND ftype='3003'");
	$t_info=$db->get_row("SELECT * FROM `sp_task` WHERE `id` = '$tid' ");
	extract($t_info);
	$ct_id=$db->get_var("select ct_id from sp_project where tid='$tid' and deleted=0 order by ct_id desc");
    tpl();
}
?>
