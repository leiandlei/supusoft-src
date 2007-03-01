<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';

$prod_arr=array("","生产地址","服务地址","经营地址");
$ct_id = (int)getgp( 'ct_id' );

$ct_info=$db->get_row("SELECT eid,review_date,major_person,pre_date FROM `sp_contract` WHERE `ct_id` = '$ct_id'");
extract( $ct_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );

$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check=unserialize($prod_check);
foreach($prod_check as $k){
	$prod_check_arr[]=$prod_arr[$k];

}






$prod_check=join("/",$prod_check_arr);
$query = $db->query( "SELECT * FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND `deleted` = '0'");
$audit_type=$audit_ver=$exc_clauses="";
$cti_codes=$isos=array();
$risk_level=$exc_basis=$add_basis="";
while( $rt = $db->fetch_array( $query ) ){
	$rt[use_code]=explode("；",$rt[use_code]);
	$rt[use_code]=array_unique($rt[use_code]);
	$rt[use_code]=join("；",$rt[use_code]);
	$_iso=f_iso($rt[iso]);
	$scope.=$rt[scope];
	$total.=$rt[total];
	$audit_code.=$_iso.":".$rt[audit_code];
	$use_code.=$rt[use_code];
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
	if($rt[iso]=='A01' and $rt['exc_clauses'])
	$exc_clauses=$rt['exc_clauses'];
	$$rt[iso]=$rt[total_num];
	$total_num=$rt[total_num];
	$yjd_num=$rt[yjdxc_num];
	$ejd_num=$rt[ejdxc_num];
	$num=$rt[ch_num];
	$jd_num=$rt[jdxc_num];

}
$cti_code = $cti_codes[$ct_id];

$audit_type=trim($audit_type,"/");
$audit_ver=trim($audit_ver,"、");



$filename = $ep_name .' 认证合同.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SL-001.xml' );
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
$output = str_replace( '{cti_code}', $cti_code, $output );
$output = str_replace( '{cta_addrcode}', $cta_addrcode, $output );
$output = str_replace( '{delegate}', $delegate, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{manager_daibiao}', $manager_daibiao, $output );
$output = str_replace( '{prod_addr}', $prod_addr, $output );
$output = str_replace( '{prod_check}', $prod_check, $output );
$output = str_replace( '{email_job}', $email_job, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{person_email}', $person_mail, $output );
$output = str_replace( '{work_code}', $work_code, $output );
$output = str_replace( '{ep_amount}', $ep_amount, $output );
$output = str_replace( '{iso}', join("/",$isos), $output );
$output = str_replace( '{audit_ver}', $audit_ver, $output );
$output = str_replace( '{audit_type}', $audit_type, $output );
$output = str_replace( '{audit_code}', $audit_code, $output );
$output = str_replace( '{use_code}', $use_code, $output );
$output = str_replace( '{risk_level}', $risk_level, $output );
$output = str_replace( '{exc_basis}', $exc_basis, $output );
$output = str_replace( '{exc_num}', $exc_num, $output );
$output = str_replace( '{add_basis}', $add_basis, $output );
$output = str_replace( '{add_num}', $add_num, $output );
$output = str_replace( '{exc}', $exc_clauses, $output );
$output = str_replace( '{shsj}', $audit_date, $output );
$output = str_replace( '{audit_code}', $audit_code, $output );
$output = str_replace( '{use_code}', $use_code, $output );
$output = str_replace( '{scope}', $scope, $output );
$output = str_replace( '{total}', $total, $output );
$output = str_replace( '{table}', $tab, $output );
$output = str_replace( '{q_num}', $A01, $output );
$output = str_replace( '{e_num}', $A02, $output );
$output = str_replace( '{s_num}', $A03, $output );
$output = str_replace( '{num}', $num, $output );
$output = str_replace( '{yjd_num}', $yjd_num, $output );
$output = str_replace( '{ejd_num}', $ejd_num, $output );
$output = str_replace( '{total_num}', $total_num, $output );
$output = str_replace( '{jd_num}', $jd_num, $output );
$output = str_replace( '{major}', $major_person, $output );
$output = str_replace( '{pre_date}', $pre_date, $output );
$output = str_replace( '{review}', mysql2date("Y年m月d日",$review_date), $output );

echo $output;
?>