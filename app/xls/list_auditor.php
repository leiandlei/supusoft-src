<?php

$task_status = array(
	1	=> '待派人',
	2	=> '待审批',
	3	=> '已审批'
);

$fields = $where = $join = '';

/* 搜索开始 */
$ep_name = getgp( 'ep_name' );
$name = getgp( 'name' );
$ctfrom = getgp( 'ctfrom' );
$ct_code = getgp( 'ct_code' );
$cti_code = getgp( 'cti_code' );


if( $ep_name ){
	$_eids = array();
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND ta.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND ta.id < -1";
	}
}


if( $name ){
	$uid = $db->get_var("SELECT id FROM sp_hr WHERE name = '$name'");
	if( $uid ){
		$where .= " AND ta.uid = '$uid'";
	} else {
		$where .= " AND ta.id < -1";
	}
}


if( $ct_code ){
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code'");
	if( $ct_id ){
		$where .= " AND ta.ct_id = '$ct_id'";
	} else {
		$where .= " AND ta.id < -1";
	}
}

if( $cti_code ){
	
		$_pids = array(-1);
		$query = $db->query("SELECT id FROM sp_project WHERE cti_code like '%$cti_code%'");
		while( $rt = $db->fetch_array( $query ) ){
			$_pids[] = $rt['id'];
		}
		if( $_pids ){
			$auditor_ids = array(-1);
			$query = $db->query("SELECT auditor_id FROM sp_task_audit_team WHERE pid IN (".implode(',',$_pids).")");
			while( $rt = $db->fetch_array( $query ) ){
				$auditor_ids[] = $rt['auditor_id'];
			}
			if( $auditor_ids ){
				$where .= " AND ta.id IN (".implode(',',$auditor_ids).")";
			} else {
				$where .= " AND ta.id < -1";
			}
		}
	} else {
		$where .= " AND ta.id < -1";
	}
}


if( $iso ){
	$where .= " AND ta.iso LIKE '%$iso%'";
}


if( $audit_type ){
	$where .= " AND ta.audit_type = '$audit_type'";
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
//$where .= " AND ta.ctfrom >= '$ctfrom' AND ta.ctfrom < '$ctfrom_e'";





$fields .= "ta.*,t.is_site,t.status,e.ep_name,e.ctfrom,t.ct_id";

$join = " LEFT JOIN sp_enterprises e ON e.eid = ta.eid";
$join .= " LEFT JOIN sp_task t ON t.id = ta.tid";

$where .= " AND ta.deleted = '0'";



$total = $db->get_var("SELECT COUNT(*) FROM sp_task_auditor ta WHERE 1 $where");
$pages = numfpage( $total, 20, $url_param );


$resdb = $aids = array();
$sql = "SELECT $fields FROM sp_task_auditor ta $join WHERE 1 $where ORDER BY ta.id DESC $pages[limit]" ;
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['taskBeginDate'] = mysql2date( 'Y-m-d H:i', $rt['taskBeginDate'] );
	$rt['taskEndDate'] = mysql2date( 'Y-m-d H:i', $rt['taskEndDate'] );
	$rt['is_site_V'] = ($rt['is_site'] == 'y' )?'是':'否';
	$rt['status_V'] = $task_status[$rt['status']];
	$resdb[$rt['id']] = $rt;
	$aids[] = $rt['id'];
}
if( $aids ){
	$join2 = " LEFT JOIN sp_project p ON p.id = tat.pid";
	$join2 .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = p.cti_id";

	$where2 = " AND tat.auditor_id IN (" . implode( ',', $aids ) . ")";

	$query = $db->query( "SELECT tat.*,p.audit_type,p.audit_code,cti.cti_code FROM sp_task_audit_team tat $join2 WHERE 1 $where2" );
	while( $rt = $db->fetch_array( $query ) ){
		!isset($resdb[$rt['auditor_id']]['cti_codes']) or $resdb[$rt['auditor_id']]['cti_codes'] = array();
		!isset($resdb[$rt['auditor_id']]['audit_types']) or $resdb[$rt['auditor_id']]['audit_types'] = array();
		!isset($resdb[$rt['auditor_id']]['isos']) or $resdb[$rt['auditor_id']]['isos'] = array();
		!isset($resdb[$rt['auditor_id']]['is_leaders']) or $resdb[$rt['auditor_id']]['is_leaders'] = array();
		!isset($resdb[$rt['auditor_id']]['audit_codes']) or $resdb[$rt['auditor_id']]['audit_codes'] = array();
		!isset($resdb[$rt['auditor_id']]['qua_types']) or $resdb[$rt['auditor_id']]['qua_types'] = array();

		$resdb[$rt['auditor_id']]['cti_codes'][] = $rt['cti_code'];
		$resdb[$rt['auditor_id']]['audit_types'][] = f_audit_type( $rt['audit_type'] );
		$resdb[$rt['auditor_id']]['isos'][] = f_iso( $rt['iso'] );
		$resdb[$rt['auditor_id']]['is_leaders'][] = ($rt['is_leader'])?'是':'否';
		$resdb[$rt['auditor_id']]['audit_codes'][] = $rt['audit_code'];
		$resdb[$rt['auditor_id']]['qua_types'][] = f_qua_type( $rt['qua_type'] );
	}
}



//输出Execl文件
$filename = iconv( 'UTF-8', 'GB2312', '项目派人_').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $filename );
header("Pragma: no-cache");
header("Expires: 0");
tpl( 'xls/list_auditor' );

?>