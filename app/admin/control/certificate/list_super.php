<?php
//监督维护列表
//项目状态数组
$project_status_array = array(
    '0' => '未安排',
    '1' => '待派人', //1-待派人|已安排
    '2' => '待审批',
    '3' => '已审批',
    '5' => '维护',
    '6' => '退回', //6-转审核部又退回监督维护
    
);
//审核类型
$audit_type_select = '';
if ($audit_type_array) {
    foreach ($audit_type_array as $code => $item) {
        if (!in_array($code, array('1001', '1002','1002','1008','1009','1010'))) 
			$audit_type_select.= "<option value=\"$code\">$item[name]</option>";
    }
}
extract($_GET, EXTR_SKIP);
$svStatus = 3;
$status_0 = $status_1 = $status_2 = $status_3 = '';
$ {
    'status_' . $svStatus
} = ' ui-tabs-active ui-state-active"';
/*$ep_name = getgp('ep_name');
$ctfrom = getgp('ctfrom');
$areacode = getgp('areacode');
$work_code = getgp('work_code');
$person = getgp('person');
$audit_ver = getgp('audit_ver');
*/
//省份下拉
$province_select =f_province_select();
unset($code, $item);
$fields = $join = $where = $page_str = '';
//要获取的字段
$fields.= "p.*,e.ep_name,e.ctfrom,e.areacode,e.ep_level,e.person";
//要关联的表
$join.= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
if ($ep_name) {
    $_eids = array();
    $_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%" . str_replace('%', '\%', trim($ep_name)) . "%'");
    while ($rt = $db->fetch_array($_query)) {
        $_eids[] = $rt['eid'];
    }
    if ($_eids) {
        $where.= " AND p.eid IN (" . implode(',', $_eids) . ")";
    }else
		$where.= " AND p.id<-1";

}
//省份
	if( $areacode ){
		$pcode = substr($areacode,0,2) . '0000';
		$_eids = array(-1);
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		$where.= " AND p.eid IN (" . implode(',', $_eids) . ")";
		unset( $_eids, $_query, $rt, $_eids );
		
		$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
	}

if ($ct_code=trim($ct_code)) {
    $where.= " AND p.ct_code = '$ct_code'";
}
if ($cti_code=trim($cti_code)) {
    $where.= " AND p.cti_code like '%$cti_code%'";
}
if ($iso) {
    $where.= " AND p.iso = '$iso'";
    $iso_select = str_replace("value=\"$iso\">", "value=\"$iso\" selected>", $iso_select);
}
if ($audit_ver) { //标准版本
    $where.= " AND p.audit_ver='$audit_ver'";
	$audit_ver_select = str_replace("value=\"$audit_ver\">", "value=\"$audit_ver\" selected>", $audit_ver_select);
}
if ($audit_type) {
    $where.= " AND p.audit_type = '$audit_type'";
    $audit_type_select = str_replace("value=\"$audit_type\">", "value=\"$audit_type\" selected>", $audit_type_select);
}
if ($pre_date_start) {
    $where.= " AND p.pre_date >= '$pre_date_start'";
}
if ($pre_date_end) {
    $where.= " AND p.pre_date <= '$pre_date_end'";
}
if ($final_date_start) {
    $where.= " AND p.final_date >= '$final_date_start'";
}
if ($final_date_end) {
    $where.= " AND p.final_date <= '$final_date_end'";
}
//合同来源限制
$len = get_ctfrom_level(current_user('ctfrom'));
if ($ctfrom && substr($ctfrom, 0, $len) == substr(current_user('ctfrom') , 0, $len)) {
    $_len = get_ctfrom_level($ctfrom);
    $len = $_len;
} else {
    $ctfrom = current_user('ctfrom');
}
switch ($len) {
    case 2:
        $add = 1000000;
        break;

    case 4:
        $add = 10000;
        break;

    case 6:
        $add = 100;
        break;

    case 8:
        $add = 1;
        break;
}
$ctfrom_e = sprintf("%08d", $ctfrom + $add);
$where.= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);
$allow_type=array('1003', '1002','1007','1008','1009','1010','2001','2002','3001');
$where .="   AND p.audit_type NOT IN ('".join("','",$allow_type)."') AND p.deleted = '0' ";
$where_0 = $where . " AND p.status IN('5','6') AND  p.sv_status NOT IN('1','2','3')";
//$where.=" AND sv_status=$svStatus";
$where_1 = $where . " AND p.sv_status=1 ";
$where_2 = $where . " AND p.sv_status=2";
$where_3 = $where . " AND p.sv_status=3";
if (!$export) {
    //为维护统计
/*     $total_0 = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where_0");
    // 待定统计
    $total_1 = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where_1");
    // 接受统计
    $total_2 =(int)$db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where_2");
 */    //不接受统计
    $total_3 = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where_3");
    $pages = numfpage($ { 'total_' . $svStatus});
}
$resdb = array();  
$query = $db->query("SELECT $fields FROM sp_project p $join WHERE 1 ${'where_' . $svStatus} ORDER BY p.pre_date,e.areacode ASC $pages[limit]");
while ($rt = $db->fetch_array($query)) {
    $cert = $db->get_row("SELECT id,certno,e_date,cert_scope FROM sp_certificate WHERE cti_id='$rt[cti_id]'  AND deleted = '0' order by status asc limit 1");
    $rt['certno'] = $cert['certno'];
    $rt['e_date'] = $cert['e_date'];
    $rt['zsid'] = $cert['id'];
    $rt['scope'] = $cert['cert_scope'];
    $rt['status'] = $project_status_array[$rt['status']];
    $rt['audit_code'] = LongToBr($rt['audit_code'], array(
        ";",
        "；"
    ));
	$rt['use_code']=LongToBr($rt['use_code'],array('；',';'));
    $rt['province'] = f_region_province($rt['areacode']);
    $rt['audit_type_V'] = f_audit_type($rt['audit_type']);
    $rt['audit_ver_V'] = f_audit_ver($rt['audit_ver']);
    $rt['ctfrom_V'] = f_ctfrom($rt['ctfrom']);
    $rt['up_date'] = mysql2date('Y-m-d', $rt['up_date']);
    if ('0000-00-00' == $rt['up_date'] || '1970-01-01' == $rt['up_date']) $rt['up_date'] = '';
    $rt['audit_ver'] = f_audit_ver($rt['audit_ver']);
	$rt[ep_phone]=$db->get_var("SELECT meta_value FROM `sp_metas_ep` WHERE `ID` = '$rt[eid]' AND `meta_name` = 'ep_phone'");
		$rt[ep_fax]=$db->get_var("SELECT meta_value FROM `sp_metas_ep` WHERE `ID` = '$rt[eid]' AND `meta_name` = 'ep_fax'");
    $resdb[$rt['id']] = $rt;
}
if (!$export) {
    tpl();
} else {
    ob_start();
    tpl('xls/list_super');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls('监督维护项目列表', $data);
}
?>   
