<?php
$zsid = (int)getgp('zsid');

/**证书信息**/
$sql_zs_Info    = "select * from sp_certificate where id=".$zsid;
// print_r($sql_zs_Info);exit;
$result_zs_Info = $db->getOne($sql_zs_Info);
/**证书信息**/

/**企业信息**/
$sql_e_Info    = "select * from sp_enterprises where eid=".$result_zs_Info['eid'];
$result_e_Info = $db->getOne($sql_e_Info);
// echo "<pre />";
// print_r($result_e_Info  );exit;
/**企业信息**/



/**标准信息**/
$sql_auditVers_Info    = "select * from sp_settings_audit_vers where iso='".$result_zs_Info['iso']."'";
$result_auditVers_Info = $db->getOne($sql_auditVers_Info);
/**标准信息**/

/**需要信息**/
$results_info = array();

$results_info['epName']     = $result_e_Info['ep_name'];//企业名称
$results_info['workCode']   = $result_e_Info['work_code'];//组织机构代码
$results_info['ctaAddrcode']= $result_e_Info['cta_addrcode'];//邮箱
$results_info['prod_addr']   = $result_e_Info['prod_addr'];//生产地址
$results_info['auditBasis'] = $result_auditVers_Info['audit_basis'];//标准
$results_info['certScope']  = $result_zs_Info['cert_scope'];//组织机构代码
$results_info['sDate']      = $result_zs_Info['s_date'];//发证日期
$results_info['eDate']      = $result_zs_Info['e_date'];//结束日期
$results_info['certno']     = $result_zs_Info['certno'];//证书编号
$results_info['iso']    = $arr_iso_name[$result_zs_Info['iso']];//证书名

$results_info['epAddr']  = empty($result_zs_Info['ep_addr'])?'':'注册地址：'.$result_zs_Info['ep_addr'];//注册地址
$results_info['ctaAddr'] = empty($result_zs_Info['cta_addr'])?'':'通通讯地址：'.$result_zs_Info['cta_addr'];//通讯地址
if( $result_zs_Info['bg_addr']==$result_zs_Info['ep_addr'] ){
	$results_info['epAddr'] = '注册/办公地址：'.$result_zs_Info['bg_addr'];
}else{
	$results_info['bgAddr']  = empty($result_zs_Info['bg_addr'])?'':'办公地址：'.$result_zs_Info['bg_addr'];//办公地址
}
if($result_zs_Info['prod_check']==1){
	$results_info['prod_Addr'] = empty($result_zs_Info['prod_Addr'])?'':'生产地址：'.$result_zs_Info['prodAddr'];//生产
}
/**需要信息**/


$daima='';

$code=strlen($result_e_Info['work_code']);
// print_r($code);exit;
if($code='18'){
	$daima='统一社会信用代码';
	$work_code=$result_e_Info['work_code'];
	// print_r($work_code);exit;
}
else{
	$daima='组织机构代码';
	$work_code=substr($result_e_Info['work_code'],0,8).'-'.substr($result_e_Info['work_code'],8,1);
	// print_r($work_code);exit;
}

/**下载**/
$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001.xml' );
$filename = '证书.doc';
$output = str_replace( '{epName}', $results_info['epName'], $tpldata );
// print_r($work_code);exit;
$output = str_replace( '{work_code}',$work_code , $output );
$output = str_replace( '{ctaAddrcode}',$results_info['ctaAddrcode'] , $output );
$output = str_replace( '{auditBasis}',$results_info['auditBasis'] , $output );
$output = str_replace( '{certScope}',$results_info['certScope'] , $output );
$output = str_replace( '{sDate}',$results_info['sDate'] , $output );
$output = str_replace( '{eDate}',$results_info['eDate'] , $output );
$output = str_replace( '{certno}',$results_info['certno'] , $output );
$output = str_replace( '{iso}',$results_info['iso'] , $output );
$output = str_replace( '{epAddr}',$results_info['epAddr'] , $output );
$output = str_replace( '{ctaAddr}',$results_info['ctaAddr'] , $output );
$output = str_replace( '{bgAddr}',$results_info['bgAddr'] , $output );
$output = str_replace( '{daima}', $daima, $output );
// print_r($results_info['prod_addr']);exit;
$output = str_replace( '{prod_addr}',$results_info['prod_addr'] , $output );

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
