<?php
 

$iso = getgp('iso'); //小类业务代码所属体系
$id = getgp('id'); //小类ID  功能：删除用
$where = '';
if (!$iso) {
    echo '错误提示，没有关联人员';
    exit;
}
//人员专业能力评价
$res = $db->query("select id,name from sp_hr where job_type like '%1001%' and is_hire='1' ");
while($row = $db->fetch_array($res)){
	$evaluater_select  .= "<option value='$row[name]'>".$row['name']."</option>";
}


//删除业务代码
if (getgp('action')) {
    $sql = "update sp_hr_audit_code set deleted='1' where id='" . getgp('id') . "' ";
    $db->query($sql);
    $code_info = $auditcode->get($id);
    $uid = $code_info['uid'];
    log_add('', $uid, '删除业务代码', '', serialize($code_info));
    unset($id);
}
$status = getgp('status');
$audit_code = trim(getgp('audit_code'));
$use_code = trim(getgp('use_code'));
if ($status) {
    $where.= " and hac.status ='$status' ";
}
// if ($audit_code) {
    // $where.= " and hac.audit_code like '$audit_code%' ";
// }
if ($use_code) {
    $where.= " and hac.use_code like '$use_code%' ";
}

$status_select = str_replace("value=\"$status\">", "value=\"$status\" selected>", $status_select);
$tip_msg = '新增人员代码';
if (!$rows[iso]) {
    $rows[iso] = $iso;
}
if (!empty($row)) {
    extract($row, EXTR_SKIP);
}
$iso_V = f_iso($iso);
 
$join.= " LEFT JOIN sp_hr_qualification hqa ON hqa.id=hac.qua_id";
$where.= " AND hac.uid = '$uid' AND hac.iso = '$iso' and hac.deleted='0' ";
$sql = "SELECT  hac.use_code,hac.audit_code FROM sp_hr_audit_code hac $join WHERE 1 $where group by hac.id  ";
$query = $db->query($sql);
$count = 0;
$code_str = '';
while ($rt = $db->fetch_array($query)) {
    $count++;
    // $code_str.= $rt['audit_code'] . '；';
    $ucode_str .= $rt[audit_code] . '；<br />';
}
//已经登记的业务代码
//$total = $db->get_var("SELECT COUNT(hac.id) FROM sp_hr_audit_code hac $join WHERE 1 $where  group by hac.id");
$pages = numfpage($count,5);
$hacs = array();
$sql = "SELECT hac.*,hqa.qua_type,hqa.status qua_status FROM sp_hr_audit_code hac $join WHERE 1 $where group by hac.id  $pages[limit]";

//是否认证评定
$arr_ping = array(
    '0' => '不能',
    '1' => '可以'
);
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    $rt['qua_type_V'] = f_qua_type($rt['qua_type']);
    $rt['iso_V'] = f_iso($rt['iso']);
	
    $rt['source'] = skill_source_V($rt['source']);
	
    $rt['audit_job'] = f_audit_job($rt['audit_job']);
    $rt['status_V'] = $status_arr[$rt['status']];
    $rt['is_assess'] = $arr_ping[$rt['is_assess']];
    $hacs[] = $rt;
}
$youxiao = 'checked';
if ($audit_ver_array) {
    foreach ($audit_ver_array as $code => $item) {
        $iso_ver_select.= "<option value=\"$item[audit_ver]\">$item[audit_ver]</option>";
    }
}

function skill_source_V($skill_array){
	 global $skill_source_array;
	 $skill_array = explode('；', $skill_array); 
	 foreach($skill_array as $v){
		$rs.=$skill_source_array[$v]['name'].'；';
		 
 	 }
	return $rs; 
}
 
//===============================================编辑情况======================
$user_info = $user->get($uid); //获取编辑器信息
 if($id){ 
	 $tip_msg = '编辑人员代码'; 
   
    $rows = $auditcode->get($id);  //获取需要编辑的内容

    //能力来源显示编辑信息
    $skll_array = explode('；', $rows['source']);
    foreach ($skll_array as $value) {
        $skill_source_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $skill_source_checkbox);
    }
    //评定方法信息
    $evaluation_methods_array = explode('；', $rows['evaluation_methods']);
    foreach ($evaluation_methods_array as $value) {
        $evaluation_methods_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $evaluation_methods_checkbox);
    }
    //评定人员
    if($rows[evaluater]){
    	$evaluater_select = str_replace("<option value='$rows[evaluater]'>", "<option value='$rows[evaluater]' selected>", $evaluater_select);
    } 
    //是否认证决定
 	//是否专业管理
	if($rows['is_assess']=='0'){
		$check_assess0='checked';
	}else{
		$check_assess1='checked';
	}
	//是否专业管理
	if($rows['is_profession']=='0'){
		$check_profession0='checked';
	}else{
		$check_profession1='checked';
	}
}	 

tpl('hr/hr_code_edit');
?>

