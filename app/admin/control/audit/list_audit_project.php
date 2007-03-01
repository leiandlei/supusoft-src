<?php
//审核安排=>审核项目查询

$project_status = array('未安排','待派人','待审批','已审批','','维护');
extract( $_GET, EXTR_SKIP );
$audit_pids = $assess_pids = array();

$fields   = $join = $where = $page_str = '';
$ep_name  = trim(getgp( 'ep_name' ));
$ctfrom   = getgp( 'ctfrom' );
$ct_code  = trim(getgp( 'ct_code' ));
$cti_code = trim(getgp( 'cti_code' ));
$iso = getgp( 'iso' );
$audit_type = getgp( 'audit_type' );

$assess_date_start = getgp( 'assess_date_start' );
$assess_date_end = getgp( 'assess_date_end' );

$audit_start_start = getgp( 'audit_start_start' );
$audit_start_end = getgp( 'audit_start_end' );
$audit_end_start = getgp( 'audit_end_start' );
$audit_end_end = getgp( 'audit_end_end' );
$audit_ver = getgp( 'audit_ver' );
$is_finance = getgp('is_finance');
$export = getgp( 'export' );
$save_date = getgp('save_date');
$save_date_end =getgp('save_date_end');
if( $ep_name ){
	$_eids = array(-1);
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND p.eid IN (".implode(',',$_eids).")";
	}
	$page_str .='&ep_name='.$ep_name;
}

//省份
if( $areacode=getgp("areacode") ){
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


//合同来源限制
$len = get_ctfrom_level( current_user( 'ctfrom' ) );
//print_r($ctfrom);exit;
//if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
//	$_len = get_ctfrom_level( $ctfrom );
//	$len = $_len;
//} else {
//	$ctfrom = current_user( 'ctfrom' );
//}
//switch( $len ){
//	case 2	: $add = 1000000; break;
//	case 4	: $add = 10000; break;
//	case 6	: $add = 100; break;
//	case 8	: $add = 1; break;
//}
//$ctfrom_e = sprintf("%08d",$ctfrom+$add);
//$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";

$ctfrom_select = str_replace( "value=\"$ctfrom\" >", "value=\"$ctfrom\" selected>" , $ctfrom_select );


//合同编号
if( $ct_code ){
   $where .= " AND p.ct_code = '$ct_code'";
	
}

if( $is_finance || $is_finance == '0'){
    $where .= " AND p.is_finance = '$is_finance'";

}

//合同项目编号
if( $cti_code ){
	$where .= " AND p.cti_code like '%$cti_code%'";
}
//认证体系
if( $iso ){
	$where .= " AND p.iso = '$iso'";
	$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>", $iso_select);
}
if($audit_ver) { //标准版本
	$where .= " AND p.audit_ver = '$audit_ver'";
    $audit_ver_select = str_replace( "value=\"$audit_ver\">", "value=\"$audit_ver\" selected>", $audit_ver_select);
}

//审核类型
if( $audit_type ){
	$where .= " AND p.audit_type = '$audit_type'";
	$audit_type_select = str_replace( "value=\"$audit_type\">", "value=\"$audit_type\" selected>", $audit_type_select);
}

$task_where = '';
//审核开始时间 起
if( $audit_start_start ){
	$task_where .= " AND tb_date >= '$audit_start_start'";
}
//审核开始时间 止
if( $audit_start_end ){
	$task_where .= " AND tb_date <= '$audit_start_end'";
}
//审核结束时间 起
if( $audit_end_start ){
	$task_where .= " AND te_date >= '$audit_end_start'";
}
//审核结束时间 止
if( $audit_end_end ){
	$task_where .= " AND te_date <= '$audit_end_end'";
}
if($ctfrom){
	$where .= " AND p.ctfrom = '$ctfrom'";
	
}
if( $task_where ){
	$tids = array();
	$query = $db->query("SELECT id FROM sp_task WHERE 1 $task_where");
	while( $rt = $db->fetch_array( $query ) ){
		$tids[] = $rt['id'];
	}

	if( $tids ){
		$where .= " AND p.tid IN (".implode(',',$tids).")";
	} else {
		$where .= " AND p.id < -1";
	}
}
//评定时间 起
if( $assess_date_start ){
    $where .= " AND p.sp_date >= '$assess_date_start'";
}
//评定时间 止
if( $assess_date_end ){
	$where .= " AND p.sp_date <= '$assess_date_end'";
} 

$where .= " AND p.deleted = '0'";

$fields .= "p.*,e.ep_name,e.ctfrom,t.tb_date,t.te_date";

/* 关联表 */
$join .= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
$join .= " LEFT JOIN sp_task t ON t.id = p.tid";

if( !$export ){
	$total = $db->get_var( "SELECT COUNT(*) FROM sp_project p WHERE 1 $where" );
	$pages = numfpage( $total);
}
$projects = array(); 
$query = $db->query( "SELECT $fields FROM sp_project p $join WHERE 1 $where ORDER BY t.te_date,e.ep_name ASC $pages[limit]" );
 while( $rt = $db->fetch_array( $query ) ){
	$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] );
	$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
	$rt['iso_V'] = f_iso( $rt['iso'] );
	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['tb_date'] = mysql2date( 'Y-m-d', $rt['tb_date'] );
	$rt['te_date'] = mysql2date( 'Y-m-d', $rt['te_date'] );
	$rt['status_V'] = $project_status[$rt['status']];
 	$cer = $db->get_row("select status,e_date from sp_certificate where cti_id='{$rt[cti_id]}' order by e_date desc");
	$rt['cert_status'] = read_cache("certstate",$cer[status]);
	$rt['e_date'] = $cer[e_date];
	
	$projects[$rt['id']] = chk_arr($rt);
}
 

if( !$export ){
	tpl( 'audit/list_audit_project' );
} else {
	ob_start();
	tpl( 'xls/list_project' );
	$data = ob_get_contents();
	ob_end_clean();

	export_xls( '审核项目列表', $data );
}
?>