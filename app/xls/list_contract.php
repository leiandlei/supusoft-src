<?php


$state_V = array(
	'未登记完',
	'待评审',
	'待审批',
	'已审批'
);


$fields = $join = $where = $item_join = $item_where = '';

$status = (int)getgp( 'status' );

$ep_name		= getgp( 'ep_name' );
$accept_user	= getgp('accept_user');
$ctfrom			= getgp( 'ctfrom' );
$audit_type		= getgp( 'audit_type' );
$accept_date_start	= getgp( 'accept_date_start' );
$accept_date_end	= getgp( 'accept_date_end' );
$ct_code		= getgp( 'ct_code' );
$cti_code		= getgp( 'cti_code' );
$audit_code		= getgp( 'audit_code' );
$is_first		= getgp( 'is_first' );
$iso			= getgp( 'iso' );

if( $ep_name ){
	$_eids = array();
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND ct.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND ct.ct_id < -1";
	}
	unset( $_eids, $_query, $rt, $_eids );
}

if( $accept_user ){	//受理人
	$create_uid = $db->get_var("SELECT id FROM hr WHERE name = '$accept_user'");
	if( $create_uid ){
		$where .= " AND ct.create_uid = '$create_uid'";
	} else {
		$where .= " AND ct.ct_id < -1";
	}
}

//合同来源限制
$len = get_ctfrom_level( current_user( 'ctfrom' ) );
if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
	$_len = get_ctfrom_level( $ctfrom );
	$len = $_len;
} else {
	$ctfrom = current_user( 'ctfrom' );
}
$last = substr($ctfrom,$len - 1,1);
$ctfrom_e = substr( $ctfrom, 0, $len -1 ).($last+1);
$_i = 8 - $len;
for( $i = 0; $i < $_i; $i++ ){
	$ctfrom_e .= '0';
}
$where .= " AND ct.ctfrom >= '$ctfrom' AND ct.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
$page_str .='&ctfrom='.$ctfrom;
unset( $len, $_len );


if( $audit_type ){ //审核类型
	$_ct_ids = array();
	$query = $db->query("SELECT ct_id FROM sp_contract_item WHERE audit_type = '$audit_type'");
	while( $rt = $db->fetch_array( $query ) ){
		$_ct_ids[] = $rt['ct_id'];
	}
	if( $_ct_ids ){
		$where .= " AND ct.ct_id IN (".implode(',',$_ct_ids).")";
	} else {
		$where .= " AND ct.ct_id < -1";
	}
	unset( $_ct_ids, $query, $rt );
}

if( $accept_date_start ){ //受理时间 起
	$where .= " AND ct.accept_date > '$accept_date_start'";
}

if( $accept_date_end ){ //受理时间 止
	$where .= " AND ct.accept_date < '$accept_date_end'";
}

if( $ct_code ){ //合同编码
	$where .= " AND ct.ct_code = '$ct_code'";
}

if( $cti_code ){ //合同项目编码
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract_item WHERE cti_code like '%$cti_code%'");
	if( $ct_id ){
		$where .= " AND ct.ct_id = '$ct_id'";
	} else {
		$where .= " AND ct.ct_id < -1";
	}
	unset( $ct_id );
}

if( $audit_code ){ //审核代码

	$_ct_ids = array();
	$audit_codes = explode( '；', $audit_code );
	$where_arr = array();
	foreach( $audit_codes as $code ){
		$where_arr[] = "audit_code LIKE '$code'";
	}
	$query = $db->query("SELECT ct_id FROM sp_contract_item WHERE ".implode(' OR ', $where_arr) );
	while( $rt = $db->fetch_array( $query ) ){
		$_ct_ids[] = $rt['ct_id'];
	}
	if( $_ct_ids ){
		$where .= " AND ct.ct_id IN (".implode(',',$_ct_ids).")";
	} else {
		$where .= " AND ct.ct_id < -1";
	}
	unset( $ct_ids, $audit_codes, $where_arr, $query, $rt );
}


if( $iso ){ //认证体系
	$_ct_ids = array();
	$query = $db->query("SELECT ct_id FROM sp_contract_item WHERE iso = '$iso'");
	while( $rt = $db->fetch_array( $query ) ){
		$_ct_ids[] = $rt['ct_id'];
	}
	if( $_ct_ids ){
		$where .= " AND ct.ct_id IN (".implode(',',$_ct_ids).")";
	} else {
		$where .= " AND ct.ct_id < -1";
	}
}


$where .= " AND ct.deleted = '0'";

$status_0_tab = $status_1_tab = $status_2_tab = $status_3_tab = '';
${'status_'.$status.'_tab'} = ' ui-tabs-active ui-state-active';

$fields .= ",e.ep_name,e.ctfrom,hr.name as accept_user";
$join = " LEFT JOIN sp_enterprises e ON e.eid = ct.eid";
$join .= " LEFT JOIN sp_hr hr ON hr.id = ct.create_uid";



$where .= " AND ct.status = '$status'";


$where .= " AND ct.deleted = '0'";

$where .= " AND ct.status = '$status'";

$fields .= ",e.ep_name,e.ctfrom,hr.name as accept_user";

$join = " INNER JOIN sp_enterprises e ON e.eid = ct.eid";
$join .= " LEFT JOIN sp_hr hr ON hr.id = ct.create_uid";


//列表
$contracts = array();
$sql = "SELECT ct.* $fields FROM sp_contract ct $join WHERE 1 $where ORDER BY ct.ct_id DESC";

$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	if( 'y' == $rt['is_first'] ){
		$rt['is_first'] = '是';
	} else {
		$rt['is_first'] = '否';
	}
	$rt['ctfrom'] = f_ctfrom( $rt['ctfrom'] );
	$contracts[$rt['ct_id']] = $rt;
}

if( $contracts ){
	$item_where .= " AND cti.ct_id IN (".implode(',',array_keys($contracts)).")";
	$sql = "SELECT cti.* FROM sp_contract_item cti $item_join WHERE 1 $item_where";

	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		isset( $contracts[$rt['ct_id']]['audit_types'] ) or $contracts[$rt['ct_id']]['audit_types'] = array();
		isset( $contracts[$rt['ct_id']]['audit_codes'] ) or $contracts[$rt['ct_id']]['audit_codes'] = array();
		isset( $contracts[$rt['ct_id']]['cti_codes'] ) or $contracts[$rt['ct_id']]['cti_codes'] = array();

		$contracts[$rt['ct_id']]['audit_types'][] = f_iso($rt['iso']) . "：" . f_audit_type( $rt['audit_type'] );
		$contracts[$rt['ct_id']]['audit_codes'][] = f_iso($rt['iso']) . "：" . $rt['audit_code'];
		$contracts[$rt['ct_id']]['cti_codes'][] = $rt['cti_code'];
	}
}



//输出Execl文件
$filename = iconv( 'UTF-8', 'GB2312', $state_V[$status].'合同_').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $filename );
header("Pragma: no-cache");
header("Expires: 0");



tpl( 'xls/list_contract' );
?>