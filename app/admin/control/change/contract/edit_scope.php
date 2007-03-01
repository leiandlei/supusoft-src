<?php
//变成评审-修改项目信息
$type  = getgp("type");
$step  = (int) getgp('step');
//$cgid  = (int) getgp('cgid'); //变更评审
$pid   = getgp('pid');
$ct_id = getgp('ct_id');
//评审项目
if ($step) {  
    $scope         = getgp('scope');
    //专业代码与使用代码
    $audit_code    = getgp('audit_code');
    $use_code      = getgp("use_code");
    $audit_note    = getgp('audit_note');
    $review_status = getgp('review_status');
    $arr           = array(
        'scope' => $scope,
        'audit_code' => $audit_code,
        'use_code' => $use_code,
        "st_num" => getgp('st_num'),
        "total" => getgp('total'),
        "pd_type" => '0',
		
    );
    //结合变更选项
    if (is_array($_POST['audit_type_note'])) {
        $arr['audit_type_note'] = implode('；', $_POST['audit_type_note']);
        if (in_array('标准转换', $_POST['audit_type_note'])) {
            if ($_POST['audit_ver'])
                $arr['audit_ver'] = $_POST['audit_ver'];
        }
    }
	if($_POST['zy_name'])
		$zy_name=$_POST['zy_name'];
	else{
		$use_codes=explode("；",$use_code);
		$zy_name=$db->get_col("SELECT zy_name FROM `sp_stff` where code in('".join("','",$use_codes)."') and zy_name<>''");
		$zy_name=array_unique($zy_name);
		$zy_name=join("；",$zy_name);
	}
	$arr['zy_name']=$zy_name;
	//$arr['review_note']=getgp('review_note');
	$arr['audit_note']=getgp('note');
	$bf_proj_info=load('audit')->get(array('id'=>$pid));
  	 $db->update('project', $arr, array(
        'id' => $pid
    ));
	$af_proj_info=load('audit')->get(array('id'=>$pid));
	
	log_add($bf_proj_info['eid'],0,'变更评审:项目号'.$bf_proj_info['cti_code'].'审核阶段：'.read_cache('audit_type',$af_proj_info['audit_type']),serialize($bf_proj_info),serialize($af_proj_info));
	
/*     //更新专业管理人员
	$bf_ct_info=load('contract')->get(array('ct_id'=>$bf_proj_info['ct_id']));
    $db->update('contract', array(
        'major_person' => getgp('major_person'),
        'review_note' => getgp('review_note'),//评审备注
        'note' => getgp('note') //项目组备注
    ), array(
        'ct_id' => $ct_id
    ));
	$af_ct_info=load('contract')->get(array('ct_id'=>$bf_proj_info['ct_id']));
	
	 	log_add($bf_proj_info['eid'],0,'变更评审:合同号'.$bf_proj_info['ct_code'].'审核阶段：'.read_cache('audit_type',$af_proj_info['audit_type']),serialize($bf_ct_info),serialize($af_ct_info));
 */ 
    $REQUEST_URI = "?c=contract&a=edit_scope&pid=$pid&type=review";
    $review_status && $REQUEST_URI = "?c=contract&a=add_review";
    showmsg('success', 'success', $REQUEST_URI);
}
//项目信息
$p_info = load('audit')->get(array(
    'id' => $pid
));
 
$ct_id  = $p_info['ct_id'];
// p($p_info);
extract($p_info);
//项目体系下排除当前的的所有标准 
$audit_vers              = $db->find_results('settings_audit_vers', " and   iso='$p_info[iso]' and audit_ver!='$p_info[audit_ver]'", 'audit_ver,audit_basis');
//结合审核项目
$p_info['old_audit_ver'] = explode('；', $p_info['audit_type_note']);
//专业审核代码
$audit_codes             = array();
$codes                   = explode('；', $p_info['audit_code']);
$query                   = $db->query("SELECT code,shangbao,risk_level,mark FROM sp_settings_audit_code WHERE  shangbao IN('" . implode("','", $codes) . "') AND iso='$p_info[iso]' and deleted=0 and  is_stop=0");
while ($rt = $db->fetch_array($query)) {
    if (!$rt['shangbao'])
        continue;
    $marks     = explode(',', $rt['mark']);
    $new_marks = array();
    foreach ($marks as $mk) {
        $mark_V = f_mark($mk);
        if ($mark_V) {
            $new_marks[] = $mark_V;
        }
    }
    $rt['mark_V']       = implode(',', $new_marks);
    $rt['risk_level_V'] = f_risk($rt['risk_level']);
    $audit_codes[]      = $rt;
}
unset($codes);
//合同信息
$ct_info = $db->find_one('contract', array(
    'ct_id' => $ct_id
));
//证书变更列表信息
$cert_chang_ls=load('change')->gets(array('cg_pid'=>$pid));
 //p($cert_chang_ls);
 
tpl();
 