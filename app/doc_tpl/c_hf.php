<?php
require( DATA_DIR . 'cache/iso.cache.php' );
require( DATA_DIR . 'cache/certpasue.cache.php' );

$cgid = (int)getgp( 'cgid' );
$cert = $db->getAll("SELECT cc.*,c.* FROM sp_certificate_change cc LEFT JOIN sp_certificate c ON cc.zsid=c.id WHERE cc.id='$cgid'");

$ep_name   = $cert['0']['cert_name'];
$cgs_date  = $cert['0']['cgs_date'];
$cge_date  = $cert['0']['cge_date'];
$cg_reason = $certpasue_array[$cert['0']['cg_reason']]['name'];
$iso       = $iso_array[$cert['0']['iso']]['name'];
$certno    = $cert['0']['certno'];
$pass_date = substr($cert['0']['pass_date'],0,10);

$filename = '评表-008-2 恢复认证注册资格通知('.$ep_name.').doc';
$tpldata = readover( DOCTPL_PATH . 'doc/PB-008-2.xml' );

$output      = str_replace( '{ep_name}', $ep_name, $tpldata );
$output      = str_replace( '{cgs_date}', $cgs_date, $output );
$output      = str_replace( '{cge_date}', $cge_date, $output );
$output      = str_replace( '{cg_reason}', $cg_reason, $output );
$output      = str_replace( '{iso}', $iso, $output );
$output      = str_replace( '{certno}', $certno, $output );
$output      = str_replace( '{pass_date}', $pass_date , $output );
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
