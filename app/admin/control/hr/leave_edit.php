<?php
if($_GET['uid']) //添加编辑信息
$hrInfo=$db->get_row( " SELECT * FROM sp_hr WHERE id=$_GET[uid]");
if($_GET['auditorId']){
	//获取需要编辑的信息
	$sql=" SELECT auditor.*,hr.name FROM sp_task_audit_team auditor LEFT JOIN sp_hr hr ON hr.id=auditor.uid WHERE auditor.id=$_GET[auditorId] ";
	$auditorInfo=$db->get_row($sql);
	$hrInfo['uid']         =$auditorInfo['uid']; 
	$hrInfo['name']        =$auditorInfo['name']; 
	
    $hrInfo['taskBeginDate']=$auditorInfo['taskBeginDate']; 
    $hrInfo['taskEndDate']  =$auditorInfo['taskEndDate']; 
	$taskBeginDate          = $hrInfo['taskBeginDate'];
	$taskEndDate            = $hrInfo['taskEndDate'];
	if($_POST){ 
		$params ['uid']           =$hrInfo['uid'];
		$params ['name']          =$hrInfo['name'];
		$tb_date                  = getgp('taskBeginDate');
		$te_date                  = getgp('taskEndDate');
		$params ['taskBeginDate'] = $tb_date;
		$params ['taskEndDate']   = $te_date;
		$params ['note']          =getgp('note');
		$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=".$params ['uid']." AND id<>".$_GET['auditorId']." AND taskBeginDate<='$te_date' AND taskBeginDate>='$tb_date' AND deleted='0'";
		$if_exist_b=$db->get_var($sql);
		$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=".$params ['uid']." AND id<>".$_GET['auditorId']." AND taskEndDate<='$te_date' AND taskEndDate>='$tb_date' AND deleted='0'";
		$if_exist_e=$db->get_var($sql);
		if ($if_exist_b=='0'&&$if_exist_e=='0') {
			$db->update('task_audit_team',$params,array('id'=>"$_GET[auditorId]"));
			$REQUEST_URI='?c=hr&a=leave_list';
			showmsg( 'success', 'success', $REQUEST_URI );  
		 	exit;
		}else{
			showmsg( '操作失败，与已录入日程冲突', 'error', $REQUEST_URI );
		}
	} 
} 
if($_POST){
	
	$name_hr  =$db->get_row( "select * from  sp_hr where name = '".$_POST['name']."' and deleted = 0");
	$params ['uid']           =$name_hr['id'];
	$params ['name']          =$_POST['name'];
	$params ['data_for']      ='6';
	$tb_date                  = getgp('taskBeginDate') . ' ' .$yjdst_time;
	$te_date                  = getgp('taskEndDate') . ' ' .$yjded_time;
	$params ['taskBeginDate'] = $tb_date;
	$params ['taskEndDate']   = $te_date;
	$params ['note']          =getgp('note');
	$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=".$params ['uid']." AND taskBeginDate<='$te_date' AND taskBeginDate>='$tb_date' AND deleted='0'";
	$if_exist_b=$db->get_var($sql);
	$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=".$params ['uid']." AND taskEndDate<='$te_date' AND taskEndDate>='$tb_date' AND deleted='0'";
	$if_exist_e=$db->get_var($sql);
	if ($if_exist_b=='0'&&$if_exist_e=='0') {
		$db->insert('task_audit_team',$params);
		$REQUEST_URI='?c=hr&a=leave_list';
		showmsg( 'success', 'success', $REQUEST_URI );
	}else{
		showmsg( '操作失败，与已录入日程冲突', 'error', $REQUEST_URI );
	}
}

$id = !empty($id)?$id:(!empty($getID)?$getID:'');
if( !empty($id) )
{
	$sql      = 'select * from `sp_task_audit_team`  where deleted=0  and `id`='.$id;
	$results = $db -> getOne($sql);
    extract($results,EXTR_OVERWRITE);
    
}

tpl();
