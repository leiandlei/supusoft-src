<?php
/*
*批量退回
*/

$approval_date = getgp('approval_date');
    $tids = explode(',', getgp('tids'));
    $tids = array_unique($tids);
    if (!$tids) print_json(array(
        'state' => 'no',
        'msg' => '请选择要审批的项目！'
    ));
    /* if (!$approval_date) print_json(array(
        'state' => 'no',
        'msg' => '请填写审批日期！'
    )); */
    $audit = load('audit');
    //审核项目 状态变更
    $pids = array();
    $query = $db->query("SELECT id FROM sp_project WHERE tid IN (" . implode(',', $tids) . ")");
    while ($rt = $db->fetch_array($query)) {
        $pids[] = $rt['id'];
        $audit->edit($rt['id'], array(
            'status' => 1
        ));
    }
    //审核任务 状态更更
    $db->update('task', array(
        'status' => 1,
    ) , array(
        'id' => $tids
    ));
	
	//撤销任务同时删除派人信息
    $re = $db->query("update sp_task_audit_team set deleted=1 where tid IN (" . implode(',', $tids) . ")");
    if(!$re){
    	print_json(array(
    	'state' => 'no',
    	'msg' => '更新deleted异常！'
    			));
    }
	//添加日志
	foreach($tids as $tid){
		$eid=$db->getField('task','eid',array('id'=>$tid));  
		log_add($eid,0,'退回到待派人，任务ID：'.$tid.'删除任务派人信息'); //
	}
	
	
    print_json(array(
        'state' => 'ok',
        'msg' => 'success'
    ));