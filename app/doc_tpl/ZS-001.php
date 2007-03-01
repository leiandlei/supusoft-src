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
$sql_auditVers_Info    = "select * from sp_settings_audit_vers where audit_ver='".$result_zs_Info['audit_ver']."'";

$result_auditVers_Info = $db->getOne($sql_auditVers_Info);
// echo "<pre />";
// print_r($result_auditVers_Info);exit;
/**标准信息**/

/**二维码**/
include_once ROOT.'/app/admin/control/output/getQRCode.php';
header('Content-type:text/html;charset=utf-8');
$image_file = "./uploads/QRcode/zs".$zsid."code.png";
$base64_content = chunk_split(base64_encode(file_get_contents($image_file)));
/**二维码**/

/**认可标志**/
$sql_mark_Info    = "select mark,cti_id from sp_certificate where id=".$zsid;
$result_mark_Info = $db->getOne($sql_mark_Info);
/**认可标志**/

//失效标准判断
$audit_ver='';
if ($result_auditVers_Info['audit_ver']=='A010101') 
{
	$audit_ver='本证书将与GB/T 19001-2008标准同时失效(2018年9月14日)';
}elseif($result_auditVers_Info['audit_ver']=='A020101'){
	$audit_ver='本证书将与GB/T 24001-2004标准同时失效(2018年9月14日)';
}else{
	$audit_ver='';
}

//不含7.3产品和服务的设计和开发 判断
$sql   = "select * from sp_project where cti_id='".$result_mark_Info['cti_id']."' and audit_type='1003' and deleted=0 ";  
$query = $db->getOne($sql);
$str1  = $query['exc_clauses_new'];
$str2  = '8.3';
if(strpos($str1,$str2) === false){     //使用绝对等于
    $exc_clauses ='';
}else{
    $exc_clauses ='不含8.3产品和服务的设计和开发';
}
//echo "<pre />";
//print_r($result_e_Info);exit;
/**需要信息**/
$results_info = array();
$results_info['zsid']        = $result_zs_Info['id'];//证书编号
$results_info['epName']      = $result_e_Info['ep_name'];//企业名称
$results_info['ctaAddrcode'] = $result_e_Info['ep_addrcode'];//注册地址邮箱
$results_info['prod_addr']   = $result_e_Info['prod_addr'];//生产地址
$results_info['auditBasis']  = $result_auditVers_Info['audit_basis'];//标准
$results_info['certScope']   = $result_zs_Info['cert_scope'];//证书范围
$results_info['first_date']  = $result_zs_Info['first_date'];//初次发证日期
$results_info['sDate']       = $result_zs_Info['s_date'];//发证日期
$results_info['eDate']       = $result_zs_Info['e_date'];//结束日期
$results_info['certno']      = $result_zs_Info['certno'];//证书编号
$results_info['iso']         = $arr_iso_name[$result_zs_Info['iso']];//证书名
$results_info['epAddr']      = empty($result_zs_Info['ep_addr'])?'':'注册地址：'.$result_zs_Info['ep_addr'];//注册地址
$results_info['ctaAddr']     = empty($result_zs_Info['cta_addr'])?'':'通讯地址：'.$result_zs_Info['cta_addr'];//通讯地址
if( $result_zs_Info['bg_addr']==$result_zs_Info['ep_addr']&&$result_zs_Info['bg_addr']==$results_info['prod_addr']){
	$results_info['epAddr']  = '注册/办公/生产地址：'.$result_zs_Info['bg_addr'];
	unset($results_info['prod_addr']);
}else if($result_zs_Info['bg_addr']==$result_zs_Info['ep_addr']){    
	$results_info['epAddr']  = '注册/办公地址：'.$result_zs_Info['bg_addr'];
}else{
	$results_info['bgAddr']    = empty($result_zs_Info['bg_addr'])?'':'办公地址：'.$result_zs_Info['bg_addr'];//办公地址
	$results_info['prod_addr'] = empty($results_info['prod_addr'])?'':'生产地址：'.$results_info['prod_addr'];//生产
}


if(strlen($result_e_Info['work_code'])==9)
{
	// $results_info['workCode']=$result_e_Info['work_code'].'-9';
	$startstr = substr($result_e_Info['work_code'], 0, 8);
	$endstr   = substr($result_e_Info['work_code'], 8, 1);
	$results_info['workCode']=$startstr.'-'.$endstr;
}else{
	$results_info['workCode']=$result_e_Info['work_code'];
}

if(strlen($result_e_Info['work_code'])==9){
	$daima='组织机构代码';
}else{
	$daima='统一社会信用代码';
}

/**需要信息**/

/**下载**/

if ($result_zs_Info['iso']=='A03') {
	
	switch ($result_mark_Info['mark']) {
		case '99':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001.xml' );
			break;
		case '01':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001-C.xml' );
			break;
		default:
			break;
	}
}else{
	
	switch ($result_mark_Info['mark']) {
		case '99':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001.xml' );
			break;
		case '01':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001-IC.xml' );
			break;
		default:
			break;
	}
}
//判断是否存在换证日期
$hzdate   = $db->get_var("select cgs_date from sp_certificate_change where zsid =".$results_info['zsid']." and deleted=0 and status=1  order by id desc");
if(!empty($hzdate))
{
	$huanzhengdate  = $hzdate;
}else{
	$huanzhengdate  = $results_info['sDate'];
	
}
$filename = '证书.doc';
$output = str_replace( '{epName}', $results_info['epName'], $tpldata );
$output = str_replace( '{daima}',$daima , $output );
$output = str_replace( '{workCode}',$results_info['workCode'] , $output );
$output = str_replace( '{ctaAddrcode}',$results_info['ctaAddrcode'] , $output );
$output = str_replace( '{auditBasis}',$results_info['auditBasis'] , $output );
$output = str_replace( '{certScope}',$results_info['certScope'] , $output );
$output = str_replace( '{first_date}',$results_info['first_date'] , $output );
$output = str_replace( '{hzdate}',$huanzhengdate , $output );

$output = str_replace( '{sDate}',$results_info['sDate'] , $output );
$output = str_replace( '{eDate}',$results_info['eDate'] , $output );
$output = str_replace( '{certno}',$results_info['certno'] , $output );
$output = str_replace( '{iso}',$results_info['iso'] , $output );
$output = str_replace( '{epAddr}',$results_info['epAddr'] , $output );
$output = str_replace( '{ctaAddr}',$results_info['ctaAddr'] , $output );
$output = str_replace( '{bgAddr}',$results_info['bgAddr'] , $output );
$output = str_replace( '{prod_addr}',$results_info['prod_addr'] , $output );
$output = str_replace( '{base64_content}',$base64_content , $output );
$output = str_replace( '{audit_ver}', $audit_ver, $output );
$output = str_replace( '{exc_clauses}', $exc_clauses, $output );

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
