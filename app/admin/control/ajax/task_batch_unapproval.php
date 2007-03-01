<?php
/*
*任务审批（批量）
*/

$approval_date = getgp('approval_date');
    $tids = getgp('tids');
    if (!$tids) print_json(array(
        'state' => 'no',
        'msg' => '请选择要审批的项目！'
    )); 
    $audit = load('audit');
    //审核项目 状态变更
    //$pids = array();
    $query = $db->query("SELECT id,tid FROM sp_project WHERE tid IN (" . $tids . ") and deleted=0");
    while ($rt = $db->fetch_array($query)) {

        $audit->edit($rt['id'], array(
            'status' => 2,
			'is_bao' => '0',
			'bao_date' => '',
			'bao_uid' => '',
		));

        //审核任务 状态更更
        $db->update('task', array(
            'status' => 2,
        ) , array(
            'id' => $rt['tid']
        ));
    }

    $sid = '';
    $query = $db->query('select id from sp_shytj where tid in('.$tids.')');
    while ( $rts = $db->fetch_array($query) ) {
        $sid .= $rts['id'].',';
    }
    if( !empty($sid) ){
        $sid = substr($sid,0,strlen($sid)-1);
        $sql = 'update sp_shytj set del=0 where id in('.$sid.')';
        $db->query($sql);
        $sql = 'update sp_shytj_detail set del=0 where sid in('.$sid.')';
        $db->query($sql);
    }
    
	//添加日志
	foreach($tids as $tid){
		$eid=$db->getField('task','eid',array('id'=>$tid));  
		log_add($eid,0,'批量撤销任务审批，任务ID：'.$tid);
	}
	
  
  
    print_json(array(
        'state' => 'ok',
        'msg' => 'success'
    ));