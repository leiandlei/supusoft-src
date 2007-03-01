<?php
$sql  = 'select * from sp_task where deleted=0';
$data = $db->getAll($sql);
foreach($data as $task)
{
	$sql = 'select * from sp_project where deleted=0 and tid='.$task['id'];
	$porject = $db->getOne($sql);
	if(!empty($porject))
	{
		$db->update('task',array('ct_id'=>$porject['ct_id'],'audit_ver'=>$porject['audit_ver'],'audit_type'=>$porject['audit_type']),array('id'=>$task['id']));
	}
	
}
?>
