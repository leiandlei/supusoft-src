<?php
require_once('data/cache/ctfrom.cache.php');

/*
*业务代码申请管理
*/
$code = $qualification = '';
$fields = $join = $where = '';
$name=getgp('name');
$easycode=getgp('easycode');
$code=getgp('code');
$ctfrom=getgp('ctfrom');
$audit_job=getgp('audit_job');
$audit_code=getgp('audit_code');
$iso=getgp('iso');
$export=getgp('export');


$url_param = '?';
foreach($_GET as $k=>$v){
	if( 'paged' == $k ) continue;
	$url_param .= "$k=$v&";
}
$url_param = substr( $url_param, 0, -1 );

$status = getgp('status');
if(!$status)$status=1;

$status_1_tab = $status_2_tab = $status_3_tab = '';
${'status_'.$status.'_tab'} = ' ui-tabs-active ui-state-active';

$where = " and h.is_hire = '1' ";
$name= trim($name);
$easycode = trim($easycode);
$h_code = trim($h_code);
$qua_no = trim($qua_no);
if( $name ){
	$where .= " AND h.name like '%$name%' ";
}
if( $easycode ){
	$where .= " AND h.easycode like '%$easycode%' ";
}
if( $code ){
	$where .= " AND h.code like '%$code%' ";
}
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
$where .= " AND h.ctfrom >= '$ctfrom' AND h.ctfrom < '$ctfrom_e' ";
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
if( $department ){
	$where .= " AND h.department = '$department' ";
	$department_select = str_replace( "value=\"$department\">", "value=\"$department\" selected>" , $department_select );
}
if( $areacode ){	//省份搜索
	$pcode = substr($areacode,0,2) . '0000';
	$where .= " AND LEFT(h.areacode,2) = '".substr($areacode,0,2)."'";
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}
if( $audit_job || $audit_job=='0' ){
	$where .= " AND h.audit_job = '$audit_job' ";
	$audit_job_select = str_replace( "value=\"$audit_job\">", "value=\"$audit_job\" selected>" , $audit_job_select );
}
if($iso){
	$where .= " AND iso = '$iso' ";
	$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>" , $iso_select );
}
$audit_code = trim($audit_code);
if($audit_code){
	$where .= " AND aca.app_audit_code like '$audit_code%' ";
}
if($qualification){
	$where .= " AND hq.qua_type = '$qualification' ";
	$qualification_select = str_replace( "value=\"$qualification\">", "value=\"$qualification\" selected>" , $qualification_select );
}
if( $audit_job || $audit_job=='0' ){
	$where .= " AND h.audit_job = '$audit_job' ";
	$audit_job_select = str_replace( "value=\"$audit_job\">", "value=\"$audit_job\" selected>" , $audit_job_select );
}

//$join = ' inner join sp_hr_qualification hq on hq.id=aca.qid ';
$join .= ' inner join sp_hr h on h.id=aca.uid ';

//$total = $db->get_var("SELECT COUNT(*) FROM sp_hr_audit_code_app aca $join WHERE 1 $where");
$state_total = array( 1 => 0, 2 => 0, 3 => 0 );
$query = $db->query( "SELECT aca.status,COUNT(*) total FROM sp_hr_audit_code_app aca $join WHERE 1 $where GROUP BY aca.status" );
while( $rt = $db->fetch_array( $query ) ){
	$state_total[$rt['status']] = $rt['total'];
}
$pages = numfpage( $state_total[$status], 20, $url_param );

$where .= " and aca.status='$status'";
$sql = "SELECT aca.*,h.ctfrom,h.name,h.code,h.audit_job FROM sp_hr_audit_code_app aca $join WHERE 1 $where ORDER BY aca.id DESC $pages[limit]";
$query = $db->query($sql);
while( $rt = $db->fetch_array( $query ) ){
	$rt['name'] = $rt['name'];
	$rt['audit_job'] = f_audit_job($rt['audit_job']);
	$rt['ctfrom'] = $ctfrom_array[$rt['ctfrom']]['name'];
	$rt['iso_v'] = f_iso($rt['iso']);
	$rt['department'] = f_department($rt['department']);
	$rt['qua_type'] = $qualification_array[$rt['qua_type']]['name'];

	$rt['status'] = $status_arr[$rt['status']];
	$datas[] = $rt;
}
 if( !$export ){
	tpl('hr/hr_code_clist');
} else {
ob_start();
	tpl( 'xls/clist_hr_code' );
	$datas = ob_get_contents();
	ob_end_clean();
	export_xls( '小类申请表', $datas );
}




?>
