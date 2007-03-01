<?php
$tid           = getgp('tid');
//取任务信息
$o_task        = $task->get(array(
    'id' => $tid
));
//任务项目
$resdb         = array();
$sql           = "SELECT * FROM sp_project WHERE 1 AND tid = '$tid' AND deleted = 0";
$query         = $db->query($sql);
$ck_is_leader  = 'success';
$ck_audit_job  = 'success';
$ck_audit_rt   = 'success';
$ck_audit_code = 'success';
$ck_audit_code_2017 = 'success';
while ($rt = $db->fetch_array($query)) {
	
	if(!empty($rt['audit_code_2017']))
	{
		$codeList  = array_unique(array_filter(explode('；',$rt['audit_code_2017'])));
		$codeims   = '';
		foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		$rt['audit_code_2017'] = $codeims;
	}

    $ck_is_leaders[$rt['id']] = false;
    $project_codes_2017[$rt['id']] = array_filter(explode('；', str_replace( array('；',';'), '；', $rt['audit_code_2017'] ) ));
	
    $xq_codes_2017[$rt['id']]      = f_audit_ver($rt['audit_ver']) . '：' . $rt['audit_code_2017'];
    if(!empty($rt['audit_code']))
	{
		$codeList  = array_unique(array_filter(explode('；',$rt['audit_code'])));
		$codeims   = '';
		foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		$rt['audit_code'] = $codeims;
	}
    $project_codes[$rt['id']] = array_filter(explode('；', str_replace( array('；',';'), '；', $rt['audit_code_2017'] ) ));
    $xq_codes[$rt['id']]      = f_audit_ver($rt['audit_ver']) . '：' . $rt['audit_code'];
    
    
    $ck_codes[$rt['id']]      = f_audit_ver($rt['audit_ver']) . '：';
    $ck_codes_2017[$rt['id']]      = f_audit_ver($rt['audit_ver']) . '：';
	
    $xq_is_leaders[$rt['id']] = f_audit_ver($rt['audit_ver']) . '：是';
    
    //是否组长
    $role_1001                = $db->get_var("SELECT COUNT(*) FROM  sp_task_audit_team  WHERE tid = '$tid' AND pid = '$rt[id]' AND role='01' and deleted=0 ");
    $is_leaders               = ($role_1001>0) ? ('有') : ('无');
    if (!$role_1001) {
        $ck_is_leader = 'error';
    }
    $ck_is_leaders[$rt['id']] = f_audit_ver($rt['audit_ver']) . "：$is_leaders";
    //有专职审核员
    $is_job                   = $db->get_var("SELECT COUNT(DISTINCT tat.id) FROM sp_task_audit_team tat LEFT JOIN sp_hr h ON tat.uid = h.id WHERE tat.tid = '$tid' and h.audit_job=1 and tat.deleted=0 ");
    //echo $is_job;exit;
    $is_job_V                 = ($is_job) ? ('是') : ('否');
    if (!$is_job) {
        $ck_audit_job = 'error';
    }
    $resdb[$rt['id']]         = $rt;
    $project_codes_2017[$rt['id']] = trim($rt['audit_code']);
    $project_codes[$rt['id']] = trim($rt['audit_code_2017']);
	
}


//人日
$_num  = 0.0;
$sql   = "SELECT * FROM sp_task_audit_team WHERE 1 AND tid = '$tid' AND deleted = 0 group by uid";

$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    if($rt['qua_type']!='03'){
        $_num += mkdate($rt['taskBeginDate'], $rt['taskEndDate']);
    }
}
if ($_num < $o_task['tk_num'] or $_num == 0.0) {
    $ck_audit_rt = 'error';
}
//每个体系总人天
$sql   = "SELECT * FROM sp_task_audit_team WHERE 1 AND tid = '$tid' AND deleted = 0";
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    //if ($rt[qua_type] == '01' || $rt[qua_type] == '02') {
    if($rt['qua_type']!='03'){
        $types_num[$rt['audit_ver']]+= mkdate($rt['taskBeginDate'], $rt['taskEndDate']);
    }
}
// if ($_num < $o_task['tk_num'] or $_num == 0.0) {
    // $ck_audit_rt = 'error';
// }
//对码
$sql   = "SELECT * FROM sp_task_audit_team WHERE 1 AND tid = '$tid' AND deleted = 0";
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    $rt['audit_code'] = str_replace(';','；',$rt['audit_code']);
    $_codes         = explode('；', $rt['audit_code']); //分解审核员代码到数组
    foreach ($_codes as $code) 
    {
        $auditor_codes[$rt['pid']][] = trim($code);
        if (@strpos($ck_codes[$rt['pid']], $code) === false) {
            $ck_codes[$rt['pid']] .= $code . '；';
        }
    }
    $ck_codes[$rt['pid']] = trim($ck_codes[$rt['pid']], "；");
	
	
	//以下为2017版本
	$rt['audit_code_2017'] = str_replace(';','；',$rt['audit_code_2017']);
    $_codes_2017         = explode('；', $rt['audit_code_2017']); //分解审核员代码到数组
    foreach ($_codes_2017 as $code_2017) 
    {
        $auditor_codes_2017[$rt['pid']][] = trim($code_2017);
        if (@strpos($ck_codes_2017[$rt['pid']], $code_2017) === false) {
            $ck_codes_2017[$rt['pid']] .= $code_2017 . '；';
        }
    }
    $ck_codes_2017[$rt['pid']] = trim($ck_codes_2017[$rt['pid']], "；");
}

if (is_array($project_codes_2017)) 
{
    foreach ($project_codes_2017 as $pid => $codes_2017) 
    {
        $codes_2017 = str_replace(array(
            ';',
            ';'
        ), '；', $codes_2017);
        $codes_2017 = explode('；', $codes_2017);
        foreach ($codes_2017 as $code_2017) {
            if (@!in_array($code_2017, $auditor_codes_2017[$pid]) && $code_2017) {
                $ck_audit_code_2017  = 'error';
                $xq_codes_2017[$pid] = str_replace($code_2017, "<span class=\"cRed\">$code_2017</span>", $xq_codes_2017[$pid]);
            } else {
                $ckcodes_2017[$pid] = $ck_codes_2017[$pid];
            }
        }
    }
}
 
if (is_array($project_codes)) 
{
    foreach ($project_codes as $pid => $codes) {
        $codes = str_replace(array(
            ';',
            ';'
        ), '；', $codes);
        $codes = explode('；', $codes);
        foreach ($codes as $code) {
            if (@!in_array($code, $auditor_codes[$pid]) && $code) {
                $ck_audit_code  = 'error';
                $xq_codes[$pid] = str_replace($code, "<span class=\"cRed\">$code</span>", $xq_codes[$pid]);
            } else {
                $ckcodes[$pid] = $ck_codes[$pid];
            }
        }
    }
}
tpl('task/check_send');