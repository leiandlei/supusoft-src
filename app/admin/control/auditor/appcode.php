<?php
//审核员=》业务代码申请
if (!$uid) {
        echo '错误提示，没有关联人员';
        exit;
    } else {
        $where = " and uid='$uid' and status='1' ";
    }
    $status_arr = array(
        '1' => "有效",
        '0' => "失效"
    );
    $qualification = load('qualification');
    $row = $qualification->get($id);
    $user_info = $user->get($uid);
    if ($row != NULL) {
        extract($row, EXTR_SKIP);
    }
    $query = $db->query("SELECT * FROM sp_hr_qualification $join WHERE 1 $where ORDER BY id DESC");
    while ($rt = $db->fetch_array($query)) {
        $rt['qua_type'] =read_cache("qualification",$rt['qua_type']);
        $rt['status'] = $status_arr[$rt['status']];
        $rt['iso_v'] = read_cache("iso",$rt['iso']);
        $rt['iso_ver'] = $audit_ver_array[$rt['iso_ver']]['audit_basis'];
        $datas[] = $rt;
    }
    $youxiao = 'checked';
    if ($qualification_array) {
        foreach ($qualification_array as $code => $item) {
            $qualification_select.= "<option value=\"$item[code]\">$item[name]</option>";
        }
    }
    if ($audit_ver_array) {
        foreach ($audit_ver_array as $code => $item) {
            $iso_ver_select.= "<option value=\"$item[audit_ver]\">$item[msg]</option>";
        }
    }
    $status = getgp('status');
    if (!$status) $status = 1;
    $status_1_tab = $status_2_tab = $status_3_tab = '';
    $ {
        'status_' . $status . '_tab'
    } = ' ui-tabs-active ui-state-active';
    //$join = "inner join sp_hr_qualification hq on hq.id=aca.qid ";
    $where = " and aca.uid='$uid' ";
    //数量统计
    $status_1_total = $db->get_var("SELECT COUNT(*) FROM sp_hr_audit_code_app aca $join WHERE 1 $where AND aca.status=1");
    $status_2_total = $db->get_var("SELECT COUNT(*) FROM sp_hr_audit_code_app aca $join WHERE 1 $where AND aca.status=2");
    $status_3_total = $db->get_var("SELECT COUNT(*) FROM sp_hr_audit_code_app aca $join WHERE 1 $where AND aca.status=3");
    $where.= " and aca.status='$status'";
    $pages = numfpage($total, 20, "?c=$c&a=$a&status=$status");
    $sql = "SELECT * FROM sp_hr_audit_code_app aca $join WHERE 1 $where ORDER BY aca.id DESC $pages[limit]";
    $query = $db->query($sql);
    $datas2 = array();
    while ($rt = $db->fetch_array($query)) {
        $rt['iso_v'] = read_cache("iso",$rt['iso']);
        $datas2[] = $rt;
    }
    tpl('auditor/appcode');