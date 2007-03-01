<?php
//新增任务
unset($datas);
$datas = $db_source->get_results("select * from zbcert_get_" . $date);
foreach ($datas as $v) {
	//变更类型
/*    if ($v['ZTYPECODES'])
        continue;*/
    //新增任务	 
    $eid      = get_eid(array(
        'work_code' => $v['ZORGID']
    ));
    $new_task = array(
        'eid' => $eid,
        'tb_date' => $v['ZAUDSTADATE'],
        'te_date' => $v['ZAUDENDDATE'],
        'ctfrom' => '01000000',
        'status' => '3',
        'old_id' => $date,// . '|' . $v['ZSQL_ID']
    );
    $tid      = get_tid($new_task, true);
    //验证任务是否有派人信息
    $is_tat   = $db_source->getField('zbverifymem_' . $date, 'ZGroMemName', array(
        'ZOSCID' => $v['ZOSCID']
    ));
    if (!$is_tat) {
		echo $date;
		echo '<br>';
        echo '没有派人信息' . $v['ZOSCID'];
        echo '<br>';
    }
    //增加项目 
    $cti_id   = $db->getField('certificate', 'cti_id', array(
        'certno' => $v['ZOSCID']
    ));
	
	
    $new_proj = array(
        'cti_id' => $cti_id,
        'ctfrom' => '01000000',
        'old_id' => $date,// . '|' . $v['ZSQL_ID'],
        'st_num' => $v['ZAUDDAYSS'],
        'status' => '3',
        'tid' => $tid,
        'pd_type' => '1',
        'redata_status' => '1', 
    );
 
	//集成合同项目表信息 
	 
	
	 
    //计算评定人员
    $pd       = explode('；', $v['ZASSMANLIST']);
    if (!$pd[1]) {
        $pd[1] = $pd[0];
        unset($pd[0]);
    }
    $new_proj['comment_a_name'] = $pd[0];
    $new_proj['comment_b_name'] = $pd[1];
	
	
	
    //p($pd);
    //处理审核类型
    if ($v['ZAUDCODE'] == '01') { //初审生成一阶段 二阶段  
        $new_proj['audit_type'] = '1002';
        load('audit')->add($new_proj);
        $new_proj['audit_type'] = '1003';
      
    } elseif ($v['ZAUDCODE'] == '02') {
        $new_proj['audit_type'] = '1007';
      
    } elseif ($v['ZAUDCODE'] == '0301') { //监督
        //监督次数
        if ($v['ZSURTIMES'] == '0') {
            $new_proj['audit_type'] = '';
        } elseif ($v['ZSURTIMES'] == '1') {
            $new_proj['audit_type'] = '1004';
        } elseif ($v['ZSURTIMES'] == '2') {
            $new_proj['audit_type'] = '1005';
        } elseif ($v['ZSURTIMES'] == '3') {
            $new_proj['audit_type'] = '1006';
        } elseif ($v['ZSURTIMES'] == '4') {
            $new_proj['audit_type'] = '1011';
        } elseif ($v['ZSURTIMES'] == '5') {
            $new_proj['audit_type'] = '1012';
        } elseif ($v['ZSURTIMES'] == '6') {
            $new_proj['audit_type'] = '1013';
        } elseif ($v['ZSURTIMES'] == '7') {
            $new_proj['audit_type'] = '1014';
        } elseif ($v['ZSURTIMES'] == '8') {
            $new_proj['audit_type'] = '1015';
        } elseif ($v['ZSURTIMES'] == '9') {
            $new_proj['audit_type'] = '1016';
        } elseif ($v['ZSURTIMES'] == '10') {
            $new_proj['audit_type'] = '1017';
        } elseif ($v['ZSURTIMES'] == '11') {
            $new_proj['audit_type'] = '1018';
        }
       
    }elseif($v['ZAUDCODE'] == '04'){
		
		$new_proj['audit_type'] = '1101';
		  
		}else{
	 	$new_proj['audit_type'] = '99'; 
	} 
	load('audit')->add($new_proj);
}
 