<?php

$fields = $join = $where = '';
$is_bao = getgp('is_bao');
if(!$is_bao){$is_bao='0';}


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


$status_is_bao_0 = $status_is_bao_1 = '';
${'status_is_bao_'.$is_bao} = ' ui-tabs-active ui-state-active';


if( $ep_name ){ //企业名称
	$where .= " AND e.ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'";
}

//合同来源限制
$len = get_ctfrom_level( current_user( 'ctfrom' ) );

if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
	$_len = get_ctfrom_level( $ctfrom );
	$len = $_len;
	$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
} else {
	$ctfrom = current_user( 'ctfrom' );
}
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
$where .= " AND LEFT(p.ctfrom,$len) = '" . substr($ctfrom, 0, $len) . "'";

if( $ct_code ){ //合同编码
	$where .= " AND ct.ct_code = '$ct_code'";
}

if( $cti_code ){ //合同项目编码
	$where .= " AND cti.cti_code like '%$cti_code%'";
}

if( $audit_type ){ //审核类型
	$where .= " AND ( p.audit_type = '$audit_type' OR cti.audit_type = '$audit_type')";
}

if( $iso ){ //认证体系
	$where .= " AND ct.ct_id IN (SELECT ct_id FROM sp_contract_item WHERE iso = '$iso')";
}

if( $audit_start_start ){ //审核开始时间 起
	$where .= " AND t.tb_date > '$audit_start_start'";
}

if( $pre_date_end ){ //审核开始时间 止
	$where .= " AND t.tb_date < '$audit_start_end'";
}

if( $audit_end_start ){ //审核结束时间 起
	$where .= " AND t.te_date > '$audit_end_start'";
}

if( $audit_end_end ){ //审核结束时间 止
	$where .= " AND t.te_date < '$audit_end_end'";
}

$where .= " AND p.deleted = '0' ";
/* 审核任务的项目 */
$fields .= "tv.*,t.tb_date,t.te_date,t.status,t.tk_num,t.create_date,p.audit_ver,p.audit_type";
$fields .= ",e.ep_name,e.ctfrom,e.eid,cti.cti_code,hr.name,p.bao_date,e.work_code,e.ep_addr,e.ctfrom,e.areacode,e.person,e.person_tel";
/*
$join .= " INNER JOIN sp_task t ON t.id = tv.tid";
$join .= " LEFT JOIN sp_hr hr ON hr.id = t.create_uid";
$join .= " INNER JOIN sp_project p ON p.id = tv.pid";
$join .= " INNER JOIN sp_contract ct ON ct.ct_id = p.ct_id";
$join .= " INNER JOIN sp_contract_item cti ON cti.cti_id = p.cti_id";
*/
$join .= " INNER JOIN sp_enterprises e ON e.eid = p.eid ";

$where .= " AND p.status = '3'";
$state_total = array(
'0'=>$db->get_var("select COUNT(*) from sp_project p $join where 1 $where and p.is_bao='0' "),
'1'=>$db->get_var("select COUNT(*) from sp_project p $join where 1 $where and p.is_bao='1' ")
);

$pages = numfpage( $state_total[$is_bao], 20, "?m=$m&a=$a&is_bao=$is_bao" );

$where .= " and p.is_bao='$is_bao'  ";

$task_projects = array();
$sql = "select p.bao_date,p.bao_uid,p.id,p.eid,p.ctfrom,p.audit_type from sp_project p $join where 1 $where order by p.id desc  ";

$query = $db->query( $sql);
while( $rt = $db->fetch_array( $query ) ){
	$e_info = $db->get_row("select ep_addr,areacode,ep_name,work_code,person,person_tel from sp_enterprises where eid='$rt[eid]' ");
	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
	$rt['ep_name'] = $e_info['ep_name'];
	$rt['person'] = $e_info['person'];
	$rt['person_tel'] = $e_info['person_tel'];
	$rt['ep_addr'] = $e_info['ep_addr'];
	$rt['work_code'] = $e_info['work_code'];
	$rt['areacode'] = $e_info['areacode'];
	$task_projects[$rt['id']] = $rt;
}

if( $task_projects ){

	//审核组信息
	$auditors = $teams = array();
	$team_fields = $team_join = $team_where = '';
	$team_fields = " * ";
	$team_join .= " INNER JOIN sp_task_audit_team tat ON tat.auditor_id  = ta.id";

	$team_where = " AND tat.pid IN (" . implode(',' , array_keys( $task_projects ) ) . ")";

	$audit_teams = array();

	$sql = "SELECT $team_fields FROM sp_task_auditor ta $team_join WHERE 1 $team_where  ";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		//if( !$rt['audit_code'] ) continue;
		isset( $audit_teams[$rt['pid']] ) or $audit_teams[$rt['pid']] = array();
		if($rt['role']=='1004'){
			$str = '';
			$str .= $rt['name'];
			$str .= "(".f_iso($rt['iso']).":专家)";
			$audit_teams[$rt['pid']]['z'][] = $str;
		}else{
			$str = '';
			$str .= $rt['name'];
			$str .= "(".f_iso($rt['iso']).":".f_qua_type($rt['qua_type']).")";
			$str .= "(".f_iso($rt['iso']).":".($rt['is_leader']?'是':'否').")";
			$audit_teams[$rt['pid']]['s'][] = $str;
		}
		$audit_teams[$rt['pid']]['tb_date'] = substr($rt['taskBeginDate'],0,10);
		$audit_teams[$rt['pid']]['te_date'] = substr($rt['taskEndDate'],0,10);
		
	}
}


//输出Execl文件
$filename = iconv( 'UTF-8', 'GB2312', '审核计划上报_').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $filename );
header("Pragma: no-cache");
header("Expires: 0");


tpl( 'xls/list_plan_project' );

?>