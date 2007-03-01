<?php
unset($datas);
$datas = $db_source->get_results("select * from zbverifymem_" . $date);
foreach ($datas as $v) {
    //人员信息
    $new_hr  = array(
        'name' => $v['ZGroMemName'],
        'card_type' => $v['ZIDType'],
        'card_no' => $v['ZID_NUMBER'],
        'audit_job' => $v['ZISSPECIAL']
    );
    $uid     = get_uid($new_hr, true);
    //资格信息
    $iso     = $db_source->getField('zbcert_get_' . $date, 'ZProCode', array(
        'ZOSCID' => $v['ZOSCID']
    ));
    $new_qua = array(
        'uid' => $uid,
        'qua_type' => $v['ZQuaID'],
        'qua_no' => $v['ZMemCertCode'],
        'iso' => substr($iso, 0, 3)
    );
    get_hr_qua($new_qua, true);
    //	 echo $db_source->sql;
    //echo $work_code;
    $cti_id    = $db->getField('certificate', 'cti_id', array(
        'certno' => $v['ZOSCID']
    ));
	if(!$cti_id){
		echo '找不到合同';
		
	}
    //,'audit_type'=>$v['ZAUDSTATUS']
    $pid       = $db->getField('project', 'id', array(
        'cti_id' => $cti_id
    ));
	if(!$pid){
		echo $db->sql;
		echo $date.'找不到对应项目';
		echo  '证书号'.$v['ZOSCID'].'<br>合同项目ID:'.$cti_id;
	 	
		echo '<br>';
		continue;
		
		
	}
    // echo $db->sql;
    //计算任务ID
    $work_code = $db_source->getField('zbcert_get_' . $date, 'ZORGID', array(
        'ZOSCID' => $v['ZOSCID']
    ));
    $eid       = get_eid(array(
        'work_code' => $work_code
    ));
    if (!$eid) {
        echo '找不到企业';
        echo '<br>';
        continue;
    }
    $tid = $db->getField('task', 'id', array(
        'eid' => $eid,
        'old_id' => $date
    ));
    //企业有任务派人=》但是没有任务
    //1.问题：任务找不到派人信息
    if (!$tid) {
        echo $db->sql;
        echo '<br>找不到对应任务<br>';
        echo '证书编号:' . $v['ZOSCID'];
        echo '<br>';
        echo '日期' . $date;
        echo 'eid' . $eid;
        echo '<br>企业名称：' . $db->getField('enterprises', 'ep_name', array(
            'eid' => $eid
        ));
        ;
        echo '<hr>';
        continue;
    }
    $new_tat = array(
        'tid' => $tid,
        'ctfrom' => '01000000',
        'uid' => $uid,
        'pid' => $pid,
        'role' => '10' . $v['ZMEMROLE'],
        'old_id' => $date . '|' . $v['ZSQL_ID'],
        'qua_type' => $v['ZQuaID'],
        'taskBeginDate' => $v['ZAUDSTADATE'],
        'taskEndDate' => $v['ZAUDENDDATE']
    );
    $tat_id  = $db->getField('task_audit_team', 'id', $new_tat);
    //$pid and 
    if (!$tat_id){
    //新增派人
        load('task')->add_team_item($new_tat);
	}  
}