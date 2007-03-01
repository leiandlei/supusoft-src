<?php
/*
 *选择评定人员（弹窗）
 */
 
    extract($_GET, EXTR_SKIP);
    //取环境变量
    $pd_id = (int)$pd_id;
    $where = '';
    //在职
    $in_uids = array();
    $query = $db->query("SELECT id FROM sp_hr WHERE is_hire = 1");
    while ($rt = $db->fetch_array($query)) {
        $in_uids[] = $rt['id'];
    }
    $where.= " AND uid IN (" . implode(',', $in_uids) . ")";
    //搜索评定人员
    if ($name) {
        $_uids = array();
        $query = $db->query("SELECT id FROM sp_hr WHERE name LIKE '%$name%'");
        while ($rt = $db->fetch_array($query)) {
            $_uids[] = $rt['id'];
        }
        if ($_uids) {
            $where.= " AND uid IN (" . implode(',', $_uids) . ")";
        } else {
            $where.= " AND id < -1";
        }
    }
    
    //取评定项目
    $pd = $db->get_row("SELECT * FROM sp_assess WHERE id = '$pd_id'");
    $_codes = explode('；', str_replace(array(
        '；',
        ';'
    ) , '；', $pd['audit_code']));
    $audit_codes = array();
    foreach ($_codes as $c) {
        array_push($audit_codes, $c);
    }
    $_where_arr = array();
    foreach ($audit_codes as $code) {
        $_where_arr[] = "(iso = '$pd[iso]' AND shangbao = '$code')";
    }
    $_where = " AND (" . implode(' OR ', $_where_arr) . ")";
    $_codes = array();
    $query = $db->query("SELECT * FROM sp_settings_audit_code WHERE 1 $_where");
    while ($rt = $db->fetch_array($query)) {
        isset($_codes[$rt['iso']]) or $_codes[$rt['iso']] = array();
        $_codes[$rt['iso']][] = $rt;
    }
    if ($_codes) {
        $where_arr = array();
        foreach ($_codes as $iso => $cs) {
            foreach ($cs as $c) {
                $w = "(iso = '$pd[iso]'";
                if ($c['pd_check_method'] == 'big') {
                    $w.= " AND LEFT(audit_code,2) = '$c[dalei]')";
                } elseif ('mid' == $c['pd_check_method']) {
                    $w.= " AND LEFT(audit_code,5) = '" . substr($c['shangbao'], 0, 5) . "')";
                } else {
                    $w.= " AND audit_code = '$c[shangbao]')";
                }
                $where_arr[] = $w;
            }
        }
        $where.= " AND (" . implode(' OR ', $where_arr) . ")";
    }
    //不能是审核组的成员
    $task_uids = array();
    $query = $db->query("SELECT uid FROM sp_task_auditor WHERE tid = '$pd[tid]' AND deleted = 0");
    while ($rt = $db->fetch_array($query)) {
        $task_uids[] = $rt['uid'];
    }
    $not_in_uids = array_unique($task_uids);
    if ($not_in_uids) {
        $where.= " AND uid NOT IN (" . implode(',', $not_in_uids) . ")";
    }
    //人员及代码
    $assess_users = $codes = array();
    $sql = "SELECT uid,iso,audit_code FROM sp_hr_audit_code WHERE 1 AND is_assess = 1 $where AND deleted = 0";
    $query = $db->query($sql);
    while ($rt = $db->fetch_array($query)) {
        isset($codes[$rt['uid']]) or $codes[$rt['uid']] = array();
        isset($codes[$rt['uid']][$rt['iso']]) or $codes[$rt['uid']][$rt['iso']] = array();
        $codes[$rt['uid']][$rt['iso']][] = $rt['audit_code'];
        $assess_users[$rt['uid']] = $rt;
    }
    $hrs = array();
    if ($assess_users) {
        $fields = $join = $where = '';
        $fields = "hqa.*,hr.code,hr.name";
        $join = " LEFT JOIN sp_hr hr ON hr.id = hqa.uid";
        $where.= " AND hqa.uid IN (" . implode(',', array_unique(array_keys($assess_users))) . ")";
        $where.= " AND hqa.iso = '$pd[iso]'";
        $where.= " AND hqa.status = 1";
        $total = $db->get_var("SELECT COUNT(*) FROM sp_hr_qualification hqa WHERE 1 $where");
        $pages = numfpage($total, 20, $url_param);
        $sql = "SELECT $fields FROM sp_hr_qualification hqa $join WHERE 1 $where $pages[limit]";
        $query = $db->query($sql);
        while ($rt = $db->fetch_array($query)) {
            $rt['iso_V'] = f_iso($rt['iso']);
            $hrs[$rt['id']] = $rt;
        }
    }
    tpl('ajax/select_pduser');