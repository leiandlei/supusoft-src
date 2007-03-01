<?php
/*
 * 合同评审撤销审批
 */

$ct_id = (int)getgp('ct_id');
   $af_info = $db->get_row("select * from sp_contract where ct_id='$ct_id' ");
    //删除任务
    $db->del('project', array(
        'ct_id' => $ct_id
    ));
    $db->update('contract', array(
        'status' => 2
    ) , array(
        'ct_id' => $ct_id
    ));
 $bf_info = $db->get_row("select * from sp_contract where ct_id='$ct_id' ");
// 日志
 
log_add($bf_info['eid'], 0, "撤销合同审批 合同编号:" . $bf_info['ct_code'] , serialize($af_info), serialize($bf_info ));


    print_json(array(
        'status' => 'ok',
        'msg' => 'success'
    ));