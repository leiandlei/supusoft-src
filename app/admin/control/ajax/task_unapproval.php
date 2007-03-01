<?php
/*
*取消审批
*/


 $tid = (int)getgp('tid');
    if (!$tid) print_json(array(
        'state' => 'no',
        'msg' => '任务ID传递错误，请联系管理员。'
    ));
    $task = load('task');
    $row = $task->get(array(
        'id' => $tid
    ));
    if (!$row) print_json(array(
        'state' => 'no',
        'msg' => '任务不存在！'
    ));
    if (2 > $row['status']) print_json(array(
        'state' => 'no',
        'msg' => '任务尚未审批！'
    ));
    $audit = load('audit');
    if ($row) {
        $pids = array();
        $query = $db->query("SELECT id FROM sp_project WHERE tid = '$tid' and deleted=0");
        while ($rt = $db->fetch_array($query)) {
            $pids[] = $rt['id'];
            $audit->edit($rt['id'], array(
                'status' => 2,
				'is_bao' => '0',
				'bao_date' => '',
				'bao_uid' => '',
            ));
        }
        $task->edit($tid, array(
            'status' => 2
        ));
    }

    $sid = '';
    $query = $db->query('select id from sp_shytj where tid='.$tid);
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

	log_add($row['eid'],0,'撤销任务审批，任务ID：'.$tid);
    print_json(array(
        'state' => 'ok',
        'msg' => 'success'
    ));