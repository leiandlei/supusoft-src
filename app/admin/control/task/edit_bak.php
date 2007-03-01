<?php
/*
 * 审核安排
 */
$tid = (int) getgp('tid');
if ($step) {
    //添加/修改 
    $tb_date  = getgp('tb_date') . ' ' . getgp('tb_time');
    $te_date  = getgp('te_date') . ' ' . getgp('te_time');
    $new_task = array(
        'eid' => (int) getgp('eid'),
        'tk_num' => getgp('jh_num'), //总人天
         'tb_date' => $tb_date, //开始日期
        'te_date' => $te_date, //结束日期
        'note' => getgp('task_note'), //备注
        'self_note' => getgp('self_note'), //备注 
    );
    /* 处理体系 */
    $st_nums  = getgp('st_num');
	if(!$st_nums){
		echo '<script>alert("人日不能为空")</script>';
		showmsg("人日不能为空","error","?c=audit&a=list_wait_arrange");
 		exit; 
	}
    $pids     = array_keys($st_nums);
    if ($tid) {
	 
		$_uids=$db->getCol('task_audit_team','uid',array('tid'=>$tid,'deleted'=>'0'));
		
        $_uids = array_merge($_uids, array(
            -1
        )); 
        $tat   = $db->get_row("SELECT id,eid FROM `sp_task_audit_team` WHERE `uid` IN (" . join(",", $_uids) . ") AND ((`taskBeginDate` >= '$tb_date' AND  `taskBeginDate`<='$te_date') or ( `taskEndDate` <= '$te_date' AND `taskEndDate` >= '$tb_date') or (`taskBeginDate` <= '$tb_date' AND `taskEndDate` >= '$te_date')) AND `deleted` = '0' AND tid <>'$tid'");
        //修改计划同步修改每个人的时间
        if ($tat[id] and $tat[eid] != getgp('eid')) {
            showmsg("有审核员时间冲突", "error", "?c=task&a=edit&tid=$tid");
            exit;
        }
        $task->edit($tid, $new_task, 1);
		
		$t_info=$task->get(array('id' => $tid));
		if($t_info[tb_date]!=$tb_date or $t_info[te_date]!=$te_date)
			$db->update("task_audit_team", array(
				"taskBeginDate" => $tb_date,
				"taskEndDate" => $te_date
			), array(
				"tid" => $tid
			));
        unset($tb_date, $te_date);
    } else {
        $tid = $task->add($new_task);
		$task_info=$task->get(array('id',$tid));
		log_add($task_info['eid'],0,'新增任务','',serialize($task_info));
		
    }
    if ($st_nums) {
        foreach ($st_nums as $pid => $st_num) {
            $audit->edit($pid, array(
                'tid' => $tid,
                'status' => '1',
               // 'st_num' => $st_num //更新人天数
            ));
        }
    }
    showmsg('success', 'success', "?c=task&a=edit_send&tid=$tid");
} else {
    //取信息
    $pids = getgp('pid');
    $bm_8 = $bm_13 = $em_12 = $em_17 = ''; //选中使用
    $tb_h = 8; //8点上午 17是下午结束时间
    $te_h = 17; //8点上午 17是下午结束时间 
    $projects = array();
    if ($pids) {
        $where = " AND id IN (" . implode(',', $pids) . ")";
    }elseif($tid) {
        $task = load('task');
        $row  = $task->get(array(
            'id' => $tid
        ));
        extract($row, EXTR_SKIP); //获取sp_task信息 
        $tb_h    = date('G', strtotime($tb_date));
        $te_h    = date('G', strtotime($te_date));
        $tb_date = mysql2date('Y-m-d', $tb_date);
        $te_date = mysql2date('Y-m-d', $te_date);
        $where   = " AND tid = '$tid'";
    }
    $eid        = 0;
    $tk_num = 0.0;
    $ct_ids     = array(
        -1
    );
    //$audit_note = "";
	/*本次安排的项目*/

    //print_r($query);exit; 
    $sql        = "SELECT id,eid,cti_id,ct_id,tid,cti_code,audit_ver,audit_type,st_num,iso FROM sp_project WHERE 1 $where order by iso";  
    $query      = $db->query($sql); 
    while ($rt = $db->fetch_array($query)) {
     //   if ($rt[audit_note])
           // $audit_note .= $rt[cti_code] . ":" . $rt[audit_note];
        $rt['audit_type_V'] = f_audit_type($rt['audit_type']);
        $rt['audit_ver_V']  = f_audit_ver($rt['audit_ver']);
        // $rt['appro_num']    = $cti->meta($rt['cti_id'], 'appro_num');
        $ct_ids[]           = $rt['ct_id'];
        $eid                = $rt['eid'];
        $cti_info           = $db->get_row("SELECT * FROM `sp_contract_item` WHERE `cti_id` = '$rt[cti_id]' and deleted=0");
        if ($rt['audit_type'] == '1003') {
            $sql             = "select st_num from sp_project where eid='$rt[eid]' and cti_id='$rt[cti_id]'  and audit_type='1002' and deleted=0 limit 1";

            $rt['yijieduan'] = $db->get_var($sql); // 一阶段审核人日
        }
		$t_num += $rt['st_num'];
        //echo $tk_num;exit;

        $jh_num += $rt['st_num'];
        //echo $jh_num;exit;
        $projects[$rt['id']] = $rt;
		if(in_array($rt[audit_type],array('1003','1007')))
			$ch_ct_id=$rt[ct_id];
 }
    $pids            = array_keys($projects);
    ${'bm_' . $tb_h} = ' selected';
    ${'em_' . $te_h} = ' selected';
    if ($projects) {
        $eids = $audit_types = array();
        foreach ($projects as $project) {
            $eids[]        = $project['eid'];
            $audit_types[] = $project['audit_type'];
        }
        $eids = array_unique($eids);
        if (count($eids) > 1)
            showmsg('不同企业不能结合审核！', 'success', $REQUEST_URI);
        $audit_types = array_unique($audit_types);
        if (in_array('1002', $audit_types) && in_array('1003', $audit_types))
            showmsg('一阶段与二阶段不能结合审核！', 'success', $REQUEST_URI);
			
    }
    $ct_ids      = array_unique($ct_ids);
    /* 企业下的未安排项目 */
    $ct_projects = array();
    $query       = $db->query("SELECT * FROM sp_project WHERE eid ='$eid' AND id NOT IN (" . implode(',', $pids) . ") AND status IN (0,5,6) AND deleted=0  ORDER BY id DESC");
    while ($rt = $db->fetch_array($query)) {
        $rt['audit_type_V']     = f_audit_type($rt['audit_type']);
        $rt['audit_ver_V']      = f_audit_ver($rt['audit_ver']);
        $ct_projects[$rt['id']] = $rt;
    }
	//只是取ct_id
	if($tid){
		$ct_id=$db->get_var("SELECT ct_id FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0' ORDER BY `ct_id` DESC ");
	    $tk_num=$db->get_var("SELECT tk_num FROM `sp_task` WHERE `id` =$tid");
    }
	else{
	   $ct_id  = $db->get_var("SELECT ct_id FROM `sp_project` WHERE `id` IN(".join(",",$pids).") AND `deleted` = '0' ORDER BY `ct_id` DESC ");  
	   $tk_num = $db->get_var("SELECT st_num FROM `sp_project` WHERE `id` IN(".join(",",$pids).") AND `deleted` = '0' ORDER BY `ct_id` DESC ");  
    }
    tpl('task/edit');
}
?>
