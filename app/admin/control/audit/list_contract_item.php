<?php
//增加特殊审核项
$cti_status = array( '未登记完', '已登记', '已评审', '已审批' );



extract( $_GET, EXTR_SKIP );

$where = $join = '';


//企业名称
if( $ep_name=trim($ep_name) ){
	$_eids = array();
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND cti.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND cti.cti_id < -1";
	}
	unset( $_eids, $_query, $rt );
}

//合同编号
if( $ct_code=trim($ct_code) ){
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code'");
	if( $ct_id ){
		$where .= " AND cti.ct_id = '$ct_id'";
	} else {
		$where .= " AND cti.cti_id < -1 ";
	}
	unset( $ct_id );
}

//合同项目编号
if( $cti_code =trim($cti_code)){
	$cti_ids=array(-1);
	$query = $db->query("SELECT cti_id FROM sp_contract_item WHERE cti_code like '%$cti_code%' and deleted=0");
	while($rt=$db->fetch_array($query)){
		$cti_ids[]=$rt[cti_id];
		}
	$where .= " AND cti.cti_id in (".implode(",",$cti_ids).")";
	
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
$where .= " AND cti.ctfrom >= '$ctfrom' AND cti.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );


//专业代码
if( $audit_code ){
	$where .= " AND cti.audit_code LIKE '%$audit_code%'";
}

//认证体系
if( $iso ){
	$where .= " AND cti.iso = '$iso'";
}

//审核类型
if( $audit_type ){
	$where .= " AND cti.audit_type = '$audit_type'";
	$audit_type_select = str_replace( "value=\"$audit_type\">", "value=\"$audit_type\" selected>", $audit_type_select);
}

//受理人
if( $accept_user ){
	$uid = $db->get_var("SELECT id FROM sp_hr WHERE name = '$accept_user'");
	if( $uid ){
		$where .= " AND cti.caeate_id = '$uid'";
	} else {
		$where .= " AND cti.cti_id < -1";
	}
}

//受理日期
if( $accept_date_start ){
	$where .= " AND cti.accept_date >= '$accept_date_start'";
}
if( $accept_date_end ){
	$where .= " AND cti.accept_date <= '$accept_date_end'";
}



$fields = $join = '';

$fields .= "";

/* 关联表 */
$join .= " LEFT JOIN sp_enterprises e ON e.eid = cti.eid";
$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = cti.ct_id";
$join .= " LEFT JOIN sp_hr ON sp_hr.id=cti.create_uid";

$where .= " AND cti.deleted = '0'";
if(!$export){
	$total = $db->get_var( "SELECT COUNT(*) FROM sp_contract_item cti WHERE 1 $where" );
	$pages = numfpage( $total);
}
$where .=" ORDER BY cti.cti_id desc";
$contract_items = array();
$sql = "SELECT cti.*,sp_hr.name,ct.ct_code,ct.accept_date,e.ep_name FROM sp_contract_item cti $join WHERE 1 $where $pages[limit]";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] );
	$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
	$rt['audit_code']   =LongToBr($rt['audit_code'],array("；",';'));
	$rt['use_code']   =LongToBr($rt['use_code'],array("；",';'));
	$rt['iso_V'] = f_iso( $rt['iso'] );
	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['name']=$rt['name'];
	$contract_items[$rt['cti_id']] = $rt;
}


if( !$export ){
		tpl( 'audit/list_contract_item' );
	} else {//导出合同项目列表
		ob_start();
		tpl( 'xls/list_contract_item' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '合同项目列表', $data );
	}
?>