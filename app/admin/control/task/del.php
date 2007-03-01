<?php
$tid = (int)getgp('tid');
	//$o_task = load( 'task' );
	$task_info=$task->get(array('id'=>$tid));
	
	
	log_add($task_info['eid'], 0, "[说明:删除任务]",'','');
	
	//审核项目恢复到未安排
	$db->update( 'project', array( 'status' => 0), array( 'tid' => $tid ) );
	//删除派人
	$task->del_send( $tid );
	//删除任务
	$task->del( array( 'id' => $tid ) );
 
	showmsg( 'success', 'success', $_SERVER['HTTP_REFERER'] );