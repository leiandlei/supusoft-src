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
	$filelist = array();
	foreach ($results as  $value) {
		$filelist[] = array(
				 'name' => $value['name']
				,'url'  => $value['content']
			);
	}
	$archive =  new PHPZip();
	$return = $archive -> create_zip($filelist,ROOT.'/uploads/file/temp/'.iconv("UTF-8","GB2312",'批量下载').rand(1,999999).date("YmdHis", time()).'.zip');
    if(!$return)echo '没有该文件';
    $archive->deldir(ROOT.'/uploads/file/temp/',false);
}else{
	if( empty($url) )exit('没有文件！');
	$url = iconv("UTF-8","GB2312",$url);
	if( !file_exists($url) ){
		echo '没有该文件';
		exit;
	}

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
