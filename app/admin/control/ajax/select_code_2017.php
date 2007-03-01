<?php

/*
*选择小类（合同评审+人员小类登记+审核员申请小类）
*/

    $iso     = getgp('iso');
    $code    = getgp('code');
    $baocode = getgp('baocode');
	
    $msg     = getgp('msg');
    $where   = '';
    if ($iso) {
        $where.= " AND iso = '$iso' ";
    }
    if ($baocode) {
        $where.= " AND shangbao like '%$baocode%' ";
    }
	if ($code) {
        $where.= " AND code like '%$code%' ";
    }
    if ($msg) {
        $where.= " AND msg like '%$msg%' ";
    }
    $codes = array();
 	if(!empty($banben))
 	{
 		$where.= " AND banben=".$banben;
 	}
    $total = $db->get_var("SELECT COUNT(*) FROM sp_settings_audit_code WHERE 1 $where and banben=1 and  is_stop=0");
    $pages = numfpage($total, 40);
    $query = $db->query("SELECT * FROM sp_settings_audit_code WHERE 1 $where and deleted=0 and banben=1 and  is_stop=0 $pages[limit]");
    while ($rt = $db->fetch_array($query)) {
        $marks = explode(',', $rt['mark']);
        $mark_arr = array();
        foreach ($marks as $mk) {
            $mark = f_mark($mk);
            if ($mark) $mark_arr[] = $mark;
        }
        $rt['risk_level'] = ($rt['risk_level'] == '00') ? '' : f_risk($rt['risk_level']);
        $rt['mark'] = implode(',', $mark_arr);
        unset($marks, $mark_arr, $mk);
        $rt['iso_V'] = f_iso($rt['iso']);
        $codes[$rt['id']] = $rt;
    }
    tpl('ajax/select_code');