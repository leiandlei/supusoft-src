<?php
//word控制器文件名与方法名一致 
//业务需求：每个企业的导出模板基本都不一致，但是控制器类似
if( getgp('downsDoc')==1 ){
	include_once ROOT.'/framework/PHPZip.class.php';
	$archive = new PHPZip();
    $archive->ZipAndDownload(getgp('url'),getgp('filename'));
    $archive->deldir(getgp('url'),false);
}else{
	$ctl_doc= DOCTPL_PATH .$a.'.php';
    // echo "<pre />";
    // print_r($ctl_doc);exit;
	if(file_exists($ctl_doc)){     //检查文件是否存在
		require_once $ctl_doc;     //检查文件是否已被包含过

	}else{
		echo '导出word控制器不存在';
		echo $ctl_doc;	
	} 
}