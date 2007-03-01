<?php

$fields = $join = $where = '';
$parent_id	= (int)getgp('parent_id');
$ep_name	= getgp( 'ep_name' );
$code		= getgp( 'code' );
$ctfrom		= getgp( 'ctfrom' );
$areacode	= getgp( 'areacode' );
$work_code	= getgp( 'work_code' );
$person		= getgp( 'person' );

/*搜索条件*/
if( $ep_name ){
	$where .= " AND e.ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'";
}
if( $code ){
	$where .= " AND e.code LIKE '%".str_replace('%','\%',$code)."%'";
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
$where .= " AND e.ctfrom >= '$ctfrom' AND e.ctfrom < '$ctfrom_e'";

if( $areacode ){
	$pcode = substr($areacode,0,2) . '0000';
	$where .= " AND LEFT(e.areacode,2) = '".substr($areacode,0,2)."'";
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}

if( $work_code ){
	$where .= " AND e.work_code = '$work_code'";
}

if( $person ){	//关联 meta表搜索
	$where .= " AND e.person = '$person')";
}

if( $parent_id ){
	$where .= " AND (e.eid = '$parent_id' OR e.parent_id = '$parent_id')";
}
$where .= " AND e.deleted = '0'";


/*列表*/
$enterprise = load( 'enterprise' );
$enterprises = array();
$query = $db->query( "SELECT e.* FROM sp_enterprises e $join WHERE 1 $where ORDER BY e.eid DESC" );
while( $rt = $db->fetch_array( $query ) ){
	$metas = array();
	$rt['province']		= f_region_province( $rt['areacode'] );
	$rt['ctfrom']		= f_ctfrom( $rt['ctfrom'] );
	$metas = $enterprise->meta( $rt['eid'] );
	$rt = array_merge( $rt, $metas );
	$enterprises[$rt['eid']] = $rt;
}
ob_start();
tpl( 'xls/list_enterprise' );
$data = ob_get_contents();
ob_end_clean();

export_xls( '企业列表', $data );
?>