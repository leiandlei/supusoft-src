<?php
//监督维护

//项目状态数组
$project_sv_status_array = array(
    '0' => '未安排',
    '1' => '待派人', //1-待派人|已安排
    '2' => '待审批',
    '3' => '已审批',
    '5' => '维护',
    '6' => '退回', //6-转审核部又退回监督维护

);
$pid = (int)getgp('pid');
$p_info = $db->get_row("select * from sp_project where id='$pid' ");
$che1=$che2=$che3='';
if($p_info['sv_status']=='1'){
	$che1='selected="selected"';
}elseif($p_info['sv_status']=='2'){
	$che2='selected="selected"';
}elseif($p_info['sv_status']=='3'){
	$che3='selected="selected"';
}
$ct_id=$p_info[ct_id];
$sql = "select id from sp_certificate where ct_id='$ct_id' and iso='$p_info[iso]' and eid='$p_info[eid]' and status > 0 and is_check ='y' order by id desc";
$zsid = $db->get_var($sql);
if ($step) {
    $sv_status = (int)getgp('sv_status');
    $ifchangecert = (int)getgp('ifchangecert');
    $note = getgp('note');
    $sv_note = getgp('sv_note');
    $final_date = getgp('final_date');
    $new_project = array(
        'ifchangecert' => $ifchangecert,
        'sv_status' => $sv_status, //维护状态 1待定，2，接受，3，不接受
        'sv_note' => $sv_note,
        'final_date' => $final_date,
        'pre_date' => get_addday($final_date,-1,0),
       // 'up_date' => current_time('mysql') ,
       // 'up_uid' => current_user('uid')
    );
    //接受时候设置项目状态为未安排
    if ($sv_status == 2) {
        $new_project['status'] = 0;
    }
    $audit->edit($pid, $new_project);
    showmsg('success', 'success', "?c=audit&a=list_super");
} else {
	extract($p_info);
    tpl('audit/edit_super');
}
?>
