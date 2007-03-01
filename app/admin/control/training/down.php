<?php
$getUrl = getgp('url');
$url = !empty($getUrl)?$getUrl:'';
$downs = getgp('downs');
if( !empty($downs) ){
	$strId = getgp('strId');
	if(empty($strId))exit;
	include_once ROOT.'/framework/PHPZip.class.php';
	include_once ROOT.'/framework/FileUtil.class.php';

	$sql = "select `content`,`name` from sp_docmanage where id in(%s)";
	$sql = sprintf($sql,substr($strId,0,strlen($strId)-1));
	$results = $db->getAll($sql);
	foreach ($results as  $value) {
		FileUtil::copyFile($value['content'],ROOT.'/uploads/file/temp/'.$value['name'].rand(1,999999).date("YmdHis", time()).'.'.pathinfo($value['content'],PATHINFO_EXTENSION) );
	}
	
	$archive = new PHPZip();
    $archive->ZipAndDownload(ROOT.'/uploads/file/temp/','批量下载');
    $archive->deldir(ROOT.'/uploads/file/temp/',false);
}else{
	if( empty($url) )exit('没有文件！');
	$url = iconv("UTF-8","GB2312//IGNORE",$url);
	$file_size=filesize($url); 
	$getName=getgp('name');
	$file_name=!empty($getName)?$getName.substr($url,strrpos($url,'.')):(substr($url,strrpos($url,'/')));
	Header("Content-type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length:".$file_size);
	Header("Content-Disposition: attachment; filename=".$file_name);
	echo file_get_contents($url);
}

?>
