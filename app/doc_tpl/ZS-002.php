<?php
require('./framework/PHPZip.class.php' );
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

$results_info['epName']     = $result_e_Info['ep_name'];//企业名称
$results_info['workCode']   = $result_e_Info['work_code'];//组织机构代码
$results_info['ctaAddrcode']= $result_e_Info['cta_addrcode'];//邮箱
$results_info['auditBasis'] = $result_auditVers_Info['audit_basis'];//标准
$results_info['certScope']  = $result_zs_Info['cert_scope'];//组织机构代码
$results_info['sDate']      = $result_zs_Info['s_date'];//发证日期
$results_info['eDate']      = $result_zs_Info['e_date'];//结束日期
$results_info['certno']     = $result_zs_Info['certno'];//证书编号
$results_info['isoName']    = $arr_iso_name[$result_zs_Info['iso']].'认证证书';//证书名

$results_info['epAddr']  = empty($result_zs_Info['ep_addr'])?'':'注册地址：'.$result_zs_Info['ep_addr'];//注册地址
$results_info['ctaAddr'] = empty($result_zs_Info['cta_addr'])?'':'通通讯地址：'.$result_zs_Info['cta_addr'];//通讯地址
if( $result_zs_Info['bg_addr']==$result_zs_Info['ep_addr'] ){
	$results_info['epAddr'] = '注册/办公地址：'.$result_zs_Info['bg_addr'];
}else{
	$results_info['bgAddr']  = empty($result_zs_Info['bg_addr'])?'':'办公地址：'.$result_zs_Info['bg_addr'];//办公地址
}
if($result_zs_Info['prod_check']==1){
	$results_info['prodAddr'] = empty($result_zs_Info['prodAddr'])?'':'生产地址：'.$result_zs_Info['prodAddr'];//生产
}

/**需要信息**/

// echo '<pre />';
// print_r($result_mark_Info);exit;
/**下载**/
if ($result_zs_Info['iso']=='A03') {
	switch ($result_mark_Info['mark']) {
		case '99':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002.xml' );
			break;
		case '01':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002-C.xml' );
			break;
		default:
			break;
	}
}else{
	switch ($result_mark_Info['mark']) {
		case '99':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002.xml' );
			break;
		case '01':
			$tpldata  = readover( DOCTPL_PATH . 'doc/ZS-002-IC.xml' );
			break;
		default:
			break;
	}
}
/**下载**/

$filename = '多场所.doc';
$output = str_replace( '{epName}', $results_info['epName'], $tpldata );
$output = str_replace( '{workCode}',$results_info['workCode'] , $output );
$output = str_replace( '{ctaAddrcode}',$results_info['ctaAddrcode'] , $output );
$output = str_replace( '{auditBasis}',$results_info['auditBasis'] , $output );
$output = str_replace( '{certScope}',$results_info['certScope'] , $output );
$output = str_replace( '{sDate}',$results_info['sDate'] , $output );
$output = str_replace( '{eDate}',$results_info['eDate'] , $output );
$output = str_replace( '{certno}',$results_info['certno'] , $output );
$output = str_replace( '{isoName}',$results_info['isoName'] , $output );
$output = str_replace( '{epAddr}',$results_info['epAddr'] , $output );
$output = str_replace( '{ctaAddr}',$results_info['ctaAddr'] , $output );
$output = str_replace( '{bgAddr}',$results_info['bgAddr'] , $output );
$output = str_replace( '{prodAddr}',$results_info['prodAddr'] , $output );
$output = str_replace( '{es_name}',$es_name , $output );
$output = str_replace( '{es_addr}',$es_addr , $output );
$output = str_replace( '{es_scope}',$es_scope, $output );
$output = str_replace( '{base64_content}',$base64_content , $output );
if($result_zs_Info['iso']=='A01')$output = str_replace( '{isoNote}','本证书将与GB/T19001-2008标准同时失效（2018年9月22日）', $output );
// $output = preg_replace("/\{.[^-]+?\}/", "", $output);
//多场所
$sql="select * from sp_enterprises_site where eid=".$result_zs_Info['eid'];
$duochangsuo=$db->getAll($sql);
$i=1;
$count=count($duochangsuo);
foreach ($duochangsuo as $key => $value) 
	{
		$filename  = $value['es_name'].'('.$i.').doc';
		$filenames = iconv( 'UTF-8', 'gbk', $filename );
	    $dates     = date('YmdHis');
		$filePath  = CONF.'downs'.'/'.$dates;
		//没有目录创建目录
		if(!is_dir($filePath)) {
		    mkdir($filePath, 0777, true);
		}
		//如果存在就删除文件
		if( file_exists($filePath.'/'.$filenames) ){
			@unlink ($filePath.'/'.$filenames); 
		}
		
		if ($i%2==0){
			$oldOutput = str_replace( '{es_name_2}',$value['es_name'], $oldOutput );
		    $oldOutput = str_replace( '{es_addr_2}',$value['es_addr'], $oldOutput );
		    $oldOutput = str_replace( '{es_scope_2}',$value['es_scope'], $oldOutput );
		}else{
			$oldOutput = $output;
			$oldOutput = str_replace( '{es_name_1}',$value['es_name'], $oldOutput );
		    $oldOutput = str_replace( '{es_addr_1}',$value['es_addr'], $oldOutput );
		    $oldOutput = str_replace( '{es_scope_1}',$value['es_scope'], $oldOutput );
		}
	    if($i%2==0)
	    {
	    	file_put_contents($filePath.'/'.$filenames,$oldOutput);
	    }elseif($key+1 == $count){
	    	$oldOutput = str_replace( '{es_name_2}','', $oldOutput );
		    $oldOutput = str_replace( '{es_addr_2}','', $oldOutput );
		    $oldOutput = str_replace( '{es_scope_2}','', $oldOutput );
		    file_put_contents($filePath.'/'.$filenames,$oldOutput);
	    }
		$i++;
	}
	$archive = new PHPZip();   //遍历指定文件夹
    $archive->ZipAndDownload(ROOT.'/data/downs/'.$dates,'打包下载');  //压缩并直接下载
    $archive->deldir(ROOT.'/data/downs/'.$dates,false);  //删除目录下所有文件和文件夹}
/**下载**/

// if( getgp('downs')==1 ){
// 	$filename = iconv( 'UTF-8', 'gbk', $filename );
// 	$filePath = CONF.'downs';
// 	//没有目录创建目录
// 	if(!is_dir($filePath)) {
// 	    mkdir($filePath, 0777, true);
// 	}
// 	//如果存在就删除文件
// 	if( file_exists($filePath.'/'.$filename) ){
// 		@unlink ($filePath.'/'.$filename); 
// 	}

// 	file_put_contents($filePath.'/'.$filename,$output);
	
// 	if( file_exists($filePath.'/'.$filename) ){
// 		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );;
// 	}
// }else{
// 	header("Content-type: application/octet-stream");
// 	header("Accept-Ranges: bytes");
// 	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
// 	echo $output;exit;
// }

