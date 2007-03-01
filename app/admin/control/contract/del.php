<?php
$ct_id = (int) getgp('ct_id');
if ($ct_id) {
    $where = array(
        'ct_id' => $ct_id
    );
    //删除合同 
    $ct->del($where);
    $ct_info = $ct->get(array(
        'ct_id' => $ct_id
    ));
    log_add($ct_info['eid'], 0, "[说明:" . $ct_info['ct_code'] . "合同删除]");
    //删除审核任务
    //通过审核项目表取得任务id
    $task_ids = $db->find_results('project', $where, 'tid');
    $task     = load('task');
    foreach ($task_ids as $v) {
        $task->del(array(
            'id' => $v['tid']
        )); //删除任务派人信息 
        $task->del_send($v['tid']); //删除派人信息
    }
    //删除审核项目
    $audit = load('audit');
    $audit->del($where);
    //删除合同项目 
    $cti->del($where);
    showmsg('success', 'success', "?c=contract&a=list");
}