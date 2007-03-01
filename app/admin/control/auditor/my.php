<?php
//我的资料

$row = $user->get($uid);
    extract($row);
    //echo current_user('ctfrom');
    $is_leader = $row['is_leader'];
    $is_hire = $row['is_hire'];
    $audit_job = $row['audit_job'];
    unset($row);
    //人员来源
    $ctfrom_select = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);
    //部门
    $department_select = str_replace("value=\"$department\">", "value=\"$department\" selected>", $department_select);
    //政治面貌
    $political_select = str_replace("value=\"$political\">", "value=\"$political\" selected>", $political_select);
    //证件类型
    $card_type_select = str_replace("value=\"$card_type\">", "value=\"$card_type\" selected>", $card_type_select);
    //人员性质
    $arr_job_type = explode('|', $job_type);
    foreach ($arr_job_type as $key => $value) {
        $job_type_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $job_type_checkbox);
    }
    //合同类型
    $arr_ct_type = explode('|', $ct_type);
    foreach ($arr_ct_type as $key => $value) {
        $ct_type_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $ct_type_checkbox);
    }
    //性别
    if ($sex == '1') {
        $sex_M = 'checked';
        $sex_F = '';
    } elseif ($sex == '2') {
        $sex_M = '';
        $sex_F = 'checked';
    }
    //选用类型
    $choose_type_select = str_replace("value=\"$choose_type\">", "value=\"$choose_type\" selected>", $choose_type_select);
    //社保登记
    $insurance_select = str_replace("value=\"$insurance\">", "value=\"$insurance\" selected>", $insurance_select);
    //审核员性质
    $audit_job_select = str_replace("value=\"$audit_job\">", "value=\"$audit_job\" selected>", $audit_job_select);
    //是否组长
    if ($is_leader == 1) {
        $is_leader_Y = 'checked';
        $is_leader_N = '';
    } else {
        $is_leader_Y = '';
        $is_leader_N = 'checked';
    }
    //在聘情况
    if ($is_hire == 1) {
        $is_hire_Y = 'checked';
        $is_hire_N = '';
    } else {
        $is_hire_Y = '';
        $is_hire_N = 'checked';
    }
    //审核员文档
    $hr_archives = array();
    $query = $db->query("SELECT * FROM sp_hr_archives WHERE uid = '$uid'");
    while ($rt = $db->fetch_array($query)) {
        $rt['ftype'] = f_atachtype($rt['ftype']);
        $hr_archives[] = $rt;
    }
    
    $upload_hr_photo_dir=get_option('upload_hr_photo_dir').current_user(uid).'.jpg'; //人员上传头像路径
    if(!file_exists($upload_hr_photo_dir)){
    	$upload_hr_photo_dir=get_option('upload_hr_photo_dir').'nophoto.jpg'; //人员上传头像路径
    }
    
    tpl();