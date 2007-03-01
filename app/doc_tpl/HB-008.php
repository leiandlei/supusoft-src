<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );


//$br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';



$ctid = (int)getgp( 'ct_id' );

$eid = (int)getgp( 'eid' );
$add_basis_check=$exc_basis_check="";

$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );


// 合同编号
$ct=$db->get_row("SELECT ct_code FROM `sp_contract` WHERE `eid` = '$eid' AND `deleted` = '0'");
// print_r($ct);exit;
extract( $ct, EXTR_SKIP );

//体系
$tx=$db->getAll("SELECT iso FROM `sp_contract_item` WHERE `eid` = '$eid' AND `deleted` = '0'");
foreach ($tx as $key => $value) {
	switch ($value['iso']) {
		case 'A01':
			$iso.='QMS'.'/';
			break;
		case 'A02':
			$iso.='EMS'.'/';
			break;
		case 'A03':
			$iso.='OHSMS';
			break;
		default:
			break;
	}
}	

extract( $tx, EXTR_SKIP );

$filename = $ep_name .' 合表008 认证审核合同及评审档案清单.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/HB-008.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

// print_r($ep_name);exit;
$output = str_replace( '{ep_name}', $ep_name, $tpldata );
// print_r($ct_code);exit;
$output = str_replace( '{ct_code}', $ct_code, $output );
$output = str_replace( '{iso}', $iso, $output );
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
