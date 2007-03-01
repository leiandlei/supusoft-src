<?php

$status_1 = '待派人';
$status_2 = '待审批';
$status_3 = '已审批';

$status = (int)getgp('status');
${"tab_".$status}="ui-tabs-active ui-state-active";
$fields = $where = $join = $page_str = '';
/******************************
 #			搜   索			  #
 ******************************/

$ep_name		= trim( getgp( 'ep_name' ) ); //企业名称
$ctfrom			= getgp( 'ctfrom' ); //合同来源
$audit_type		= getgp( 'audit_type' ); //审核类型
$ct_code		= trim( getgp( 'ct_code' ) ); //合同编号
$cti_code		= trim( getgp( 'cti_code' ) ); //合同项目编号
$audit_code		= trim( getgp( 'audit_code' ) ); //审核代码
$is_first		= getgp( 'is_first' ); //是否初次
$iso			= getgp( 'iso' ); //认证体系
$audit_ver = getgp( 'audit_ver' );
$audit_start_start	= trim( getgp('audit_start_start') ); //审核开始时间 起
$audit_start_end= trim( getgp('audit_start_end') ); //审核开始时间 止
$audit_end_start= trim( getgp('audit_end_start') ); //审核结束时间 起
$audit_end_end	= trim( getgp('audit_end_end') ); //审核结束时间 止

$export			= getgp( 'export' );


//企业名称
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
//合同编号
if( $ct_code ){
	$_tids=array();
	$sql="SELECT tid FROM sp_project WHERE ct_code = '$ct_code'";
	$query=$db->query($sql);
	while( $rt = $db->fetch_array( $query ) ){
		$_tids[]=$rt[tid];
	}
	if($_tids){
		$where .= " AND t.id in (".implode(',',$_tids).")";
	} else {
		$where .= " AND t.id < -1";
	}
	unset( $_tids, $query, $rt );
}
//项目编号
if( $cti_code ){
	$_tids=array();
	$sql="SELECT tid FROM sp_project WHERE cti_code = '$cti_code'";
	$query=$db->query($sql);
	while( $rt = $db->fetch_array( $query ) ){
		$_tids[]=$rt[tid];
	}
	if($_tids){
		$where .= " AND t.id in (".implode(',',$_tids).")";
	} else {
		$where .= " AND t.id < -1";
	}
	unset( $_tids, $query, $rt );
}


//合同来源限制
$len = get_ctfrom_level( current_user( 'ctfrom' ) );
if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
	$_len = get_ctfrom_level( $ctfrom );
	$len = $_len;
} else {
	$ctfrom = current_user( 'ctfrom' );
}
switch( $len ){
	case 2	: $add = 1000000; break;
	case 4	: $add = 10000; break;
	case 6	: $add = 100; break;
	case 8	: $add = 1; break;
}
$ctfrom_e = sprintf("%08d",$ctfrom+$add);
$where .= " AND t.ctfrom >= '$ctfrom' AND t.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );

//认证体系
if( $iso ){
	$where .= " AND t.iso LIKE '%$iso%'";
	$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>", $iso_select);
}
if($audit_ver) { //标准版本
	$_tids = array();
	$query = $db->query("SELECT tid FROM sp_project WHERE audit_ver = '$audit_ver'");
	while( $rt = $db->fetch_array( $query ) ){
		$_tids[] = $rt['tid'];
	}
	if( $_tids ){
		$where .= " AND t.id IN (".implode(',',$_tids).")";
	}else {
		$where .= " AND t.id < -1";
	}
	unset( $_tids, $query, $rt );
}

//审核类型
if( $audit_type ){
	$where .= " AND t.audit_type like '%$audit_type%'";
	$audit_type_select = str_replace( "value=\"$audit_type\">", "value=\"$audit_type\" selected>", $audit_type_select);
}
//审核开始时间 起
if( $audit_start_start ){
	$where .= " AND t.tb_date >= '$audit_start_start'";
}
//审核开始时间 止
if( $audit_start_end ){
	$where .= " AND t.tb_date <= '$audit_start_end'";
}
//审核结束时间 起
if( $audit_end_start ){
	$where .= " AND t.te_date >= '$audit_end_start'";
}
//审核结束时间 止
if( $audit_end_end ){
	$where .= " AND t.te_date <= '$audit_end_end'";
}




