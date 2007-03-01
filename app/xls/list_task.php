<?php

$task_status = array(
	1	=> '待派人',
	2	=> '待审批',
	3	=> '已审批'
);


$status = (int)getgp('status');

$fields = $where = $join = $page_str = '';
/******************************
 #			搜   索			  #
 ******************************/

$ep_name		= getgp( 'ep_name' ); //企业名称
$ctfrom			= getgp( 'ctfrom' ); //合同来源
$audit_type		= getgp( 'audit_type' ); //审核类型
$ct_code		= getgp( 'ct_code' ); //合同编号
$cti_code		= getgp( 'cti_code' ); //合同项目编号
$audit_code		= getgp( 'audit_code' ); //审核代码
$is_first		= getgp( 'is_first' ); //是否初次
$iso			= getgp( 'iso' ); //认证体系
$audit_start_start	= getgp('audit_start_start'); //审核开始时间 起
$audit_start_end= getgp('audit_start_end'); //审核开始时间 止
$audit_end_start= getgp('audit_end_start'); //审核结束时间 起
$audit_end_end	= getgp('audit_end_end'); //审核结束时间 止


if( $ep_name ){
	$_eids = array();
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND t.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND t.id < -1";
	}
}

if( $ct_code ){
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code'");
	if( $ct_id ){
		$where .= " AND t.ct_id = '$ct_id'";
	} else {
		$where .= " AND t.id < -1";
	}
}

if( $cti_code ){
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract_item WHERE cti_code like '%$cti_code%'");
	if( $ct_id ){
		$where .= " AND t.ct_id = '$ct_id'";
	} else {
		$where .= " AND t.id < -1";	
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
$where .= " AND t.ctfrom >= '$ctfrom' AND t.ctfrom < '$ctfrom_e'";

//认证体系
if( $iso ){
	$where .= " t.iso LIKE '%$iso%'";
}

//审核类型
if( $audit_type ){
	$where .= " AND t.audit_type = '$audit_type'";
}
//审核开始时间 起
if( $audit_start_s ){
	$where .= " AND t.tb_date >= '$audit_start_s'";
}
//审核开始时间 止
if( $audit_start_e ){
	$where .= " AND t.tb_date <= '$audit_start_e'";
}
//审核结束时间 起
if( $audit_end_s ){
	$where .= " AND t.te_date >= '$audit_end_s'";
}
//审核结束时间 止
if( $audit_end_e ){
	$where .= " AND t.te_date <= '$audit_end_e'";
}

$where .= " AND t.deleted = '0'";
/* 审核任务的项目 */
$fields .= "p.id,p.audit_ver,p.audit_type,t.id tid,t.tb_date,t.te_date,t.status,t.tk_num,t.create_date";
$fields .= ",e.ep_name,e.ctfrom,e.eid,cti.cti_code,hr.name";

$join .= " LEFT JOIN sp_task_vice tv ON tv.tid = t.id";
$join .= " LEFT JOIN sp_project p ON p.id = tv.pid";
$join .= " LEFT JOIN sp_hr hr ON hr.id = t.create_uid";
$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = p.ct_id";
$join .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = p.cti_id";
$join .= " LEFT JOIN sp_enterprises e ON e.eid = t.eid";




$where .= " AND t.status = '$status'";

$task_projects = $tids = array();
$sql = "SELECT $fields FROM sp_task t $join WHERE 1 $where ORDER BY t.id DESC" ;

$query = $db->query( $sql);
while( $rt = $db->fetch_array( $query ) ){
	isset( $task_projects[$rt['tid']] ) or $task_projects[$rt['tid']] = array(
			'ep_name'	=> $rt['ep_name'],
			'tb_date' => mysql2date( 'Y-m-d', $rt['tb_date'] ),
			'te_date' => mysql2date( 'Y-m-d', $rt['te_date'] ),
			'ctfrom_V' => f_ctfrom( $rt['ctfrom'] ),
			'create_date' => mysql2date( 'Y-m-d', $rt['create_date'] ),
			'status'	=> $rt['status'],
			'tk_num'	=> $rt['tk_num'],
			'name'		=> $rt['name']
	);

	isset( $task_projects[$rt['tid']]['audit_vers'] ) or $task_projects[$rt['tid']]['audit_vers'] = array();
	isset( $task_projects[$rt['tid']]['audit_types'] ) or $task_projects[$rt['tid']]['audit_types'] = array();
	isset( $task_projects[$rt['tid']]['cti_codes'] ) or $task_projects[$rt['tid']]['cti_codes'] = array();

	$task_projects[$rt['tid']]['audit_vers'][] = f_audit_ver( $rt['audit_ver'] );
	$task_projects[$rt['tid']]['audit_types'][] = f_audit_type( $rt['audit_type'] );
	$task_projects[$rt['tid']]['cti_codes'][] = $rt['cti_code'];


	$tids[] = $rt['tid'];
	//$task_projects[$rt['tid']] = $rt;
}

if( $task_projects ){

	//审核组信息
	$auditors = $teams = array();
	$team_fields = $team_join = $team_where = '';
	$team_fields .= "tat.*,p.iso,p.audit_ver,ta.id as auditorid,ta.uid,ta.tid,ta.name";

	$team_join .= " LEFT JOIN sp_task_auditor ta ON ta.id = tat.auditor_id";
	$team_join .= " LEFT JOIN sp_project p ON p.id = tat.pid";

	$team_where = " AND tat.tid IN (".implode(',',$tids).")";

	$audit_teams = array();

	$sql = "SELECT $team_fields FROM sp_task_audit_team tat $team_join WHERE 1 $team_where";

	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		if( !$rt['role'] ) continue;
		isset( $audit_teams[$rt['tid']] ) or $audit_teams[$rt['tid']] = array();
		$str = '';
		$str .= $rt['name'];
		$str .= "(".f_iso($rt['iso']).":".f_qua_type($rt['qua_type']).")";
		$str .= "(".($rt['is_leader']?'是':'否').")";
		$audit_teams[$rt['tid']][] = $str;
	}
}




//输出Execl文件
$filename = iconv( 'UTF-8', 'GB2312', $task_status[$status].'计划_').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $filename );
header("Pragma: no-cache");
header("Expires: 0");

tpl( "xls/list_task" );

?>