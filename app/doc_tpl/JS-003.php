<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
// $br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';
$prod_arr=array("","生产地址","服务地址","运营地址");

$A01=$A02=$A03="□";
$tid = (int)getgp( 'tid' );

$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
$prod_check=str_replace('\"','"',$prod_check);
$prod_check=str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check=unserialize($prod_check);
foreach($prod_check as $k){
	$prod_check_arr[]=$prod_arr[$k];

}
$prod_check=join("/",$prod_check_arr);


// 项目编号
$ct=$db->get_row("SELECT ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );


$query = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$audit_type=$audit_ver=$scope_q=$scope_e=$scope_o=$scope_n="";
$cti_codes=$isos=array();
while( $rt = $db->fetch_array( $query ) ){
	$rt[use_code]=explode("；",$rt[use_code]);
	$rt[use_code]=join("；",array_unique($rt[use_code]));
	$scope.=$rt[scope];
	$audit_code.=f_iso($rt[iso]).":".$rt[audit_code];
	$use_code.=f_iso($rt[iso]).":".$rt[use_code];
	$isos[$rt[iso]]=f_iso($rt[iso]);
	$audit_type.=f_iso($rt[iso]).f_audit_type($rt[audit_type])."/";
	$audit_ver.= $audit_ver_array[$rt[audit_ver]][audit_basis]."、";
	$cti_codes[$rt[cti_id]]=$rt[cti_code];
	$$rt['iso']="■";
}

$audit_type=trim($audit_type,"/");
$audit_ver=trim($audit_ver,"、");
 
//审核组信息


$leader = $auditors = array();
$sql="SELECT name,role,uid FROM sp_task_audit_team  WHERE tid = '$tid' and deleted=0";
$query = $db->query( $sql);

while( $rt = $db->fetch_array( $query ) ){
	if( $rt['role']=="1001" ){
		$leader=$rt[name];
	} else {
		$auditors[$rt[uid]]=$rt['name'];
	} 
	
}

$audit_date=mysql2date( 'Y年n月j日',$tb_date)." 至 ".mysql2date( 'Y年n月j日',$te_date);

$filename = $ep_name .' 技术专家委员会评议报告.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/JS-003.xml' );
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
$output = str_replace( '{cta_addrcode}', $cta_addrcode, $output );
$output = str_replace( '{prod_addr}', $prod_addr, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{prod_check}', $prod_check, $output );
$output = str_replace( '{manager_daibiao}', $manager_daibiao, $output );
$output = str_replace( '{phone_daibiao}', $phone_daibiao, $output );
$output = str_replace( '{email_job}', $email_job, $output );
$output = str_replace( '{ct_code}', $ct_code, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{person_email}', $person_mail, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{iso}', join("/",$isos), $output );
$output = str_replace( '{app_date}',date("Y年m月d日"), $output );
$output = str_replace( '{audit_ver}', $audit_ver, $output );
$output = str_replace( '{audit_date}', $audit_date, $output );
$output = str_replace( '{audit_code}', $audit_code, $output );
$output = str_replace( '{use_code}', $use_code, $output );
$output = str_replace( '{scope}', $scope, $output );
$output = str_replace( '{table}', $tab, $output );
$output = str_replace( '{leader}', $leader, $output );
$output = str_replace( '{auditor}', join(",",$auditors), $output );
$output = str_replace( '{A01}', $A01, $output );
$output = str_replace( '{A02}', $A02, $output );
$output = str_replace( '{A03}', $A03, $output );
$output = str_replace( '{app_date}',date("Y年m月d日"), $output );

echo $output;
?>