$where .= " AND t.deleted = '0' AND t.status='3'";


$state_total = array(0,0,0,0);

//计算数量
if( !$export ){
	$query = $db->query("SELECT t.jh_sp_status,COUNT(*) total FROM sp_task t  WHERE 1 $where AND t.jh_sp_status IN (0,1) GROUP BY t.jh_sp_status");
	while( $rt = $db->fetch_array( $query ) ){
		$state_total[$rt['jh_sp_status']] = $rt['total'];
	}
	$pages = numfpage( $state_total[$status]);
}

$where .= " AND t.jh_sp_status = '$status'";

//审核任务 主表
$tasks = array();
$query = $db->query( "SELECT t.*,e.ep_name,hr.name FROM sp_task t LEFT JOIN sp_enterprises e ON e.eid = t.eid LEFT JOIN sp_hr hr ON hr.id = t.create_uid WHERE 1 $where ORDER BY t.te_date  $pages[limit]" );
// echo $db->sql;
while( $rt = $db->fetch_array( $query ) ){
	$rt['tb_date'] = mysql2date( 'Y-m-d H:i', $rt['tb_date'] );
	$rt['te_date'] = mysql2date( 'Y-m-d H:i', $rt['te_date'] );

	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['create_date'] = mysql2date( 'Y-m-d', $rt['create_date'] );
	$rt['leader']=$db->get_var("SELECT name FROM `sp_task_audit_team` WHERE `tid` = '$rt[id]' AND `deleted` = '0' AND `role` = '01' ");
	$tasks[$rt['id']] = $rt;
}



if( $tasks ){

	/* 审核任务的项目 */
	$fields .= "p.tid,p.id,p.audit_ver,p.audit_type,p.cti_code";

	//$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = p.ct_id";
	//$join .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = p.cti_id";

	$sql = "SELECT $fields FROM sp_project p $join WHERE 1 AND p.tid IN (".implode(',',array_keys($tasks)).") AND p.deleted = 0 " ;
	$query = $db->query( $sql);
	while( $rt = $db->fetch_array( $query ) ){
		$tasks[$rt['tid']]['audit_vers'][] = f_audit_ver( $rt['audit_ver'] );
		$tasks[$rt['tid']]['audit_types'][] = f_audit_type( $rt['audit_type'] );
		$tasks[$rt['tid']]['cti_codes'][] = $rt['cti_code'];



	}
	//审核组信息

	/* $team_fields = $team_join = $team_where = '';

	$team_fields .= "tat.iso,tat.role,tat.qua_type,tat.tid,tat.name";

	//$team_join .= " LEFT JOIN sp_hr hr ON hr.id= tat.uid";

	$team_where = " AND tat.tid IN (".implode(',',array_keys($tasks)).")";

	$sql1 = "SELECT $team_fields FROM sp_task_audit_team tat WHERE 1 AND tat.deleted = 0 $team_where  ";

	$query1 = $db->query( $sql1 );
	
	while( $row = $db->fetch_array( $query1 ) ){
		$str = '';
		//$str .=$f;
		$str .= $row['name'];
		$str .= "(".f_iso($row['iso']).":".f_qua_type($row['qua_type']).")(";
		$str.=read_cache('audit_role',$row['role']).")";
		isset( $tasks[$row['tid']]['audit'] ) or $tasks[$row['tid']]['audit'] = array();
		$tasks[$row['tid']]['audit'][] =$str;
		
	} */
}


if( !$export ){
	tpl();
} else {
	ob_start();
	tpl( 'xls/list_plan' );
	$data = ob_get_contents();
	ob_end_clean();

	export_xls( '项目计划列表', $data );
}
?>