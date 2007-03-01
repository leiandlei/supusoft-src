<?php

	$tid = (int)getgp('tid');
	//$jihuas = getgp('jihua');
    $task_note = load('task_note');
    $task = load('task');
	$val = array(
		'jh_re_note' => getgp('jh_re_note'),
	);
    $value = array(
       // 'bccccs' => getgp('bccccs') ,
       // 'bcccbm' => getgp('bcccbm') ,
      //   'zdsj' => getgp('zdsj') ,
        'step2' => getgp('step2') ,
        'step3' => getgp('step3') ,


    );
    $row = $task_note->get($tid);
    $res = $task->get(array('id'=>$tid));
    if ($row) {
        $task_note->edit($tid, $value);
    } else {
        $task_note->add($tid, $value);
    }
	
	if ($res) {
        $task->edit($tid, $val);
    } else {
        $task->add($tid, $val);
    }
 if($upload_file_date=getgp("upload_file_date")){
	$db->update("task",array("upload_file_date"=>date("Y-m-d H:i:s")),array("id"=>$tid));
	$query=$db->query("SELECT * FROM `sp_project` WHERE `tid` = '$tid' and deleted=0");
	while($rt=$db->fetch_array($query)){
		if($rt[audit_type]=='1003'){
			
			$_tid=$db->get_var("SELECT tid FROM `sp_project` WHERE `cti_id` = '$rt[cti_id]' and deleted=0 and audit_type='1002'");
			$db->update("task",array("upload_file_date"=>date("Y-m-d H:i:s")),array("id"=>$_tid));
			
		}
		
		
	}
	
 }
if($upload_plan_date=getgp("upload_plan_date")){
	$db->update("task",array("upload_plan_date"=>date("Y-m-d H:i:s")),array("id"=>$tid));
 
 } 
$db->update("task",array("bufuhe"=>getgp("bufuhe")),array("id"=>$tid));
    $REQUEST_URI = '?c=auditor&a=task';
    showmsg('success', 'success', $REQUEST_URI);