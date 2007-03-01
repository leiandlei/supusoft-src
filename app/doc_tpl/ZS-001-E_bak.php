<?php
$zsid = (int)getgp('zsid');

/**证书信息**/
$sql_zs_Info    = "select * from sp_certificate where id=".$zsid;
$result_zs_Info = $db->getOne($sql_zs_Info);
/**证书信息**/

/**企业信息**/
$sql_e_Info    = "select * from sp_enterprises where eid=".$result_zs_Info['eid'];
$result_e_Info = $db->getOne($sql_e_Info);
/**企业信息**/

/**标准信息**/
$sql_auditVers_Info    = "select * from sp_settings_audit_vers where iso='".$result_zs_Info['iso']."'";
$result_auditVers_Info = $db->getOne($sql_auditVers_Info);
/**标准信息**/

/**需要信息**/
$results_info = array();

$results_info['epName']     = $result_e_Info['ep_name_e'];//企业名称
$results_info['workCode']   = $result_e_Info['work_code'];//组织机构代码
$results_info['ctaAddrcode']= $result_e_Info['cta_addrcode'];//邮箱
$results_info['auditBasis'] = $result_auditVers_Info['audit_basis'];//标准
$results_info['certScope']  = $result_zs_Info['cert_scope_e'];//组织机构代码
$results_info['sDate']      = explode('-',$result_zs_Info['s_date']);//发证日期
$results_info['eDate']      = explode('-',$result_zs_Info['e_date']);//结束日期
$results_info['certno']     = $result_zs_Info['certno'];//证书编号
$results_info['iso']    = $arr_iso_name_e[$result_zs_Info['iso']];//证书名

$results_info['epAddr']  = empty($result_zs_Info['ep_addr'])?'':'Registered Address:'.$result_zs_Info['ep_addr_e'];//注册地址
if( $result_zs_Info['bg_addr']==$result_zs_Info['ep_addr'] ){
	$results_info['epAddr'] = 'Registered/Office Address：'.$result_zs_Info['bg_addr_e'];
}else{
	$results_info['bgAddr']  = empty($result_zs_Info['bg_addr'])?'':'Office Address：'.$result_zs_Info['bg_addr_e'];//办公地址
}
if($result_zs_Info['prod_check']==1){
	$results_info['prodAddr'] = empty($result_zs_Info['prodAddr'])?'':'Production Address：'.$result_zs_Info['prodAddr_e'];//生产
}
/**需要信息**/

// echo '<pre />';
// print_r($results_info);exit;

/**下载**/
$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001-E.xml' );
$filename = '证书(英).doc';
$output = str_replace( '{epName}', $results_info['epName'], $tpldata );
$output = str_replace( '{workCode}',$results_info['workCode'] , $output );
$output = str_replace( '{ctaAddrcode}',$results_info['ctaAddrcode'] , $output );
$output = str_replace( '{auditBasis}',$results_info['auditBasis'] , $output );
$output = str_replace( '{certScope}',$results_info['certScope'] , $output );
$output = str_replace( '{sDate}',$results_info['sDate'][2].'-'.$results_info['sDate'][1].'-'.$results_info['sDate'][0] , $output );
$output = str_replace( '{eDate}',$results_info['eDate'][2].'-'.$results_info['eDate'][1].'-'.$results_info['eDate'][0] , $output );
$output = str_replace( '{certno}',$results_info['certno'] , $output );
$output = str_replace( '{iso}',$results_info['iso'] , $output );
$output = str_replace( '{epAddr}',$results_info['epAddr'] , $output );
$output = str_replace( '{ctaAddr}',$results_info['ctaAddr'] , $output );
$output = str_replace( '{bgAddr}',$results_info['bgAddr'] , $output );
$output = str_replace( '{prodAddr}',$results_info['prodAddr'] , $output );


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
