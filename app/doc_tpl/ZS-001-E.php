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
/**标准信息**/

/**二维码**/
include_once ROOT.'/app/admin/control/output/getQRCode.php';
header('Content-type:text/html;charset=utf-8');
$image_file     = "./uploads/QRcode/zs".$zsid."code.png";
$base64_content = chunk_split(base64_encode(file_get_contents($image_file)));
/**二维码**/
//失效标准判断
$audit_ver='';
if ($result_auditVers_Info['audit_ver']=='A010101') 
{
	$audit_ver='The certificate will invalid at the same time as GB/T 19001-2008(14-09-2018)';
}elseif($result_auditVers_Info['audit_ver']=='A020101'){
	$audit_ver='The certificate will invalid at the same time as GB/T 24001-2004(14-09-2018)';
}else{
	$audit_ver='';
}

/**认可标志**/
$sql_mark_Info    = "select mark,cti_id from sp_certificate where id=".$zsid;
$result_mark_Info = $db->getOne($sql_mark_Info);
/**认可标志**/
//不含7.3产品和服务的设计和开发 判断
$sql   = "select * from sp_project where cti_id='".$result_mark_Info['cti_id']."' and audit_type='1003' and deleted=0 ";  
$query = $db->getOne($sql);
$str1  = $query['exc_clauses_new'];
$str2  = '8.3';
if(strpos($str1,$str2) === false){     //使用绝对等于
    $exc_clauses ='';
}else{
    $exc_clauses ='Exclude 8.3 design and development.';
}

/**需要信息**/
$results_info = array();

$results_info['zsid']       = $result_zs_Info['id'];//证书编号
$results_info['epName']     = $result_e_Info['ep_name_e'];//企业名称
// $results_info['workCode']   = $result_e_Info['work_code'];//组织机构代码
$results_info['ctaAddrcode']= $result_e_Info['cta_addrcode'];//邮箱
$results_info['epAddrcode'] = $result_e_Info['ep_addrcode'];//邮箱
$results_info['prod_addr_e']= $result_e_Info['prod_addr_e'];//生产地址
$results_info['auditBasis'] = $result_auditVers_Info['audit_basis'];//标准

// $results_info['certScope']  = $result_e_Info['work_code'];//组织机构代码


	// if($result_zs_Info['audit_ver']=='A020102'||$result_zs_Info['audit_ver']=='A010103')
	// {
	// 	$results_info['daoqi']  = '';//到期时间
	// }else
	// {
	// 	$results_info['daoqi']  = 'The certificate will invalid at the same time as GB/T 19001-2008(14-9-2018)';
	// }

// echo "<pre />";
// print_r($results_info['daoqi']);exit;
// 

$results_info['first_date'] = explode('-',$result_zs_Info['first_date']);//初次发证日期
$results_info['sDate']      = explode('-',$result_zs_Info['s_date']);//发证日期
$results_info['eDate']      = explode('-',$result_zs_Info['e_date']);//结束日期
$results_info['certno']     = $result_zs_Info['certno'];//证书编号
$results_info['iso']        = $arr_iso_name_e[$result_zs_Info['iso']];//证书名
$results_info['certScope']  = $result_zs_Info['cert_scope_e'];
$results_info['epAddr']     = empty($result_zs_Info['ep_addr'])?'':'Registered Address：'.$result_zs_Info['ep_addr_e'];//注册地址

if( $result_zs_Info['bg_addr']==$result_zs_Info['ep_addr'] &&$result_zs_Info['bg_addr']==$result_zs_Info['prod_addr'] ){
	$results_info['epAddr']  = 'Registered/Office/Production Address：'.$result_zs_Info['bg_addr_e'];
	unset($results_info['prod_addr_e']);

}else if($result_zs_Info['bg_addr']==$result_zs_Info['ep_addr']){

	$results_info['epAddr']  = 'Registered/Office Address：'.$result_zs_Info['bg_addr_e'];
}else{

	$results_info['bgAddr']  = empty($result_zs_Info['bg_addr'])?'':'Office Address：'.$result_zs_Info['bg_addr_e'];//办公地址
	$results_info['prodAddr']= empty($results_info['prod_addr_e'])?'':'Production Address：'.$results_info['prod_addr_e'];//生产s

}
	

