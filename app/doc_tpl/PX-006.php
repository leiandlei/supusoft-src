<?php
$id = (int)getgp('id');
$sql      = "select *,sti.id from `sp_training_info` sti left join `sp_training_student` sts on sti.s_id=sts.id left join `sp_training_lesson`  stl on sti.l_id=stl.id where 1 and sti.`status`=1 and sts.`status`=1 and stl.`status`=1 and sti.id=".$id;
$results  = $db->getOne($sql);

$filename = '三标一体.doc';
$tpldata  = readover( DOCTPL_PATH . 'doc/PX-006.xml' );
$output   = str_replace( '{s_name}', $results['s_name'], $tpldata);//姓名
$output   = str_replace( '{i_zsnum}', $results['i_zsnum'], $output);//证书编号
$output   = str_replace( '{i_zstime}', substr($results['i_zstime'],0,10), $output);//发证日期

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
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
?>
