<?php
$zsid = (int)getgp('zsid');

/**证书信息**/
$sql_zs_Info    = "select * from sp_certificate where id=".$zsid;
$result_zs_Info = $db->getOne($sql_zs_Info);
// echo "<pre />";
// print_r($result_zs_Info);exit;
/**证书信息**/

/**企业信息**/
$sql_e_Info    = "select * from sp_enterprises where eid=".$result_zs_Info['eid'];
$result_e_Info = $db->getOne($sql_e_Info);
/**企业信息**/

/**标准信息**/
$sql_auditVers_Info    = "select * from sp_settings_audit_vers where audit_ver='".$result_zs_Info['audit_ver']."'";
$result_auditVers_Info = $db->getOne($sql_auditVers_Info);
/**标准信息**/

/**认可标志**/
$sql_mark_Info    = "select mark from sp_certificate where id=".$zsid;
$result_mark_Info = $db->getOne($sql_mark_Info);
/**认可标志**/

/**二维码**/
include_once ROOT.'/app/admin/control/output/getQRCode.php';
header('Content-type:text/html;charset=utf-8');
$image_file = "./uploads/QRcode/zs".$zsid."code.png";
$base64_content = chunk_split(base64_encode(file_get_contents($image_file)));
// echo $base64_content;exit;
/**二维码**/

/**需要信息**/
$results_info = array();
$results_info['epName']     = $result_e_Info['ep_name_e'];
$results_info['workCode']   = $result_e_Info['work_code'];//组织机构代码
$results_info['ctaAddrcode']= $result_e_Info['cta_addrcode'];//邮箱
$results_info['auditBasis'] = $result_auditVers_Info['audit_basis'];//标准
$results_info['sDate']      = explode('-',$result_zs_Info['s_date']);//发证日期
$results_info['eDate']      = explode('-',$result_zs_Info['e_date']);//结束日期
$results_info['certno']     = $result_zs_Info['certno'];//证书编号
$results_info['isoName']    = $arr_iso_name_e[$result_zs_Info['iso']];//证书名

/**需要信息**/

// echo '<pre />';
// print_r($result_e_Info);exit;
/**下载**/
if ($result_zs_Info['iso']=='A03') {
	switch ($result_mark_Info['mark']) {
		case '99':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002-E.xml' );
			break;
		case '01':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002-E-C.xml' );
			break;
		default:
			break;
	}
}else{
	switch ($result_mark_Info['mark']) {
		case '99':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002-E.xml' );
			break;
		case '01':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002-E-IC.xml' );
			break;
		default:
			break;
	}
}
/**下载**/

$filename = '多场所(英).doc';
$output = str_replace( '{epName}', $results_info['epName'], $tpldata );
$output = str_replace( '{auditBasis}',$results_info['auditBasis'] , $output );
$output = str_replace( '{sDate}',$results_info['sDate'][2].'-'.$results_info['sDate'][1].'-'.$results_info['sDate'][0] , $output );
$output = str_replace( '{eDate}',$results_info['eDate'][2].'-'.$results_info['eDate'][1].'-'.$results_info['eDate'][0] , $output );
$output = str_replace( '{certno}',$results_info['certno'] , $output );
$output = str_replace( '{isoName}',$results_info['isoName'] , $output );
$output = str_replace( '{base64_content}',$base64_content , $output );
if($result_zs_Info['iso']=='A01')$output = str_replace( '{isoNote}','This certificate will invalid at the expiry date of BG/T19001-2008(22nd September 2018)', $output );

//多场所
$sql="select * from sp_enterprises_site where eid=".$result_zs_Info['eid'];
$duochangsuo=$db->getAll($sql);
$i=1;
foreach ($duochangsuo as  $value) {
	$output = str_replace( '{es_name_'.$i.'}',$value['es_name_e'], $output );
	$output = str_replace( '{es_addr_'.$i.'}',$value['es_addr_e'], $output );
	$output = str_replace( '{es_scope_'.$i.'}',$value['es_scope_e'], $output );
	$i++;
}

$output = preg_replace("/\{.[^-]+?\}/", "", $output);
if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	$filePath = CONF.'downs';
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
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );;
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
/**下载**/
