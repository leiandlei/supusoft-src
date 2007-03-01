<?php

$fields = $join = $where = $item_join = $item_where = '';
$status = (int)getgp( 'status' );
$where .= " AND ct.deleted = '0'";


$fields .= ",e.ep_name,e.ctfrom";

$join = " INNER JOIN sp_enterprises e ON e.eid = ct.eid";

//搜索开始
$a = getgp( 'a' );
$ep_name = getgp( 'ep_name' );
$code = getgp('code');
$ct_code = getgp( 'ct_code' );
$cti_code = getgp( 'cti_code' );
$ctfrom	= getgp( 'ctfrom' );
$areacode = getgp( 'areacode' );
$status	= getgp( 'status' );

if( $ep_name ){		//企业名称
	$where .= " AND e.ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'";
}
if( $code ){		//企业编号
	$where .= " AND e.code LIKE '%".str_replace('%','\%',$code)."%'";
}

if($ct_code){
	$where .= " AND ct.ct_code LIKE '%$ct_code%' ";
}

if($cti_code){
	$where .= " AND ct.ct_id IN (SELECT ct_id FROM sp_contract_item WHERE cti_code LIKE '%$cti_code%')";
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
$where .= " AND LEFT(e.ctfrom,$len) = '" . substr($ctfrom, 0, $len) . "'";


if( $areacode ){	//省份搜索
	$pcode = substr($areacode,0,2) . '0000';
	$where .= " AND LEFT(e.areacode,2) = '".substr($areacode,0,2)."'";
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}


$contract_costs = array();

$sql = "SELECT ct.* $fields FROM sp_contract ct $join WHERE 1 $where ORDER BY ct.ct_id DESC";
$query = $db->query($sql);

while( $rt = $db->fetch_array( $query ) ){
	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['cti_codes'] = implode('<br>',$ct_codes);
	$rt['status'] = $status_arry[$rt['status']];
	$contract_costs[$rt['ct_id']] = $rt;
}

if( $contract_costs ){
	$query = $db->query("SELECT ct_id,cti_code FROM sp_contract_item WHERE ct_id IN (".implode(',',array_keys($contract_costs)).")");
	while( $rt = $db->fetch_array( $query ) ){
		isset( $contract_costs[$rt['ct_id']]['cti_codes'] ) or $contract_costs[$rt['ct_id']]['cti_codes'] = array();
		$contract_costs[$rt['ct_id']]['cti_codes'][] = $rt['cti_code'];
	}
}

//输出Execl文件
/*$filename = iconv( 'UTF-8', 'GB2312', '合同费用登记列表_').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $filename );
header("Pragma: no-cache");
header("Expires: 0");*/


tpl( 'xls/list_contract_cost' );

?>