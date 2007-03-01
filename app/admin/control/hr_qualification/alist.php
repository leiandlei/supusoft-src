<?php
/*
*注册资格登记
*/

$code=$fields = $join = $where = '';
$where = " and username!='admin' and job_type !='' and is_hire='1'  ";
$url_param = '?';
extract( $_GET, EXTR_SKIP );
foreach($_GET as $k=>$v){
	if( 'paged' == $k ) continue;
	$url_param .= "$k=$v&";
}
$url_param = substr( $url_param, 0, -1 );
$name = trim($name);
if( $name ){
	$where .= " AND name like '%$name%' ";
}
$easycode = trim($easycode);
if( $easycode ){
	$where .= " AND easycode like '%$easycode%' ";
}
$h_code = trim($h_code);
if( $h_code ){
	$where .= " AND code like '%$h_code%' ";
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
$where .= " AND ctfrom >= '$ctfrom' AND ctfrom < '$ctfrom_e' ";

$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );

if( $department ){
	$where .= " AND department = '$department' ";
	$department_select = str_replace( "value=\"$department\">", "value=\"$department\" selected>" , $department_select );
}

if( $audit_job || $audit_job=='0' ){
	$where .= " AND audit_job = '$audit_job' ";

}
$where .= " AND deleted = 0";
if(!$export){
	$total = $db->get_var("SELECT COUNT(*) FROM sp_hr $join WHERE 1 $where");
	$pages = numfpage( $total, 20, $url_param );
}
$sql = "SELECT * FROM sp_hr $join WHERE 1 $where ORDER BY id DESC $pages[limit]" ;
$query = $db->query($sql);
while( $rt = $db->fetch_array( $query ) ){
	$rt['ctfrom']		= f_ctfrom( $rt['ctfrom'] );
	$rt['audit_job'] = read_cache("audit_job",$rt['audit_job']);
	$rt['areacode']		= f_region_province( $rt['areacode'] );	//取省地址
	//$rt['sex']		= $rt['sex'] ;
	if ($rt['sex']=='1'){$rt['sex']='男';}elseif($rt['sex']=='2'){$rt['sex']='女';}
	$rt['is_hire']		= $rt['is_hire'];
	$rt['department'] 	= f_department($rt['department']);
	$rt['mail']			= $user->meta($rt['id'],'mail' );
	$rt['note']			= $user->meta($rt['id'],'note' );
	$qualis[] = $rt;
}
$users=$qualis;
if(!$export ){
		tpl('hr/qualification_alist');
	} else {//导出注册资格登记数据
		ob_start();
		tpl( 'xls/list_hr' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '注册资格登记数据', $data );
	}