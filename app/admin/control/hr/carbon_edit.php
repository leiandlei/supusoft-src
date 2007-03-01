<?php

if($_GET['uid']) //添加编辑信息
$hrInfo=$db->get_row( " SELECT * FROM sp_hr WHERE id=$_GET[uid]");
if($_GET['auditorId']){
	//获取需要编辑的信息
	$sql=" SELECT auditor.*,hr.name FROM sp_task_audit_team auditor LEFT JOIN sp_hr hr ON hr.id=auditor.uid WHERE auditor.id=$_GET[auditorId] ";
	$auditorInfo=$db->get_row($sql);
	$hrInfo['uid']          =$auditorInfo['uid']; 
	$hrInfo['name']         =$auditorInfo['name']; 
    $hrInfo['taskBeginDate']=$auditorInfo['taskBeginDate']; 
    $hrInfo['taskEndDate']  =$auditorInfo['taskEndDate']; 
	$taskBeginDate          = $hrInfo['taskBeginDate'];
	$taskEndDate            = $hrInfo['taskEndDate'];
     
	if($_POST){ 		
	$yjdst_time = getgp('yjdst_time');
	$yjded_time = getgp('yjded_time');
	$params ['uid']           =$hrInfo['uid'];
	$params ['name']          =$hrInfo['name'];
	$params ['taskBeginDate'] =getgp('taskBeginDate');
	$params ['taskEndDate']   =getgp('taskEndDate');
	$params ['note']          =getgp('note');
	$db->update('task_audit_team',$params,array('id'=>"$_GET[auditorId]"));
	$REQUEST_URI='?c=hr&a=carbon_list';
	showmsg( 'success', 'success', $REQUEST_URI );  
 	exit;	
	} 
} 
if($_POST){
	$name_hr  =$db->get_row( "select id from  sp_hr where name = '".$_POST['name']."' and deleted = 0");
	$params ['uid']           =$name_hr['id'];
	$params ['name']          =$_POST['name'];
	$params ['data_for']      ='2';
	$params ['taskBeginDate'] =getgp('taskBeginDate');
	$params ['taskEndDate']   =getgp('taskEndDate');
	$params ['note']          =getgp('note');

	$db->insert('task_audit_team',$params);
	$REQUEST_URI='?c=hr&a=carbon_list';
	showmsg( 'success', 'success', $REQUEST_URI ); 
}
  
 
	$id = !empty($id)?$id:(!empty($getID)?$getID:'');
	if( !empty($id) )
	{
		$sql      = 'select * from `sp_task_audit_team`  where deleted=0  and `id`='.$id;
		$results = $db -> getOne($sql);
	    extract($results,EXTR_OVERWRITE);
	    
	}
 
	
tpl( );
