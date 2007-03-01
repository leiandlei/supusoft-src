<?php

require( DATA_DIR . 'cache/audit_ver.cache.php' );

// $br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';

$prod_arr=array("","生产地址","服务地址","运营地址");

$ct_id   = (int)getgp( 'ct_id' );

$tid     = (int)getgp( 'tid' );

$ct_info =$db->get_row("SELECT eid FROM `sp_contract` WHERE `ct_id` = '$ct_id'");

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



// 项目编号

$ct=$db->get_row("SELECT ct_code,scope FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");

extract( $ct, EXTR_SKIP );







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

	$audit_type.=$rt[audit_type];

	//$audit_type.=$_iso.f_audit_type($rt[audit_type])."/";

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



//$audit_type=trim($audit_type,"/");

$audit_ver=trim($audit_ver,"、");



 $leader=$db->get_row("select name from `sp_task_audit_team` where `tid`='$tid' and `role`='01'");

extract( $leader, EXTR_SKIP );



$filename = '审表020 获证客户需求及相关意见反馈卡('.$ep_name.').doc';

//读入模板文件 

$tpldata = readover( DOCTPL_PATH . 'doc/SH-020.xml' );





//企业信息部分

$arr_search = array('<','>','&','\'','"');

$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');

$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ct_code}', $ct_code, $tpldata );

$output = str_replace( '{ep_name}', $ep_name, $output );

$output = str_replace( '{ep_addr}', $ep_addr, $output );

$output = str_replace( '{prod_addr}', $prod_addr, $output );

$output = str_replace( '{scope}', $scope, $output );

$output = str_replace( '{leader}', $name, $output );

//print_r($leader);exit;



if( getgp('downs')==1 ){

	$filename = iconv( 'UTF-8', 'gbk', $filename );

	if(!empty(getgp('dates'))){

		$filePath = CONF.'downs'.'/'.getgp('dates');

	}else{

		$filePath = CONF.'downs';

	}

	//没有目录创建目录

	if(!is_dir($filePath)) {

	    mkdir($filePath, 0777, true);

	}

	//如果存在就删除文件

	if( file_exists($filePath.'/'.$filename) ){

		@unlink ($filePath.'/'.$filename); 

	}



	file_put_contents($filePath.'/'.$filename,$output);

	

	if( file_exists($filePath.'/'.$filename) ){

		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );

	}

}else{

	header("Content-type: application/octet-stream");

	header("Accept-Ranges: bytes");

	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );

	echo $output;exit;

}

?>