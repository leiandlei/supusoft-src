<?php 
//审核计划列表
require_once( ROOT . '/data/cache/audit_type.cache.php' ); 
$ctfrom_select = f_ctfrom_select();//合同来源
$iso_select =f_select('iso');//认证体系 
//审核类型
if( $audit_type_array ){
	foreach( $audit_type_array as $code => $item ){
		if( in_array( $code, array( '1003','1004','1005', '1006','1007' ) ) )
		$audit_type_select .= "<option value=\"$code\">$item[name]</option>";
	}
}  
extract( $_GET, EXTR_SKIP );
$fields = $join = $where = '';
$is_bao = intval( $is_bao ); 
$status_is_bao_0 = $status_is_bao_1 = '';
${'status_is_bao_'.$is_bao} = ' ui-tabs-active ui-state-active';

if( $ep_name ){ //企业名称
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
$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );




//合同编号
if( $ct_code ){
	$where .= " AND p.ct_code like '%$ct_code%'";
}
//项目编号
if( $cti_code ){
	
	$where .= " AND p.cti_code like '%$cti_code%'";
}

if( $audit_type ){ //审核类型
	$where .= " AND p.audit_type ='$audit_type'";
	$audit_type_select=str_replace("value=\"$audit_type\"","value=\"$audit_type\" selected",$audit_type_select);
}

if( $iso ){ //认证体系
	$where .= " AND p.iso ='$iso'";
}

if( $audit_start_start ){ //审核开始时间 起
	$where .= " AND t.tb_date >= '$audit_start_start 00:00:00'";
}

if( $audit_start_end ){ //审核开始时间 止
	$where .= " AND t.tb_date <= '$audit_start_end 24:00:00'";
}

if( $audit_end_start ){ //审核结束时间 起
	$where .= " AND t.te_date >= '$audit_end_start 00:00:00'";
}

if( $audit_end_end ){ //审核结束时间 止
	$where .= " AND t.te_date <= '$audit_end_end 24:00:00'";
}

$where .= " AND t.deleted = '0' ";
$where .= " AND t.status = '3'";
$where .= " AND p.tid >0 and p.deleted=0";

$join .= " LEFT JOIN sp_project p ON p.tid = t.id";
$join .= " LEFT JOIN sp_enterprises e ON e.eid = t.eid ";
$allow_type=array("1002");
// $where .=" AND p.audit_type not in('".join("','",$allow_type)."')";
if( !$export ){ 
	$state_total = array(
		'0'=>$db->get_var("select COUNT(*) from sp_project p LEFT JOIN sp_task t ON p.tid = t.id where 1  $where and p.is_bao='0'"),// 
		'1'=>$db->get_var("select COUNT(*) from sp_project p LEFT JOIN sp_task t ON p.tid = t.id where 1  $where and p.is_bao='1'")// 
	);
	$pages = numfpage( $state_total[$is_bao], 20, $url_param );
}
// echo $db->sql."<br/>";
$where .= " and p.is_bao='$is_bao'  ";

$task_projects = array(); 
$sql = "SELECT t.ctfrom,t.id,t.tb_date,t.te_date,p.bao_date,p.bao_uid,p.id pid,p.iso,p.audit_type,p.tid,e.eid,e.ep_name,e.ep_phone,e.person,e.ep_addr,e.work_code,e.areacode FROM sp_project p LEFT JOIN sp_task t ON p.tid = t.id LEFT JOIN sp_enterprises e ON e.eid = p.eid WHERE 1 $where ORDER BY t.tb_date,e.ep_name $pages[limit]";


$query = $db->query( $sql);
$date=thedate_add(date('Y-m-d H:i:s'), 7, 'day');
while( $rt = $db->fetch_array( $query ) ){
	if($is_bao==0 and $date>$rt['tb_date'])
		$rt['style']="color:red;";
	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
	$rt['tb_date'] = substr(mysql2date( 'Y-m-d A', $rt['tb_date'] ), 0, -1 );
	if(substr($rt['te_date'] ,11)=="12:00:00")
		$rt['te_date']=mysql2date( 'Y-m-d', $rt['te_date'] )." A";
	else
		$rt['te_date'] = substr(mysql2date( 'Y-m-d A', $rt['te_date'] ), 0, -1 );
	switch( $rt['audit_type'] ){
		case '1002' : $type = '04'; break;
		case '1003' : $type = '01'; break;
		case '1007' : $type = '02'; break;
		default : $type = '03'; break;
	}
	$rt['audit_type'] = $type;
	
	if( $rt['iso'] == 'F' ){
		$rt['iso'] = 'A04';
	} elseif( $rt['iso'] == 'H' ){
		$rt['iso'] = 'A05';
	}
	$rt[s]=$db->get_col("SELECT   name FROM `sp_task_audit_team` WHERE `tid` = '$rt[tid]' AND qua_type<>'04' AND role<>'04' AND deleted=0 and iso='$rt[iso]' order by role");
	$rt[z]=$db->get_col("SELECT   name FROM `sp_task_audit_team` WHERE `tid` = '$rt[tid]' AND qua_type='04' AND role<>'04' AND deleted=0 and iso='$rt[iso]'");
	$task_projects[$rt['pid']] = $rt;
}
//p($task_projects);

if( !$export ){
	tpl( 'audit/audit_plan_report' );
} else {
	ob_start();
	tpl( 'xls/list_audit_plan_report' );
	$data = ob_get_contents();
	ob_end_clean(); 
	export_xls( '审核计划', $data );
}  