if(strlen($result_e_Info['work_code'])==9){
	$startstr = substr($result_e_Info['work_code'], 0, 8);
	$endstr   = substr($result_e_Info['work_code'], 8, 1);
	$results_info['workCode']=$startstr.'-'.$endstr;
}else{
	$results_info['workCode']=$result_e_Info['work_code'];
}

if(strlen($result_e_Info['work_code'])==9){
	$daima='Enterprise Organization Code';
}else{
	$daima='Unified Social Credit Code';
}



/**需要信息**/

/**下载**/
if ($result_zs_Info['iso']=='A03') {
	$isoA0301 = 'Occupational';
    $isoA0302 = 'Health and Safety';
	switch ($result_mark_Info['mark']) {

		case '99':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001-E.xml' );
			break;
		case '01':
			
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001-E-C.xml' );
			unset($results_info['daoqi']);
			break;
		default:
			break;
	}
}else{
	switch ($result_mark_Info['mark']) 
	{
		case '99':
			if($result_zs_Info['iso']=='A01')
			{
				$isoA0302 = 'Quality';
			}else if($result_zs_Info['iso']=='A02'){
                $isoA0302 = 'Environmental';
			}
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001-E.xml' );
			break;
		case '01':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-001-E-IC.xml' );
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
	$huanzhengdate      = explode('-',$huanzhengdate);//换证时间
}else{
	$huanzhengdate  = $results_info['sDate'];
	
}

$filename    = '证书(英).doc';
$ep_name_e   = str_replace('&','&amp;',$results_info['epName']);
$output      = str_replace( '{epName}',$ep_name_e, $tpldata );
$output      = str_replace( '{daima}',$daima , $output );
$output      = str_replace( '{workCode}',$results_info['workCode'] , $output );
$output      = str_replace( '{ctaAddrcode}',$results_info['epAddrcode'] , $output );//注册地址邮编
$output      = str_replace( '{auditBasis}',$results_info['auditBasis'] , $output );
$output      = str_replace( '{certScope}',$results_info['certScope'] , $output );


$output      = str_replace( '{isoA0301}',$isoA0301 , $output );
$output      = str_replace( '{isoA0302}',$isoA0302 , $output );

if($result_zs_Info['audit_ver']=='A020102'||$result_zs_Info['audit_ver']=='A010103')
{
	$output = str_replace( '{daoqi}',$results_info['daoqi'] , $output );
}else
{
	$output = str_replace( '{daoqi}',$results_info['daoqi'] , $output );
}


$output = str_replace( '{first_date}',$results_info['first_date'][0].'-'.$results_info['first_date'][1].'-'.$results_info['first_date'][2] , $output );
$output = str_replace( '{sDate}',$results_info['sDate'][0].'-'.$results_info['sDate'][1].'-'.$results_info['sDate'][2] , $output );

$output = str_replace( '{eDate}',$results_info['eDate'][0].'-'.$results_info['eDate'][1].'-'.$results_info['eDate'][2] , $output );

$output = str_replace( '{hzdate}',$huanzhengdate[0].'-'.$huanzhengdate[1].'-'.$huanzhengdate[2] , $output );
$output = str_replace( '{certno}',$results_info['certno'] , $output );
$output = str_replace( '{prod_addr_e}',$results_info['prod_addr_e'] , $output );
$output = str_replace( '{iso}',$results_info['iso'] , $output );
$output = str_replace( '{epAddr}',$results_info['epAddr'] , $output );
$output = str_replace( '{ctaAddr}',$results_info['ctaAddr'] , $output );
$output = str_replace( '{bgAddr}',$results_info['bgAddr'] , $output );
$output = str_replace( '{prodAddr}',$results_info['prodAddr'] , $output );
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
