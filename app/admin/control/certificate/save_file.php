<?php

$status_1 = '待派人';
$status_2 = '待审批';
$status_3 = '已审批';
extract($_GET);

$status = (int)$status;

${"tab_".$status}=" ui-tabs-active ui-state-active";
$fields = $where = $join = $page_str = '';
/******************************
 #			搜   索			  #
 ******************************/



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

//省份
if( $areacode=getgp("areacode") ){
	$pcode = substr($areacode,0,2) . '0000';
	$_eids = array(-1);
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	$where .= " AND t.eid IN (".implode(',',$_eids).")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
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
	$sql="SELECT tid FROM sp_project WHERE cti_code like '%$cti_code%' and deleted=0";
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
	$where .= " AND t.tb_date >= '$audit_start_start 00:00:00'";
}
//审核开始时间 止
if( $audit_start_end ){
	$where .= " AND t.tb_date <= '$audit_start_end 23:00:00'";
}
//审核结束时间 起
if( $audit_end_start ){
	$where .= " AND t.te_date >= '$audit_end_start 00:00:00'";
}
//审核结束时间 止
if( $audit_end_end ){
	$where .= " AND t.te_date <= '$audit_end_end 23:00:00'";
}
// 归档时间 起
if( $save_date ){
    $where .=" AND t.save_date >= '$save_date 00:00:00'";
}
// 归档时间 至
if ( $save_date_end ){
    $where .= " AND t.save_date <= '$save_date_end 00:00:00'";
}

$where .= " AND t.deleted = '0'";


$state_total = array(0,0,0,0);

//计算数量
if( !$export ){
	$query = $db->query("SELECT t.save_status,COUNT(*) total FROM sp_task t  WHERE 1 $where AND t.save_status IN (0,1) GROUP BY t.save_status");
	while( $rt = $db->fetch_array( $query ) ){
		$state_total[$rt['save_status']] = $rt['total'];
	}
	$pages = numfpage( $state_total[$status]);
}

$where .= " AND t.save_status = '$status'";

//审核任务
$tasks = array();
$query = $db->query( "SELECT t.*,e.ep_name,e.areacode,e.person,e.person_tel,e.ep_phone,e.ep_fax,e.ep_level,hr.name FROM sp_task t LEFT JOIN sp_enterprises e ON e.eid = t.eid LEFT JOIN sp_hr hr ON hr.id = t.create_uid WHERE 1 $where ORDER BY t.te_date DESC $pages[limit]" );
while( $rt = $db->fetch_array( $query ) ){
	$rt[num]=mkdate($rt['tb_date'],$rt['te_date']);
	$rt['tb_date'] = mysql2date( 'Y-m-d H:i', $rt['tb_date'] );
	$rt['te_date'] = mysql2date( 'Y-m-d H:i', $rt['te_date'] );
	$rt['province']		= f_region_province( $rt['areacode'] );

	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['create_date'] = mysql2date( 'Y-m-d', $rt['create_date'] );
	if($rt[note] or $rt[self_note])
		$rt[style1]='style="background-color:#f00;"';
	$rt['leader']=$db->get_var("SELECT name FROM `sp_task_audit_team` WHERE `tid` = '$rt[id]' AND `deleted` = '0' AND `role` = '01'");
	$tasks[$rt['id']] = chk_arr($rt);
}



if( $tasks){

	/* 审核任务的项目 */
	$fields .= "p.tid,p.ct_id,p.id,p.audit_ver,p.audit_type,p.cti_code,p.use_code,p.iso";
	$sql = "SELECT $fields FROM sp_project p $join WHERE 1 AND p.tid IN (".implode(',',array_keys($tasks)).") AND p.deleted = 0 ORDER BY p.iso " ;
	$query = $db->query( $sql);
	while( $rt = $db->fetch_array( $query ) ){
		isset( $tasks[$rt['tid']]['audit_vers'] ) or $tasks[$rt['tid']]['audit_vers'] = array();
		isset( $tasks[$rt['tid']]['audit_types'] ) or $tasks[$rt['tid']]['audit_types'] = array();
		isset( $tasks[$rt['tid']]['cti_codes'] ) or $tasks[$rt['tid']]['cti_codes'] = array();
		isset( $tasks[$rt['tid']]['use_codes'] ) or $tasks[$rt['tid']]['use_codes'] = array();

		$tasks[$rt['tid']]['audit_vers'][] = f_audit_ver( $rt['audit_ver'] );
		$tasks[$rt['tid']]['audit_types'][] = f_audit_type( $rt['audit_type'] );
		$tasks[$rt['tid']]['cti_codes'][] = $rt['cti_code'];
		$tasks[$rt['tid']]['use_codes'][] = $rt['use_code'];
	}


	 
} 


if( !$export ){
	tpl();
} else {
	ob_start();
	tpl( 'xls/list_task' );
	$data = ob_get_contents();
	ob_end_clean();

	export_xls( '项目计划列表', $data );
}
?>