<?php

$project_status = array( '未安排', '待派人', '待审批', '已审批' );

$status = (int)getgp( 'status' );


/******************************
 #			搜   索			  #
 ******************************/

$fields = $join = $where = '';

$ep_name		= getgp( 'ep_name' ); //企业名称
$ctfrom			= getgp( 'ctfrom' ); //合同来源
$audit_type		= getgp( 'audit_type' ); //审核类型
$ct_code		= getgp( 'ct_code' ); //合同编号
$cti_code		= getgp( 'cti_code' ); //合同项目编号
$audit_code		= getgp( 'audit_code' ); //审核代码
$is_first		= getgp( 'is_first' ); //是否初次
$iso			= getgp( 'iso' ); //认证体系
$pre_date_start	= getgp('pre_date_start'); //计划时间
$pre_date_end	= getgp('pre_date_end'); //计划时间
$final_date_start	= getgp( 'final_date_start' ); //最后监察时间 起
$final_date_end	= getgp( 'final_date_end' ); //最后监察时间 止


//企业名称
if( $ep_name ){
	$_eids = array();
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND p.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND p.id < -1";
	}
	unset( $_eids, $_query, $rt );
}

//合同编号
if( $ct_code ){
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code'");
	if( $ct_id ){
		$where .= " AND p.ct_id = '$ct_id'";
	} else {
		$where .= " AND p.id < -1 ";
	}
	unset( $ct_id );
}

//合同项目编号
if( $cti_code ){
	$cti_id = $db->get_var("SELECT ct_id FROM sp_contract_item WHERE cti_code like '%$cti_code%'");
	if( $cti_id ){
		$where .= " AND p.cti_id = '$cti_id'";
	} else {
		$where .= " AND p.id < -1";
	}
	$page_str .= '&cti_code='.$cti_code;
}

//认证体系
if( $iso ){
	$where .= " AND p.iso = '$iso'";
}

//审核类型
if( $audit_type ){
	$where .= " AND p.audit_type = '$audit_type'";
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
$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";

//计划开始时间 开始
if( $pre_date_start ){
	$where .= " AND p.pre_date >= '$pre_date_start'";
}
//计划开始时间 结束
if( $pre_date_end ){
	$where .= " AND p.pre_date <= '$pre_date_end'";
}
//最后审核日期 开始
if( $final_date_start ){
	$where .= " AND p.final_date >= '$final_date_start'";
}
//最后审核日期 结束
if( $final_date_end ){
	$where .= " AND p.final_date <= '$final_date_end'";
}


//要获取的字段
$fields .= "p.*,e.ep_name,e.ctfrom,ct.ct_code,ct.pre_date,ci.cti_code";

//要关联的表
$join .= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = p.ct_id";
$join .= " LEFT JOIN sp_contract_item ci ON ci.cti_id = p.cti_id";

$where .= " AND p.is_drop = '0' AND p.deleted = '0'";

$where .= " AND p.status = '0'";
$sql = "SELECT $fields FROM sp_project p $join WHERE 1 $where ORDER BY p.id DESC $pages[limit]";

$projects = array();

$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$rt['audit_ver'] = f_audit_ver( $rt['audit_ver'] );
	$rt['audit_type'] = f_audit_type( $rt['audit_type'] );
	$rt['ctfrom'] = f_ctfrom( $rt['ctfrom'] );
	$rt['final_date'] == '0000-00-00' && $rt['final_date'] = '';
	$rt['status'] = $project_status[$rt['status']];
	$projects[$rt['id']] = $rt;
}





//输出Execl文件
$filename = iconv( 'UTF-8', 'GB2312', '未安排项目').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $filename );
header("Pragma: no-cache");
header("Expires: 0");

tpl( 'xls/list_project' );

?>