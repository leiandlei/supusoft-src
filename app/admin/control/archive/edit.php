<?php
/*
 *资料回收
 */
$eid = (int) getgp('eid');
$tid = (int) getgp('tid');
$huishouStatus = getgp('huishouStatus');
if ($step = getgp("step")) {
        $jh_sp_date = getgp("jh_sp_date");
        $jh_sp_note = getgp("jh_sp_note");
        $name       = $db->get_var("SELECT name FROM `sp_hr` WHERE `id` = '" . current_user('uid') . "'");
        $db->update("task", array(
            "jh_sp_date" => current_time('mysql'),
            "jh_sp_note" => $jh_sp_note,
            "jh_sp_name" => $name
        ), array(
            "id" => $tid
        ));
        $pids         = array_map('intval', getgp('pid'));
        $sql_ctid     = "select ct_id from sp_project where id in(".implode(',',$pids).") and redata_status=0";
        $arr_ctid     = $db->getAll($sql_ctid);
        foreach ($arr_ctid as  $value) {
            $arrCtid[0] = $value['ct_id'];
        }

        $sql_pid      = "select id from sp_project where ct_id in(".implode(',',$arrCtid).") and redata_status=0";
        $arr_pid      = $db->getAll($sql_pid);
        foreach ($arr_pid as $value) {
            $pids[] = $value['id'];
        }
        $pids = array_unique($pids);

        $redata_dates = array_map('trim', getgp('redata_date'));
        $to_jwh_date  = array_map('trim', getgp('to_jwh_date'));
        $redata_note  = array_map('trim', getgp('redata_note'));
        $pd_pids      = array();
        // echo '<pre />';
        // print_r($pids);exit;
        if ($pids) {
            foreach ($pids as $k => $pid) {
                /* 更新项目表 */
                // if (!$redata_dates[$k])
                //     continue;
                $pd_pids[]   = $pid;
                $new_project = array(
                    'redata_date' => $redata_dates[$k],
                    'redata_uid' => current_user('uid'),
                    'redata_note' => $redata_note[$k],
                    'to_jwh_date' => $to_jwh_date[$k],
                );
                if($huishouStatus == 1)$new_project['redata_status']=1;
                $audit->edit($pid, $new_project);
                // 日志
                do {
                    log_add($eid, 0, "资料回收", NULL, serialize($new_project));
                } while (false);
            }
            /* 生成评定 */
            if($huishouStatus == 1){
                if ($pd_pids and 0) {
                    $projects = array();
                    $where    = " AND id IN (" . implode(',', $pd_pids) . ")";
                    $sql      = "SELECT * FROM sp_project WHERE 1 $where";
                    $query    = $db->query($sql);
                    while ($rt = $db->fetch_array($query)) {
                        $projects[$rt['id']] = $rt;
                    }
                    $projects = magic_gpc($projects, 1);
                    $old_pids = array();
                    $query    = $db->query("SELECT pid FROM sp_assess WHERE pid IN (" . implode(',', $pids) . ")");
                    while ($rt = $db->fetch_array($query)) {
                        $old_pids[$rt['pid']] = $rt['pid'];
                    }
                    $ADDSQL = array();
                    foreach ($projects as $row) {
                        //避免重负出现评定数据，审核类型，审核项目id
                        if (isset($old_pids[$row['id']]))
                            continue;
                        $ADDSQL[] = "( '{$row['ctfrom']}','{$row['ifchangecert']}','{$row['eid']}', '{$row['ct_id']}', '{$row['cti_id']}', '{$row['id']}', '{$row['tid']}', '{$row['iso']}', '{$row['audit_ver']}', '{$row['audit_type']}', '{$row['audit_code']}', '{$row['use_code']}', '{$row['prod_id']}', '{$row['prod_ver']}', '{$row['scope']}', '{$row['scope_e']}', '$row[mark]' )";
                    }
                    if ($ADDSQL) {
                        $sql = "INSERT INTO sp_assess ( ctfrom,if_cert ,eid, ct_id, cti_id, pid, tid, iso, audit_ver, audit_type, audit_code, use_code, prod_id, prod_ver, scope, scope_e, mark ) VALUES " . implode(',', $ADDSQL);
                        $db->query($sql);
                    }
                }
            }
        }
    
    $url = getgp("url");
    showmsg('success', 'success', $url);
} else {
    $url           = $_SERVER['HTTP_REFERER'];
    $task_projects = array();
    $sql           = "SELECT * FROM sp_project WHERE 1 AND tid = '$tid'";
    $query         = $db->query($sql);
    while ($rt = $db->fetch_array($query)) {
        $rt['audit_type_V'] = f_audit_type($rt['audit_type']);
        $rt['redata_date'] == '0000-00-00' && $rt['redata_date'] = '';
        $rt['to_jwh_date'] == '0000-00-00' && $rt['to_jwh_date'] = '';
        $rt['iso']       = f_iso($rt['iso']);
        $task_projects[] = $rt;
    }
    //审核文档
    $sql = "select * from sp_attachments where eid='$eid' ORDER BY `sort`";
    $res = $db->query($sql);
    while ($rt = $db->fetch_array($res)) {
        $rt['uid']              = f_username($rt['create_uid']);
        $enterprises_archives[] = $rt;
    }
    $task_info = $db->get_row("SELECT jh_sp_date,jh_sp_note,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
    extract($task_info);
    $tb_date = trim($tb_date, ":00:00");
    $te_date = trim($te_date, ":00:00");
    tpl();
}