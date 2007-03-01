<?php
$status_0         = '未安排';
$status_1         = '待派人';
$status_2         = '待审批';
$status_3         = '已审批';
/******************************
#			搜   索			  #
******************************/
$fields           = $join = $where = $page_str = '';
$ep_name          = getgp('ep_name'); //企业名称
$ep_addr          = getgp('ep_addr'); //企业地址
$ctfrom           = getgp('ctfrom'); //合同来源
$audit_type       = getgp('audit_type'); //审核类型
$ct_code          = trim(getgp('ct_code')); //合同编号
$cti_code         = trim(getgp('cti_code')); //合同项目编号
$audit_code       = getgp('audit_code'); //审核代码
$is_first         = getgp('is_first'); //是否初次
$iso              = getgp('iso'); //认证体系
$audit_ver        = getgp('audit_ver');
$pre_date_start   = getgp('pre_date_start'); //计划时间
$pre_date_end     = getgp('pre_date_end'); //计划时间
$final_date_start = getgp('final_date_start'); //最后监察时间 起
$final_date_end   = getgp('final_date_end'); //最后监察时间 止
$areacode         = getgp('areacode');
$export           = getgp('export');
//企业名称
if ($ep_name) {
    $_eids  = array();
    $_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%" . str_replace('%', '\%', trim($ep_name)) . "%'");
    while ($rt = $db->fetch_array($_query)) {
        $_eids[] = $rt['eid'];
    }
    if ($_eids) {
        $where .= " AND p.eid IN (" . implode(',', $_eids) . ")";
    } else {
        $where .= " AND p.id < -1";
    }
    unset($_eids, $_query, $rt);
}
//省份
if ($areacode) {
    $pcode  = substr($areacode, 0, 2) . '0000';
    $_eids  = array(
        -1
    );
    $_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '" . substr($areacode, 0, 2) . "'");
    while ($rt = $db->fetch_array($_query)) {
        $_eids[] = $rt['eid'];
    }
    $where .= " AND p.eid IN (" . implode(',', $_eids) . ")";
    unset($_eids, $_query, $rt, $_eids);
    $province_select = str_replace("value=\"$pcode\">", "value=\"$pcode\" selected>", $province_select);
}
//企业地址
if ($ep_addr) {
    $_eids  = array();
    $_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_addr LIKE '%" . str_replace('%', '\%', $ep_addr) . "%'");
    while ($rt = $db->fetch_array($_query)) {
        $_eids[] = $rt['eid'];
    }
    if ($_eids) {
        $where .= " AND p.eid IN (" . implode(',', $_eids) . ")";
    } else {
        $where .= " AND p.id < -1";
    }
    unset($_eids, $_query, $rt);
}
//合同编号
if ($ct_code) {
    $where .= " AND p.ct_code = '$ct_code'";
}
//合同项目编号
if ($cti_code) {
    $where .= " AND p.cti_code like '%$cti_code%'";
}
//认证体系
if ($iso) {
    $where .= " AND p.iso = '$iso'";
    $iso_select = str_replace("value=\"$iso\">", "value=\"$iso\" selected>", $iso_select);
}
if ($audit_ver) { //标准版本
    $where .= " AND p.audit_ver = '$audit_ver'";
}
//审核类型
if ($audit_type) {
    $where .= " AND p.audit_type = '$audit_type'";
    $audit_type_select = str_replace("value=\"$audit_type\">", "value=\"$audit_type\" selected>", $audit_type_select);
}
//合同来源限制
$len = get_ctfrom_level(current_user('ctfrom'));
if ($ctfrom && substr($ctfrom, 0, $len) == substr(current_user('ctfrom'), 0, $len)) {
    $_len = get_ctfrom_level($ctfrom);
    $len  = $_len;
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
$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);
//计划开始时间 开始
if ($pre_date_start) {
    $where .= " AND p.pre_date >= '$pre_date_start'";
}
//计划开始时间 结束
if ($pre_date_end) {
    $where .= " AND p.pre_date <= '$pre_date_end'";
}
//最后审核日期 开始
if ($final_date_start) {
    $where .= " AND p.final_date >= '$final_date_start'";
}
//最后审核日期 结束
if ($final_date_end) {
    $where .= " AND p.final_date <= '$final_date_end'";
}

//标签状态
$tag=$_GET['status']?$_GET['status']:'mouth';
$tag_status[$tag]='ui-tabs-active ui-state-active';

 

//要获取的字段
$fields .= "p.*,e.ep_name,e.ctfrom,e.ep_level,e.areacode,e.person,e.person_tel,e.ep_phone,e.ep_fax,ct.audit_require,ct.signe_name";
//要关联的表
$join .= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = p.ct_id";
$where .= " AND p.deleted = '0'";
if (!$export) {
    $total['all'] = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where AND p.status = 0 ");
  	 $now=date('Y-m');
	 $mouth_s=$now.'-01';
	 $mouth_e=$now.'-31';
	 $mouth_where=$where." AND p.pre_date>='$mouth_s' AND p.pre_date<='$mouth_e'";
	if($tag=='mouth'){
		$where=$mouth_where;
	}
 	$total['mouth'] = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $mouth_where AND p.status = 0 ");
	
    $pages = numfpage($total[$tag]);
}


$where .= " AND p.status = '0'";
$sql      = "SELECT $fields FROM sp_project p $join WHERE 1 $where ORDER BY p.final_date ASC $pages[limit]";

$projects = array();
$query    = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    $rt['audit_ver_V']  = f_audit_ver($rt['audit_ver']);
    $rt['audit_type_V'] = f_audit_type($rt['audit_type']);
    $rt['is_finance_V'] = ($rt['is_finance'] == '2')?'收完':'未收完';
    $rt['ctfrom_V']     = f_ctfrom($rt['ctfrom']);
    $rt['status_V']     = ${'status_' . $rt['status']};
    $rt['province']     = f_region_province($rt['areacode']);
    if ($rt[audit_require])
        $rt[style] = 'style="background-color:#f00;"';
	if($rt[audit_type]=='1007'){
		$rt['cert_date']=$db->get_var("SELECT e_date FROM `sp_certificate` WHERE `eid` = '$rt[eid]' AND `deleted` = '0' AND `cti_id` <> '$rt[cti_id]' ORDER BY `e_date` DESC ");
	}else{
		$rt['cert_date']=$db->get_var("SELECT e_date FROM `sp_certificate` WHERE  `deleted` = '0' AND `cti_id` ='$rt[cti_id]' ORDER BY `e_date` DESC ");
	
	}
    $projects[$rt['id']] = chk_arr($rt);
}
if (!$export) {
	var_dump(tpl('audit/list_wait_arrange'));
    tpl('audit/list_wait_arrange');
} else {
    ob_start();
    tpl('xls/list_wait_arrange');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls('审核项目列表', $data);
}
?>