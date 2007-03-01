<?php
$id = (int)getgp('id');
$sql      = "select *,sti.id from `sp_training_info` sti left join `sp_training_student` sts on sti.s_id=sts.id left join `sp_training_lesson`  stl on sti.l_id=stl.id where 1 and sti.`status`=1 and sts.`status`=1 and stl.`status`=1 and sti.id=".$id;
$results  = $db->getOne($sql);
$results['l_st_time']  = sprintf(str_replace("-", "%s", $results['l_st_time']),'年','月').'日';
$results['l_en_time']  = sprintf(str_replace("-", "%s", $results['l_en_time']),'年','月').'日';
$filename = 'SC09审查员培训证.doc';
$tpldata  = readover( DOCTPL_PATH . 'doc/PX-010.xml' );
$output   = str_replace( '{s_name}', $results['s_name'], $tpldata);//姓名
$output   = str_replace( '{i_zsnum}', $results['i_zsnum'], $output);//证书编号
$output   = str_replace( '{l_st_time}', $results['l_st_time'], $output);//培训开始时间
$output   = str_replace( '{l_en_time}', $results['l_en_time'], $output);//培训结束时间
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
