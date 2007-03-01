<?php
	function remove_code($arr_code){
	
		$str = str_replace(array(
								';',
								'；'
							) , ",", $arr_code);
		$arr = explode(',', $str);
		$arr = array_unique($arr); //去除数组中重复值
		foreach ($arr as $k => $v) {
			if ($v == '') {
				unset($arr[$k]); //去除空值

			}
		};
		return $arr;
	}

	$aca = load('auditcodeapp');
    $id = getgp('id');
    $acaid = getgp('acaid');
    $qua_id = getgp('qua_id');
    $iso = getgp('iso');
    $note = getgp('note');
    // $app_audit_code = getgp('app_audit_code');
    $app_use_code = getgp('app_use_code');
    // $app_audit_code=remove_code($app_audit_code);
    $app_use_code=remove_code($app_use_code);
    //
    $where = " AND iso = '$iso'";
    $where.= " AND code IN ('" . join("','", $app_use_code) . "')";
    echo $sql = "SELECT code FROM sp_settings_audit_code WHERE 1 $where";
    $rs = $db->get_results($sql);
    if (!$rs) {
        echo "<script>alert('提交小类系统中有不存在的，请仔细检查');</script>";
        tpl('auditor/appcode_edit');
        exit;
    }
    $value = array(
        'iso' => getgp('iso') ,
        'uid' => $uid,
        'qua_id' => $qua_id,
        'app_use_code' => join('；', $app_use_code) ,
        'note' => getgp('note') ,

    );
    if ($acaid) {
        $value['status'] = 1;
        $aca->edit($acaid, $value);
    } else {
        $aca->add($value);
    }
    echo "<script>window.parent.close_windows();</script>";
    exit;