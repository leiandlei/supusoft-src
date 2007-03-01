<?php

$zsid = (int)getgp('zsid');
$sql  = "select * from sp_certificate c left join sp_enterprises e on c.eid=e.eid where c.deleted=0 and c.id=".$zsid;

$r_c  = $db->getOne($sql);
// echo "<pre />";
// print_r($r_c );exit;
switch ($r_c['mark']){
			case '01':
				$mark='CNAS';
				break;

			case '99':
				$mark='LLL';
				break;
			default:
				break;
		}
extract( $r_c , EXTR_SKIP );
//删减条款
$a='';
$a=$r_c['eid'];
$sj=$db->getAll("SELECT  exc_clauses  FROM `sp_contract_item` WHERE `eid` = '$a'  AND iso IN ('$iso') AND `deleted` = '0'");
foreach ($sj as $key => $value) {
	$tk=$value['exc_clauses'];
}
//审核标准
switch ($iso) {
	case 'A01':
		$shbz='GB/T 19001-2008/ISO9001:2008';
		break;
	case 'A02':
		$shbz='GB/T 24001-2004/ISO14001:2004';
		break;
	case 'A03':
		$shbz='GB/T 28001-2011';
		break;
	default:
		break;
}

$daima='';
$code=strlen($r_c['work_code']);
if($code='18'){
	$daima='统一社会信用代码';
	$work_code=$r_c['work_code'];
}else{
	$daima='组织机构代码';
	$work_code=substr($work_code,0,8).'-'.substr($work_code,8,1);
}


$filename = $ep_name .'认证证书审批表.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/PB004-1.xml' );


//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);
$output = str_replace( '{ep_name}', $ep_name, $tpldata );

$output = str_replace( '{certno}', $certno, $output );

$output = str_replace( '{daima}', $daima, $output );
$output = str_replace( '{work_code}', $work_code, $output );
$output = str_replace( '{ep_addr}', $ep_addr, $output );
$output = str_replace( '{cta_addrcode}', $cta_addrcode, $output );
$output = str_replace( '{cert_scope}', $cert_scope, $output );
$output = str_replace( '{s_date}', $s_date, $output );
$output = str_replace( '{e_date}', $e_date, $output );
//删减条款
$output = str_replace( '{tk}', $tk, $output );
//审核标准
$output = str_replace( '{shbz}', $shbz, $output );
$output = str_replace( '{mark}', $mark, $output );

if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	$filePath = CONF.'downs';
	//没有目录创建目录
	if(!is_dir($filePath)) {
	    mkdir($filePath, 0777, true);
	}
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