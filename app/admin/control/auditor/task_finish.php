<?php
$tid = (int)getgp('tid');
    $rect_finish = getgp('rect_finish');
    //评定设为已整改 无整改
	$query=$db->query("SELECT * FROM `sp_project` WHERE `tid` = '$tid' and deleted=0");
	while($r=$db->fetch_array($query)){
		if($r[pd_type]=='1')
			$is_finish='1';
		$up=array('rect_finish' => $rect_finish,'is_finish' => $is_finish);
    $db->update('project', $up , array('id' => $r[id])); 
	}
	$db->update('task', array(
            'rect_finish' => $rect_finish,
            'rect_date' => date("Y-m-d")
        ) , array(
            'id' => $tid 
        )); 
	$re_notes=getgp("re_note");
	foreach($re_notes as $id=>$re_note){
		$db->update("assess_notes",
					array(	"re_note"=>$re_note,
							"re_uid"=>current_user("uid"),
						),
					array("id"=>$id));
	
	
	}
    showmsg('success', 'success', "?c=auditor&a=task_edit&tid=$tid");