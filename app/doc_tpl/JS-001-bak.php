<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
//$br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';

$prod_arr=array("","生产地址","服务地址","运营地址");
$ct_id = (int)getgp( 'ct_id' );
// var_dump($ct_id);
// E();
$ct_info=$db->get_row("SELECT eid FROM `sp_contract` WHERE `ct_id` = '$ct_id'");
extract( $ct_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
$prod_check=unserialize($prod_check);
foreach($prod_check as $k){
	$prod_check_arr[]=$prod_arr[$k];

}
$prod_check=join("/",$prod_check_arr);
$query = $db->query( "SELECT * FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND `deleted` = '0'");

$audit_type=$audit_ver=$scope_q=$scope_e=$scope_o=$scope_n="";
$cti_codes=$isos=array();
$risk_level=$exc_basis=$add_basis="";
while( $rt = $db->fetch_array( $query ) ){
	$rt[use_code]=explode("；",$rt[use_code]);
	$rt[use_code]=array_unique($rt[use_code]);
	$rt[use_code]=join("；",$rt[use_code]);
	$_iso=f_iso($rt[iso]);
	$scope.=$_iso.":".$rt[scope];
	$audit_code.=$_iso.":".$rt[audit_code];
	$use_code.=$_iso.":".$rt[use_code];
	$isos[$rt[iso]]=$_iso;
	$audit_type.=$_iso.f_audit_type($rt[audit_type])."/";
	$audit_ver.= $audit_ver_array[$rt[audit_ver]][audit_basis]."、";
	$cti_codes[$rt[cti_id]]=$rt[cti_code];
	$risk_level.=$_iso.":".read_cache("risk_level",$rt['risk_level']);
	$exc_basis.=$_iso.$br;
	$exc_basis_arr=unserialize($rt['exc_basis']);
	foreach($exc_basis_arr as $k){
		$exc_basis.=read_cache("exc_basis",$k).$br;
	}
	$exc_num.=$_iso.":".$rt['exc_num'];
	$add_basis.=$_iso.$br;
	$add_basis_arr=unserialize($rt['add_basis']);
	foreach($add_basis_arr as $k){
		$add_basis.=read_cache("add_basis",$k).$br;
	}
	$add_num.=$_iso.":".$rt['add_num'];
}

$audit_type=trim($audit_type,"/");
$audit_ver=trim($audit_ver,"、");
 
$filename = $ep_name .' BG-16 申请评审记录表.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/JS-001.xml' );
header("Content-type: application/octet-stream");
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{ep_addr}', $ep_addr, $output );
$output = str_replace( '{cta_addr}', $cta_addr, $output );
$output = str_replace( '{manager_daibiao}', $manager_daibiao, $output );
$output = str_replace( '{phone_daibiao}', $phone_daibiao, $output );
$output = str_replace( '{prod_addr}', $prod_addr, $output );
$output = str_replace( '{prod_check}', $prod_check, $output );
$output = str_replace( '{email_job}', $email_job, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{person_mail}', $person_mail, $output );
$output = str_replace( '{person_job}', $person_job, $output );
$output = str_replace( '{work_code}', $work_code, $output );
$output = str_replace( '{ep_amount}', $ep_amount, $output );
$output = str_replace( '{delegate}', $delegate, $output );
$output = str_replace( '{industry}', $industry, $output );
$output = str_replace( '{nature}', read_cache("nature",$nature), $output );
$output = str_replace( '{areacode}', $areacode, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{cat_addrcode}', $cat_addrcode, $output );
$output = str_replace( '{website}', $website, $output );
$output = str_replace( '{capital}', $capital, $output );
$output = str_replace( '{iso}', join("/",$isos), $output );
$output = str_replace( '{audit_ver}', $audit_ver, $output );
$output = str_replace( '{audit_type}', $audit_type, $output );
/* 
$output = str_replace( '{audit_code}', $audit_code, $output );
$output = str_replace( '{use_code}', $use_code, $output );
$output = str_replace( '{risk_level}', $risk_level, $output );
$output = str_replace( '{exc_basis}', $exc_basis, $output );
$output = str_replace( '{exc_num}', $exc_num, $output );
$output = str_replace( '{add_basis}', $add_basis, $output );
$output = str_replace( '{add_num}', $add_num, $output );
$output = str_replace( '{shsj}', $audit_date, $output );
$output = str_replace( '{audit_code}', $audit_code, $output );
$output = str_replace( '{use_code}', $use_code, $output );
$output = str_replace( '{scope}', $scope, $output );
$output = str_replace( '{table}', $tab, $output ); */

echo $output;
?>